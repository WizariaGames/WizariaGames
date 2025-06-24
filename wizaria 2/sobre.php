<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <link rel="icon" href="<?= url('logo.png') ?>" type="image/png" />
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sobre - <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= url('styles.css') ?>" />
</head>

<body>
  <header>
    <div class="container">
      <img src="<?= url('logo.png') ?>" width="80" height="80" alt="Logo <?= SITE_NAME ?>">
      <h1><?= SITE_NAME ?></h1>
      <nav>
        <a href="<?= url('index.php') ?>">Início</a>
        <a href="<?= url('sobre.php') ?>" class="active">Sobre</a>
        <a href="<?= url('jogos.php') ?>">Jogos</a>


        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="<?= url('perfil.php') ?>"><i class="fas fa-user"></i> Perfil</a>
          <a href="<?= url('logout.php') ?>"><i class="fas fa-sign-out-alt"></i> Sair</a>
        <?php else: ?>
          <a href="<?= url('login.php') ?>"><i class="fas fa-sign-in-alt"></i> Login</a>

        <?php endif; ?>

      </nav>
    </div>
  </header>

  <section class="hero">
    <div class="container">
      <h2>Sobre a Wizaria Games</h2>
      <p>Somos uma equipe apaixonada por criar experiências de jogo únicas e autorais.</p> <!-- Seu conteúdo sobre aqui -->
    </div>
  </section>

  <footer>
    <div class="container">
      <p>&copy; 2025 Wizaria Games - Todos os direitos reservados.</p>
    </div>
  </footer>
  <!-- Blob Interativo -->
  <div id="blob"></div>
  <!-- Fundo Interativo -->
  <div id="interactive-background-container">
    <!-- Substitua pelos caminhos das suas imagens e ajuste os estilos inline como quiser -->
    <!-- Exemplo: Ajuste top, left, width, height para cada imagem -->
    <img src="<?= url('imagens-interactive/anao.png') ?>" class="interactive-bg-element" style="top: 75%; left: 10%; width:  550px; height: auto;">
    <img src="<?= url('imagens-interactive/arqueira.png') ?>" class="interactive-bg-element" style="top: 65%; left: 80%; width: 500px; height: auto;">
    <img src="<?= url('imagens-interactive/largatixocerto.png') ?>" class="interactive-bg-element" style="top: 70%; left: 60%; width: 600px; height: auto;">
    <img src="<?= url('imagens-interactive/jorlancerto.png') ?>" class="interactive-bg-element" style="top: 60%; left: 15%; width: 550px; height: auto;">
    <img src="<?= url('imagens-interactive/ogro.png') ?>" class="interactive-bg-element" style="top: 50%; left: 5%; width: 450px; height: auto;">
    <img src="<?= url('imagens-interactive/mago.png') ?>" class="interactive-bg-element" style="top: 70%; left: 37.5%; width: 500px; height: auto;">
    <img src="<?= url('imagens-interactive/petux.png') ?>" class="interactive-bg-element" style="top: 50%; left: 67%; width: 600px; height: auto;">
    <img src="<?= url('imagens-interactive/ork.png') ?>" class="interactive-bg-element" style="top: 70%; left: 3%; width: 450px; height: auto;">
    <img src="<?= url('imagens-interactive/legolassembosta.png') ?>" class="interactive-bg-element" style="top: 60%; left: -10%; width: 600px; height: auto;">

    <!-- Adicione mais elementos conforme necessário -->
  </div>

 
  <script src="<?= url('blob.js') ?>"></script>
  <script src="<?= url('interactive-background.js') ?>"></script> <!-- Novo script -->
</body>

</html>