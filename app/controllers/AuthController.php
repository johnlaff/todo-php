<?php

/**
 * AuthController
 *
 * Responsável pela autenticação: cadastro de novos usuários, login e logout.
 * Faz parte da camada de CONTROLE do padrão MVC.
 */
class AuthController extends Controller
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    /**
     * Exibe o formulário de login (GET) ou processa as credenciais (POST).
     */
    public function login(): void
    {
        // Usuário já autenticado vai direto para o dashboard.
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('dashboard/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf('auth/login');

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->users->findByEmail($email);

            // Confere a senha comparando com o hash bcrypt armazenado.
            if ($user && password_verify($password, $user->password)) {
                // Regenera o id da sessão para evitar ataques de session fixation.
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user->id;
                $_SESSION['name']    = $user->name;

                $this->setFlash('success', 'Bem-vindo(a), ' . $user->name . '!');
                $this->redirect('dashboard/index');
            }

            // Mensagem genérica: não revela se o e-mail existe ou não.
            // Preserva o e-mail digitado para não obrigar o usuário a redigitar.
            $this->setFlash('error', 'Credenciais inválidas.');
            $_SESSION['old'] = ['email' => $email];
            $this->redirect('auth/login');
        }

        $this->render('auth/login');
    }

    /**
     * Exibe o formulário de cadastro (GET) ou cria a conta (POST).
     */
    public function register(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('dashboard/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf('auth/register');

            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validações dos campos do formulário.
            $errors = [];
            if ($name === '') {
                $errors[] = 'Informe o seu nome.';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Informe um e-mail válido.';
            }
            if (strlen($password) < 6) {
                $errors[] = 'A senha deve ter ao menos 6 caracteres.';
            } elseif (strlen($password) > 72) {
                // O bcrypt trunca silenciosamente em 72 bytes; limitamos para evitar isso.
                $errors[] = 'A senha deve ter no máximo 72 caracteres.';
            }
            // Só consulta o banco se os dados básicos forem válidos.
            if (empty($errors) && $this->users->emailExists($email)) {
                $errors[] = 'E-mail já cadastrado.';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->setFlash('error', $error);
                }
                // Preserva os dados digitados (menos a senha) para reexibir no formulário.
                $_SESSION['old'] = ['name' => $name, 'email' => $email];
                $this->redirect('auth/register');
            }

            // Gera o hash bcrypt da senha — a senha em texto puro nunca é armazenada.
            $hash = password_hash($password, PASSWORD_BCRYPT);

            try {
                $this->users->create($name, $email, $hash);
            } catch (PDOException $e) {
                // Se duas requisições simultâneas passarem pela verificação acima,
                // a constraint UNIQUE do banco (SQLSTATE 23000) garante a unicidade.
                if ($e->getCode() === '23000') {
                    $this->setFlash('error', 'E-mail já cadastrado.');
                    $_SESSION['old'] = ['name' => $name, 'email' => $email];
                    $this->redirect('auth/register');
                }
                // Qualquer outro erro de banco sobe para o tratador global.
                throw $e;
            }

            $this->setFlash('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
            $this->redirect('auth/login');
        }

        $this->render('auth/register');
    }

    /**
     * Encerra a sessão do usuário (logout) e volta para a tela de login.
     * Aceita apenas POST com token CSRF válido (evita logout forçado via CSRF).
     */
    public function logout(): void
    {
        $this->requirePost('auth/login');
        $this->verifyCsrf('auth/login');

        // Limpa os dados e destrói a sessão atual (logout efetivo).
        $_SESSION = [];
        session_unset();
        session_destroy();

        // Garante uma sessão ativa apenas para transportar a mensagem de saída.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->setFlash('success', 'Você saiu da sua conta.');
        $this->redirect('auth/login');
    }
}
