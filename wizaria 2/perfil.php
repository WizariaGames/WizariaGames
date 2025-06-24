<?php

require_once __DIR__ . '/config.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: ' . url('login.php'));
    exit;
}

// Busca dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch();

// Busca jogos favoritos
$stmtFavoritos = $pdo->prepare("
    SELECT j.* FROM jogos j
    JOIN favoritos f ON j.id = f.jogo_id
    WHERE f.usuario_id = ?
");
$stmtFavoritos->execute([$_SESSION['user_id']]);
$favoritos = $stmtFavoritos->fetchAll();

// Busca avaliações
$stmtAvaliacoes = $pdo->prepare("
    SELECT a.*, j.titulo, j.imagem FROM avaliacoes a
    JOIN jogos j ON a.jogo_id = j.id
    WHERE a.usuario_id = ?
    ORDER BY a.data_avaliacao DESC
");
$stmtAvaliacoes->execute([$_SESSION['user_id']]);
$avaliacoes = $stmtAvaliacoes->fetchAll();

// Busca jogos não avaliados para sugerir avaliação
$stmtNaoAvaliados = $pdo->prepare("
    SELECT j.* FROM jogos j
    LEFT JOIN avaliacoes a ON j.id = a.jogo_id AND a.usuario_id = ?
    WHERE a.id IS NULL AND j.id NOT IN (SELECT jogo_id FROM favoritos WHERE usuario_id = ?) /* Para não sugerir favoritos já listados */
    LIMIT 5
");
$stmtNaoAvaliados->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$naoAvaliados = $stmtNaoAvaliados->fetchAll();

$uploadMessage = '';
$uploadError = false;

// Processamento do upload da foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['nova_foto_perfil'])) {
    $targetDir = __DIR__ . "/uploads/profile_pictures/";
    $fileName = basename($_FILES["nova_foto_perfil"]["name"]);
    $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid('user_' . $_SESSION['user_id'] . '_', true) . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;

    // Verifica se é uma imagem real
    $check = getimagesize($_FILES["nova_foto_perfil"]["tmp_name"]);
    if ($check === false) {
        $uploadMessage = "O arquivo não é uma imagem válida.";
        $uploadError = true;
    }
    // Verifica o tamanho do arquivo (ex: máximo 2MB)
    elseif ($_FILES["nova_foto_perfil"]["size"] > 2000000) {
        $uploadMessage = "Desculpe, sua foto é muito grande (máximo 2MB).";
        $uploadError = true;
    }
    // Permite certos formatos de arquivo
    elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        $uploadMessage = "Desculpe, apenas arquivos JPG, JPEG, PNG & GIF são permitidos.";
        $uploadError = true;
    }
    // Tenta fazer o upload
    elseif (move_uploaded_file($_FILES["nova_foto_perfil"]["tmp_name"], $targetFile)) {
        // Deleta a foto antiga se existir
        if (!empty($usuario['foto_perfil']) && file_exists($targetDir . $usuario['foto_perfil'])) {
            unlink($targetDir . $usuario['foto_perfil']);
        }
        // Atualiza o banco de dados
        $stmtUpdateFoto = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
        $stmtUpdateFoto->execute([$newFileName, $_SESSION['user_id']]);
        $usuario['foto_perfil'] = $newFileName; // Atualiza a variável local para exibição imediata
        $uploadMessage = "Sua foto de perfil foi atualizada com sucesso!";
    } else {
        $uploadMessage = "Desculpe, houve um erro ao enviar sua foto.";
        $uploadError = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Perfil - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= url('styles.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <div class="container">
            <img src="<?= url('logo.png') ?>" width="80" height="80" alt="Logo <?= SITE_NAME ?>">

            <h1><?= SITE_NAME ?></h1>
            <nav>
                <a href="<?= url('index.php') ?>"><i class="fas fa-home"></i> Início</a>
                <a href="<?= url('logout.php') ?>"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </nav>
    </header>

    <main class="perfil-container">
        <section class="dados-usuario">
            <h2><i class="fas fa-user"></i> Meu Perfil</h2>
            <div class="perfil-header">
                <div class="foto-perfil-container">
                    <?php
                    $fotoPerfil = asset('images/default_avatar.png'); // Imagem padrão
                    if (!empty($usuario['foto_perfil']) && file_exists(__DIR__ . '/uploads/profile_pictures/' . $usuario['foto_perfil'])) {
                        $fotoPerfil = url('uploads/profile_pictures/' . $usuario['foto_perfil']);
                    }
                    ?>
                    <img src="<?= $fotoPerfil ?>" alt="Foto de Perfil de <?= htmlspecialchars($usuario['nome']) ?>" class="foto-perfil">
                </div>
                <div class="info-usuario">
                    <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?></p>
                    <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
                    <p><strong>Membro desde:</strong> <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?></p>
                </div>
            </div>
            <form action="<?= url('perfil.php') ?>" method="POST" enctype="multipart/form-data" class="form-upload-foto">
                <label for="nova_foto_perfil">Alterar foto de perfil:</label>
                <input type="file" name="nova_foto_perfil" id="nova_foto_perfil" accept="image/png, image/jpeg, image/gif" required>
                <button type="submit" class="btn-upload-foto"><i class="fas fa-upload"></i> Enviar Nova Foto</button>
            </form>
            <?php if ($uploadMessage): ?>
                <p class="upload-message <?= $uploadError ? 'error' : 'success' ?>"><?= $uploadMessage ?></p>
            <?php endif; ?>
            </div>
        </section>

        <!-- Seção de Sugestão para Avaliar -->
        <?php if (!empty($naoAvaliados)): ?>
            <section class="sugestao-avaliar">
                <h2><i class="fas fa-edit"></i> Avalie esses jogos</h2>
                <div class="lista-jogos">
                    <?php foreach ($naoAvaliados as $jogo): ?>
                        <div class="jogo-card">
                            <?php
                                $image_filename = $jogo['imagem'] ?? 'default_game_banner.png';
                                $image_filename = str_replace(['images/', 'games/'], '', $image_filename);
                            ?>
                            <img src="<?= asset('images/games/' . $image_filename) ?>" alt="<?= htmlspecialchars($jogo['titulo']) ?>">
                            <h3><?= htmlspecialchars($jogo['titulo']) ?></h3>
                            <a href="<?= url('avaliar.php?id=' . $jogo['id']) ?>" class="btn-avaliar">
                                <i class="fas fa-star"></i> Avaliar
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Seção de Favoritos -->
        <section class="favoritos">
            <h2><i class="fas fa-heart"></i> Meus Favoritos</h2>
            <?php if (empty($favoritos)): ?>
                <p>Você ainda não favoritou nenhum jogo.</p>
            <?php else: ?>
                <div class="lista-jogos">
                    <?php foreach ($favoritos as $jogo): ?>
                        <div class="jogo-card">
                            <?php
                                $image_filename = $jogo['imagem'] ?? 'default_game_banner.png';
                                $image_filename = str_replace(['images/', 'games/'], '', $image_filename);
                                // --- DEBUG START ---
                                // echo "DEBUG: Image Filename: " . $image_filename . "<br>";
                                // echo "DEBUG: Image Path: " . asset('images/games/' . $image_filename) . "<br>";
                            ?>
                            <img src="<?= asset('images/games/' . $image_filename) ?>" alt="<?= htmlspecialchars($jogo['titulo']) ?>">
                            <h3><?= htmlspecialchars($jogo['titulo']) ?></h3>
                            <div class="jogo-info">
                                <span class="genero"><?= htmlspecialchars($jogo['genero']) ?></span>
                                <span class="ano"><?= $jogo['ano_lancamento'] ?></span>
                            </div>
                            <div class="jogo-actions">
                                <a href="<?= url('jogo.php?id=' . $jogo['id']) ?>" class="btn-detalhes">Ver detalhes</a>
                                <a href="<?= url('avaliar.php?id=' . $jogo['id']) ?>" class="btn-avaliar">
                                    <i class="fas fa-star"></i> Avaliar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Seção de Avaliações -->
        <section class="avaliacoes">
            <h2><i class="fas fa-star"></i> Minhas Avaliações</h2>
            <?php if (empty($avaliacoes)): ?>
                <p>Você ainda não avaliou nenhum jogo.</p>
            <?php else: ?>
                <div class="lista-avaliacoes">
                    <?php foreach ($avaliacoes as $avaliacao): ?>
                        <div class="avaliacao-card">
                            <div class="avaliacao-header">
                                <?php
                                    $image_filename = $avaliacao['imagem'] ?? 'default_game_banner.png';
                                    $image_filename = str_replace(['images/', 'games/'], '', $image_filename);
                                ?>
                                <img src="<?= asset('images/games/' . $image_filename) ?>" alt="<?= htmlspecialchars($avaliacao['titulo']) ?>" class="avaliacao-jogo-img">
                                <h3><?= htmlspecialchars($avaliacao['titulo']) ?></h3>
                            </div>
                            <div class="nota">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $avaliacao['nota'] ? 'active' : '' ?>"></i>
                                <?php endfor; ?>
                                <span class="nota-valor"><?= $avaliacao['nota'] ?>/5</span>
                            </div>
                            <?php if ($avaliacao['comentario']): ?>
                                <p class="comentario">"<?= htmlspecialchars($avaliacao['comentario']) ?>"</p>
                            <?php endif; ?>
                            <small>Avaliado em: <?= date('d/m/Y', strtotime($avaliacao['data_avaliacao'])) ?></small>
                            <a href="<?= url('avaliar.php?id=' . $avaliacao['jogo_id']) ?>" class="btn-editar-avaliacao">
                                <i class="fas fa-edit"></i> Editar Avaliação
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Todos os direitos reservados.</p>
    </footer>
<!-- Blob Interativo -->
  <div id="blob"></div>
  <!-- Fundo Interativo -->
  <div id="interactive-background-container">
    <!-- Adicione suas <img> com class="interactive-bg-element" aqui, como no index.php -->
  </div>
  
  <script src="<?= url('blob.js') ?>"></script>
  <script src="<?= url('interactive-background.js') ?>"></script>
</body>

</html>