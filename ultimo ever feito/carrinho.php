<?php
require_once __DIR__ . "/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $acao = isset($_POST["acao"]) ? $_POST["acao"] : "";

        if ($acao === "esvaziar_carrinho") {
            ew_clear_cart();
            $_SESSION["sucesso_carrinho"] = "Carrinho esvaziado com sucesso.";
            ew_redirect("carrinho.php");
        }

        if ($acao === "remover_item") {
            ew_remove_cart_item(isset($_POST["item_id"]) ? (int)$_POST["item_id"] : 0);
            $_SESSION["sucesso_carrinho"] = "Produto removido do carrinho.";
            ew_redirect("carrinho.php");
        }

        if (isset($_POST["produto_id"]) || isset($_POST["produto"])) {
            $produto_id = isset($_POST["produto_id"]) ? (int)$_POST["produto_id"] : 0;

            if ($produto_id <= 0 && !empty($_POST["produto"])) {
                $produtoEncontrado = ew_product_by_name($_POST["produto"]);
                $produto_id = $produtoEncontrado ? (int)$produtoEncontrado["id"] : 0;
            }

            ew_add_to_cart($produto_id, isset($_POST["quantidade"]) ? (int)$_POST["quantidade"] : 1, isset($_POST["tamanho"]) ? $_POST["tamanho"] : "");
        }
    } catch (Exception $e) {
        $_SESSION["erro_carrinho"] = $e->getMessage();
    }
}

$erroCarrinho = isset($_SESSION["erro_carrinho"]) ? $_SESSION["erro_carrinho"] : "";
unset($_SESSION["erro_carrinho"]);
$sucessoCarrinho = isset($_SESSION["sucesso_carrinho"]) ? $_SESSION["sucesso_carrinho"] : "";
unset($_SESSION["sucesso_carrinho"]);

$itens = ew_cart_items();
$total = ew_cart_total($itens);
$primeiroItem = count($itens) > 0 ? $itens[0] : null;
$usuario = ew_current_user();
$checkoutUrl = $usuario ? "finalizar.php" : "login.php?redirect=finalizar.php";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Carrinho - EverWear</title>

<style>
:root{
    --green:#00573D;
    --green-2:#007A56;
    --brown:#00573D;
    --text:#13211b;
    --line:rgba(19,33,27,.10);
}

