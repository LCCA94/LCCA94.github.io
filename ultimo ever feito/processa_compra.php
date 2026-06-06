<?php
session_start();

$checkout = $_SESSION['checkout'] ?? [];

$produto = $_POST['produto'] ?? ($_SESSION['produto'] ?? 'Camiseta EcoWear');
$preco = isset($_POST['preco']) ? (float) $_POST['preco'] : (isset($_SESSION['preco']) ? (float) $_SESSION['preco'] : 99.90);
$imagem = $_POST['imagem'] ?? 'novacamisamarrom.png';
$cor = $_POST['cor'] ?? 'Marrom';

$email = $checkout['email'] ?? '';
$cep = $checkout['cep'] ?? '';
$endereco = $checkout['endereco'] ?? '';
$numero = $checkout['numero'] ?? '';
$complemento = $checkout['complemento'] ?? '';
$bairro = $checkout['bairro'] ?? '';
$cidade = $checkout['cidade'] ?? '';
$estado = $checkout['estado'] ?? '';
$pagamento = $checkout['pagamento'] ?? 'pix';

$pagamentos = [
    'pix' => 'Pix',
    'cartao_credito' => 'Cartão de crédito',
    'cartao_debito' => 'Cartão de débito',
];

$pagamentoLabel = $pagamentos[$pagamento] ?? 'Pix';

$_SESSION['compra_concluida'] = [
    'produto' => $produto,
    'preco' => $preco,
    'imagem' => $imagem,
    'cor' => $cor,
    'email' => $email,
    'cep' => $cep,
    'endereco' => $endereco,
    'numero' => $numero,
    'complemento' => $complemento,
    'bairro' => $bairro,
    'cidade' => $cidade,
    'estado' => $estado,
    'pagamento' => $pagamentoLabel,
];



session_destroy();

