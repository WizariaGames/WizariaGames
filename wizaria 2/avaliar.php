<?php
require_once __DIR__ . '/config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . url('login.php'));
    exit;
}

// Obtém o ID do jogo da URL
$jogo_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$jogo_id) {
    header('Location: ' . url('index.php'));
    exit;
}

// Busca informações do jogo
$stmt = $pdo->prepare("SELECT id, titulo FROM jogos WHERE id = ?");
$stmt->execute([$jogo_id]);
$jogo = $stmt->fetch();

if (!$jogo) {
    header('Location: ' . url('index.php'));
    exit;
}

// Processa o formulário de avaliação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nota = filter_input(INPUT_POST, 'nota', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 5]
    ]);
    $comentario = filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($nota) {
        try {
            // Verifica se já existe avaliação do usuário para este jogo
            $stmt = $pdo->prepare("SELECT id FROM avaliacoes WHERE usuario_id = ? AND jogo_id = ?");
            $stmt->execute([$_SESSION['user_id'], $jogo_id]);
            
            if ($stmt->rowCount() > 0) {
                // Atualiza avaliação existente
                $stmt = $pdo->prepare("UPDATE avaliacoes SET nota = ?, comentario = ? WHERE usuario_id = ? AND jogo_id = ?");
                $stmt->execute([$nota, $comentario, $_SESSION['user_id'], $jogo_id]);
                $mensagem = "Avaliação atualizada com sucesso!";
            } else {
                // Cria nova avaliação
                $stmt = $pdo->prepare("INSERT INTO avaliacoes (usuario_id, jogo_id, nota, comentario) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $jogo_id, $nota, $comentario]);
                $mensagem = "Avaliação enviada com sucesso!";
            }
            
            // Redireciona com mensagem de sucesso
            $_SESSION['mensagem'] = $mensagem;
            //header('Location: ' . url('detalhes.php?id=' . $jogo_id));
            header('Location: ' . url('perfil.php')); 
            exit;
            
        } catch (PDOException $e) {
            $erro = "Erro ao processar avaliação. Por favor, tente novamente.";
            if (DEBUG_MODE) {
                error_log("Erro na avaliação: " . $e->getMessage());
            }
        }
    } else {
        $erro = "Por favor, selecione uma nota válida (1 a 5 estrelas).";
    }
}

// Busca avaliação existente do usuário (se houver)
$avaliacao_usuario = null;
$stmt = $pdo->prepare("SELECT nota, comentario FROM avaliacoes WHERE usuario_id = ? AND jogo_id = ?");
$stmt->execute([$_SESSION['user_id'], $jogo_id]);
$avaliacao_usuario = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar <?= htmlspecialchars($jogo['titulo']) ?> - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= url('styles.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <h1><?= SITE_NAME ?></h1>
        <nav>
            <a href="<?= url('index.php') ?>"><i class="fas fa-home"></i> Início</a>
            <a href="<?= url('perfil.php') ?>"><i class="fas fa-user"></i> Perfil</a>
        </nav>
    </header>

    <main class="avaliar-container">
        <h1>Avaliar: <?= htmlspecialchars($jogo['titulo']) ?></h1>
        
        <?php if (isset($erro)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="form-avaliar">
            <div class="avaliacao-estrelas">
                <label>Sua avaliação:</label>
                <div class="estrelas">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="estrela<?= $i ?>" name="nota" value="<?= $i ?>" 
                            <?= ($avaliacao_usuario && $avaliacao_usuario['nota'] == $i) ? 'checked' : '' ?>>
                        <label for="estrela<?= $i ?>"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="comentario">Comentário (opcional):</label>
                <textarea id="comentario" name="comentario" rows="4"><?= 
                    $avaliacao_usuario['comentario'] ?? '' 
                ?></textarea>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-paper-plane"></i> Enviar Avaliação
            </button>
            
            <a href="<?= url('detalhes.php?id=' . $jogo_id) ?>" class="btn btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </form>
    </main>

    <!-- Blob Interativo -->
    <div id="blob"></div>
    <!-- Fundo Interativo -->
    <div id="interactive-background-container">
        <!-- Adicione suas <img> com class="interactive-bg-element" aqui, como no index.php -->
    </div>

    <script src="<?= url('blob.js') ?>"></script>
    <script src="<?= url('interactive-background.js') ?>"></script>

    <script>
        // Efeito interativo nas estrelas
        document.querySelectorAll('.estrelas input').forEach(radio => {
            radio.addEventListener('change', function() {
                const estrelas = document.querySelectorAll('.estrelas label i');
                const nota = parseInt(this.value);
                
                estrelas.forEach((estrela, index) => {
                    if (index < nota) {
                        estrela.style.color = '#FFD700';
                    } else {
                        estrela.style.color = '#ccc';
                    }
                });
            });
        });
        
        // Inicializa as estrelas se já houver avaliação
        document.addEventListener('DOMContentLoaded', function() {
            const notaSelecionada = document.querySelector('.estrelas input:checked');
            if (notaSelecionada) {
                notaSelecionada.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>