<?php

/**
 * Database
 *
 * Implementa o Design Pattern SINGLETON.
 *
 * O objetivo do Singleton aqui é garantir que exista uma única instância da
 * conexão PDO durante toda a requisição, evitando abrir várias conexões com o
 * banco. Os elementos que caracterizam o padrão são:
 *
 *   - Construtor privado          -> impede o uso de "new Database()" fora da classe.
 *   - Propriedade estática $instance -> guarda a única instância existente.
 *   - Método estático getInstance() -> ponto de acesso global e controlado.
 *   - __clone() e __wakeup() bloqueados -> impedem duplicar a instância.
 */
class Database
{
    /**
     * Única instância da classe (parte do Singleton).
     */
    private static ?Database $instance = null;

    /**
     * Conexão PDO compartilhada por toda a aplicação.
     */
    private PDO $connection;

    /**
     * Construtor PRIVADO: só a própria classe pode se instanciar.
     * Cria a conexão PDO a partir das constantes definidas em config/config.php.
     */
    private function __construct()
    {
        // Monta a DSN (Data Source Name) com host, banco e charset.
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            // Erros de banco são lançados como exceções (facilita o tratamento).
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Os resultados vêm como arrays associativos por padrão.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Usa prepared statements nativos do banco (mais seguro que os emulados).
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    /**
     * Retorna a única instância de Database, criando-a na primeira chamada.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Devolve a conexão PDO para que os repositórios executem as queries.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Bloqueia a clonagem da instância (parte do Singleton).
     */
    private function __clone()
    {
    }

    /**
     * Bloqueia a desserialização da instância (parte do Singleton).
     */
    public function __wakeup(): void
    {
        throw new \Exception('Não é permitido desserializar um Singleton.');
    }
}
