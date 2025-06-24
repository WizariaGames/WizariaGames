<?php
if (defined('NETJOGOS_FUNCOES')) {
    return;
}
define('NETJOGOS_FUNCOES', true);

function registrarUsuario($nome, $email, $senha) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            return false;
        }

        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute([
            htmlspecialchars($nome),
            filter_var($email, FILTER_SANITIZE_EMAIL),
            password_hash($senha, PASSWORD_BCRYPT)
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        if (DEBUG_MODE) error_log("Erro ao registrar: " . $e->getMessage());
        return false;
    }
}

function fazerLogin($email, $senha) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([filter_var($email, FILTER_SANITIZE_EMAIL)]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            return true;
        }
        return false;
    } catch (PDOException $e) {
        if (DEBUG_MODE) error_log("Erro no login: " . $e->getMessage());
        return false;
    }
}

function getJogos($genero = null, $busca = null) {
    global $pdo;
    
    $sql = "SELECT * FROM jogos WHERE 1=1";
    $params = [];
    
    if ($genero) {
        $sql .= " AND genero = ?";
        $params[] = htmlspecialchars($genero);
    }
    
    if ($busca) {
        $sql .= " AND (titulo LIKE ? OR descricao LIKE ?)";
        $searchTerm = "%".htmlspecialchars($busca)."%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        if (DEBUG_MODE) error_log("Erro ao buscar jogos: " . $e->getMessage());
        return [];
    }
}
?>