<?php include 'header.php'; ?>

<div class="container">
    <h2 class="mb-4">Seu Carrinho de Compras</h2>
    <?php
    if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
        $ids_produtos = implode(',', array_keys($_SESSION['carrinho']));
        $sql = "SELECT * FROM produtos WHERE id IN ($ids_produtos)";
        $total_geral = 0;
    ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col">Produto</th>
                    <th scope="col" class="text-center">Preço Unit.</th>
                    <th scope="col" class="text-center">Quantidade (kg)</th>
                    <th scope="col" class="text-center">Subtotal</th>
                    <th scope="col" class="text-center">Remover</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result = mysqli_query($link, $sql)) {
                    while ($produto = mysqli_fetch_assoc($result)) {
                        $id_produto = $produto['id'];
                        $quantidade = $_SESSION['carrinho'][$id_produto];
                        $subtotal = $produto['preco'] * $quantidade;
                        $total_geral += $subtotal;
                ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" class="me-3">
                            <strong><?php echo htmlspecialchars($produto['nome']); ?></strong>
                        </div>
                    </td>
                    <td class="text-center">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                    <td class="text-center">
                        <form action="carrinho_acoes.php" method="post" class="d-inline-flex justify-content-center">
                             <input type="hidden" name="acao" value="up">
                             <input type="hidden" name="id" value="<?php echo $id_produto; ?>">
                             <div class="input-group" style="max-width: 150px;">
                                <input type="number" name="quantidade" class="form-control" value="<?php echo $quantidade; ?>" min="0.1" step="0.1">
                                <button type="submit" class="btn btn-outline-secondary" title="Atualizar quantidade">
                                    <i class="bi bi-arrow-clockwise"></i> </button>
                             </div>
                        </form>
                    </td>
                    <td class="text-center"><strong>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></strong></td>
                    <td class="text-center">
                        <a href="carrinho_acoes.php?acao=del&id=<?php echo $id_produto; ?>" class="btn btn-outline-danger btn-sm btn-remover-item" title="Remover item">
                            <i class="bi bi-x-lg"></i> </a>
                    </td>
                </tr>
                <?php
                    }
                    mysqli_free_result($result);
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap">
        <div>
            <a href="produtos.php" class="btn btn-outline-secondary">Continuar Comprando</a>
            <a href="carrinho_acoes.php?acao=limpar" class="btn btn-outline-danger btn-limpar-carrinho">Limpar Carrinho</a>
        </div>
        <div class="text-end">
            <h4>Total: <span class="text-danger">R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></span></h4>
            <div class="d-grid gap-2 d-md-block">
                <a href="checkout.php" class="btn btn-primary btn-lg">Finalizar Compra</a>
            </div>
        </div>
    </div>

    <?php
    } else {
        echo "<div class='alert alert-info text-center'>Seu carrinho está vazio. <a href='produtos.php'>Ver produtos</a>.</div>";
    }
    mysqli_close($link);
    ?>
</div>

<?php include 'footer.php'; ?>