<?php
include 'header.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

function validaCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/is', '', $cpf);
    if (strlen($cpf) != 11) { return false; }
    if (preg_match('/(\d)\1{10}/', $cpf)) { return false; }
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) { $d += $cpf[$c] * (($t + 1) - $c); }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) { return false; }
    }
    return true;
}

if (isset($_POST['atualizar_dados'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $id_usuario = $_SESSION['id'];
    $erros_dados = [];

    $cpf_para_salvar = $_SESSION['cpf'];
    if (empty($_SESSION['cpf']) && !empty($_POST['cpf'])) {
        $cpf_novo = trim($_POST['cpf']);
        if (validaCPF($cpf_novo)) {
            $sql_check_cpf = "SELECT id FROM usuarios WHERE cpf = ? AND id != ?";
            if($stmt_check = mysqli_prepare($link, $sql_check_cpf)){
                mysqli_stmt_bind_param($stmt_check, "si", $cpf_novo, $id_usuario);
                mysqli_stmt_execute($stmt_check);
                mysqli_stmt_store_result($stmt_check);
                if(mysqli_stmt_num_rows($stmt_check) > 0){
                    $erros_dados[] = "Este CPF já está em uso por outra conta.";
                } else {
                    $cpf_para_salvar = $cpf_novo;
                }
                mysqli_stmt_close($stmt_check);
            }
        } else {
            $erros_dados[] = "O CPF informado é inválido.";
        }
    }
    
    if (empty($nome)) $erros_dados[] = "O nome não pode ficar em branco.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros_dados[] = "Por favor, insira um email válido.";

    $sql_check_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    if($stmt_check = mysqli_prepare($link, $sql_check_email)){
        mysqli_stmt_bind_param($stmt_check, "si", $email, $id_usuario);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        if(mysqli_stmt_num_rows($stmt_check) > 0){
            $erros_dados[] = "Este email já está em uso por outra conta.";
        }
        mysqli_stmt_close($stmt_check);
    }
    
    if (empty($erros_dados)) {
        $sql = "UPDATE usuarios SET nome = ?, email = ?, cpf = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssi", $nome, $email, $cpf_para_salvar, $id_usuario);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['nome'] = $nome;
                $_SESSION['email'] = $email;
                if (!empty($cpf_para_salvar)) $_SESSION['cpf'] = $cpf_para_salvar;
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Seus dados foram atualizados com sucesso!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Ocorreu um erro ao atualizar seus dados.'];
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'text' => implode("<br>", $erros_dados)];
    }
    header("Location: minha_conta.php");
    exit();
}

if (isset($_POST['atualizar_senha'])) {
    $senha_atual = trim($_POST['senha_atual']);
    $nova_senha = trim($_POST['nova_senha']);
    $confirma_nova_senha = trim($_POST['confirma_nova_senha']);
    $id_usuario = $_SESSION['id'];
    $erros_senha = [];

    if (empty($senha_atual) || empty($nova_senha) || empty($confirma_nova_senha)) {
        $erros_senha[] = "Todos os campos de senha são obrigatórios.";
    }
    if ($nova_senha !== $confirma_nova_senha) {
        $erros_senha[] = "A nova senha e a confirmação não coincidem.";
    }
    if (strlen($nova_senha) < 6) {
        $erros_senha[] = "A nova senha deve ter no mínimo 6 caracteres.";
    }

    if(empty($erros_senha)) {
        $sql_get_pass = "SELECT senha FROM usuarios WHERE id = ?";
        if ($stmt_get_pass = mysqli_prepare($link, $sql_get_pass)) {
            mysqli_stmt_bind_param($stmt_get_pass, "i", $id_usuario);
            mysqli_stmt_execute($stmt_get_pass);
            
            mysqli_stmt_store_result($stmt_get_pass);
            
            mysqli_stmt_bind_result($stmt_get_pass, $hashed_senha_db);
            
            if (mysqli_stmt_fetch($stmt_get_pass)) {
                if (password_verify($senha_atual, $hashed_senha_db)) {
                    $sql_update_pass = "UPDATE usuarios SET senha = ? WHERE id = ?";
                    if ($stmt_update_pass = mysqli_prepare($link, $sql_update_pass)) {
                        $param_nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                        mysqli_stmt_bind_param($stmt_update_pass, "si", $param_nova_senha_hash, $id_usuario);
                        
                        if (mysqli_stmt_execute($stmt_update_pass)) {
                             $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Senha alterada com sucesso!'];
                        } else {
                             $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Ocorreu um erro ao atualizar sua senha.'];
                        }
                        mysqli_stmt_close($stmt_update_pass);
                    }
                } else {
                     $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'A senha atual informada está incorreta.'];
                }
            }
        } else {
             $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Ocorreu um erro no servidor.'];
        }
         if(isset($stmt_get_pass)) mysqli_stmt_close($stmt_get_pass);

    } else {
         $_SESSION['flash_message'] = ['type' => 'danger', 'text' => implode("<br>", $erros_senha)];
    }
    
    header("Location: minha_conta.php");
    exit();
}
?>

<h2>Minha Conta</h2>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Alterar meus dados</div>
            <div class="card-body">
                <form action="minha_conta.php" method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($_SESSION['nome']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="cpf" class="form-label">CPF</label>
                        <?php if (empty($_SESSION['cpf'])): ?>
                            <input type="text" class="form-control" id="cpf" name="cpf" placeholder="Digite seu CPF (apenas números)" required>
                            <div class="form-text">Este dado é necessário para finalizar compras e não poderá ser alterado depois.</div>
                        <?php else: ?>
                            <input type="text" class="form-control" id="cpf" name="cpf" value="<?php echo htmlspecialchars($_SESSION['cpf']); ?>" disabled readonly>
                            <div class="form-text">O CPF não pode ser alterado.</div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="atualizar_dados" class="btn btn-primary">Salvar Alterações</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Alterar Senha</div>
            <div class="card-body">
                <form action="minha_conta.php" method="POST">
                    <div class="mb-3">
                        <label for="senha_atual" class="form-label">Senha Atual</label>
                        <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                    </div>
                    <div class="mb-3">
                        <label for="nova_senha" class="form-label">Nova Senha</label>
                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                    </div>
                     <div class="mb-3">
                        <label for="confirma_nova_senha" class="form-label">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="confirma_nova_senha" name="confirma_nova_senha" required>
                    </div>
                    <button type="submit" name="atualizar_senha" class="btn btn-primary">Alterar Senha</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
include 'footer.php'; 
?>