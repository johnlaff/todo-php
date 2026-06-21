# 🧩 Conclusão

> Documento complementar ao [README](../README.md): explica as decisões de código e as estruturas utilizadas no **Gerenciador de Tarefas (To-Do)**.

O projeto entrega um gerenciador de tarefas (To-Do) completo em **PHP 8 puro**, com cadastro e autenticação de usuários e operações de criar, listar, editar, alternar status e excluir tarefas. A organização segue o padrão **MVC** estendido com duas camadas de apoio, refletida fisicamente nas pastas `app/{core,controllers,models,repositories,views}`: os **Models** (`User`, `Task`) são entidades de domínio com propriedades tipadas que mapeiam as tabelas e centralizam regras leves, como as constantes `STATUS_PENDENTE`/`STATUS_CONCLUIDA` e o método `isCompleted()`; os **Controllers** orquestram o fluxo (entrada → validação → repositório → render/redirect) sem nunca escrever SQL, herdando de uma classe `abstract Controller` que padroniza `render()`, `redirect()`, `requireAuth()`, `requirePost()` e `verifyCsrf()`; e as **Views** cuidam só da apresentação, escapando toda saída dinâmica com a função global `e()`.

Três **design patterns** sustentam essa arquitetura. O **Singleton** (`Database`) garante uma única conexão `PDO` por requisição — com construtor privado, `getInstance()` com *lazy initialization* e bloqueio de `__clone()`/`__wakeup()` — evitando conexões redundantes e centralizando a configuração. O **Repository** (`TaskRepository`, `UserRepository`) isola todo o acesso a dados, expondo métodos de intenção de negócio (`findByUserId`, `create`, `updateStatus`) e mapeando linhas em objetos via `fromArray()`; trocar o SGBD exigiria mudar apenas essa camada. O **Front Controller** (`public/index.php`) é o ponto único de entrada que concentra autoloading, sessão e roteamento antes de delegar ao `Router`.

Em termos de **estruturas e recursos do PHP**, o código usa propriedades e retornos tipados (inclusive `?Tipo` anuláveis e `void`), classe abstrata e herança, `spl_autoload_register` para carregamento sob demanda, **PDO com prepared statements** de placeholders nomeados (`ATTR_EMULATE_PREPARES => false`) e `ReflectionMethod` para despacho seguro de rotas. As escolhas de segurança formam uma defesa em profundidade: senhas com `password_hash(...PASSWORD_BCRYPT)`, token **CSRF** comparado por `hash_equals()`, escape **anti-XSS** com `htmlspecialchars(ENT_QUOTES)`, cookie de sessão endurecido (`HttpOnly`, `SameSite=Lax`, `use_strict_mode`) e *ownership check* duplicado (no controller e no `WHERE ... AND user_id`).

O resultado é uma aplicação enxuta, coesa e didática, em que cada camada tem responsabilidade única e a segurança é aplicada de forma uniforme — demonstrando na prática como organizar código orientado a objetos em PHP moderno.

## Em resumo, o projeto demonstra

- **Arquitetura MVC** estendida com camadas `Repository` e `Core`, cada uma com responsabilidade única.
- Aplicação prática de **três design patterns**: Singleton, Repository e Front Controller.
- Uso de **recursos modernos do PHP 8**: tipagem estrita, classes abstratas, herança, Reflection e autoloading.
- **Acesso a dados seguro** com PDO e prepared statements nomeados, isolado nos repositórios.
- **Defesa em profundidade**: bcrypt para senhas, CSRF com `hash_equals()`, escape anti-XSS e sessão endurecida.
- **Isolamento por usuário** garantido em duas camadas (controller e cláusula SQL `WHERE`).
