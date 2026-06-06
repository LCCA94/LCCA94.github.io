<?php
require_once __DIR__ . "/config.php";

header("Content-Type: application/json; charset=utf-8");

try {
    $stmt = ew_db()->query(
        "SELECT id, nome, preco, imagem, cor, descricao, estoque
         FROM produtos
         WHERE ativo = 1
         ORDER BY id ASC"
    );

    echo json_encode(array(
        "sucesso" => true,
        "produtos" => $stmt->fetchAll()
    ));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "sucesso" => false,
        "mensagem" => $e->getMessage()
    ));
}
?>
