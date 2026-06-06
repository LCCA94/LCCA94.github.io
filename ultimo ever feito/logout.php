<?php
require_once __DIR__ . "/config.php";

unset($_SESSION["usuario_id"]);
ew_redirect("login.php");
?>
