<?php

/**
 * TaskRepository
 *
 * Implementa o Design Pattern REPOSITORY para a entidade Task.
 *
 * Centraliza todo o acesso ao banco relacionado a tarefas. Os controllers
 * apenas invocam estes métodos (findByUserId, create, update, delete, ...),
 * sem escrever SQL diretamente.
 *
 * Todas as queries usam prepared statements (proteção contra SQL Injection).
 */
class TaskRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todas as tarefas de um usuário, da mais recente para a mais antiga.
     *
     * @return Task[]
     */
    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute([':user_id' => $userId]);

        $tasks = [];
        foreach ($stmt->fetchAll() as $row) {
            $tasks[] = Task::fromArray($row);
        }

        return $tasks;
    }

    /**
     * Busca uma tarefa pelo id. Retorna null se não encontrar.
     * A verificação de propriedade (ownership) é feita no controller.
     */
    public function findById(int $id): ?Task
    {
        $stmt = $this->db->prepare('SELECT * FROM tasks WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? Task::fromArray($row) : null;
    }

    /**
     * Cria uma nova tarefa para o usuário e retorna o id gerado.
     */
    public function create(int $userId, string $title, ?string $description): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tasks (user_id, title, description) VALUES (:user_id, :title, :description)'
        );
        $stmt->execute([
            ':user_id'     => $userId,
            ':title'       => $title,
            ':description' => $description,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza o título e a descrição de uma tarefa do usuário.
     * O user_id no WHERE garante a propriedade (ownership) também na camada de
     * dados — defesa em profundidade, além da checagem feita no controller.
     */
    public function update(int $id, int $userId, string $title, ?string $description): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE tasks SET title = :title, description = :description
              WHERE id = :id AND user_id = :user_id'
        );

        return $stmt->execute([
            ':title'       => $title,
            ':description' => $description,
            ':id'          => $id,
            ':user_id'     => $userId,
        ]);
    }

    /**
     * Define o status de uma tarefa do usuário ('pendente' ou 'concluida').
     */
    public function updateStatus(int $id, int $userId, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE tasks SET status = :status WHERE id = :id AND user_id = :user_id'
        );

        return $stmt->execute([
            ':status'  => $status,
            ':id'      => $id,
            ':user_id' => $userId,
        ]);
    }

    /**
     * Exclui uma tarefa do usuário (com user_id no WHERE para reforçar o ownership).
     */
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM tasks WHERE id = :id AND user_id = :user_id');

        return $stmt->execute([
            ':id'      => $id,
            ':user_id' => $userId,
        ]);
    }
}
