<?php
require_once __DIR__ . "/config.php";

$isJson = isset($_SERVER["HTTP_ACCEPT"]) && strpos($_SERVER["HTTP_ACCEPT"], "application/json") !== false;

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Requisição inválida.");
    }

    $produto_id = isset($_POST["produto_id"]) ? (int)$_POST["produto_id"] : 0;

    if ($produto_id <= 0 && !empty($_POST["produto"])) {
        $produto = ew_product_by_name($_POST["produto"]);
        $produto_id = $produto ? (int)$produto["id"] : 0;
    }

    $quantidade = isset($_POST["quantidade"]) ? (int)$_POST["quantidade"] : 1;
    $tamanho = isset($_POST["tamanho"]) ? $_POST["tamanho"] : "";

    ew_add_to_cart($produto_id, $quantidade, $tamanho);

    if ($isJson) {
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(array(
            "sucesso" => true,
            "mensagem" => "Produto adicionado ao carrinho.",
            "quantidade" => ew_cart_count()
        ));
        exit;
    }

    ew_redirect("carrinho.php");
} catch (Exception $e) {
    if ($isJson) {
        http_response_code(400);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(array(
            "sucesso" => false,
            "mensagem" => $e->getMessage()
        ));
        exit;
    }

    $_SESSION["erro_carrinho"] = $e->getMessage();
    ew_redirect("carrinho.php");
}
?>
