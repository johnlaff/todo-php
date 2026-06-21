<?php

/**
 * Configurações da aplicação.
 *
 * Ajuste estas constantes de acordo com o ambiente onde o projeto for executado.
 * Os valores padrão atendem a uma instalação típica do XAMPP, onde o MariaDB/MySQL
 * roda em localhost e o usuário "root" não possui senha.
 */

// Endereço do servidor de banco de dados.
define('DB_HOST', 'localhost');

// Nome do banco de dados (criado pelo script sql/schema.sql).
define('DB_NAME', 'todo_mvc');

// Usuário do banco. No XAMPP padrão é "root".
define('DB_USER', 'root');

// Senha do banco. No XAMPP padrão fica em branco — altere se necessário.
define('DB_PASS', '');

// Charset da conexão. utf8mb4 garante suporte completo a acentos e emojis.
define('DB_CHARSET', 'utf8mb4');
