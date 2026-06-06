<?php
session_start();
?>

<?php ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Produtos</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="darkmode.css?v=2">
</head>

<body>

<div class="container">
  <h1 class="titulo">Produtos</h1>

  <div class="grid-produtos">

    <div class="card">
      <h3>Camiseta EcoWear</h3>
      <p>Preço: R$99,90</p>
      <form method="post" action="add_to_cart.php">
        <input type="hidden" name="produto_id" value="1">
        <input type="hidden" name="produto" value="Camiseta EcoWear">
        <input type="hidden" name="preco" value="99.90">
        <input type="hidden" name="quantidade" value="1">
        <button class="btn">Adicionar ao Carrinho</button>
      </form>
    </div>

    <div class="card">
      <h3>Camisa DryFit</h3>
      <p>Preço: R$119,90</p>
      <form method="post" action="add_to_cart.php">
        <input type="hidden" name="produto_id" value="2">
        <input type="hidden" name="produto" value="Camisa DryFit">
        <input type="hidden" name="preco" value="119.90">
        <input type="hidden" name="quantidade" value="1">
        <button class="btn">Adicionar ao Carrinho</button>
      </form>
    </div>

    <div class="card">
      <h3>Camisa Bege Premium</h3>
      <p>Preço: R$129,90</p>
      <form method="post" action="add_to_cart.php">
        <input type="hidden" name="produto_id" value="3">
        <input type="hidden" name="produto" value="Camisa Bege Premium">
        <input type="hidden" name="preco" value="129.90">
        <input type="hidden" name="quantidade" value="1">
        <button class="btn">Adicionar ao Carrinho</button>
      </form>
    </div>

  </div>
</div>

<script src="darkmode.js?v=2"></script>
</body>
</html>
