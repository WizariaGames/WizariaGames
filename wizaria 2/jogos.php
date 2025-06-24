<?php require_once __DIR__ . '/config.php'; ?>

<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jogos - <?= SITE_NAME ?></title>
  <link rel="icon" href="<?= url('logo.png') ?>" type="image/png" />
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
        <a href="<?= url('sobre.php') ?>">Sobre</a>
        <a href="<?= url('jogos.php') ?>" class="active">Jogos</a>
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="<?= url('perfil.php') ?>"><i class="fas fa-user"></i> Perfil</a>
          <a href="<?= url('logout.php') ?>"><i class="fas fa-sign-out-alt"></i> Sair</a>
        <?php else: ?>
          <a href="<?= url('login.php') ?>"><i class="fas fa-sign-in-alt"></i> Login</a>

        <?php endif; ?>
      </nav>
    </div>
  </header>


  <main class="container games-page-content">
    <h2>Nossos Jogos</h2>

    <div class="search-filter-bar">
      <input type="text" placeholder="Buscar jogo...">
      <select name="category">
        <option value="">Todas as Categorias</option>
        <option value="acao">Ação</option>
        <option value="aventura">Aventura</option>
        <!-- Outras categorias -->
      </select>
      <button class="btn-filter">Filtrar</button>
    </div>

    <section class="games-list">
      <!-- Seus .game-card entram aqui -->
      <article class="game-card">
        <h3>Nome do Jogo 1</h3>
        <p>Descrição breve do jogo.</p>
        <a href="#" class="btn btn-play">Jogar</a>
      </article>
      <article class="game-card">
        <h3>Nome do Jogo 2</h3>
        <p>Descrição breve do jogo.</p>
        <a href="#" class="btn btn-play">Jogar</a>
      </article>
      <!-- Mais cards de jogos -->
    </section>

    <nav class="pagination" aria-label="Paginação de jogos">
      <a href="#">Anterior</a>
      <a href="#" class="active">1</a>
      <a href="#">2</a>
      <a href="#">3</a>
      <span>...</span>
      <a href="#">10</a>
      <a href="#">Próxima</a>
    </nav>

  </main>

  <footer>
    <div class="container">
      <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> - Todos os direitos reservados.</p>
    </div>
  </footer>
  <!-- Blob Interativo -->
  <div id="blob"></div>
  <!-- Fundo Interativo -->
  <div id="interactive-background-container">
    <!-- Adicione suas <img> com class="interactive-bg-element" aqui, como no index.php -->
  </div>
 
 
  <script src="<?= url('blob.js') ?>"></script> 
  <script src="<?= url('interactive-background.js') ?>"></script> <!-- Novo script -->


</body>

</html>
