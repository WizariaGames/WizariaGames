<?php 
require_once __DIR__ . '/config.php'; 

// Busca os jogos em destaque do banco de dados.
// Por enquanto, vamos pegar os 4 jogos mais recentes.
$stmt = $pdo->query("SELECT * FROM jogos ORDER BY id DESC LIMIT 4");
$featured_games = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= SITE_NAME ?> - Cyborg Inspired</title>
  <link rel="icon" href="<?= url('logo.png') ?>" type="image/png" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= url('styles.css') ?>" />

  <!-- Owl Carousel CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
</head>

<body>
  <header>
    <div class="container">
      <img src="<?= url('logo.png') ?>" width="80" height="80" alt="Logo <?= SITE_NAME ?>">
      <h1><?= SITE_NAME ?></h1>

      <nav>
        <a href="<?= url('index.php') ?>" class="active">Início</a>
        <a href="<?= url('sobre.php') ?>">Sobre</a>
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
      <h2>Explore o universo dos nossos jogos</h2>
      <p>Autoriais, criativos e cheios de ação!</p>
      <a href="<?= url('jogos.php') ?>" class="btn">Ver Todos os Jogos</a> <!-- Alterado para link direto -->
    </div>
  </section>

  <section class="featured-games container">
    <h2>Jogos em Destaque</h2>
    <div class="owl-carousel owl-theme">
      <?php foreach ($featured_games as $jogo): ?>
        <div class="item">
          <article class="game-card">
            <div class="game-card-image-container">
              <?php 
                // Define uma imagem padrão caso a do jogo não seja encontrada
                $image_filename = $jogo['imagem'] ?? 'default_game_banner.png';
                // Remove any leading 'images/' or 'games/' if they exist in the filename
                $image_filename = str_replace(['images/', 'games/'], '', $image_filename);
                $image_path = asset('images/games/' . $image_filename);
                // --- DEBUG START ---
                // echo "DEBUG: Image Filename: " . $image_filename . "<br>";
                // echo "DEBUG: Image Path: " . $image_path . "<br>";
                $image_alt = htmlspecialchars($jogo['titulo']);
              ?>
              <img src="<?= $image_path ?>" alt="Imagem do Jogo <?= $image_alt ?>">
            </div>
            <h3><?= htmlspecialchars($jogo['titulo']) ?></h3>
            <p><?= htmlspecialchars(substr($jogo['descricao'], 0, 50)) ?>...</p>
            
            <!-- O link agora aponta para jogo.php, que vai carregar o jogo corretamente -->
            <a href="<?= url('jogo.php?id=' . $jogo['id']) ?>" class="btn btn-play">Jogar</a>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </section>



  <footer>
    <div class="container">
      <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> - Todos os direitos reservados.</p>
    </div>
  </footer>
  <!-- Blob Interativo -->
  <div id="blob"></div>





  <!-- jQuery (necessário para Owl Carousel) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <!-- Owl Carousel JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

  <script src="<?= url('blob.js') ?>"></script>
  <script src="<?= url('interactive-background.js') ?>"></script> <!-- Novo script -->

  <script>
    $(document).ready(function () {
      $(".owl-carousel").owlCarousel({
        loop: true,
        margin: 20, // Espaçamento entre os cards
        nav: true,    // Habilita botões de navegação (anterior/próximo)
        dots: true,   // Habilita os pontos de paginação
        navText: ["<i class='fas fa-chevron-left'></i>", "<i class='fas fa-chevron-right'></i>"], // Ícones personalizados para navegação
        autoplay: true, // Inicia o carrossel automaticamente
        autoplayTimeout: 5000, // Tempo entre slides (5 segundos)
        autoplayHoverPause: true, // Pausa ao passar o mouse
        responsive: {
          0: { items: 1 },       // 1 item em telas pequenas
          768: { items: 2 },      // 2 itens em telas médias
          1000: { items: 3 }      // 3 itens em telas maiores (ajuste conforme o tamanho dos seus cards)
        }
      });
    });
  </script>

</body>

</html>