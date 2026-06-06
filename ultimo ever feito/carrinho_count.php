<?php
require_once __DIR__ . "/config.php";

header("Content-Type: application/json; charset=utf-8");

try {
    echo json_encode(array(
        "sucesso" => true,
        "quantidade" => ew_cart_count()
    ));
} catch (Exception $e) {
    echo json_encode(array(
        "sucesso" => false,
        "quantidade" => 0
    ));
}
?>
