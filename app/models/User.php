<?php

/**
 * User (Model)
 *
 * Representa um usuário do sistema — a entidade correspondente à tabela "users".
 * É um objeto simples de dados, usado para transportar informações entre o
 * repositório, os controllers e as views.
 */
class User
{
    public int $id;
    public string $name;
    public string $email;
    public string $password;   // armazena o hash bcrypt, nunca a senha em texto puro
    public ?string $created_at;

    /**
     * Cria um objeto User a partir de um array (uma linha vinda do banco).
     */
    public static function fromArray(array $row): User
    {
        $user = new self();
        $user->id         = (int) ($row['id'] ?? 0);
        $user->name       = $row['name'] ?? '';
        $user->email      = $row['email'] ?? '';
        $user->password   = $row['password'] ?? '';
        $user->created_at = $row['created_at'] ?? null;

        return $user;
    }
}
