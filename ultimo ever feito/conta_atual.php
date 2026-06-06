<?php
require_once __DIR__ . "/config.php";

header("Content-Type: application/json; charset=utf-8");

try {
    $usuario = ew_current_user();

    echo json_encode(array(
        "sucesso" => true,
        "logado" => $usuario !== null,
        "usuario" => $usuario ? array(
            "id" => (int)$usuario["id"],
            "nome" => $usuario["nome"],
            "email" => $usuario["email"],
            "admin" => !empty($usuario["admin"])
        ) : null
    ));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "sucesso" => false,
        "mensagem" => $e->getMessage()
    ));
}
?>
