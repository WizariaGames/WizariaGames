<?php
require_once __DIR__ . '/config.php';

$jogo_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$jogo_id) {
    header('Location: ' . url('jogos.php'));
    exit;
}

// Fetch game details from the database
// Assuming 'jogos' table has: id, titulo, descricao, imagem (banner/thumbnail),
// genero, ano_lancamento, arquivo_jogo_js (e.g., 'html5game/MyGame.js'),
// largura_jogo (e.g., 640), altura_jogo (e.g., 360).
$stmt = $pdo->prepare("SELECT * FROM jogos WHERE id = ?");
$stmt->execute([$jogo_id]);
$jogo = $stmt->fetch();

if (!$jogo) {
    $page_title = "Jogo não encontrado";
    $error_message = "O jogo que você está procurando não foi encontrado.";
    // Consider setting a 404 header: http_response_code(404);
} else {
    $page_title = htmlspecialchars($jogo['titulo']);

    // Fetch reviews for this game
    $stmtReviews = $pdo->prepare("
        SELECT a.*, u.nome as usuario_nome 
        FROM avaliacoes a
        JOIN usuarios u ON a.usuario_id = u.id
        WHERE a.jogo_id = ?
        ORDER BY a.data_avaliacao DESC
    ");
    $stmtReviews->execute([$jogo_id]);
    $reviews = $stmtReviews->fetchAll();

    // Calculate average rating
    $average_rating = 0;
    $rating_count = count($reviews);
    if ($rating_count > 0) {
        $total_score = 0;
        foreach ($reviews as $review_item) { 
            $total_score += $review_item['nota'];
        }
        $average_rating = round($total_score / $rating_count, 1);
    }

    $is_favorited = false;
    $user_review = null; // User's own review for this game
    if (isset($_SESSION['user_id'])) {
        $stmtFav = $pdo->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND jogo_id = ?");
        $stmtFav->execute([$_SESSION['user_id'], $jogo_id]);
        if ($stmtFav->fetch()) {
            $is_favorited = true;
        }

        $stmtUserReview = $pdo->prepare("SELECT * FROM avaliacoes WHERE usuario_id = ? AND jogo_id = ?");
        $stmtUserReview->execute([$_SESSION['user_id'], $jogo_id]);
        $user_review = $stmtUserReview->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= isset($page_title) ? $page_title : 'Jogo' ?> - <?= SITE_NAME ?></title>
        <link rel="icon" href="<?= url('logo.png') ?>" type="image/png" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="<?= url('styles.css') ?>" />

        <?php if (isset($jogo) && !empty($jogo['arquivo_jogo_js'])): ?>
        <!-- Styles for GameMaker game embedding -->
        <style>
                        .game-embed-section .game-canvas-container {
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 20px 0;
                background: #000; /* Black background for the game area */
                padding: 0; /* No padding around canvas div */
                border-radius: 8px;
                overflow: hidden; /* Ensure canvas fits rounded corners if any */
            }
            /* This div is part of GameMaker's typical HTML structure */
            div.gm4html5_div_class {
                margin: 0px;
                padding: 0px;
                border: 0px;
                 display: flex; /* To center canvas if smaller than container */
                justify-content: center;
                align-items: center;
            }

            canvas {
                image-rendering: optimizeSpeed;
                -webkit-interpolation-mode: nearest-neighbor;
                -ms-touch-action: none;
                touch-action: none;
                margin: 0px;
                padding: 0px;
                border: 0px; /* GameMaker games usually don't have a border on canvas */
            }
            :-webkit-full-screen #canvas {
                width: 100%;
                height: 100%;
            }

        </style>

         <?php endif; ?>
    
    </head>


    

    <body>
        <header>
            <div class="container">
                <img src="<?= url('logo.png') ?>" width="80" height="80" alt="Logo <?= SITE_NAME ?>">
                <h1><?= SITE_NAME ?></h1>
                <nav>
                    <a href="<?= url('index.php') ?>">Início</a>
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

        <main class="container game-detail-page">
            <?php if (isset($error_message)): ?>
                <div class="alert error"><?= $error_message ?></div>
                <p><a href="<?= url('jogos.php') ?>" class="btn">Voltar para Jogos</a></p>
            <?php elseif (isset($jogo)): ?>
                <section class="game-header">
                    <h1><?= htmlspecialchars($jogo['titulo']) ?></h1>
                    <?php 
                    $banner_image_filename = $jogo['imagem'] ?? 'default_game_banner.png';
                    // Remove any leading 'images/' or 'games/' if they exist in the filename
                    $banner_image_filename = str_replace(['images/', 'games/'], '', $banner_image_filename);
                    $banner_image = asset('images/games/' . $banner_image_filename); // Default
                    if (!empty($jogo['imagem'])) {
                        $potential_banner_path = APP_ROOT . '/assets/images/games/' . $jogo['imagem'];
                        if (file_exists($potential_banner_path)) {
                            $banner_image = asset('images/games/' . $jogo['imagem']);
                        }
                    }
                    ?>
                    <img src="<?= $banner_image ?>" alt="Banner do jogo <?= htmlspecialchars($jogo['titulo']) ?>" class="game-banner">
                </section>

                <div class="game-layout-columns">
                    <section class="game-main-content">
                        <?php if (!empty($jogo['arquivo_jogo_js']) && !empty($jogo['largura_jogo']) && !empty($jogo['altura_jogo'])): ?>
                        <div class="game-embed-section">
                            <h2>Jogar Agora</h2>
                            <div class="game-canvas-container">
                                <div class="gm4html5_div_class" id="gm4html5_div_id">
                                    <canvas id="canvas" width="<?= (int)$jogo['largura_jogo'] ?>" height="<?= (int)$jogo['altura_jogo'] ?>">
                                        <p>Seu navegador não suporta HTML5 canvas.</p>
                                    </canvas>
                                </div>
                            </div>
                            <script type="text/javascript" src="<?= url($jogo['arquivo_jogo_js']) ?>?cachebust=<?= time() ?>"></script>
                        </div>
                        <?php else: ?>
                        <div class="game-embed-section">
                            <h2>Jogar Agora</h2>
                            <p>Prévia do jogo não disponível ou configuração incompleta.</p>
                        </div>
                        <?php endif; ?>

                        <div class="game-details">
                            <h2>Sobre o Jogo</h2>
                            <p><?= nl2br(htmlspecialchars($jogo['descricao'] ?? 'Nenhuma descrição disponível.')) ?></p>
                            <p><strong>Gênero:</strong> <?= htmlspecialchars($jogo['genero'] ?? 'Não especificado') ?></p>
                            <p><strong>Ano de Lançamento:</strong> <?= htmlspecialchars($jogo['ano_lancamento'] ?? 'Não especificado') ?></p>
                        </div>
                    </section>

                    <aside class="game-sidebar">
                        <div class="game-rating-summary">
                            <h3>Avaliação Média</h3>
                            <?php if ($rating_count > 0): ?>
                                <div class="stars">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="fas fa-star <?= $s <= floor($average_rating) ? 'active' : ($s - 0.5 <= $average_rating ? 'half-active' : '') ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p><?= $average_rating ?>/5 (<?= $rating_count ?> <?= $rating_count === 1 ? 'avaliação' : 'avaliações' ?>)</p>
                            <?php else: ?>
                                <p>Ainda não há avaliações.</p>
                            <?php endif; ?>
                        </div>

                        <div class="game-actions">
                            <h3>Ações</h3>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="<?= url('avaliar.php?id=' . $jogo['id']) ?>" class="btn btn-primary">
                                    <i class="fas fa-star"></i> <?= $user_review ? 'Editar sua Avaliação' : 'Avaliar este Jogo' ?>
                                </a>
                                <button id="btnToggleFavorite" class="btn <?= $is_favorited ? 'btn-danger' : 'btn-secondary' ?>" data-jogo-id="<?= $jogo['id'] ?>">
                                    <i class="fas <?= $is_favorited ? 'fa-heart' : 'fa-heart-broken' ?>"></i>
                                    <span id="favoriteText"><?= $is_favorited ? 'Remover dos Favoritos' : 'Adicionar aos Favoritos' ?></span>
                                </button>
                            <?php else: ?>
                                <p><a href="<?= url('login.php?redirect=' . urlencode(url('jogo.php?id=' . $jogo['id']))) ?>">Faça login</a> para avaliar ou favoritar.</p>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>

                <section class="game-reviews-section">
                    <h2>Avaliações dos Usuários</h2>
                    <?php if (empty($reviews)): ?>
                        <p>Este jogo ainda não possui avaliações. <?= isset($_SESSION['user_id']) ? 'Seja o primeiro a avaliar!' : '' ?></p>
                    <?php else: ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review_item): ?>
                                <article class="review-card">
                                    <div class="review-author">
                                        <i class="fas fa-user-circle fa-2x"></i>
                                        <div>
                                            <strong><?= htmlspecialchars($review_item['usuario_nome']) ?></strong>
                                            <span class="review-date"> - <?= date('d/m/Y H:i', strtotime($review_item['data_avaliacao'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="review-rating stars">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <i class="fas fa-star <?= $s <= $review_item['nota'] ? 'active' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if (!empty($review_item['comentario'])): ?>
                                        <p class="review-comment">"<?= nl2br(htmlspecialchars($review_item['comentario'])) ?>"</p>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review_item['usuario_id']): ?>
                                        <a href="<?= url('avaliar.php?id=' . $jogo['id']) ?>" class="btn btn-small btn-edit-review">Editar sua avaliação</a>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            <?php endif; // end if $jogo exists ?>
        </main>

        <footer>
            <div class="container">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> - Todos os direitos reservados.</p>
            </div>
        </footer>

        <div id="blob"></div>
        <div id="interactive-background-container">
            <img src="<?= url('assets/images/elemento1.png') ?>" class="interactive-bg-element" style="top: 20%; left: 10%; width: 80px; height: auto;">
            <img src="<?= url('assets/images/elemento2.png') ?>" class="interactive-bg-element" style="top: 75%; left: 85%; width: 100px; height: auto;">
        </div>
    
        <script src="<?= url('blob.js') ?>"></script> 
        <script src="<?= url('interactive-background.js') ?>"></script>
        
        <?php if (isset($jogo) && !empty($jogo['arquivo_jogo_js']) && !empty($jogo['largura_jogo']) && !empty($jogo['altura_jogo'])): ?>
        <script>
            // Standard GameMaker HTML5 initialization.
            if (typeof GameMaker_Init !== 'undefined') {
                window.onload = GameMaker_Init; 
            } else {
                console.error("GameMaker_Init function not found. The game may not start. Check game file: <?= htmlspecialchars($jogo['arquivo_jogo_js'] ?? 'N/A') ?>");
                const canvasContainer = document.querySelector('.game-canvas-container');
                if(canvasContainer) canvasContainer.innerHTML = "<p style='color:white; text-align:center;'>Erro ao carregar o jogo. Função de inicialização não encontrada.</p>";
            }
        </script>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && isset($jogo)): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const favButton = document.getElementById('btnToggleFavorite');
            if (favButton) {
                favButton.addEventListener('click', function() {
                    const jogoId = this.dataset.jogoId;
                    const favoriteTextSpan = document.getElementById('favoriteText');
                    const icon = this.querySelector('i');

                    // IMPORTANT: Create 'ajax/toggle_favorite.php' and implement CSRF protection.
                    // For CSRF, generate a token in PHP, store in session, pass with AJAX, validate on server.
                    // Example: const csrfToken = '<?= $_SESSION["csrf_token"] ?? "" ?>';
                    fetch('<?= url('ajax/toggle_favorite.php') ?>', { 
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        // body: 'jogo_id=' + jogoId + '&csrf_token=' + csrfToken
                        body: 'jogo_id=' + jogoId // Add CSRF token in a real application
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.isFavorited) {
                                favoriteTextSpan.textContent = 'Remover dos Favoritos';
                                favButton.classList.remove('btn-secondary');
                                favButton.classList.add('btn-danger');
                                icon.className = 'fas fa-heart';
                            } else {
                                favoriteTextSpan.textContent = 'Adicionar aos Favoritos';
                                favButton.classList.remove('btn-danger');
                                favButton.classList.add('btn-secondary');
                                icon.className = 'fas fa-heart-broken'; 
                            }
                        } else {
                            alert('Erro: ' + (data.message || 'Não foi possível atualizar o status de favorito.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error toggling favorite:', error);
                        alert('Erro de comunicação ao tentar favoritar.');
                    });
                });
            }
        });
        </script>
        <?php endif; ?>
        

    </body>


    
</html>
