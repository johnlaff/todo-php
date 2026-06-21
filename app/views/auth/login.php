<?php
/** View: formulário de login. */

// Recupera o e-mail digitado anteriormente (em caso de erro) e limpa.
$old = $_SESSION['old'] ?? ['email' => ''];
unset($_SESSION['old']);
?>
<section class="card card--auth">
    <h1 class="card__title">Entrar</h1>
    <p class="card__subtitle">Acesse a sua conta para gerenciar suas tarefas.</p>

    <form action="<?= e(url('auth/login')) ?>" method="post" class="form" novalidate>
        <?= csrf_field() ?>
        <label class="form__field">
            <span class="form__label">E-mail</span>
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required autofocus>
        </label>

        <label class="form__field">
            <span class="form__label">Senha</span>
            <input type="password" name="password" required>
        </label>

        <button type="submit" class="btn btn--primary btn--block">Entrar</button>
    </form>

    <p class="card__foot">
        Não tem uma conta?
        <a href="<?= e(url('auth/register')) ?>">Cadastre-se</a>
    </p>
</section>
