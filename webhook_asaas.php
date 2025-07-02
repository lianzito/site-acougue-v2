<?php
$log_file = __DIR__ . '/webhook_log.txt';

function log_message($message) {
    global $log_file;
    file_put_contents($log_file, date("Y-m-d H:i:s") . " - " . $message . "\n", FILE_APPEND);
}

log_message("--- Nova Notificação Recebida ---");

require_once 'config.php';

$webhook_token = '';
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $lc_headers = array_change_key_case($headers, CASE_LOWER);
    if (isset($lc_headers['asaas-access-token'])) {
        $webhook_token = trim($lc_headers['asaas-access-token']);
    }
}
if (empty($webhook_token) && isset($_SERVER['HTTP_ASAAS_ACCESS_TOKEN'])) {
    $webhook_token = trim($_SERVER['HTTP_ASAAS_ACCESS_TOKEN']);
}


if (empty($webhook_token) || !hash_equals(ASAAS_WEBHOOK_SECRET, $webhook_token)) {
    log_message("ERRO: Falha na autenticação do webhook. Token recebido: '{$webhook_token}'. Token esperado: '" . ASAAS_WEBHOOK_SECRET . "'");
    http_response_code(401);
    die('Acesso negado.');
}

log_message("INFO: Token de segurança validado com sucesso.");

$json_body = file_get_contents('php://input');
log_message("CORPO DA REQUISIÇÃO: " . $json_body);

$payload = json_decode($json_body, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    log_message("ERRO: Payload JSON inválido.");
    http_response_code(400);
    die('Payload inválido.');
}

if (isset($payload['event']) && isset($payload['payment'])) {
    $pagamento = $payload['payment'];
    
    if (isset($pagamento['externalReference']) && !empty($pagamento['externalReference'])) {
        
        $local_pedido_id = $pagamento['externalReference'];
        $id_pagamento_asaas = $pagamento['id'];
        $novo_status = $pagamento['status'];

        if ($payload['event'] === 'PAYMENT_DELETED') {
            $novo_status = 'DELETED';
        }

        log_message("INFO: Processando evento '{$payload['event']}'. Pedido Local ID: '{$local_pedido_id}'. Novo Status: '{$novo_status}'. Asaas Payment ID: '{$id_pagamento_asaas}'");

        $sql_update = "UPDATE pedidos SET status = ?, id_asaas_pagamento = ? WHERE id = ?";
        
        if ($stmt = mysqli_prepare($link, $sql_update)) {
            mysqli_stmt_bind_param($stmt, "ssi", $novo_status, $id_pagamento_asaas, $local_pedido_id);
            mysqli_stmt_execute($stmt);
            
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                log_message("SUCESSO: Pedido local #{$local_pedido_id} atualizado para '{$novo_status}'.");
            } else {
                log_message("AVISO: Nenhum pedido encontrado no banco de dados com o ID local '{$local_pedido_id}' ou o status já era o mesmo.");
            }
            
            mysqli_stmt_close($stmt);
            http_response_code(200);
            
        } else {
            log_message("ERRO: Falha ao preparar a query de atualização. Erro: " . mysqli_error($link));
            http_response_code(500);
        }
    } else {
        $pagamento_id_log = isset($pagamento['paymentLink']) ? $pagamento['paymentLink'] : (isset($pagamento['id']) ? $pagamento['id'] : 'N/A');
        log_message("AVISO: Notificação recebida para a cobrança Asaas ID '{$pagamento_id_log}', mas não continha 'externalReference'. Ignorando.");
        http_response_code(200);
    }
} else {
    log_message("ERRO: Payload JSON não continha os campos 'event' e 'payment'.");
    http_response_code(400);
}

mysqli_close($link);
exit();
?>