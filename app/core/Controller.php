<?php

/**
 * Controller (base)
 *
 * Classe abstrata herdada por todos os controllers. Reúne as funcionalidades
 * comuns da camada de controle no padrão MVC:
 *
 *   - render()      -> renderiza uma view dentro do layout padrão (header/footer).
 *   - redirect()    -> redireciona para uma rota interna da aplicação.
 *   - requireAuth() -> protege rotas que exigem usuário autenticado.
 *   - setFlash()    -> registra mensagens de sucesso/erro (flash messages).
 */
abstract class Controller
{
    /**
     * Renderiza uma view dentro do layout (cabeçalho + conteúdo + rodapé).
     *
     * @param string $view Caminho relativo da view, sem extensão (ex.: 'auth/login').
     * @param array  $data Variáveis que ficarão disponíveis dentro da view.
     */
    protected function render(string $view, array $data = []): void
    {
        $viewFile = APP_PATH . '/views/' . $view . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            echo 'View não encontrada: ' . e($view);
            return;
        }

        // Transforma as chaves do array em variáveis locais (ex.: 'tasks' => $tasks),
        // tornando-as acessíveis dentro do arquivo da view.
        extract($data);

        require APP_PATH . '/views/layouts/header.php';
        require $viewFile;
        require APP_PATH . '/views/layouts/footer.php';
    }

    /**
     * Redireciona para uma rota interna e encerra a execução do script.
     */
    protected function redirect(string $route = ''): void
    {
        header('Location: ' . url($route));
        exit;
    }

    /**
     * Proteção de rotas: se não houver usuário autenticado na sessão, registra
     * uma mensagem e redireciona para a tela de login.
     */
    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->setFlash('error', 'Você precisa estar logado para acessar essa página.');
            $this->redirect('auth/login');
        }
    }

    /**
     * Registra uma mensagem flash na sessão, exibida na próxima requisição.
     *
     * @param string $type    Tipo da mensagem: 'success' ou 'error'.
     * @param string $message Texto a ser exibido ao usuário.
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    /**
     * Garante que a requisição seja do tipo POST. Em caso contrário (ex.: alguém
     * acessou por GET uma ação que altera estado), registra um aviso e redireciona.
     * Usado nas ações destrutivas (excluir, alternar status, logout).
     */
    protected function requirePost(string $redirectTo = 'dashboard/index'): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->setFlash('error', 'Ação inválida.');
            $this->redirect($redirectTo);
        }
    }

    /**
     * Valida o token CSRF enviado no formulário contra o token guardado na sessão.
     * Protege as ações que alteram estado contra requisições forjadas (CSRF).
     * A comparação usa hash_equals() para evitar ataques de timing.
     */
    protected function verifyCsrf(string $redirectTo = 'dashboard/index'): void
    {
        $token   = $_POST['_csrf'] ?? '';
        $session = $_SESSION['csrf_token'] ?? '';

        if (!is_string($token) || $token === '' || !hash_equals($session, $token)) {
            $this->setFlash('error', 'Sessão expirada ou requisição inválida. Tente novamente.');
            $this->redirect($redirectTo);
        }
    }
}
