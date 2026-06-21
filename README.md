# Gerenciador de Tarefas (To-Do)

Sistema web de gerenciamento de tarefas desenvolvido em **PHP puro** com
arquitetura **MVC** e aplicação explícita de **design patterns**. Cada usuário
autenticado gerencia apenas as suas próprias tarefas.

Projeto desenvolvido para a disciplina de **Aplicações para a Internet**.

---

## Tecnologias utilizadas

- **PHP 8** puro (sem frameworks)
- **MySQL / MariaDB** acessado via **PDO** com *prepared statements*
- **HTML5** e **CSS3** (folha de estilo própria, sem frameworks)
- **Sessões nativas** do PHP para autenticação
- Ambiente alvo: **XAMPP** (Apache + MariaDB/MySQL + PHP)

---

## Arquitetura MVC

O código é organizado seguindo o padrão **Model-View-Controller**, separando
claramente as responsabilidades:

- **Model** (`app/models/`): entidades do domínio (`User`, `Task`). Representam
  os dados e regras simples ligadas a eles.
- **View** (`app/views/`): camada de apresentação. Apenas exibe os dados que o
  controller envia, escapando toda saída com `htmlspecialchars()`.
- **Controller** (`app/controllers/`): recebe a requisição, coordena os
  repositórios e escolhe qual view renderizar.

O acesso ao banco fica isolado na camada de **repositories** (`app/repositories/`),
mantendo os controllers livres de SQL.

---

## Design patterns aplicados

Os padrões abaixo estão comentados no código, no ponto exato em que são aplicados.

### 1. Singleton — `app/core/Database.php`
Garante uma **única instância da conexão PDO** por requisição. Possui construtor
privado, propriedade estática `$instance`, método estático `getInstance()` e
bloqueio de `__clone()` / `__wakeup()`.

### 2. Repository — `app/repositories/UserRepository.php` e `TaskRepository.php`
Toda a lógica de acesso ao banco fica isolada nos repositórios. Os controllers
nunca escrevem SQL: apenas chamam métodos como `findByEmail()`, `create()`,
`findByUserId()`, `update()` e `delete()`.

### 3. Front Controller — `public/index.php`
É o **único ponto de entrada** da aplicação. Carrega a configuração, registra o
autoloader, inicia a sessão, define a `BASE_URL` e delega o roteamento à classe
`Router`.

---

## Segurança

- **PDO + prepared statements** em todas as queries (proteção contra SQL Injection).
- **Senhas com hash bcrypt** via `password_hash()` / `password_verify()` — a senha
  em texto puro nunca é armazenada.
- **`htmlspecialchars()`** em toda saída de dados do usuário (proteção contra XSS).
- **Proteção de rotas**: páginas internas exigem sessão ativa (`requireAuth()`),
  redirecionando para o login quando não há usuário autenticado.
- **Verificação de propriedade (ownership)** em duas camadas: no controller (antes
  de qualquer operação) e no próprio `WHERE` das queries de escrita — um usuário
  não consegue editar, concluir ou excluir tarefas de outro.
- **Proteção contra CSRF**: todo formulário envia um token único da sessão, validado
  com `hash_equals()` antes de qualquer operação que altere estado.
- **Ações destrutivas só por POST**: excluir, alternar status e logout exigem `POST`
  (não são acionáveis por um simples link/`GET`).
- **Cookie de sessão endurecido**: `HttpOnly`, `SameSite=Lax`, `Secure` (em HTTPS) e
  `session.use_strict_mode` ativado.
- **`session_regenerate_id()`** após o login (proteção contra session fixation).
- **Tratador global de erros**: exceções não tratadas viram uma página 500 genérica,
  sem expor stack traces.
- **`.htaccess`** nas pastas `app/`, `config/` e `sql/` negando acesso direto via URL.

---

## Estrutura de pastas

```
todo-php/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── TaskController.php
│   ├── core/
│   │   ├── Database.php        (Singleton)
│   │   ├── Controller.php      (controller base: render, redirect, requireAuth)
│   │   └── Router.php          (resolve a rota e despacha o controller/ação)
│   ├── models/
│   │   ├── User.php
│   │   └── Task.php
│   ├── repositories/
│   │   ├── UserRepository.php  (Repository)
│   │   └── TaskRepository.php  (Repository)
│   └── views/
│       ├── layouts/
│       │   ├── header.php
│       │   └── footer.php
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       ├── dashboard/
│       │   └── index.php
│       └── tasks/
│           ├── create.php
│           └── edit.php
├── config/
│   └── config.php
├── public/
│   ├── index.php              (Front Controller: único ponto de entrada)
│   └── css/
│       └── style.css
├── sql/
│   └── schema.sql
├── .gitignore
└── README.md
```

---

## Como rodar no XAMPP

1. **Copie o projeto** para a pasta `htdocs` do XAMPP. Exemplo final:
   `C:\xampp\htdocs\todo-php`.

2. **Inicie o Apache e o MySQL** pelo painel de controle do XAMPP.

3. **Importe o banco de dados**. Abra o phpMyAdmin
   (`http://localhost/phpmyadmin`), vá em *Importar* e selecione o arquivo
   `sql/schema.sql`. Isso cria o banco `todo_mvc` e as tabelas `users` e `tasks`.
   *(Alternativa por linha de comando: `mysql -u root < sql/schema.sql`.)*

4. **Ajuste a configuração**, se necessário, em `config/config.php`
   (host, nome do banco, usuário e senha). Os valores padrão já funcionam em uma
   instalação típica do XAMPP (`root` sem senha).

5. **Acesse a aplicação** no navegador, sempre apontando para a pasta `public/`:
   ```
   http://localhost/todo-php/public/
   ```

6. **Crie uma conta** na tela de cadastro e comece a usar.

> A `BASE_URL` é detectada automaticamente, então o projeto funciona mesmo que
> você o coloque em uma subpasta diferente dentro do `htdocs`.

---

## Funcionalidades

- Cadastro de usuário (com e-mail único e senha protegida por bcrypt).
- Login e logout com mensagens amigáveis (flash messages).
- Dashboard com saudação e lista de tarefas do usuário logado.
- CRUD completo de tarefas: criar, listar, editar, excluir.
- Alternância de status entre **pendente** e **concluída**.
- Confirmação antes de excluir uma tarefa.

---

## Rotas

O roteamento é feito por query string (`index.php?r=controller/acao`), o que
dispensa o `mod_rewrite` do Apache.

| Rota                       | Método      | Ação                                  |
|----------------------------|-------------|---------------------------------------|
| `?r=auth/login`            | GET / POST  | Tela de login / autenticação          |
| `?r=auth/register`         | GET / POST  | Tela de cadastro                      |
| `?r=auth/logout`           | POST        | Logout                                |
| `?r=dashboard/index`       | GET         | Dashboard com as tarefas              |
| `?r=task/create`           | GET / POST  | Criar tarefa                          |
| `?r=task/edit&id=ID`       | GET / POST  | Editar tarefa                         |
| `?r=task/toggle&id=ID`     | POST        | Alternar status da tarefa             |
| `?r=task/delete&id=ID`     | POST        | Excluir tarefa                        |

> As ações que alteram dados (`logout`, `task/toggle`, `task/delete`, além de
> `create`/`edit`/`register`/`login` no envio) são feitas por `POST` com token CSRF.
