<?php

/**
 * Cabeçalho comum a todas as páginas.
 *
 * Abre o documento HTML, monta a barra de navegação (que muda conforme o
 * usuário esteja ou não autenticado) e exibe as mensagens flash.
 */

// Define se há um usuário autenticado para montar o menu adequado.
$isLogged = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gerenciador de Tarefas</title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/css/style.css">
</head>
<body>
    <header class="topbar">
        <div class="topbar__inner">
            <a class="brand" href="<?= e(url($isLogged ? 'dashboard/index' : 'auth/login')) ?>">
                <span class="brand__mark" aria-hidden="true">&#10003;</span>
                <span class="brand__text">Gerenciador de Tarefas</span>
            </a>

            <nav class="nav">
                <?php if ($isLogged): ?>
                    <span class="nav__user">Olá, <?= e($_SESSION['name'] ?? '') ?></span>
                    <a class="nav__link" href="<?= e(url('dashboard/index')) ?>">Dashboard</a>
                    <a class="nav__link" href="<?= e(url('task/create')) ?>">Nova tarefa</a>
                    <!-- Logout via POST + CSRF para evitar logout forçado por requisição forjada. -->
                    <form action="<?= e(url('auth/logout')) ?>" method="post" class="nav__form">
                        <?= csrf_field() ?>
                        <button type="submit" class="nav__logout">Sair</button>
                    </form>
                <?php else: ?>
                    <a class="nav__link" href="<?= e(url('auth/login')) ?>">Entrar</a>
                    <a class="nav__link nav__link--ghost" href="<?= e(url('auth/register')) ?>">Cadastrar</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php
        // Exibe as mensagens flash (sucesso/erro) e em seguida as remove da sessão.
        if (!empty($_SESSION['flash'])) :
            foreach ($_SESSION['flash'] as $flash) : ?>
                <div class="alert alert--<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endforeach;
            unset($_SESSION['flash']);
        endif;
        ?>
