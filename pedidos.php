<?php
include 'header.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

function callAsaasAPI(string $method, string $url, array|string|null $data = null) {
    $curl = curl_init();
    $headers = ['Content-Type: application/json', 'access_token: ' . ASAAS_API_KEY, 'User-Agent: Acougue Nosso'];
    if ($method == "POST") {
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    } else {
        if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $result = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return ['body' => json_decode($result, true), 'http_code' => $http_code];
}

// Função para traduzir e colorir o status
function formatarStatus($status) {
    $traducao = [
        'PENDING' => 'Pendente',
        'RECEIVED' => 'Pago',
        'CONFIRMED' => 'Pago',
        'OVERDUE' => 'Vencido',
        'DELETED' => 'Cancelado',
        'REFUNDED' => 'Devolvido'
    ];
    $cor = 'bg-secondary';
    if ($status === 'RECEIVED' || $status === 'CONFIRMED') $cor = 'bg-success';
    if ($status === 'OVERDUE' || $status === 'DELETED') $cor = 'bg-danger';

    $status_traduzido = isset($traducao[$status]) ? $traducao[$status] : $status;

    return '<span class="badge ' . $cor . '">' . htmlspecialchars($status_traduzido) . '</span>';
}

$id_usuario = $_SESSION['id'];
$sql_pedidos = "SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY data_criacao DESC";
?>

<div class="container">
    <h2 class="mb-4">Meus Pedidos</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Pedido</th>
                    <th>Data</th>
                    <th>Itens</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($stmt = mysqli_prepare($link, $sql_pedidos)) {
                    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    if (mysqli_num_rows($result) > 0) {
                        while ($pedido = mysqli_fetch_assoc($result)) {
                            $status_local = $pedido['status'];
                            $id_cobranca_asaas = $pedido['id_asaas_cobranca'];
                            
                            // Para pedidos que não estão em um estado final, verifica o status atual na Asaas
                            if (!in_array($status_local, ['RECEIVED', 'CONFIRMED', 'DELETED', 'REFUNDED'])) {
                                $response_api = callAsaasAPI('GET', ASAAS_API_URL . '/paymentLinks/' . $id_cobranca_asaas);
                                if($response_api['http_code'] == 200 && isset($response_api['body']['payment']['status'])) {
                                    $status_real = $response_api['body']['payment']['status'];
                                    
                                    if ($response_api['body']['deleted'] === true) {
                                        $status_real = 'DELETED';
                                    }

                                    if($status_real !== $status_local) {
                                        // Atualiza o status no banco de dados local se mudou
                                        $status_local = $status_real;
                                        $id_pagamento_real = $response_api['body']['payment']['id'];
                                        $sql_update = "UPDATE pedidos SET status = ?, id_asaas_pagamento = ? WHERE id = ?";
                                        if($stmt_update = mysqli_prepare($link, $sql_update)){
                                            mysqli_stmt_bind_param($stmt_update, "ssi", $status_local, $id_pagamento_real, $pedido['id']);
                                            mysqli_stmt_execute($stmt_update);
                                            mysqli_stmt_close($stmt_update);
                                        }
                                    }
                                }
                            }
                            
                            echo '<tr>';
                            echo '<td>#' . htmlspecialchars($pedido['id']) . '</td>';
                            echo '<td>' . date('d/m/Y', strtotime($pedido['data_criacao'])) . '</td>';
                            
                            $itens_html = '<ul>';
                            $itens = json_decode($pedido['itens_pedido'], true);
                            if (is_array($itens)) {
                                foreach($itens as $item){
                                    $itens_html .= '<li>' . htmlspecialchars($item['nome']) . ' (' . $item['quantidade'] . 'kg)</li>';
                                }
                            }
                            $itens_html .= '</ul>';
                            echo '<td>' . $itens_html . '</td>';
                            
                            echo '<td>R$ ' . number_format($pedido['valor_total'], 2, ',', '.') . '</td>';
                            echo '<td>' . formatarStatus($status_local) . '</td>';
                            
                            if($status_local === 'PENDING' || $status_local === 'OVERDUE'){
                                echo '<td><a href="' . htmlspecialchars($pedido['link_pagamento']) . '" target="_blank" class="btn btn-success btn-sm">Pagar</a></td>';
                            } else {
                                echo '<td>-</td>';
                            }
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6" class="text-center">Você ainda não fez nenhum pedido.</td></tr>';
                    }
                    mysqli_stmt_close($stmt);
                }
                mysqli_close($link);
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>