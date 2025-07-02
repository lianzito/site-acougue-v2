<?php include 'header.php'; ?>

<div class="p-5 mb-4 bg-light rounded-3 text-center">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Bem-vindo ao Açougue Nosso</h1>
        <p class="fs-4">Desde 1985, oferecendo as melhores carnes com a tradição e a qualidade que sua família merece.</p>
        <p>Selecionamos nossos produtos com rigor para garantir o máximo de sabor e frescor no seu churrasco e nas suas refeições do dia a dia. Navegue por nossas categorias e descubra a diferença que a qualidade faz.</p>
        <a href="produtos.php" class="btn btn-danger btn-lg mt-3">Ver Nossos Produtos</a>
    </div>
</div>

<div class="row text-center">
    <div class="col-md-4">
        <h4>Qualidade Garantida</h4>
        <p>Carnes frescas e de procedência controlada.</p>
    </div>
    <div class="col-md-4">
        <h4>Tradição no Corte</h4>
        <p>Cortes especiais feitos por quem entende do assunto.</p>
    </div>
    <div class="col-md-4">
        <h4>Atendimento Especial</h4>
        <p>Estamos prontos para ajudar você a escolher a melhor peça.</p>
    </div>
</div>


<?php 
    mysqli_close($link);
    include 'footer.php'; 
?>