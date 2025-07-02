<?php
require_once 'config.php';

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (isset($_POST['acao'])) {

    if ($_POST['acao'] == 'add' && isset($_POST['id'])) {
        $id_produto = (int)$_POST['id'];
        $quantidade = isset($_POST['quantidade']) ? (float)$_POST['quantidade'] : 1;

        if ($quantidade > 0) {
            if (isset($_SESSION['carrinho'][$id_produto])) {
                $_SESSION['carrinho'][$id_produto] += $quantidade;
            } else {
                $_SESSION['carrinho'][$id_produto] = $quantidade;
            }
        }
        
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Produto adicionado com sucesso!',
                'cart_count' => isset($_SESSION['carrinho']) ? count($_SESSION['carrinho']) : 0
            ]);
            exit;
        }
    }

    if ($_POST['acao'] == 'up' && isset($_POST['id']) && isset($_POST['quantidade'])) {
        $id_produto = (int)$_POST['id'];
        $quantidade = (float)$_POST['quantidade'];

        if ($quantidade > 0 && isset($_SESSION['carrinho'][$id_produto])) {
           $_SESSION['carrinho'][$id_produto] = $quantidade;
        } else {
           unset($_SESSION['carrinho'][$id_produto]);
        }
    }
}

if (isset($_GET['acao'])) {

    if ($_GET['acao'] == 'del' && isset($_GET['id'])) {
        $id_produto = (int)$_GET['id'];
        if (isset($_SESSION['carrinho'][$id_produto])) {
            unset($_SESSION['carrinho'][$id_produto]);
        }
    }

    if ($_GET['acao'] == 'limpar') {
        unset($_SESSION['carrinho']);
        $_SESSION['flash_message'] = ['type' => 'info', 'text' => 'Seu carrinho foi esvaziado.'];
    }
}

header('location: carrinho.php');
exit;
?>