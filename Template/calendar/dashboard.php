<div class="page-header">
    <h2><?= t('Release calendar') ?></h2>
</div>

<?php $base = array('user_id' => $user['id'], 'plugin' => 'DueCalendar', 'filter_project_id' => $filter_project_id, 'status' => $status); ?>

<?php ob_start() ?>
<form method="get" action="<?= $this->url->dir() ?>" class="due-calendar-filters">
    <input type="hidden" name="controller" value="DueCalendarController">
    <input type="hidden" name="action" value="dashboard">
    <input type="hidden" name="plugin" value="DueCalendar">
    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
    <input type="hidden" name="month" value="<?= date('Y-m', $month_start) ?>">
    <select name="filter_project_id" onchange="this.form.submit()">
        <option value="0"><?= t('All projects') ?></option>
        <?php foreach ($projects as $id => $name): ?>
            <option value="<?= $id ?>" <?= $filter_project_id == $id ? 'selected' : '' ?>><?= $this->text->e($name) ?></option>
        <?php endforeach ?>
    </select>
    <select name="status" onchange="this.form.submit()">
        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>><?= t('All tasks') ?></option>
        <option value="open" <?= $status === 'open' ? 'selected' : '' ?>><?= t('Open tasks') ?></option>
        <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>><?= t('Closed tasks') ?></option>
    </select>
    <noscript><button type="submit" class="btn"><?= t('Filter') ?></button></noscript>
</form>
<?php $toolbar = ob_get_clean() ?>

<?= $this->render('DueCalendar:calendar/grid', array(
    'controller' => 'DueCalendarController',
    'action' => 'dashboard',
    'base_params' => $base,
    'month_start' => $month_start,
    'grid' => $grid,
    'tasks_by_day' => $tasks_by_day,
    'colors' => $colors,
    'show_project' => $filter_project_id == 0,
    'toolbar' => $toolbar,
)) ?>
