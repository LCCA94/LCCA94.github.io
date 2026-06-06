<?php
require_once __DIR__ . "/config.php";

ew_ensure_admin_account();
ew_require_admin();

$erro = "";
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = isset($_POST["acao"]) ? $_POST["acao"] : "";
    $produto_id = isset($_POST["produto_id"]) ? (int)$_POST["produto_id"] : 0;

    try {
        if ($acao === "remover_produto") {
            $stmt = ew_db()->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?");
            $stmt->execute(array($produto_id));
            $mensagem = "Produto removido da loja.";
        }

        if ($acao === "restaurar_produto") {
            $stmt = ew_db()->prepare("UPDATE produtos SET ativo = 1 WHERE id = ?");
            $stmt->execute(array($produto_id));
            $mensagem = "Produto voltou para a loja.";
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$produtos = ew_db()->query("SELECT * FROM produtos ORDER BY ativo DESC, id ASC")->fetchAll();
$totalProdutos = ew_db()->query("SELECT COUNT(*) AS total FROM produtos WHERE ativo = 1")->fetch();
$totalPedidos = ew_db()->query("SELECT COUNT(*) AS total FROM pedidos")->fetch();
$faturamento = ew_db()->query("SELECT COALESCE(SUM(total), 0) AS total FROM pedidos")->fetch();
$pedidos = ew_db()->query(
    "SELECT p.id, p.email, p.total, p.status, p.criado_em, u.nome
     FROM pedidos p
     INNER JOIN usuarios u ON u.id = p.usuario_id
     ORDER BY p.criado_em DESC
     LIMIT 8"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel Admin - EverWear</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Arial, Helvetica, sans-serif;}
body{min-height:100vh;background:#f4f7f5;color:#13211b;}
.topbar{background:#00573D;color:white;}
.topbar-inner{max-width:1180px;margin:0 auto;padding:18px 20px;display:flex;justify-content:space-between;align-items:center;gap:16px;}
.brand{display:flex;align-items:center;gap:12px;font-weight:800;font-size:22px;}
.brand img{width:54px;height:54px;object-fit:contain;background:white;border-radius:12px;padding:4px;}
.nav{display:flex;gap:10px;flex-wrap:wrap;}
.nav a{color:white;text-decoration:none;font-weight:700;padding:10px 12px;border:1px solid rgba(255,255,255,.25);border-radius:10px;}
.page{max-width:1180px;margin:0 auto;padding:30px 20px 56px;}
.hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-end;margin-bottom:22px;}
.hero h1{font-size:36px;color:#10251d;margin-bottom:6px;}
.hero p{color:#5d6f66;line-height:1.5;}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px;}
.stat{background:white;border:1px solid rgba(0,87,61,.10);border-radius:8px;padding:18px;box-shadow:0 10px 24px rgba(0,0,0,.04);}
.stat span{display:block;color:#64756d;font-size:13px;margin-bottom:8px;}
.stat strong{font-size:28px;color:#00573D;}
.panel{background:white;border:1px solid rgba(0,87,61,.10);border-radius:8px;box-shadow:0 14px 32px rgba(0,0,0,.05);overflow:hidden;margin-bottom:22px;}
.panel-head{padding:18px 20px;border-bottom:1px solid rgba(0,87,61,.10);display:flex;justify-content:space-between;gap:12px;align-items:center;}
.panel-head h2{font-size:22px;color:#10251d;}
.table-wrap{overflow:auto;}
table{width:100%;border-collapse:collapse;min-width:760px;}
th,td{padding:14px 16px;text-align:left;border-bottom:1px solid rgba(0,87,61,.08);font-size:14px;vertical-align:middle;}
th{background:#f7faf8;color:#365146;font-size:12px;text-transform:uppercase;letter-spacing:.04em;}
td img{width:58px;height:58px;object-fit:contain;background:#f7faf8;border-radius:8px;border:1px solid rgba(0,87,61,.08);}
.status{display:inline-flex;padding:6px 9px;border-radius:999px;font-weight:800;font-size:12px;}
.ativo{background:#e9f8ef;color:#176a3a;}
.removido{background:#fff0f0;color:#8a2d2d;}
.btn{border:none;border-radius:10px;padding:10px 12px;font-weight:800;cursor:pointer;}
.btn-danger{background:#8a2d2d;color:white;}
.btn-ok{background:#00573D;color:white;}
.alerta{padding:14px 16px;margin-bottom:18px;border-radius:8px;font-weight:800;}
.erro{background:#fff0f0;color:#8a2d2d;border:1px solid #efbbbb;}
.sucesso{background:#eef8f2;color:#1e6b40;border:1px solid #b9e3c7;}
@media(max-width:820px){.topbar-inner,.hero{flex-direction:column;align-items:flex-start;}.stats{grid-template-columns:1fr;}.nav a{width:100%;text-align:center;}}
</style>
<link rel="stylesheet" href="darkmode.css?v=2">
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand">
            <img src="everwearlogo.png" alt="EverWear">
            <span>Painel Admin</span>
        </div>
        <nav class="nav">
            <a href="index.html">Ver loja</a>
            <a href="carrinho.php">Carrinho</a>
            <a href="logout.php">Sair</a>
        </nav>
    </div>
</header>

<main class="page">
    <section class="hero">
        <div>
            <h1>Admin EverWear</h1>
            <p>Controle produtos, acompanhe pedidos e remova itens da loja.</p>
        </div>
    </section>

    <?php if ($erro !== "") { ?>
        <div class="alerta erro"><?php echo ew_h($erro); ?></div>
    <?php } ?>

    <?php if ($mensagem !== "") { ?>
        <div class="alerta sucesso"><?php echo ew_h($mensagem); ?></div>
    <?php } ?>

    <section class="stats">
        <div class="stat">
            <span>Produtos ativos</span>
            <strong><?php echo (int)$totalProdutos["total"]; ?></strong>
        </div>
        <div class="stat">
            <span>Pedidos</span>
            <strong><?php echo (int)$totalPedidos["total"]; ?></strong>
        </div>
        <div class="stat">
            <span>Faturamento</span>
            <strong>R$ <?php echo number_format((float)$faturamento["total"], 2, ",", "."); ?></strong>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2>Produtos</h2>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Produto</th>
                        <th>Preco</th>
                        <th>Estoque</th>
                        <th>Status</th>
                        <th>Acao</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto) { ?>
                        <tr>
                            <td><img src="<?php echo ew_h($produto["imagem"]); ?>" alt="<?php echo ew_h($produto["nome"]); ?>"></td>
                            <td>
                                <strong><?php echo ew_h($produto["nome"]); ?></strong><br>
                                <span><?php echo ew_h($produto["cor"]); ?></span>
                            </td>
                            <td>R$ <?php echo number_format((float)$produto["preco"], 2, ",", "."); ?></td>
                            <td><?php echo (int)$produto["estoque"]; ?></td>
                            <td>
                                <?php if ((int)$produto["ativo"] === 1) { ?>
                                    <span class="status ativo">Ativo</span>
                                <?php } else { ?>
                                    <span class="status removido">Removido</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ((int)$produto["ativo"] === 1) { ?>
                                    <form method="post">
                                        <input type="hidden" name="acao" value="remover_produto">
                                        <input type="hidden" name="produto_id" value="<?php echo (int)$produto["id"]; ?>">
                                        <button class="btn btn-danger" type="submit">Remover</button>
                                    </form>
                                <?php } else { ?>
                                    <form method="post">
                                        <input type="hidden" name="acao" value="restaurar_produto">
                                        <input type="hidden" name="produto_id" value="<?php echo (int)$produto["id"]; ?>">
                                        <button class="btn btn-ok" type="submit">Restaurar</button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2>Pedidos recentes</h2>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pedidos) === 0) { ?>
                        <tr><td colspan="6">Nenhum pedido registrado ainda.</td></tr>
                    <?php } ?>
                    <?php foreach ($pedidos as $pedido) { ?>
                        <tr>
                            <td>#<?php echo (int)$pedido["id"]; ?></td>
                            <td><?php echo ew_h($pedido["nome"]); ?></td>
                            <td><?php echo ew_h($pedido["email"]); ?></td>
                            <td>R$ <?php echo number_format((float)$pedido["total"], 2, ",", "."); ?></td>
                            <td><?php echo ew_h($pedido["status"]); ?></td>
                            <td><?php echo date("d/m/Y H:i", strtotime($pedido["criado_em"])); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script src="darkmode.js?v=2"></script>
</body>
</html>
