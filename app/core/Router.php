<?php

/**
 * Router
 *
 * Resolve a rota recebida via query string no formato ?r=controller/acao
 * e despacha a execução para o controller e a ação correspondentes.
 *
 * O roteamento por query string dispensa o uso de mod_rewrite, funcionando
 * em qualquer instalação do XAMPP sem configuração extra do Apache.
 *
 * Exemplos de rotas:
 *   ?r=auth/login        -> AuthController::login()
 *   ?r=dashboard/index   -> DashboardController::index()
 *   ?r=task/create       -> TaskController::create()
 */
class Router
{
    /**
     * Recebe a rota ("controller/acao") e executa a ação correspondente.
     */
    public function dispatch(string $route): void
    {
        // Rota padrão quando nenhuma é informada na URL.
        if (trim($route) === '') {
            $route = 'auth/login';
        }

        // Separa a rota em controller e ação. A ação é opcional (padrão: index).
        $parts = explode('/', $route);
        // Normaliza para minúsculas, deixando as rotas canônicas (ex.: TASK == task).
        $controller = strtolower($parts[0] !== '' ? $parts[0] : 'auth');
        $action     = strtolower(isset($parts[1]) && $parts[1] !== '' ? $parts[1] : 'index');

        // Converte o nome da rota para o nome da classe: "task" -> "TaskController".
        $controllerClass = ucfirst($controller) . 'Controller';

        // O controller precisa existir (o autoloader tenta carregá-lo aqui).
        if (!class_exists($controllerClass)) {
            $this->notFound();
            return;
        }

        // A ação precisa existir e ser um método público "comum" (evita expor
        // métodos internos como render/redirect ou métodos mágicos como __construct).
        if (!method_exists($controllerClass, $action) || strncmp($action, '__', 2) === 0) {
            $this->notFound();
            return;
        }

        $reflection = new ReflectionMethod($controllerClass, $action);
        if (!$reflection->isPublic() || $reflection->isStatic()) {
            $this->notFound();
            return;
        }

        // Instancia o controller e invoca a ação solicitada.
        $instance = new $controllerClass();
        $instance->$action();
    }

    /**
     * Responde com um erro 404 (página não encontrada).
     */
    private function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404</h1><p>Página não encontrada.</p>';
    }
}
