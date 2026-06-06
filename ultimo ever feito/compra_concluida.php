<?php
session_start();

$pedido = array();

if (isset($_SESSION["compra_concluida"])) {
    $pedido = $_SESSION["compra_concluida"];
}

$produto = isset($pedido["produto"]) ? $pedido["produto"] : "Camiseta EcoWear";
$preco = isset($pedido["preco"]) ? floatval($pedido["preco"]) : 99.90;
$imagem = isset($pedido["imagem"]) ? $pedido["imagem"] : "novacamisamarrom.png";
$cor = isset($pedido["cor"]) ? $pedido["cor"] : "Marrom";
$email = isset($pedido["email"]) ? $pedido["email"] : "";
$cep = isset($pedido["cep"]) ? $pedido["cep"] : "";
$endereco = isset($pedido["endereco"]) ? $pedido["endereco"] : "";
$numero = isset($pedido["numero"]) ? $pedido["numero"] : "";
$complemento = isset($pedido["complemento"]) ? $pedido["complemento"] : "";
$bairro = isset($pedido["bairro"]) ? $pedido["bairro"] : "";
$cidade = isset($pedido["cidade"]) ? $pedido["cidade"] : "";
$estado = isset($pedido["estado"]) ? $pedido["estado"] : "";
$pagamento = isset($pedido["pagamento"]) ? $pedido["pagamento"] : "Pix";

