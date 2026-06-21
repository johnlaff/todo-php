<?php

/**
 * UserRepository
 *
 * Implementa o Design Pattern REPOSITORY para a entidade User.
 *
 * Toda a lógica de acesso ao banco relacionada a usuários fica isolada aqui.
 * Os controllers nunca escrevem SQL: apenas chamam os métodos públicos deste
 * repositório (findByEmail, findById, create, ...). Isso mantém a camada de
 * controle limpa e centraliza as consultas em um único lugar.
 *
 * Todas as queries usam prepared statements (proteção contra SQL Injection).
 */
class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        // Obtém a conexão única através do Singleton Database.
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Busca um usuário pelo e-mail. Retorna null se não encontrar.
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        return $row ? User::fromArray($row) : null;
    }

    /**
     * Busca um usuário pelo id. Retorna null se não encontrar.
     */
    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? User::fromArray($row) : null;
    }

    /**
     * Verifica se já existe um usuário cadastrado com o e-mail informado.
     * Usado para impedir cadastros duplicados.
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cria um novo usuário e retorna o id gerado.
     * A senha deve chegar já com hash (bcrypt) — ver AuthController::register().
     */
    public function create(string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
        );
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $passwordHash,
        ]);

        return (int) $this->db->lastInsertId();
    }
}
