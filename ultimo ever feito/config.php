<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set("America/Sao_Paulo");

define("DB_HOST", "localhost");
define("DB_NAME", "everwear");
define("DB_USER", "root");
define("DB_PASS", "");

function ew_db() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ));
    }

    return $pdo;
}

function ew_h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

function ew_column_exists($tabela, $coluna) {
    $stmt = ew_db()->prepare(
        "SELECT COUNT(*) AS total
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?"
    );
    $stmt->execute(array($tabela, $coluna));
    $row = $stmt->fetch();

    return !empty($row) && (int)$row["total"] > 0;
}

function ew_ensure_admin_schema() {
    static $verificado = false;

    if ($verificado) {
        return;
    }

    if (!ew_column_exists("usuarios", "admin")) {
        ew_db()->exec("ALTER TABLE usuarios ADD COLUMN admin TINYINT(1) NOT NULL DEFAULT 0 AFTER senha_hash");
    }

    $verificado = true;
}

function ew_ensure_admin_account() {
    ew_ensure_admin_schema();

    $stmt = ew_db()->prepare(
        "INSERT INTO usuarios (nome, email, senha_hash, admin)
         VALUES (?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE
           nome = VALUES(nome),
           senha_hash = VALUES(senha_hash),
           admin = 1"
    );
    $stmt->execute(array(
        "Administrador",
        "admin@everwear.com",
        password_hash("admin123", PASSWORD_DEFAULT)
    ));
}

function ew_current_user() {
    if (empty($_SESSION["usuario_id"])) {
        return null;
    }

    ew_ensure_admin_schema();

    $stmt = ew_db()->prepare("SELECT id, nome, email, admin FROM usuarios WHERE id = ?");
    $stmt->execute(array($_SESSION["usuario_id"]));
    $usuario = $stmt->fetch();

    return $usuario ?: null;
}

function ew_login_user($usuario_id) {
    $_SESSION["usuario_id"] = (int)$usuario_id;
    ew_merge_guest_cart((int)$usuario_id);
}

function ew_redirect($url) {
    header("Location: " . $url);
    exit;
}

function ew_is_admin() {
    $usuario = ew_current_user();

    return $usuario && !empty($usuario["admin"]);
}

function ew_require_admin() {
    if (empty($_SESSION["usuario_id"])) {
        ew_redirect("login.php?redirect=admin.php");
    }

    if (!ew_is_admin()) {
        http_response_code(403);
        echo "Acesso restrito ao administrador.";
        exit;
    }
}

function ew_cart_owner_where(&$params) {
    if (!empty($_SESSION["usuario_id"])) {
        $params[":usuario_id"] = (int)$_SESSION["usuario_id"];
        return "ci.usuario_id = :usuario_id";
    }

    $params[":sessao_id"] = session_id();
    return "ci.sessao_id = :sessao_id";
}

function ew_product_by_name($nome) {
    $stmt = ew_db()->prepare("SELECT * FROM produtos WHERE nome = ? AND ativo = 1 LIMIT 1");
    $stmt->execute(array($nome));
    return $stmt->fetch();
}

function ew_product_by_id($id) {
    $stmt = ew_db()->prepare("SELECT * FROM produtos WHERE id = ? AND ativo = 1 LIMIT 1");
    $stmt->execute(array((int)$id));
    return $stmt->fetch();
}

function ew_add_to_cart($produto_id, $quantidade = 1, $tamanho = "") {
    $produto = ew_product_by_id($produto_id);

    if (!$produto) {
        throw new Exception("Produto não encontrado.");
    }

    $quantidade = max(1, (int)$quantidade);
    $tamanho = trim((string)$tamanho);

    $params = array(":produto_id" => (int)$produto_id, ":tamanho" => $tamanho);
    $ownerWhere = ew_cart_owner_where($params);

    $stmt = ew_db()->prepare("SELECT id, quantidade FROM carrinho_itens ci WHERE $ownerWhere AND produto_id = :produto_id AND tamanho = :tamanho LIMIT 1");
    $stmt->execute($params);
    $item = $stmt->fetch();

    $quantidadeAtual = $item ? (int)$item["quantidade"] : 0;
    $novaQuantidade = $quantidadeAtual + $quantidade;

    if ($novaQuantidade > (int)$produto["estoque"]) {
        throw new Exception("Estoque insuficiente para " . $produto["nome"] . ".");
    }

    if ($item) {
        $stmt = ew_db()->prepare("UPDATE carrinho_itens SET quantidade = ?, atualizado_em = NOW() WHERE id = ?");
        $stmt->execute(array($novaQuantidade, (int)$item["id"]));
    } else {
        $stmt = ew_db()->prepare("INSERT INTO carrinho_itens (usuario_id, sessao_id, produto_id, tamanho, quantidade) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(array(
            !empty($_SESSION["usuario_id"]) ? (int)$_SESSION["usuario_id"] : null,
            empty($_SESSION["usuario_id"]) ? session_id() : null,
            (int)$produto_id,
            $tamanho,
            $quantidade
        ));
    }

    $_SESSION["produto"] = $produto["nome"];
    $_SESSION["preco"] = $produto["preco"];
    $_SESSION["tamanho"] = $tamanho;

    return $produto;
}

