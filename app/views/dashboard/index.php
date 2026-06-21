<?php
/**
 * View: dashboard com a lista de tarefas do usuário.
 *
 * @var Task[] $tasks    Tarefas do usuário logado.
 * @var string $userName Nome do usuário logado (para a saudação).
 */
?>
<section class="dash">
    <div class="dash__head">
        <div>
            <h1 class="dash__title">Olá, <?= e($userName) ?> <span aria-hidden="true">&#128075;</span></h1>
            <p class="dash__subtitle">Estas são as suas tarefas.</p>
        </div>
        <a href="<?= e(url('task/create')) ?>" class="btn btn--primary">+ Nova tarefa</a>
    </div>

    <?php if (empty($tasks)) : ?>
        <div class="empty">
            <p class="empty__text">Você ainda não tem tarefas cadastradas.</p>
            <a href="<?= e(url('task/create')) ?>" class="btn btn--primary">Criar a primeira tarefa</a>
        </div>
    <?php else : ?>
        <ul class="tasks">
            <?php foreach ($tasks as $task) : ?>
                <li class="task <?= $task->isCompleted() ? 'task--done' : '' ?>">
                    <div class="task__main">
                        <h2 class="task__title"><?= e($task->title) ?></h2>

                        <?php if (!empty($task->description)) : ?>
                            <p class="task__desc"><?= nl2br(e($task->description)) ?></p>
                        <?php endif; ?>

                        <div class="task__meta">
                            <span class="badge badge--<?= $task->isCompleted() ? 'done' : 'pending' ?>">
                                <?= e(ucfirst($task->status)) ?>
                            </span>
                            <?php if (!empty($task->created_at)) : ?>
                                <span class="task__date">
                                    Criada em <?= e(date('d/m/Y', strtotime($task->created_at))) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="task__actions">
                        <!-- Concluir/Reabrir e Excluir alteram estado: usam POST + token CSRF. -->
                        <form action="<?= e(url('task/toggle') . '&id=' . $task->id) ?>" method="post" class="task__form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--small">
                                <?= $task->isCompleted() ? 'Reabrir' : 'Concluir' ?>
                            </button>
                        </form>

                        <a class="btn btn--small" href="<?= e(url('task/edit') . '&id=' . $task->id) ?>">
                            Editar
                        </a>

                        <form action="<?= e(url('task/delete') . '&id=' . $task->id) ?>" method="post" class="task__form"
                              onsubmit="return confirm('Tem certeza que deseja excluir esta tarefa?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--small btn--danger">Excluir</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
