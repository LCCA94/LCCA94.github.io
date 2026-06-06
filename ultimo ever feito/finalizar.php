<?php
require_once __DIR__ . "/config.php";

$usuario = ew_current_user();

if (!$usuario) {
    ew_redirect("login.php?redirect=finalizar.php");
}

$itens = ew_cart_items();

if (count($itens) === 0) {
    ew_redirect("carrinho.php");
}

$primeiroItem = $itens[0];
$total = ew_cart_total($itens);
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dados = array(
        "email" => isset($_POST["email"]) ? trim($_POST["email"]) : "",
        "cep" => isset($_POST["cep"]) ? trim($_POST["cep"]) : "",
        "endereco" => isset($_POST["endereco"]) ? trim($_POST["endereco"]) : "",
        "numero" => isset($_POST["numero"]) ? trim($_POST["numero"]) : "",
        "bairro" => isset($_POST["bairro"]) ? trim($_POST["bairro"]) : "",
        "cidade" => isset($_POST["cidade"]) ? trim($_POST["cidade"]) : "",
        "estado" => isset($_POST["estado"]) ? strtoupper(trim($_POST["estado"])) : "",
        "complemento" => isset($_POST["complemento"]) ? trim($_POST["complemento"]) : "",
        "pagamento" => isset($_POST["pagamento"]) ? trim($_POST["pagamento"]) : "",
        "nome_cartao" => isset($_POST["nome_cartao"]) ? trim($_POST["nome_cartao"]) : "",
        "numero_cartao" => isset($_POST["numero_cartao"]) ? trim($_POST["numero_cartao"]) : "",
        "validade" => isset($_POST["validade"]) ? trim($_POST["validade"]) : "",
        "cvv" => isset($_POST["cvv"]) ? trim($_POST["cvv"]) : ""
    );

    if ($dados["email"] == "" || $dados["cep"] == "" || $dados["endereco"] == "" || $dados["numero"] == "" || $dados["bairro"] == "" || $dados["cidade"] == "" || $dados["estado"] == "" || $dados["pagamento"] == "") {
        $erro = "Preencha todos os campos obrigatórios.";
    } else if (stripos($dados["pagamento"], "cart") !== false && ($dados["nome_cartao"] == "" || $dados["numero_cartao"] == "" || $dados["validade"] == "" || $dados["cvv"] == "")) {
        $erro = "Preencha todos os dados do cartao para finalizar a compra.";
    } else {
        try {
            $pedido = ew_create_order($dados);
            $itemConfirmado = $pedido["itens"][0];

            $_SESSION["compra_concluida"] = array(
                "pedido_id" => $pedido["id"],
                "produto" => $itemConfirmado["nome"],
                "preco" => $pedido["total"],
                "imagem" => $itemConfirmado["imagem"],
                "cor" => $itemConfirmado["cor"],
                "email" => $dados["email"],
                "cep" => $dados["cep"],
                "endereco" => $dados["endereco"],
                "numero" => $dados["numero"],
                "complemento" => $dados["complemento"],
                "bairro" => $dados["bairro"],
                "cidade" => $dados["cidade"],
                "estado" => $dados["estado"],
                "pagamento" => $dados["pagamento"],
                "itens" => $pedido["itens"],
                "total" => $pedido["total"]
            );

            ew_redirect("compra_concluida.php");
        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    }
}

$pagamentoSelecionado = isset($_POST["pagamento"]) ? $_POST["pagamento"] : "Pix";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Finalizar Compra - EverWear</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    background:linear-gradient(135deg,#f3f8f5,#e7f0eb);
    color:#10251d;
    min-height:100vh;
}

.topo{
    background:white;
    padding:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.06);
}