function e(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Compra Finalizada - EverWear</title>
<style>
    :root{
        --green:#00573D;
        --green-2:#0b7a56;
        --brown:#5d3d2c;
        --bg:#edf4ef;
        --text:#13211b;
        --line:rgba(19,33,27,.10);
    }

    *{ box-sizing:border-box; }

    body{
        margin:0;
        font-family:Arial, Helvetica, sans-serif;
        background:
            radial-gradient(circle at top left, rgba(0,87,61,.18), transparent 28%),
            radial-gradient(circle at top right, rgba(93,61,44,.16), transparent 24%),
            linear-gradient(180deg, #f8fbf8 0%, #eef4ef 100%);
        color:var(--text);
        min-height:100vh;
    }

    .wrap{
        max-width:1080px;
        margin:0 auto;
        padding:34px 20px 56px;
    }

    .page-logo{
        display:inline-flex;
        align-items:center;
        gap:12px;
        color:var(--green);
        text-decoration:none;
        font-size:24px;
        font-weight:800;
        margin-bottom:18px;
    }

    .page-logo img{
        width:48px;
        height:48px;
        object-fit:contain;
        background:#fff;
        border-radius:12px;
        padding:4px;
        box-shadow:0 8px 18px rgba(0,87,61,.14);
    }

    .success-shell{
        background:rgba(255,255,255,.9);
        border:1px solid rgba(255,255,255,.8);
        box-shadow:0 24px 55px #00573D(16,38,29,.10);
        border-radius:28px;
        overflow:hidden;
    }

    .hero{
        padding:28px 28px 0;
    }

    .mark{
        width:64px;
        height:64px;
        border-radius:18px;
        background:linear-gradient(135deg, var(--green), var(--brown));
        color:#fff;
        display:grid;
        place-items:center;
        font-size:26px;
        font-weight:800;
        box-shadow:0 14px 26px rgba(0,87,61,.18);
        margin-bottom:16px;
    }

    h1{
        margin:0;
        font-size:clamp(30px, 3.5vw, 48px);
        line-height:1.05;
        color:#12261f;
    }

    .subtitle{
        margin:12px 0 0;
        color:#5b6c63;
        line-height:1.6;
        font-size:16px;
        max-width:760px;
    }

    .content{
        display:grid;
        grid-template-columns: 340px 1fr;
        gap:24px;
        padding:28px;
    }

    .product{
        background:#f8fbf8;
        border:1px solid var(--line);
        border-radius:24px;
        padding:20px;
    }

    .product img{
        width:100%;
        height:auto;
        display:block;
        object-fit:contain;
        margin-bottom:18px;
    }

    .pill-row{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-bottom:14px;
    }

    .pill{
        display:inline-flex;
        align-items:center;
        padding:8px 12px;
        border-radius:999px;
        background:#eef5f0;
        color:#355448;
        font-size:12px;
        font-weight:700;
    }

    .product h2{
        margin:0 0 8px;
        font-size:26px;
    }

    .product p{
        margin:0;
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

    .details{
        display:grid;
        gap:14px;
    }

    .box{
        background:#fff;
        border:1px solid var(--line);
        border-radius:22px;
        padding:20px;
    }

    .box h3{
        margin:0 0 14px;
        font-size:20px;
        color:#12261f;
    }

    .rows{
        display:grid;
        gap:12px;
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
    }

    .row strong{
        color:#355448;
    }

    .row span{
        text-align:right;
    }

    .total{
        background:linear-gradient(135deg, var(--green), var(--brown));
        color:#fff;
    }

    .total strong,
    .total span{
        color:#fff;
        font-size:16px;
        font-weight:800;
    }

    .actions{
        display:flex;
        gap:12px;
        flex-wrap:wrap;
        margin-top:6px;
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
    }

    .primary{
        background:linear-gradient(135deg, var(--green), var(--green-2));
        color:#fff;
        box-shadow:0 16px 28px #00573D(0,87,61,.20);
    }

    .secondary{
        background:#f1f6f3;
        color:#1e4234;
        border-color:#00573D(0,87,61,.12);
    }

    .note{
        margin-top:14px;
        padding:14px 16px;
        border-radius:16px;
        background:#f7faf8;
        border:1px solid #00573D(0,87,61,.08);
        color:#516259;
        font-size:14px;
        line-height:1.6;
    }

    @media (max-width: 860px){
        .content{
            grid-template-columns:1fr;
        }
    }
    
</style>
<link rel="stylesheet" href="darkmode.css?v=2">
</head>
<body>
    <main class="wrap">
        <a href="index.html" class="page-logo">
            <img src="everwearlogo.png" alt="EverWear">
            <span>EverWear</span>
        </a>
        <section class="success-shell">
            <div class="hero">
                <div class="mark">✓</div>

<div class="scroll-indicator">
    <span></span>
</div>
                <h1>Compra finalizada com sucesso</h1>
                <p class="subtitle">Seu pedido foi registrado com os dados informados. Abaixo está o resumo completo para a sua conferência.</p>
            </div>

            <div class="content">
                <aside class="product">
                    <img src="<?php echo e($imagem); ?>" alt="<?php echo e($produto); ?>">
                    <div class="pill-row">
                        <span class="pill">Cor: <?php echo e($cor); ?></span>
                        <span class="pill">Pagamento: <?php echo e($pagamentoLabel); ?></span>
                    </div>
                    <h2><?php echo e($produto); ?></h2>
                    <p>Pedido confirmado com acabamento premium e envio alinhado aos dados de entrega informados.</p>
                    <div class="price">R$ <?php echo number_format($preco, 2, ',', '.'); ?></div>
                </aside>

                <section class="details">
                    <div class="box">
                        <h3>Dados do cliente</h3>
                        <div class="rows">
                            <div class="row"><strong>E-mail</strong><span><?php echo e($email); ?></span></div>
                            <div class="row"><strong>CEP</strong><span><?php echo e($cep); ?></span></div>
                            <div class="row"><strong>Endereço</strong><span><?php echo e($endereco); ?>, <?php echo e($numero); ?></span></div>
                            <div class="row"><strong>Complemento</strong><span><?php echo e($complemento !== '' ? $complemento : '-'); ?></span></div>
                            <div class="row"><strong>Bairro</strong><span><?php echo e($bairro); ?></span></div>
                            <div class="row"><strong>Cidade / UF</strong><span><?php echo e($cidade); ?> - <?php echo e($estado); ?></span></div>
                        </div>
                    </div>

                    <div class="box">
                        <h3>Resumo do pagamento</h3>
                        <div class="rows">
                            <div class="row"><strong>Produto</strong><span><?php echo e($produto); ?></span></div>
                            <div class="row"><strong>Forma de pagamento</strong><span><?php echo e($pagamentoLabel); ?></span></div>
                            <div class="row total"><strong>Total pago</strong><span>R$ <?php echo number_format($preco, 2, ',', '.'); ?></span></div>
                        </div>

                        <div class="actions">
                            <a class="btn primary" href="index.html">Voltar para a loja</a>
                            <a class="btn secondary" href="index.html#produtos">Revisar carrinho</a>
                        </div>

                        <div class="note">
                            A confirmação pode ser enviada ao e-mail informado. Se quiser, você também pode adaptar esta tela para gerar um código de pedido automático.
                        </div>
                    </div>
                </section>
            </div>
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