*{
    box-sizing:border-box;
    margin:0;
    padding:0;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    background:
        radial-gradient(circle at top left, rgba(0,87,61,.16), transparent 28%),
        radial-gradient(circle at top right, rgba(0,87,61,.14), transparent 24%),
        linear-gradient(180deg, #f8fbf8 0%, #eef4ef 100%);
    color:var(--text);
    min-height:100vh;
}

.topbar{
    background:rgba(255,255,255,.90);
    border-bottom:1px solid var(--line);
    box-shadow:0 8px 24px rgba(0,0,0,.04);
}

.topbar-inner{
    max-width:1180px;
    margin:0 auto;
    padding:18px 20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
}

.brand{
    display:flex;
    align-items:center;
    gap:12px;
    font-weight:700;
    color:var(--green);
}
.brand-logo{
    width:70px;
    height:70px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.brand-logo img{
    width:100%;
    height:100%;
    object-fit:contain;
    display:block;
}

.top-note{
    font-size:14px;
    color:#5b6c63;
}

.page{
    max-width:1180px;
    margin:0 auto;
    padding:36px 20px 56px;
}

.hero{
    margin-bottom:24px;
}

.hero h1{
    font-size:42px;
    color:#12261f;
    margin-bottom:10px;
}

.hero p{
    max-width:720px;
    color:#5b6c63;
    line-height:1.6;
    font-size:16px;
}

.panel{
    background:white;
    box-shadow:0 24px 55px rgba(0,87,61,.10);
    border-radius:28px;
    overflow:hidden;
    border:1px solid rgba(0,87,61,.08);
}

.panel-head{
    padding:24px 24px 0;
}

.panel-title{
    font-size:24px;
    color:#00573D;
    margin-bottom:8px;
}

.panel-subtitle{
    color:#5b6c63;
    font-size:14px;
    line-height:1.5;
}

.content{
    display:grid;
    grid-template-columns:360px 1fr;
    gap:24px;
    padding:24px;
}

.product-card{
    background:#f8fbf8;
    border:1px solid var(--line);
    border-radius:24px;
    padding:20px;
}

.product-image{
    background:linear-gradient(180deg, #f7f7f4, #eaf0e8);
    border-radius:20px;
    padding:16px;
    min-height:280px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:18px;
}

.product-image img{
    max-width:100%;
    max-height:260px;
    object-fit:contain;
    display:block;
}

.pill-row{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin-bottom:14px;
}

.pill{
    padding:8px 12px;
    border-radius:999px;
    background:#eef5f0;
    color:#355448;
    font-size:12px;
    font-weight:700;
}

.product-card h2{
    margin-bottom:8px;
    font-size:26px;
    color:#12261f;
}

.product-card p{
    color:#5b6c63;
    line-height:1.6;
    font-size:15px;
}

.price{
    margin-top:16px;
    font-size:32px;
    font-weight:800;
    color:var(--green);
}

.summary{
    display:grid;
    gap:14px;
}

.box{
    background:white;
    border:1px solid var(--line);
    border-radius:22px;
    padding:20px;
}

.box h3{
    margin-bottom:14px;
    font-size:20px;
    color:#12261f;
}

.row{
    display:flex;
    justify-content:space-between;
    gap:16px;
    padding:13px 14px;
    border-radius:14px;
    background:#f7faf8;
    font-size:14px;
    line-height:1.5;
    margin-bottom:10px;
}

.row strong{
    color:#355448;
}

.row span{
    text-align:right;
}

.total{
    background:linear-gradient(135deg, var(--green), var(--brown));
    color:white;
    margin-bottom:0;
}

.total strong,
.total span{
    color:white;
    font-size:16px;
    font-weight:800;
}

.actions{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    margin-top:18px;
}

.btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:15px 20px;
    border-radius:16px;
    text-decoration:none;
    font-weight:800;
    border:1px solid transparent;
    min-width:200px;
}

.primary{
    background:linear-gradient(135deg, var(--green), var(--green-2));
    color:white;
    box-shadow:0 16px 28px rgba(0,87,61,.20);
}

.secondary{
    background:#f1f6f3;
    color:#1e4234;
    border-color: rgba(0,87,61,.12);
}

.danger{
    background:#fff0f0;
    color:#8a2d2d;
    border-color:#efbbbb;
}

.btn-small{
    min-width:auto;
    padding:8px 12px;
    border-radius:12px;
    font-size:12px;
    cursor:pointer;
}

.inline-form{
    display:inline-flex;
    margin-left:10px;
}

.empty{
    text-align:center;
    padding:54px 24px;
}

.empty h2{
    margin-bottom:10px;
    font-size:30px;
    color:#12261f;
}

.empty p{
    margin:0 auto 22px;
    max-width:520px;
    color:#5b6c63;
    line-height:1.6;
}

.alerta{
    margin:24px 24px 0;
    padding:14px 16px;
    border-radius:14px;
    background:#fff0f0;
    color:#8a2d2d;
    border:1px solid #efbbbb;
    font-weight:bold;
    font-size:14px;
}

.alerta.sucesso{
    background:#eef8f2;
    color:#1e6b40;
    border-color:#b9e3c7;
}

@media(max-width:860px){
    .content{
        grid-template-columns:1fr;
    }

    .hero h1{
        font-size:34px;
    }

    .topbar-inner{
        flex-direction:column;
        text-align:center;
    }

    .btn{
        width:100%;
    }
}
</style>
<link rel="stylesheet" href="darkmode.css?v=2">
</head>

<body>
<header class="topbar">
    <div class="topbar-inner">

        <div class="brand">

            <div class="brand-logo">
                <img src="everwearlogo.png" alt="EverWear">
            </div>

            <div>
                <div style="font-size:22px;font-weight:800;color:#00573D;">
                    EverWear
                </div>
                <div class="top-note">
                    Seu carrinho de compras
                </div>
            </div>

        </div>

        <div class="top-note">
            Revise antes de seguir para o checkout
        </div>

    </div>
</header>

<main class="page">
    <section class="hero">
        <h1>Seu carrinho</h1>
        <p>Confira o produto selecionado e avance para a finalização com os dados de entrega e pagamento.</p>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Resumo do pedido</h2>
            <p class="panel-subtitle">O carrinho mostra os itens selecionados e prepara a etapa de pagamento.</p>
        </div>

        <?php if ($erroCarrinho !== "") { ?>
            <div class="alerta"><?php echo ew_h($erroCarrinho); ?></div>
        <?php } ?>

        <?php if ($sucessoCarrinho !== "") { ?>
            <div class="alerta sucesso"><?php echo ew_h($sucessoCarrinho); ?></div>
        <?php } ?>

        <?php if ($primeiroItem) { ?>

            <div class="content">
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo ew_h($primeiroItem["imagem"]); ?>" alt="<?php echo ew_h($primeiroItem["nome"]); ?>">
                    </div>

                    <div class="pill-row">
                        <span class="pill">Cor: <?php echo ew_h($primeiroItem["cor"]); ?></span>

                        <?php if ($primeiroItem["tamanho"] != "") { ?>
                            <span class="pill">Tamanho: <?php echo ew_h($primeiroItem["tamanho"]); ?></span>
                        <?php } ?>

                        <span class="pill">Estoque: <?php echo (int)$primeiroItem["estoque"]; ?></span>
                    </div>

                    <h2><?php echo ew_h($primeiroItem["nome"]); ?></h2>
                    <p><?php echo ew_h($primeiroItem["descricao"]); ?></p>

                    <div class="price">
                        R$ <?php echo number_format($total, 2, ",", "."); ?>
                    </div>
                </div>

                <div class="summary">
                    <div class="box">
                        <h3>Detalhes</h3>

                        <?php foreach ($itens as $item) { ?>
                            <div class="row">
                                <strong><?php echo ew_h($item["nome"]); ?></strong>
                                <span>
                                    <?php echo (int)$item["quantidade"]; ?>x
                                    <?php if ($item["tamanho"] != "") { ?>
                                        • Tam. <?php echo ew_h($item["tamanho"]); ?>
                                    <?php } ?>
                                    • R$ <?php echo number_format(((float)$item["preco"]) * ((int)$item["quantidade"]), 2, ",", "."); ?>
                                    <form class="inline-form" method="post">
                                        <input type="hidden" name="acao" value="remover_item">
                                        <input type="hidden" name="item_id" value="<?php echo (int)$item["item_id"]; ?>">
                                        <button class="btn danger btn-small" type="submit">Remover</button>
                                    </form>
                                </span>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <strong>Status</strong>
                            <span>Pronto para checkout</span>
                        </div>

                        <div class="row total">
                            <strong>Total</strong>
                            <span>R$ <?php echo number_format($total, 2, ",", "."); ?></span>
                        </div>
                    </div>

                    <div class="box">
                        <h3>Próximo passo</h3>

                        <p style="margin:0;color:#5b6c63;line-height:1.6;font-size:15px;">
                            Vá para a finalização e informe e-mail, CEP, endereço e forma de pagamento.
                        </p>

                        <div class="actions">
                            <a class="btn primary" href="<?php echo ew_h($checkoutUrl); ?>">Finalizar compra</a>
                            <a class="btn secondary" href="index.html#produtos">Continuar comprando</a>
                            <form method="post">
                                <input type="hidden" name="acao" value="esvaziar_carrinho">
                                <button class="btn danger" type="submit">Esvaziar carrinho</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php } else { ?>

            <div class="empty">
                <h2>Seu carrinho está vazio</h2>
                <p>Escolha um produto primeiro para vê-lo aqui com imagem, valor e acesso à finalização.</p>
                <a class="btn primary" href="index.html#produtos">Ir para os produtos</a>
            </div>

        <?php } ?>
    </section>
</main>
<script>
    if(localStorage.getItem("darkMode") === "on"){
    document.body.classList.add("dark-mode");
}

function toggleDarkMode(){
    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("darkMode","on");
    }else{
        localStorage.setItem("darkMode","off");
    }
}
</script>

<script src="darkmode.js?v=2"></script>
</body>
</html>