.topo-conteudo{
    max-width:1150px;
    margin:auto;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logo{
    display:flex;
    align-items:center;
    gap:12px;
    color:#00573D;
    font-size:24px;
    font-weight:bold;
}

.logo img{
    width:45px;
    height:45px;
    object-fit:contain;
    border-radius:12px;
    background:white;
    padding:4px;
    box-shadow:0 8px 18px rgba(0,87,61,.14);
}

.seguro{
    color:#66756d;
    font-size:14px;
}

.container{
    max-width:1150px;
    margin:auto;
    padding:40px 20px;
}

.titulo{
    margin-bottom:28px;
}

.titulo h1{
    font-size:42px;
    color:#10251d;
    margin-bottom:8px;
}

.titulo p{
    color:#66756d;
    font-size:16px;
}

.grid{
    display:grid;
    grid-template-columns:0.9fr 1.1fr;
    gap:25px;
    align-items:start;
}

.card{
    background:white;
    border-radius:24px;
    padding:25px;
    box-shadow:0 20px 45px rgba(0,0,0,0.08);
    border:1px solid rgba(0,87,61,0.08);
}

.card h2{
    color:#00573D;
    margin-bottom:8px;
    font-size:24px;
}

.sub{
    color:#66756d;
    font-size:14px;
    margin-bottom:20px;
}

.imagem-produto{
    width:100%;
    height:330px;
    background:#f7faf8;
    border-radius:20px;
    border:1px solid rgba(0,87,61,0.12);
    display:flex;
    justify-content:center;
    align-items:center;
    overflow:hidden;
    margin-bottom:20px;
}

.imagem-produto img{
    width:100%;
    height:100%;
    object-fit:contain;
    padding:20px;
}

.badges{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:15px;
}

.badge{
    background:#eef7f2;
    color:#00573D;
    padding:8px 12px;
    border-radius:50px;
    font-size:13px;
    font-weight:bold;
}

.nome-produto{
    font-size:28px;
    margin-bottom:10px;
}

.descricao{
    color:#66756d;
    line-height:1.5;
    margin-bottom:18px;
}

.preco{
    color:#00573D;
    font-size:34px;
    font-weight:bold;
    margin-bottom:18px;
}

.total{
    background:linear-gradient(135deg,#00573D,#0b7a56);
    color:white;
    border-radius:18px;
    padding:18px;
    display:flex;
    justify-content:space-between;
    font-size:18px;
    font-weight:bold;
}

.alerta{
    padding:14px 16px;
    border-radius:14px;
    margin-bottom:18px;
    font-weight:bold;
    font-size:14px;
}

.erro{
    background:#fff0f0;
    color:#8a2d2d;
    border:1px solid #efbbbb;
}

.area{
    margin-bottom:22px;
}

.area h3{
    font-size:17px;
    margin-bottom:12px;
    color:#10251d;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}

.campo{
    display:flex;
    flex-direction:column;
    gap:7px;
}

.campo.full{
    grid-column:1 / -1;
}

label{
    font-size:14px;
    font-weight:bold;
    color:#263a31;
}

input{
    height:54px;
    border:1px solid rgba(0,87,61,0.18);
    border-radius:14px;
    padding:0 15px;
    font-size:15px;
    outline:none;
    background:white;
}

input:focus{
    border-color:#00573D;
    box-shadow:0 0 0 4px rgba(0,87,61,0.10);
}

.pagamentos{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
}

.pagamento{
    cursor:pointer;
}

.pagamento input{
    display:none;
}

.pagamento-card{
    min-height:90px;
    border:1px solid rgba(0,87,61,0.18);
    border-radius:18px;
    padding:15px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    gap:6px;
    transition:0.2s;
}

.pagamento-card strong{
    color:#10251d;
}

.pagamento-card small{
    color:#66756d;
}

.pagamento input:checked + .pagamento-card{
    border-color:#00573D;
    background:#f0faf5;
    box-shadow:0 0 0 4px rgba(0,87,61,0.10);
}

.pix-box{
    display:none;
    margin-top:16px;
    background:#f7faf8;
    border:1px solid rgba(0,87,61,0.14);
    border-radius:20px;
    padding:18px;
    text-align:center;
}

.pix-box.ativo{
    display:block;
}

.pix-box h4{
    color:#00573D;
    font-size:18px;
    margin-bottom:8px;
}

.pix-box p{
    color:#66756d;
    font-size:14px;
    line-height:1.5;
    margin-bottom:14px;
}

.qr-code{
    width:190px;
    height:190px;
    margin:0 auto 14px;
    background:white;
    border-radius:16px;
    padding:12px;
    border:1px solid rgba(0,87,61,0.16);
    box-shadow:0 12px 25px rgba(0,0,0,0.06);
    display:grid;
    grid-template-columns:repeat(21, 1fr);
    grid-template-rows:repeat(21, 1fr);
    gap:2px;
}

.qr-pixel{
    background:transparent;
    border-radius:1px;
}

.qr-pixel.black{
    background:#111;
}

.pix-codigo{
    background:white;
    border:1px dashed rgba(0,87,61,0.35);
    color:#263a31;
    border-radius:14px;
    padding:12px;
    font-size:13px;
    word-break:break-all;
    margin-top:10px;
}

.cartao-extra{
    display:none;
    grid-template-columns:1fr 1fr;
    gap:14px;
    margin-top:14px;
}

.cartao-extra.ativo{
    display:grid;
}

.acoes{
    display:flex;
    flex-direction:column;
    gap:12px;
    margin-top:24px;
}

.btn{
    width:100%;
    min-height:56px;
    border:none;
    border-radius:16px;
    font-size:16px;
    font-weight:bold;
    cursor:pointer;
    display:flex;
    justify-content:center;
    align-items:center;
    text-decoration:none;
}

.btn-finalizar{
    background:linear-gradient(135deg,#00573D,#0b7a56);
    color:white;
    box-shadow:0 14px 28px rgba(0,87,61,0.22);
}

.btn-voltar{
    background:#eef5f1;
    color:#00573D;
    border:1px solid rgba(0,87,61,0.12);
}

.aviso{
    margin-top:15px;
    color:#66756d;
    font-size:13px;
    line-height:1.5;
}

.linha-item{
    display:flex;
    justify-content:space-between;
    gap:12px;
    background:#f7faf8;
    border-radius:14px;
    padding:12px 14px;
    margin-bottom:10px;
    color:#66756d;
    font-size:14px;
}

@media(max-width:900px){
    .grid{
        grid-template-columns:1fr;
    }

    .form-grid,
    .pagamentos,
    .cartao-extra{
        grid-template-columns:1fr;
    }

    .titulo h1{
        font-size:34px;
    }

    .imagem-produto{
        height:270px;
    }

    .topo-conteudo{
        flex-direction:column;
        gap:10px;
    }
}
</style>
<link rel="stylesheet" href="darkmode.css?v=2">
</head>

<body>

<header class="topo">
    <div class="topo-conteudo">
        <div class="logo"><img src="everwearlogo.png" alt="EverWear"> EverWear</div>
        <div class="seguro">Checkout seguro • Pagamento protegido</div>
    </div>
</header>

<main class="container">

    <section class="titulo">
        <h1>Finalizar compra</h1>
        <p>Confira seu produto, preencha seus dados e escolha a forma de pagamento.</p>
    </section>

    <div class="grid">

        <section class="card">
            <h2>Resumo do pedido</h2>
            <p class="sub">Produtos selecionados no carrinho.</p>

            <div class="imagem-produto">
                <img src="<?php echo ew_h($primeiroItem["imagem"]); ?>" alt="<?php echo ew_h($primeiroItem["nome"]); ?>">
            </div>

            <div class="badges">
                <span class="badge">Itens: <?php echo count($itens); ?></span>
                <span class="badge">Linha premium</span>
            </div>

            <h3 class="nome-produto"><?php echo ew_h($primeiroItem["nome"]); ?></h3>
            <p class="descricao"><?php echo ew_h($primeiroItem["descricao"]); ?></p>

            <?php foreach ($itens as $item) { ?>
                <div class="linha-item">
                    <strong><?php echo ew_h($item["nome"]); ?></strong>
                    <span><?php echo (int)$item["quantidade"]; ?>x R$ <?php echo number_format((float)$item["preco"], 2, ",", "."); ?></span>
                </div>
            <?php } ?>

            <div class="preco">
                R$ <?php echo number_format($total, 2, ",", "."); ?>
            </div>

            <div class="total">
                <span>Total</span>
                <span>R$ <?php echo number_format($total, 2, ",", "."); ?></span>
            </div>
        </section>

        <section class="card">
            <h2>Entrega e pagamento</h2>
            <p class="sub">Preencha as informações abaixo.</p>

            <?php if ($erro != "") { ?>
                <div class="alerta erro"><?php echo ew_h($erro); ?></div>
            <?php } ?>

            <form method="post" autocomplete="on">

                <div class="area">
                    <h3>Contato</h3>
                    <div class="form-grid">
                        <div class="campo full">
                            <label for="email">E-mail *</label>
                            <input id="email" type="email" name="email" placeholder="seuemail@exemplo.com" value="<?php echo isset($_POST["email"]) ? ew_h($_POST["email"]) : ew_h($usuario["email"]); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="area">
                    <h3>Endereço</h3>

                    <div class="form-grid">
                        <div class="campo">
                            <label for="cep">CEP *</label>
                            <input id="cep" type="text" name="cep" placeholder="00000-000" maxlength="9" value="<?php echo isset($_POST["cep"]) ? ew_h($_POST["cep"]) : ""; ?>" required>
                        </div>

                        <div class="campo">
                            <label for="estado">Estado *</label>
                            <input id="estado" type="text" name="estado" placeholder="SP" maxlength="2" value="<?php echo isset($_POST["estado"]) ? ew_h($_POST["estado"]) : ""; ?>" required>
                        </div>

                        <div class="campo full">
                            <label for="endereco">Endereço *</label>
                            <input id="endereco" type="text" name="endereco" placeholder="Rua, avenida ou travessa" value="<?php echo isset($_POST["endereco"]) ? ew_h($_POST["endereco"]) : ""; ?>" required>
                        </div>

                        <div class="campo">
                            <label for="numero">Número *</label>
                            <input id="numero" type="text" name="numero" placeholder="123" value="<?php echo isset($_POST["numero"]) ? ew_h($_POST["numero"]) : ""; ?>" required>
                        </div>

                        <div class="campo">
                            <label for="bairro">Bairro *</label>
                            <input id="bairro" type="text" name="bairro" placeholder="Centro" value="<?php echo isset($_POST["bairro"]) ? ew_h($_POST["bairro"]) : ""; ?>" required>
                        </div>

                        <div class="campo">
                            <label for="cidade">Cidade *</label>
                            <input id="cidade" type="text" name="cidade" placeholder="São Paulo" value="<?php echo isset($_POST["cidade"]) ? ew_h($_POST["cidade"]) : ""; ?>" required>
                        </div>

                        <div class="campo">
                            <label for="complemento">Complemento</label>
                            <input id="complemento" type="text" name="complemento" placeholder="Apto, bloco, casa" value="<?php echo isset($_POST["complemento"]) ? ew_h($_POST["complemento"]) : ""; ?>">
                        </div>
                    </div>
                </div>

                <div class="area">
                    <h3>Forma de pagamento</h3>

                    <div class="pagamentos">
                        <label class="pagamento">
                            <input type="radio" name="pagamento" value="Pix" <?php if ($pagamentoSelecionado == "Pix" || $pagamentoSelecionado == "pix") { echo "checked"; } ?>>
                            <div class="pagamento-card">
                                <strong>Pix</strong>
                                <small>Gera QR Code fictício pelo valor.</small>
                            </div>
                        </label>

                        <label class="pagamento">
                            <input type="radio" name="pagamento" value="Cartão de crédito" <?php if ($pagamentoSelecionado == "Cartão de crédito") { echo "checked"; } ?>>
                            <div class="pagamento-card">
                                <strong>Cartão crédito</strong>
                                <small>Pagamento parcelado.</small>
                            </div>
                        </label>

                        <label class="pagamento">
                            <input type="radio" name="pagamento" value="Cartão de débito" <?php if ($pagamentoSelecionado == "Cartão de débito") { echo "checked"; } ?>>
                            <div class="pagamento-card">
                                <strong>Cartão débito</strong>
                                <small>Pagamento direto.</small>
                            </div>
                        </label>
                    </div>

                    <div id="pixBox" class="pix-box">
                        <h4>Pagamento via Pix</h4>
                        <p>QR Code fictício gerado para o valor deste pedido. Ele não realiza pagamento e não leva para lugar nenhum.</p>

                        <div class="qr-code" id="fakeQrCode" data-valor="<?php echo number_format($total, 2, ".", ""); ?>"></div>

                        <div class="pix-codigo">
                            Código Pix: EVERWEAR-<?php echo number_format($total, 2, ".", ""); ?>
                        </div>
                    </div>

                    <div id="cartaoExtra" class="cartao-extra">
                        <div class="campo full">
                            <label for="nome_cartao">Nome no cartão</label>
                            <input id="nome_cartao" type="text" name="nome_cartao" placeholder="Nome impresso no cartão">
                        </div>

                        <div class="campo full">
                            <label for="numero_cartao">Número do cartão</label>
                            <input id="numero_cartao" type="text" name="numero_cartao" placeholder="0000 0000 0000 0000">
                        </div>

                        <div class="campo">
                            <label for="validade">Validade</label>
                            <input id="validade" type="text" name="validade" placeholder="MM/AA">
                        </div>

                        <div class="campo">
                            <label for="cvv">CVV</label>
                            <input id="cvv" type="text" name="cvv" placeholder="123">
                        </div>
                    </div>
                </div>

                <div class="acoes">
                    <button type="submit" class="btn btn-finalizar">Confirmar pedido</button>
                    <a href="carrinho.php" class="btn btn-voltar">Voltar ao carrinho</a>
                </div>

                <p class="aviso">
                    Seus dados serão usados apenas para concluir a compra e organizar a entrega.
                </p>

            </form>
        </section>

    </div>

</main>

<script>
var radios = document.getElementsByName("pagamento");
var cartaoExtra = document.getElementById("cartaoExtra");
var pixBox = document.getElementById("pixBox");
var cartaoCampos = [
    document.getElementById("nome_cartao"),
    document.getElementById("numero_cartao"),
    document.getElementById("validade"),
    document.getElementById("cvv")
];

function atualizarPagamento(){
    var valor = "Pix";

    for(var i = 0; i < radios.length; i++){
        if(radios[i].checked){
            valor = radios[i].value;
        }
    }

    if(valor == "Pix" || valor == "pix"){
        pixBox.className = "pix-box ativo";
        cartaoExtra.className = "cartao-extra";
    } else if(valor == "Cartão de crédito" || valor == "Cartão de débito"){
        pixBox.className = "pix-box";
        cartaoExtra.className = "cartao-extra ativo";
    } else {
        pixBox.className = "pix-box";
        cartaoExtra.className = "cartao-extra";
    }
}

function criarQrFalso() {
    var qr = document.getElementById("fakeQrCode");

    if (!qr) {
        return;
    }

    var valor = qr.getAttribute("data-valor");
    var texto = "EVERWEAR-PIX-FALSO-VALOR-" + valor;

    qr.innerHTML = "";

    for (var i = 0; i < 441; i++) {
        var pixel = document.createElement("div");
        pixel.className = "qr-pixel";

        var calculo = texto.charCodeAt(i % texto.length) + i * 7 + Math.floor(parseFloat(valor) * 100);

        if (calculo % 3 == 0 || calculo % 7 == 0) {
            pixel.className = "qr-pixel black";
        }

        qr.appendChild(pixel);
    }

    desenharMarcador(qr, 0, 0);
    desenharMarcador(qr, 14, 0);
    desenharMarcador(qr, 0, 14);
}

function desenharMarcador(qr, inicioX, inicioY) {
    var pixels = qr.children;

    for (var y = 0; y < 7; y++) {
        for (var x = 0; x < 7; x++) {
            var pos = (inicioY + y) * 21 + (inicioX + x);

            if (pos >= 0 && pos < pixels.length) {
                if (
                    x == 0 || x == 6 ||
                    y == 0 || y == 6 ||
                    (x >= 2 && x <= 4 && y >= 2 && y <= 4)
                ) {
                    pixels[pos].className = "qr-pixel black";
                } else {
                    pixels[pos].className = "qr-pixel";
                }
            }
        }
    }
}

for(var i = 0; i < radios.length; i++){
    radios[i].onclick = atualizarPagamento;
}

criarQrFalso();
atualizarPagamento();

function atualizarCamposCartao(){
    var selecionado = "";

    for(var i = 0; i < radios.length; i++){
        if(radios[i].checked){
            selecionado = radios[i].value.toLowerCase();
        }
    }

    var usaCartao = selecionado.indexOf("cart") !== -1;

    for(var c = 0; c < cartaoCampos.length; c++){
        if(cartaoCampos[c]){
            cartaoCampos[c].required = usaCartao;
        }
    }
}

for(var r = 0; r < radios.length; r++){
    radios[r].addEventListener("change", atualizarCamposCartao);
}

atualizarCamposCartao();
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
