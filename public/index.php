<?php

/**
 * Front Controller (Design Pattern: FRONT CONTROLLER)
 *
 * Este é o ÚNICO ponto de entrada da aplicação — todas as requisições passam
 * por aqui. Suas responsabilidades são:
 *
 *   1. Carregar as configurações.
 *   2. Registrar o autoloader das classes.
 *   3. Iniciar a sessão (com cookie endurecido) e o token CSRF.
 *   4. Detectar a BASE_URL dinamicamente.
 *   5. Definir funções utilitárias globais (e(), url(), csrf_field()).
 *   6. Delegar o roteamento para a classe Router.
 *
 * O ideal é que o DocumentRoot do Apache aponte para a pasta public/, deixando
 * o restante do código (app/, config/, sql/) fora do alcance do navegador. Como
 * fallback para instalações em subpasta do htdocs, essas pastas também trazem um
 * arquivo .htaccess que bloqueia o acesso direto via URL.
 */

// Caminho absoluto até a pasta app/ (usado pelo autoloader e pelas views).
define('APP_PATH', dirname(__DIR__) . '/app');

// 1. Carrega as constantes de configuração (dados de conexão com o banco).
require_once dirname(__DIR__) . '/config/config.php';

// 2. Autoloader simples: localiza as classes nas pastas de app/ pelo nome do
//    arquivo (ex.: a classe "TaskController" está em controllers/TaskController.php).
spl_autoload_register(function (string $class): void {
    $directories = ['core', 'controllers', 'models', 'repositories'];

    foreach ($directories as $dir) {
        $file = APP_PATH . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

// 3. Inicia a sessão (usada na autenticação e nas mensagens flash).
//    Antes de iniciar, reforça a segurança do cookie de sessão:
//      - HttpOnly: o cookie não é acessível via JavaScript (mitiga roubo por XSS).
//      - SameSite=Lax: o cookie não é enviado em requisições cross-site (mitiga CSRF).
//      - Secure: enviado apenas via HTTPS quando a conexão for segura.
//      - use_strict_mode: o PHP rejeita IDs de sessão forjados (mitiga session fixation).
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
]);
session_start();

// Gera um token CSRF único por sessão, usado para validar os formulários.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 4. Detecta a BASE_URL dinamicamente a partir de $_SERVER, de modo que os
//    links funcionem independentemente da pasta em que o projeto estiver no
//    htdocs (ex.: http://localhost/todo-php/public).
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $protocol . '://' . $host . $basePath);

// 5. Funções utilitárias globais ------------------------------------------------

/**
 * Escapa um valor para saída segura em HTML (proteção contra XSS).
 * Deve envolver toda saída de dados que venha do usuário.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Monta a URL completa de uma rota interna da aplicação.
 * Ex.: url('task/create') => http://localhost/todo-php/public/index.php?r=task/create
 */
function url(string $route = ''): string
{
    $base = BASE_URL . '/index.php';

    return $route === '' ? $base : $base . '?r=' . $route;
}

/**
 * Devolve o token CSRF da sessão atual.
 */
function csrf_token(): string
{
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Gera o campo oculto com o token CSRF para embutir nos formulários POST.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

// Tratador global de exceções: evita expor stack traces ao usuário e exibe uma
// página de erro genérica (importante porque o XAMPP costuma vir com a diretiva
// display_errors ligada).
set_exception_handler(function (Throwable $e): void {
    http_response_code(500);
    error_log('Erro não tratado: ' . $e->getMessage());
    echo '<!doctype html><html lang="pt-br"><meta charset="utf-8">'
        . '<title>Erro</title><h1>500 &mdash; Erro interno</h1>'
        . '<p>Ocorreu um erro inesperado. Tente novamente mais tarde.</p></html>';
});

// 6. Resolve a rota e despacha o controller/ação correspondente.
//    Normaliza o parâmetro de rota: se vier como array (?r[]=x), trata como vazio
//    para evitar um TypeError (Router::dispatch espera uma string).
$route = $_GET['r'] ?? '';
if (!is_string($route)) {
    $route = '';
}

$router = new Router();
$router->dispatch($route);
