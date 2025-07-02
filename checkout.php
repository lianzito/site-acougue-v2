<?php
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['flash_message'] = ['type' => 'warning', 'text' => 'Você precisa fazer login para finalizar a compra!'];
    header('location: login.php');
    exit;
}
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    header('location: carrinho.php');
    exit;
}
if (empty($_SESSION['cpf'])) {
    $_SESSION['flash_message'] = ['type' => 'warning', 'text' => 'É necessário ter um CPF cadastrado para finalizar a compra. Por favor, atualize seus dados em Minha Conta.'];
    header('location: minha_conta.php');
    exit;
}

function callAsaasAPI(string $method, string $url, array|string|null $data = null) {
    $curl = curl_init();
    $headers = [
        'Content-Type: application/json',
        'access_token: ' . ASAAS_API_KEY,
        'User-Agent: Acougue Nosso'
    ];
    curl_setopt($curl, CURLOPT_URL, ASAAS_API_URL . $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if ($method == "POST") {
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    $result = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);
    return ['body' => json_decode($result, true), 'raw_body' => $result, 'http_code' => $http_code, 'curl_error' => $curl_error];
}

$total_geral = 0;
$itens_pedido_array = [];
$ids_produtos = implode(',', array_map('intval', array_keys($_SESSION['carrinho'])));
$sql_produtos = "SELECT id, nome, preco FROM produtos WHERE id IN ($ids_produtos)";
$result_produtos = mysqli_query($link, $sql_produtos);

if ($result_produtos) {
    while ($produto = mysqli_fetch_assoc($result_produtos)) {
        $quantidade = $_SESSION['carrinho'][$produto['id']];
        $total_geral += $produto['preco'] * $quantidade;
        $itens_pedido_array[] = ['nome' => $produto['nome'], 'quantidade' => $quantidade, 'preco_unitario' => $produto['preco']];
    }
}
$itens_pedido_json = json_encode($itens_pedido_array);

try {
    $asaas_customer_id = null;
    $api_response_get = callAsaasAPI('GET', '/customers?cpfCnpj=' . $_SESSION['cpf']);

    if ($api_response_get['http_code'] >= 300) throw new Exception("Falha ao buscar cliente na Asaas.");

    if (!empty($api_response_get['body']['data'])) {
        $asaas_customer_id = $api_response_get['body']['data'][0]['id'];
    } else {
        $customer_data = json_encode(["name" => $_SESSION['nome'], "email" => $_SESSION['email'], "cpfCnpj" => $_SESSION['cpf']]);
        $api_response_create = callAsaasAPI('POST', '/customers', $customer_data);
        if (isset($api_response_create['body']['id'])) {
            $asaas_customer_id = $api_response_create['body']['id'];
        } else {
            throw new Exception("Não foi possível criar seu cadastro na plataforma de pagamentos.");
        }
    }

    if ($asaas_customer_id) {
        $sql_insert_pedido = "INSERT INTO pedidos (id_usuario, valor_total, status, itens_pedido, data_criacao) VALUES (?, ?, 'PENDING', ?, NOW())";
        if ($stmt = mysqli_prepare($link, $sql_insert_pedido)) {
            mysqli_stmt_bind_param($stmt, "ids", $_SESSION['id'], $total_geral, $itens_pedido_json);
            mysqli_stmt_execute($stmt);
            $pedido_id_local = mysqli_insert_id($link);
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception("Não foi possível registrar o pedido no banco de dados.");
        }

        if (!$pedido_id_local) throw new Exception("Falha ao obter ID do novo pedido.");

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $baseUrl = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
        if (substr($baseUrl, -1) !== '/') $baseUrl .= '/';

        $payment_link_data = json_encode([
            'name' => 'Pedido #' . $pedido_id_local . ' para ' . $_SESSION['nome'],
            'customer' => $asaas_customer_id,
            'billingType' => 'UNDEFINED',
            'chargeType' => 'DETACHED',
            'dueDateLimitDays' => 2,
            'value' => $total_geral,
            'description' => 'Pedido #' . $pedido_id_local . ' - Açougue Nosso',
            'externalReference' => (string)$pedido_id_local,
            // 'redirectUrl' => $baseUrl . 'index.php', // servidor local 
            'redirectUrl' => 'https://54f6-191-13-80-193.ngrok-free.app/acougue-Asaas/index.php', // servidor gnork
        ]);

        $api_response_link = callAsaasAPI('POST', '/paymentLinks', $payment_link_data);
        
        if (!isset($api_response_link['body']['id'])) {
            mysqli_query($link, "DELETE FROM pedidos WHERE id = $pedido_id_local");
            throw new Exception("Falha ao gerar o link de pagamento. Resposta: " . htmlspecialchars($api_response_link['raw_body']));
        }

        $response_link = $api_response_link['body'];
        
        $sql_update_pedido = "UPDATE pedidos SET id_asaas_cobranca = ?, link_pagamento = ? WHERE id = ?";
        if ($stmt_update = mysqli_prepare($link, $sql_update_pedido)) {
             mysqli_stmt_bind_param($stmt_update, "ssi", $response_link['id'], $response_link['url'], $pedido_id_local);
             mysqli_stmt_execute($stmt_update);
             mysqli_stmt_close($stmt_update);
        }

        unset($_SESSION['carrinho']);
        header("Location: " . $response_link['url']);
        exit();
    }

} catch (Exception $e) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'ERRO: ' . $e->getMessage()];
    header('location: carrinho.php');
    exit;
}

mysqli_close($link);
?>