function limpar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Compra Concluída - EverWear</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body{font-family:Arial,sans-serif;background:#f0f8f5;color:#10251d;min-height:100vh;margin:0;padding:0;}
    .container{max-width:1100px;margin:auto;padding:40px 20px;}
    .page-logo{display:inline-flex;align-items:center;gap:12px;color:#00573D;text-decoration:none;font-size:24px;font-weight:bold;margin-bottom:18px;}
    .page-logo img{width:48px;height:48px;object-fit:contain;background:white;border-radius:12px;padding:4px;box-shadow:0 8px 18px rgba(0,87,61,.14);}
    .card{background:white;border-radius:28px;padding:35px;box-shadow:0 25px 55px #00573D(0,0,0,.10);border:1px solid #00573D(0,87,61,.10);}
    .sucesso-topo{text-align:center;margin-bottom:35px;}
    .check{width:80px;height:80px;border-radius:24px;background:linear-gradient(135deg,#00573D,#0b7a56);color:white;display:flex;align-items:center;justify-content:center;font-size:42px;font-weight:bold;margin:0 auto 18px;}
    h1{font-size:42px;color:#00573D;margin-bottom:10px;}
    .sub{color:#66756d;font-size:16px;line-height:1.6;}
    .grid{display:grid;grid-template-columns:360px 1fr;gap:24px;}
    .produto{background:#f7faf8;border-radius:24px;padding:22px;border:1px solid #00573D(0,87,61,.10);}
    .produto-img{width:100%;height:300px;display:flex;align-items:center;justify-content:center;background:white;border-radius:20px;margin-bottom:18px;overflow:hidden;}
    .produto-img img{width:100%;height:100%;object-fit:contain;padding:18px;}
    .badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;}
    .badge{background:#eef7f2;color:#00573D;padding:8px 12px;border-radius:50px;font-size:13px;font-weight:bold;}
    .produto h2{font-size:26px;margin-bottom:10px;}
    .produto p{color:#66756d;line-height:1.5;font-size:14px;}
    .preco{color:#00573D;font-size:32px;font-weight:bold;margin-top:18px;}
    .info{display:grid;gap:18px;}
    .box{border:1px solid #00573D(0,87,61,.10);border-radius:22px;padding:22px;background:#fff;}
    .box h3{color:#00573D;font-size:22px;margin-bottom:15px;}
    .linha{display:flex;justify-content:space-between;gap:16px;background:#f7faf8;padding:13px 15px;border-radius:14px;margin-bottom:10px;font-size:14px;}
    .linha strong{color:#263a31;}
    .linha span{text-align:right;color:#53635b;}
    .total{background:linear-gradient(135deg,#00573D,#5d3d2c);color:white;}
    .total strong,.total span{color:white;font-size:16px;font-weight:bold;}
    .etapas{display:grid;gap:12px;}
    .etapa{background:#f7faf8;border-radius:16px;padding:15px;display:flex;gap:12px;}
    .bolinha{width:15px;height:15px;border-radius:50%;background:#00573D;margin-top:4px;flex:0 0 auto;box-shadow:0 0 0 6px rgba(0,87,61,.10);}
    .etapa strong{display:block;margin-bottom:4px;}
    .etapa p{color:#66756d;font-size:14px;line-height:1.5;}
    .botoes{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px;}
    .btn{flex:1;min-width:220px;min-height:50px;border-radius:16px;display:flex;justify-content:center;align-items:center;text-decoration:none;font-weight:bold;color:white;}
    .btn-loja{background:linear-gradient(135deg,#00573D,#0b7a56);}
    .btn-carrinho{background:#eef5f1;color:#00573D;border:1px solid #00573D(0,87,61,.15);}
    @media(max-width:900px){.grid{grid-template-columns:1fr;}.linha{flex-direction:column;}.linha span{text-align:left;}}
    </style>
<link rel="stylesheet" href="darkmode.css?v=2">
</head>
<body>

<div class="container">
    <a href="index.html" class="page-logo">
        <img src="everwearlogo.png" alt="EverWear">
        <span>EverWear</span>
    </a>
    <div class="card">
        <div class="sucesso-topo">
            <div class="check">✓</div>
            <h1>Compra concluída</h1>
            <p class="sub">Seu pedido foi confirmado com sucesso. Prazo estimado: 5 a 10 dias úteis.</p>
        </div>

        <div class="grid">
            <div class="produto">
                <div class="produto-img">
                    <img src="<?php echo limpar($imagem); ?>" alt="<?php echo limpar($produto); ?>">
                </div>
                <div class="badges">
                    <span class="badge">Cor: <?php echo limpar($cor); ?></span>
                    <span class="badge">Pagamento: <?php echo limpar($pagamento); ?></span>
                </div>
                <h2><?php echo limpar($produto); ?></h2>
                <p>Seu pedido será preparado para envio.</p>
                <div class="preco">R$ <?php echo number_format($preco,2,",","."); ?></div>
            </div>

            <div class="info">
                <div class="box">
                    <h3>Dados da entrega</h3>
                    <div class="linha"><strong>Endereço</strong><span><?php echo limpar($endereco); ?>, <?php echo limpar($numero); ?><?php if($complemento!=""){ echo " - ".limpar($complemento); } ?></span></div>
                    <div class="linha"><strong>Bairro</strong><span><?php echo limpar($bairro); ?></span></div>
                    <div class="linha"><strong>Cidade / Estado</strong><span><?php echo limpar($cidade); ?> - <?php echo limpar($estado); ?></span></div>
                    <div class="linha"><strong>CEP</strong><span><?php echo limpar($cep); ?></span></div>
                </div>

                <div class="box">
                    <h3>Resumo final</h3>
                    <div class="linha"><strong>E-mail</strong><span><?php echo limpar($email); ?></span></div>
                    <div class="linha"><strong>Produto</strong><span><?php echo limpar($produto); ?></span></div>
                    <div class="linha"><strong>Pagamento</strong><span><?php echo limpar($pagamento); ?></span></div>
                    <div class="linha total"><strong>Total pago</strong><span>R$ <?php echo number_format($preco,2,",","."); ?></span></div>
                </div>

                <div class="box">
                    <h3>Status do pedido</h3>
                    <div class="etapas">
                        <div class="etapa"><span class="bolinha"></span><div><strong>Compra confirmada</strong><p>Recebemos seu pedido e dados de entrega.</p></div></div>
                        <div class="etapa"><span class="bolinha"></span><div><strong>Separação do produto</strong><p>Produto será separado e preparado para envio.</p></div></div>
                        <div class="etapa"><span class="bolinha"></span><div><strong>Entrega prevista</strong><p>Entrega prevista em 5 a 10 dias úteis.</p></div></div>
                    </div>

                    <div class="botoes">
                        <a href="index.html" class="btn btn-loja">Voltar para a loja</a>
                        <a href="carrinho.php" class="btn btn-carrinho">Ver carrinho</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
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
