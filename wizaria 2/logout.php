<?php
require_once __DIR__ . '/config.php';

// Destrói a sessão
$_SESSION = [];
session_destroy();

// Redireciona para a página inicial
header('Location: ' . url('index.php'));
exit;
?>