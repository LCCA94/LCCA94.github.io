<?php
require_once __DIR__ . "/config.php";

ew_ensure_admin_account();

$erro = "";
$sucesso = "";
$redirect = isset($_GET["redirect"]) ? $_GET["redirect"] : (isset($_POST["redirect"]) ? $_POST["redirect"] : "index.html");

$usuarioLogado = ew_current_user();

if ($usuarioLogado) {
    if (!empty($usuarioLogado["admin"]) && $redirect === "index.html") {
        ew_redirect("admin.php");
    }

    ew_redirect($redirect);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = isset($_POST["acao"]) ? $_POST["acao"] : "login";
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $senha = isset($_POST["senha"]) ? $_POST["senha"] : "";

    try {
        if ($acao === "cadastro") {
            $nome = isset($_POST["nome"]) ? trim($_POST["nome"]) : "";

            if ($nome === "" || $email === "" || $senha === "") {
                throw new Exception("Preencha nome, e-mail e senha.");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Informe um e-mail válido.");
            }

            if (strlen($senha) < 6) {
                throw new Exception("A senha precisa ter pelo menos 6 caracteres.");
            }

            ew_ensure_admin_schema();

            $stmt = ew_db()->prepare("INSERT INTO usuarios (nome, email, senha_hash, admin) VALUES (?, ?, ?, 0)");
            $stmt->execute(array($nome, $email, password_hash($senha, PASSWORD_DEFAULT)));
            ew_login_user((int)ew_db()->lastInsertId());
            ew_redirect($redirect);
        }

        if ($email === "" || $senha === "") {
            throw new Exception("Informe e-mail e senha.");
        }

        $stmt = ew_db()->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute(array($email));
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($senha, $usuario["senha_hash"])) {
            throw new Exception("E-mail ou senha incorretos.");
        }

        ew_login_user((int)$usuario["id"]);

        if (!empty($usuario["admin"]) && $redirect === "index.html") {
            ew_redirect("admin.php");
        }

        ew_redirect($redirect);
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - EverWear</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Arial, Helvetica, sans-serif;}
body{min-height:100vh;background:linear-gradient(135deg,#f3f8f5,#e7f0eb);color:#10251d;display:flex;align-items:center;justify-content:center;padding:30px;}
.shell{width:100%;max-width:980px;background:white;border-radius:28px;box-shadow:0 25px 55px rgba(0,87,61,.10);overflow:hidden;border:1px solid rgba(0,87,61,.10);}
.topo{padding:28px;background:#00573D;color:white;display:flex;justify-content:space-between;gap:16px;align-items:center;}
.topo h1{font-size:32px;}
.topo a{color:white;text-decoration:none;font-weight:bold;}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:0;}
.card{padding:30px;}
.card:first-child{border-right:1px solid rgba(0,87,61,.10);}
h2{color:#00573D;margin-bottom:8px;font-size:24px;}
p{color:#66756d;font-size:14px;line-height:1.5;margin-bottom:20px;}
.campo{display:flex;flex-direction:column;gap:7px;margin-bottom:14px;}
label{font-weight:bold;font-size:14px;color:#263a31;}
input{height:52px;border:1px solid rgba(0,87,61,.18);border-radius:14px;padding:0 15px;font-size:15px;outline:none;}
input:focus{border-color:#00573D;box-shadow:0 0 0 4px rgba(0,87,61,.10);}
.btn{width:100%;min-height:54px;border:none;border-radius:16px;background:linear-gradient(135deg,#00573D,#0b7a56);color:white;font-size:16px;font-weight:bold;cursor:pointer;}
.alerta{padding:14px 16px;border-radius:14px;margin:0 30px 20px;font-weight:bold;font-size:14px;background:#fff0f0;color:#8a2d2d;border:1px solid #efbbbb;}
@media(max-width:800px){.grid{grid-template-columns:1fr;}.card:first-child{border-right:none;border-bottom:1px solid rgba(0,87,61,.10);}.topo{flex-direction:column;text-align:center;}}
</style>
<link rel="stylesheet" href="darkmode.css?v=2">
</head>
<body>
<main class="shell">
    <div class="topo">
        <div>
            <h1>EverWear</h1>
            <div>Entre para continuar sua compra</div>
        </div>
        <a href="index.html">Voltar para a loja</a>
    </div>

    <?php if ($erro !== "") { ?>
        <div class="alerta"><?php echo ew_h($erro); ?></div>
    <?php } ?>

    <div class="grid">
        <section class="card">
            <h2>Entrar</h2>
            <p>Acesse sua conta para finalizar pedidos e salvar suas compras.</p>
            <form method="post">
                <input type="hidden" name="acao" value="login">
                <input type="hidden" name="redirect" value="<?php echo ew_h($redirect); ?>">
                <div class="campo">
                    <label for="login_email">E-mail</label>
                    <input id="login_email" type="email" name="email" required>
                </div>
                <div class="campo">
                    <label for="login_senha">Senha</label>
                    <input id="login_senha" type="password" name="senha" required>
                </div>
                <button class="btn" type="submit">Entrar</button>
            </form>
        </section>

        <section class="card">
            <h2>Criar conta</h2>
            <p>Cadastre-se uma vez e use o mesmo login nas próximas compras.</p>
            <form method="post">
                <input type="hidden" name="acao" value="cadastro">
                <input type="hidden" name="redirect" value="<?php echo ew_h($redirect); ?>">
                <div class="campo">
                    <label for="cad_nome">Nome</label>
                    <input id="cad_nome" type="text" name="nome" required>
                </div>
                <div class="campo">
                    <label for="cad_email">E-mail</label>
                    <input id="cad_email" type="email" name="email" required>
                </div>
                <div class="campo">
                    <label for="cad_senha">Senha</label>
                    <input id="cad_senha" type="password" name="senha" minlength="6" required>
                </div>
                <button class="btn" type="submit">Criar conta</button>
            </form>
        </section>
    </div>
</main>
<script src="darkmode.js?v=2"></script>
</body>
</html>
