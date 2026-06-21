<?php
/** View: formulário de cadastro. */

// Recupera os dados digitados anteriormente (em caso de erro de validação)
// para reexibi-los, e em seguida limpa-os da sessão.
$old = $_SESSION['old'] ?? ['name' => '', 'email' => ''];
unset($_SESSION['old']);
?>
<section class="card card--auth">
    <h1 class="card__title">Criar conta</h1>
    <p class="card__subtitle">Cadastre-se para começar a organizar suas tarefas.</p>

    <form action="<?= e(url('auth/register')) ?>" method="post" class="form" novalidate>
        <?= csrf_field() ?>
        <label class="form__field">
            <span class="form__label">Nome</span>
            <input type="text" name="name" value="<?= e($old['name']) ?>" required autofocus>
        </label>

        <label class="form__field">
            <span class="form__label">E-mail</span>
            <input type="email" name="email" value="<?= e($old['email']) ?>" required>
        </label>

        <label class="form__field">
            <span class="form__label">Senha</span>
            <input type="password" name="password" minlength="6" required>
            <small class="form__hint">Mínimo de 6 caracteres.</small>
        </label>

        <button type="submit" class="btn btn--primary btn--block">Cadastrar</button>
    </form>

    <p class="card__foot">
        Já tem uma conta?
        <a href="<?= e(url('auth/login')) ?>">Entrar</a>
    </p>
</section>
