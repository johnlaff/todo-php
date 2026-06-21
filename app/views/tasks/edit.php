<?php
/**
 * View: formulário de edição de tarefa.
 *
 * @var Task $task Tarefa a ser editada (já validada como pertencente ao usuário).
 */
?>
<section class="card">
    <h1 class="card__title">Editar tarefa</h1>
    <p class="card__subtitle">Altere os dados e salve para atualizar a tarefa.</p>

    <form action="<?= e(url('task/edit') . '&id=' . $task->id) ?>" method="post" class="form" novalidate>
        <?= csrf_field() ?>
        <label class="form__field">
            <span class="form__label">Título *</span>
            <input type="text" name="title" maxlength="150" value="<?= e($task->title) ?>" required autofocus>
        </label>

        <label class="form__field">
            <span class="form__label">Descrição</span>
            <textarea name="description" rows="5"><?= e($task->description) ?></textarea>
        </label>

        <div class="form__actions">
            <button type="submit" class="btn btn--primary">Salvar alterações</button>
            <a href="<?= e(url('dashboard/index')) ?>" class="btn btn--ghost">Cancelar</a>
        </div>
    </form>
</section>
