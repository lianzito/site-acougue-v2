<?php
require_once 'config.php';

function validaCPF($cpf) {
    $cpf = preg_replace( '/[^0-9]/is', '', $cpf );
    if (strlen($cpf) != 11) { return false; }
    if (preg_match('/(\d)\1{10}/', $cpf)) { return false; }
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) { $d += $cpf[$c] * (($t + 1) - $c); }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) { return false; }
    }
    return true;
}


if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

$erro_cadastro = $erro_login = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);
    $cpf = preg_replace('/[^0-9]/', '', trim($_POST["cpf"]));

    if (empty($nome)) {
        $erro_cadastro = "Por favor, insira seu nome.";
    } elseif (empty($email)) {
        $erro_cadastro = "Por favor, insira um email.";
    } elseif (!validaCPF($cpf)) {
        $erro_cadastro = "O CPF informado não é válido.";
    } elseif (empty($senha)) {
        $erro_cadastro = "Por favor, insira uma senha.";
    } elseif (strlen($senha) < 6) {
        $erro_cadastro = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $sql = "SELECT id FROM usuarios WHERE email = ? OR cpf = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $param_email, $param_cpf);
            $param_email = $email;
            $param_cpf = $cpf;
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $erro_cadastro = "Este email ou CPF já está em uso.";
                }
            } else {
                echo "Ops! Algo deu errado. Tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    if (empty($erro_cadastro)) {
        $sql = "INSERT INTO usuarios (nome, email, cpf, senha) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $param_nome, $param_email, $param_cpf, $param_senha);
            
            $param_nome = $nome;
            $param_email = $email;
            $param_cpf = $cpf;
            $param_senha = password_hash($senha, PASSWORD_DEFAULT); 
            
            if (mysqli_stmt_execute($stmt)) {
                header("location: login.php?cadastro=sucesso");
            } else {
                echo "Ops! Algo deu errado. Tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);

    if (empty($email) || empty($senha)) {
        $erro_login = "Por favor, preencha todos os campos.";
    } else {
        $sql = "SELECT id, nome, email, cpf, senha FROM usuarios WHERE email = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $nome, $email_db, $cpf_db, $hashed_senha);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($senha, $hashed_senha)) {
                            session_regenerate_id();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["nome"] = $nome;
                            $_SESSION["email"] = $email_db;
                            $_SESSION["cpf"] = $cpf_db;
                            
                            header("location: index.php");
                        } else {
                            $erro_login = "A senha que você inseriu não é válida.";
                        }
                    }
                } else {
                    $erro_login = "Nenhuma conta encontrada com esse email.";
                }
            } else {
                echo "Ops! Algo deu errado. Tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($link);
?>

<?php include 'header.php'; ?>

<div class="container">
    <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px;">
        <div class="form-container" style="flex: 1; min-width: 300px;">
            <h2>Login</h2>
            <p>Acesse sua conta para continuar.</p>
            <?php if (!empty($erro_login)) echo '<div class="alert alert-danger">' . $erro_login . '</div>'; ?>
            
            <form action="login.php" method="post">
                <input type="email" name="email" class="form-control mb-2" placeholder="Seu Email" required>
                <input type="password" name="senha" class="form-control mb-3" placeholder="Sua Senha" required>
                <button type="submit" name="login" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
        <div class="form-container" style="flex: 1; min-width: 300px;">
            <h2>Ainda não tem conta? Cadastre-se</h2>
            <p>É rápido e fácil.</p>
            <?php 
                if (!empty($erro_cadastro)) echo '<div class="alert alert-danger">' . $erro_cadastro . '</div>'; 
                if (isset($_GET['cadastro']) && $_GET['cadastro'] == 'sucesso') echo '<div class="alert alert-success">Cadastro realizado com sucesso! Faça seu login.</div>';
            ?>
            <form action="login.php" method="post">
                <input type="text" name="nome" class="form-control mb-2" placeholder="Nome Completo" required>
                <input type="email" name="email" class="form-control mb-2" placeholder="Seu Email" required>
                <input type="text" name="cpf" class="form-control mb-2" placeholder="CPF (apenas números)" required>
                <input type="password" name="senha" class="form-control mb-3" placeholder="Crie uma Senha (mín. 6 caracteres)" required>
                <button type="submit" name="cadastrar" class="btn btn-success w-100">Cadastrar</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>