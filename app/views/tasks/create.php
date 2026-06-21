<?php /** View: formulário de criação de tarefa. */ ?>
<section class="card">
    <h1 class="card__title">Nova tarefa</h1>
    <p class="card__subtitle">Preencha os dados abaixo. Apenas o título é obrigatório.</p>

    <form action="<?= e(url('task/create')) ?>" method="post" class="form" novalidate>
        <?= csrf_field() ?>
        <label class="form__field">
            <span class="form__label">Título *</span>
            <input type="text" name="title" maxlength="150" required autofocus>
        </label>

        <label class="form__field">
            <span class="form__label">Descrição</span>
            <textarea name="description" rows="5" placeholder="Detalhes da tarefa (opcional)"></textarea>
        </label>

        <div class="form__actions">
            <button type="submit" class="btn btn--primary">Salvar tarefa</button>
            <a href="<?= e(url('dashboard/index')) ?>" class="btn btn--ghost">Cancelar</a>
        </div>
    </form>
</section>