function ew_cart_items() {
    $params = array();
    $ownerWhere = ew_cart_owner_where($params);

    $stmt = ew_db()->prepare(
        "SELECT ci.id AS item_id, ci.tamanho, ci.quantidade, p.*
         FROM carrinho_itens ci
         INNER JOIN produtos p ON p.id = ci.produto_id
         WHERE $ownerWhere
         ORDER BY ci.criado_em DESC"
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function ew_cart_count() {
    $params = array();
    $ownerWhere = ew_cart_owner_where($params);

    $stmt = ew_db()->prepare("SELECT COALESCE(SUM(quantidade), 0) AS total FROM carrinho_itens ci WHERE $ownerWhere");
    $stmt->execute($params);
    $row = $stmt->fetch();

    return (int)$row["total"];
}

function ew_cart_total($itens = null) {
    if ($itens === null) {
        $itens = ew_cart_items();
    }

    $total = 0;

    foreach ($itens as $item) {
        $total += ((float)$item["preco"]) * ((int)$item["quantidade"]);
    }

    return $total;
}

function ew_clear_cart() {
    if (!empty($_SESSION["usuario_id"])) {
        $stmt = ew_db()->prepare("DELETE FROM carrinho_itens WHERE usuario_id = ?");
        $stmt->execute(array((int)$_SESSION["usuario_id"]));
        return;
    }

    $stmt = ew_db()->prepare("DELETE FROM carrinho_itens WHERE sessao_id = ?");
    $stmt->execute(array(session_id()));
}

function ew_remove_cart_item($item_id) {
    $params = array(":item_id" => (int)$item_id);
    $ownerWhere = ew_cart_owner_where($params);

    $stmt = ew_db()->prepare("DELETE ci FROM carrinho_itens ci WHERE ci.id = :item_id AND $ownerWhere");
    $stmt->execute($params);
}

function ew_merge_guest_cart($usuario_id) {
    $sessao_id = session_id();

    if ($sessao_id === "") {
        return;
    }

    $stmt = ew_db()->prepare("SELECT produto_id, tamanho, quantidade FROM carrinho_itens WHERE sessao_id = ? AND usuario_id IS NULL");
    $stmt->execute(array($sessao_id));
    $itens = $stmt->fetchAll();

    foreach ($itens as $item) {
        $stmt = ew_db()->prepare("SELECT id, quantidade FROM carrinho_itens WHERE usuario_id = ? AND produto_id = ? AND tamanho = ? LIMIT 1");
        $stmt->execute(array($usuario_id, (int)$item["produto_id"], $item["tamanho"]));
        $existente = $stmt->fetch();

        if ($existente) {
            $stmt = ew_db()->prepare("UPDATE carrinho_itens SET quantidade = quantidade + ?, atualizado_em = NOW() WHERE id = ?");
            $stmt->execute(array((int)$item["quantidade"], (int)$existente["id"]));
        } else {
            $stmt = ew_db()->prepare("UPDATE carrinho_itens SET usuario_id = ?, sessao_id = NULL, atualizado_em = NOW() WHERE sessao_id = ? AND usuario_id IS NULL AND produto_id = ? AND tamanho = ?");
            $stmt->execute(array($usuario_id, $sessao_id, (int)$item["produto_id"], $item["tamanho"]));
        }
    }

    $stmt = ew_db()->prepare("DELETE FROM carrinho_itens WHERE sessao_id = ? AND usuario_id IS NULL");
    $stmt->execute(array($sessao_id));
}

function ew_create_order($dados) {
    $usuario = ew_current_user();

    if (!$usuario) {
        throw new Exception("Faça login para finalizar a compra.");
    }

    $itens = ew_cart_items();

    if (count($itens) === 0) {
        throw new Exception("Seu carrinho está vazio.");
    }

    $pdo = ew_db();
    $pdo->beginTransaction();

    try {
        $total = ew_cart_total($itens);

        foreach ($itens as $item) {
            $stmt = $pdo->prepare("SELECT estoque FROM produtos WHERE id = ? FOR UPDATE");
            $stmt->execute(array((int)$item["id"]));
            $produtoAtual = $stmt->fetch();

            if (!$produtoAtual || (int)$produtoAtual["estoque"] < (int)$item["quantidade"]) {
                throw new Exception("Estoque insuficiente para " . $item["nome"] . ".");
            }
        }

        $stmt = $pdo->prepare(
            "INSERT INTO pedidos
            (usuario_id, email, cep, endereco, numero, complemento, bairro, cidade, estado, pagamento, total, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmado')"
        );
        $stmt->execute(array(
            (int)$usuario["id"],
            $dados["email"],
            $dados["cep"],
            $dados["endereco"],
            $dados["numero"],
            $dados["complemento"],
            $dados["bairro"],
            $dados["cidade"],
            $dados["estado"],
            $dados["pagamento"],
            $total
        ));

        $pedido_id = (int)$pdo->lastInsertId();

        foreach ($itens as $item) {
            $subtotal = ((float)$item["preco"]) * ((int)$item["quantidade"]);

            $stmt = $pdo->prepare(
                "INSERT INTO pedido_itens
                (pedido_id, produto_id, nome_produto, preco_unitario, quantidade, tamanho, cor, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute(array(
                $pedido_id,
                (int)$item["id"],
                $item["nome"],
                (float)$item["preco"],
                (int)$item["quantidade"],
                $item["tamanho"],
                $item["cor"],
                $subtotal
            ));

            $stmt = $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ?");
            $stmt->execute(array((int)$item["quantidade"], (int)$item["id"]));
        }

        ew_clear_cart();
        $pdo->commit();

        return array("id" => $pedido_id, "itens" => $itens, "total" => $total);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>
