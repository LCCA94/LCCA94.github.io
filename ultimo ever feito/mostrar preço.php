<?php
$preco = 99.90;
$desconto = 0; // define padrão

if(isset($_POST["desconto"])){
  $desconto = $_POST["desconto"];
}

if($desconto > 0){
  $precoFinal = $preco - ($preco * $desconto);
} else {
  $precoFinal = $preco;
}

echo "Total: R$ " . number_format($precoFinal, 2, ',', '.');
?>