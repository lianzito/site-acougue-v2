<?php include 'header.php'; ?>

<div class="text-center mb-4">
    <a href="produtos.php" class="btn btn-outline-danger">Todos</a>
    <a href="produtos.php?categoria=Bovino" class="btn btn-outline-danger">Bovinos</a>
    <a href="produtos.php?categoria=Suíno" class="btn btn-outline-danger">Suínos</a>
    <a href="produtos.php?categoria=Aves" class="btn btn-outline-danger">Aves</a>
</div>

<h2 class="mb-4">Nossos Produtos</h2>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
    <?php
    $categoria_filtro = isset($_GET['categoria']) ? $_GET['categoria'] : '';
    $sql = "SELECT * FROM produtos";
    if (!empty($categoria_filtro)) {
        $sql .= " WHERE categoria = ?";
    }

    $stmt = mysqli_prepare($link, $sql);
    if (!empty($categoria_filtro)) {
        mysqli_stmt_bind_param($stmt, "s", $categoria_filtro);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0){
        while($produto = mysqli_fetch_assoc($result)){
            echo '<div class="col">';
            echo '  <div class="card h-100 produto-card">';
            echo '      <img src="' . htmlspecialchars($produto['imagem']) . '" class="card-img-top" style="height: 200px; object-fit: cover;" alt="' . htmlspecialchars($produto['nome']) . '">';
            echo '      <div class="card-body d-flex flex-column">';
            echo '          <h5 class="card-title">' . htmlspecialchars($produto['nome']) . '</h5>';
            echo '          <p class="card-text text-muted"><small>' . htmlspecialchars($produto['descricao']) . '</small></p>';
            echo '          <p class="card-text fs-4 fw-bold mt-auto">R$ ' . number_format($produto['preco'], 2, ',', '.') . ' /kg</p>';
            echo '          <form action="carrinho_acoes.php" method="post" class="form-add-to-cart">';
            echo '              <input type="hidden" name="acao" value="add">';
            echo '              <input type="hidden" name="id" value="' . $produto['id'] . '">';
            echo '              <div class="input-group">';
            echo '                  <input type="number" name="quantidade" class="form-control" value="0.1" min="0.1" step="0.1">';
            echo '                  <button type="submit" class="btn btn-danger">Adicionar</button>';
            echo '              </div>';
            echo '          </form>';
            echo '      </div>';
            echo '  </div>';
            echo '</div>';
        }
    } else{
        echo "<p class='col-12'>Nenhum produto encontrado nesta categoria.</p>";
    }
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    ?>
</div>

<?php include 'footer.php'; ?>