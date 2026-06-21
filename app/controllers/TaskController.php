<?php

/**
 * TaskController
 *
 * Controla o CRUD de tarefas do usuário autenticado.
 *
 * Regras de segurança aplicadas em todas as ações:
 *   - requireAuth(): todas exigem usuário logado.
 *   - ownership check: nas operações sobre uma tarefa específica (editar,
 *     alternar status, excluir), confirma-se que a tarefa pertence ao usuário
 *     logado antes de executar qualquer alteração.
 *   - CSRF + POST: as ações que alteram estado só aceitam POST e exigem um
 *     token CSRF válido, evitando requisições forjadas.
 */
class TaskController extends Controller
{
    private TaskRepository $tasks;

    public function __construct()
    {
        $this->tasks = new TaskRepository();
    }

    /**
     * Exibe o formulário de criação (GET) ou salva a nova tarefa (POST).
     */
    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf('task/create');

            $title       = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');

            // O título é obrigatório.
            if ($title === '') {
                $this->setFlash('error', 'O título da tarefa é obrigatório.');
                $this->redirect('task/create');
            }

            $this->tasks->create(
                (int) $_SESSION['user_id'],
                $title,
                $description !== '' ? $description : null
            );

            $this->setFlash('success', 'Tarefa criada com sucesso!');
            $this->redirect('dashboard/index');
        }

        $this->render('tasks/create');
    }

    /**
     * Exibe o formulário de edição (GET) ou salva as alterações (POST).
     */
    public function edit(): void
    {
        $this->requireAuth();

        $id   = (int) ($_GET['id'] ?? 0);
        $task = $this->findOwnedTaskOrRedirect($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf('task/edit&id=' . $id);

            $title       = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($title === '') {
                $this->setFlash('error', 'O título da tarefa é obrigatório.');
                $this->redirect('task/edit&id=' . $id);
            }

            $this->tasks->update(
                $id,
                (int) $_SESSION['user_id'],
                $title,
                $description !== '' ? $description : null
            );

            $this->setFlash('success', 'Tarefa atualizada com sucesso!');
            $this->redirect('dashboard/index');
        }

        $this->render('tasks/edit', ['task' => $task]);
    }

    /**
     * Alterna o status da tarefa entre pendente e concluída (somente POST).
     */
    public function toggle(): void
    {
        $this->requireAuth();
        $this->requirePost();
        $this->verifyCsrf();

        $id   = (int) ($_GET['id'] ?? 0);
        $task = $this->findOwnedTaskOrRedirect($id);

        // Calcula o status oposto usando as constantes do model.
        $novoStatus = $task->isCompleted() ? Task::STATUS_PENDENTE : Task::STATUS_CONCLUIDA;
        $this->tasks->updateStatus($id, (int) $_SESSION['user_id'], $novoStatus);

        $this->setFlash('success', 'Status da tarefa atualizado.');
        $this->redirect('dashboard/index');
    }

    /**
     * Exclui uma tarefa do usuário (somente POST).
     */
    public function delete(): void
    {
        $this->requireAuth();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int) ($_GET['id'] ?? 0);
        $this->findOwnedTaskOrRedirect($id);

        $this->tasks->delete($id, (int) $_SESSION['user_id']);

        $this->setFlash('success', 'Tarefa excluída.');
        $this->redirect('dashboard/index');
    }

    /**
     * Busca a tarefa pelo id e garante que ela pertence ao usuário logado
     * (ownership check). Se não existir ou for de outro usuário, redireciona
     * para o dashboard. Centraliza a verificação usada por edit/toggle/delete.
     */
    private function findOwnedTaskOrRedirect(int $id): Task
    {
        $task = $this->tasks->findById($id);

        if ($task === null || $task->user_id !== (int) $_SESSION['user_id']) {
            $this->setFlash('error', 'Tarefa não encontrada.');
            $this->redirect('dashboard/index');
        }

        return $task;
    }
}
