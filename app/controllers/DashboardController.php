<?php

/**
 * DashboardController
 *
 * Controla a área logada do sistema. Exibe a saudação ao usuário e a lista
 * das tarefas que pertencem a ele.
 */
class DashboardController extends Controller
{
    /**
     * Página inicial da área logada: lista as tarefas do usuário autenticado.
     */
    public function index(): void
    {
        // Proteção de rota: exige usuário autenticado.
        $this->requireAuth();

        $taskRepo = new TaskRepository();

        // Busca apenas as tarefas do usuário logado (isolamento por user_id).
        $tasks = $taskRepo->findByUserId((int) $_SESSION['user_id']);

        $this->render('dashboard/index', [
            'tasks'    => $tasks,
            'userName' => $_SESSION['name'] ?? 'usuário',
        ]);
    }
}
