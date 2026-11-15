<?php
    $color = isset($colors[$task['color_id']]) ? $colors[$task['color_id']] : array('background' => '#f5f7f4', 'border' => '#aaa');
    $classes = array('due-calendar-task');
    if ($task['is_active'] == 0) { $classes[] = 'due-calendar-task-closed'; }
    if ($task['is_overdue']) { $classes[] = 'due-calendar-task-overdue'; }
    if (! empty($detailed)) { $classes[] = 'due-calendar-task-detailed'; }
    $assignee = $task['assignee_name'] ?: $task['assignee_username'];
    $tooltip = $task['title'];
    if (! empty($task['column_name'])) { $tooltip .= ' · '.$task['column_name']; }
    if (! empty($assignee)) { $tooltip .= ' · '.$assignee; }
    $time = $task['has_time'] ? '<span class="due-calendar-time">'.date('H:i', $task['date_due']).'</span>' : '';
?>
<a class="<?= implode(' ', $classes) ?>"
   style="background-color: <?= $this->text->e($color['background']) ?>; border-left-color: <?= $this->text->e($color['border']) ?>;"
   href="<?= $this->url->href('TaskViewController', 'show', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>"
   title="<?= $this->text->e($tooltip) ?>">
<?php if (! empty($detailed)): ?>
    <?php if ($time !== '' || $show_project): ?>
        <span class="due-calendar-meta"><?= $time ?><?php if ($show_project): ?><span class="due-calendar-project"><?= $this->text->e($task['project_name']) ?></span><?php endif ?></span>
    <?php endif ?>
    <span class="due-calendar-task-title">#<?= $task['id'] ?> <?= $this->text->e($task['title']) ?></span>
    <span class="due-calendar-details">
        <?php if (! empty($task['column_name'])): ?><span><i class="fa fa-columns"></i> <?= $this->text->e($task['column_name']) ?></span><?php endif ?>
        <?php if (! empty($assignee)): ?><span><i class="fa fa-user"></i> <?= $this->text->e($assignee) ?></span><?php endif ?>
        <?php if (! empty($task['category_name'])): ?><span><i class="fa fa-tag"></i> <?= $this->text->e($task['category_name']) ?></span><?php endif ?>
    </span>
<?php elseif ($show_project): ?>
    <span class="due-calendar-meta"><?= $time ?><span class="due-calendar-project"><?= $this->text->e($task['project_name']) ?></span></span>
    <span class="due-calendar-task-title">#<?= $task['id'] ?> <?= $this->text->e($task['title']) ?></span>
<?php else: ?>
    <span class="due-calendar-line"><?= $time ?><span class="due-calendar-task-title">#<?= $task['id'] ?> <?= $this->text->e($task['title']) ?></span></span>
<?php endif ?>
</a>
