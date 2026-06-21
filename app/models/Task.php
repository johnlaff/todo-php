<?php

/**
 * Task (Model)
 *
 * Representa uma tarefa — a entidade correspondente à tabela "tasks".
 * Cada tarefa pertence a um usuário (campo user_id).
 */
class Task
{
    // Valores possíveis para o status (centralizam o vocabulário do domínio,
    // evitando "strings mágicas" espalhadas pelo código).
    public const STATUS_PENDENTE  = 'pendente';
    public const STATUS_CONCLUIDA = 'concluida';

    public int $id;
    public int $user_id;
    public string $title;
    public ?string $description;
    public string $status;       // 'pendente' ou 'concluida'
    public ?string $created_at;

    /**
     * Cria um objeto Task a partir de um array (uma linha vinda do banco).
     */
    public static function fromArray(array $row): Task
    {
        $task = new self();
        $task->id          = (int) ($row['id'] ?? 0);
        $task->user_id     = (int) ($row['user_id'] ?? 0);
        $task->title       = $row['title'] ?? '';
        $task->description = $row['description'] ?? null;
        $task->status      = $row['status'] ?? self::STATUS_PENDENTE;
        $task->created_at  = $row['created_at'] ?? null;

        return $task;
    }

    /**
     * Indica se a tarefa já foi concluída.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_CONCLUIDA;
    }
}
