
<?php
require_once __DIR__ . '/includes/funcoes.php';
// Verifica se já foi incluído
if (defined('NETJOGOS_CONFIG')) {
    return;
}
define('NETJOGOS_CONFIG', true);

// Configurações básicas
define('SITE_NAME', 'Wizaria Games');
define('SITE_URL', 'http://localhost/wizaria%202');
define('APP_ROOT', __DIR__);
define('DEBUG_MODE', true);

// Configurações de Sessão
session_name('WEBJOGOS_SESSION');
session_start();

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'webjogos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3307');
define('DB_CHARSET', 'utf8mb4');

// Conexão PDO
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=".DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Funções auxiliares
if (!function_exists('url')) {
    function url($path = '') {
        return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset($path = '') {
        return url('assets/' . ltrim($path, '/'));
    }
}

// Inclui funções principais

?>