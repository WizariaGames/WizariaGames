<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem";
    } elseif (registrarUsuario($nome, $email, $senha)) {
        fazerLogin($email, $senha);
        header('Location: ' . url('perfil.php'));
        exit;
    } else {
        $erro = "Erro ao cadastrar. E-mail já existe ou dados inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= url('styles.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="auth-page">
    <img src="<?= url('logo.png') ?>" width="100" height="100" alt="Logo <?= SITE_NAME ?>">

    <h1><?= SITE_NAME ?></h1>

    <div class="auth-container">
        <h1><i class="fas fa-user-plus"></i> Cadastro</h1>

        <?php if (isset($erro)): ?>
            <div class="alert error"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nome"><i class="fas fa-user"></i> Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="senha"><i class="fas fa-lock"></i> Senha:</label>
                <input type="password" id="senha" name="senha" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirmar_senha"><i class="fas fa-lock"></i> Confirmar Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
            </div>

            <button type="submit" class="btn"><i class="fas fa-user-plus"></i> Cadastrar</button>
        </form>

        <p class="auth-link">
            Já tem conta? <a href="<?= url('login.php') ?>">Faça login</a>
        </p>
    </div>
    <!-- Blob Interativo -->
  <div id="blob"></div>
  <!-- Fundo Interativo -->
  <div id="interactive-background-container">
    <!-- Imagens copiadas do index.php (ou você pode definir novas) -->
    <img src="<?= url('assets/images/elemento1.png') ?>" class="interactive-bg-element" style="top: 20%; left: 10%; width: 80px; height: auto;">
    <img src="<?= url('assets/images/elemento2.png') ?>" class="interactive-bg-element" style="top: 75%; left: 85%; width: 100px; height: auto;">
    <img src="<?= url('assets/images/elemento3.png') ?>" class="interactive-bg-element" style="top: 50%; left: 45%; width: 120px; height: auto;">  </div>
  
  <script src="<?= url('blob.js') ?>"></script>
  <script src="<?= url('interactive-background.js') ?>"></script>
</body>

</html>