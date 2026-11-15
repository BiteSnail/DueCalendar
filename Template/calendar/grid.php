<?php
    $currentMonth = date('Y-m', $month_start);
    $prevMonth = date('Y-m', strtotime('-1 month', $month_start));
    $nextMonth = date('Y-m', strtotime('+1 month', $month_start));
    $today = date('Y-m-d');
    $weekdays = array(t('Mon'), t('Tue'), t('Wed'), t('Thu'), t('Fri'), t('Sat'), t('Sun'));
    $monthTotal = 0;
    foreach ($tasks_by_day as $day => $list) {
        if (strpos($day, $currentMonth) === 0) {
            $monthTotal += count($list);
        }
    }
?>
<div class="due-calendar">
    <div class="due-calendar-header">
        <div class="due-calendar-nav">
            <?= $this->url->link('<i class="fa fa-chevron-left"></i>', $controller, $action, array_merge($base_params, array('month' => $prevMonth)), false, 'btn', t('Previous month')) ?>
            <?= $this->url->link(t('Today'), $controller, $action, array_merge($base_params, array('month' => date('Y-m'))), false, 'btn') ?>
            <?= $this->url->link('<i class="fa fa-chevron-right"></i>', $controller, $action, array_merge($base_params, array('month' => $nextMonth)), false, 'btn', t('Next month')) ?>
            <h3 class="due-calendar-title"><?= date('Y', $month_start) ?>. <?= date('m', $month_start) ?></h3>
            <span class="due-calendar-count"><?= t('%d release(s) this month', $monthTotal) ?></span>
        </div>
        <div class="due-calendar-toolbar"><?= $toolbar ?></div>
    </div>

    <table class="due-calendar-grid">
        <thead>
            <tr>
                <?php foreach ($weekdays as $i => $label): ?>
                    <th class="<?= $i >= 5 ? 'due-calendar-weekend' : '' ?>"><?= $label ?></th>
                <?php endforeach ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($grid['weeks'] as $week): ?>
            <tr>
            <?php foreach ($week as $i => $day): ?>
                <?php
                    $classes = array('due-calendar-day');
                    if (strpos($day, $currentMonth) !== 0) { $classes[] = 'due-calendar-other-month'; }
                    if ($day === $today) { $classes[] = 'due-calendar-today'; }
                    if ($i >= 5) { $classes[] = 'due-calendar-weekend'; }
                    $dayTasks = isset($tasks_by_day[$day]) ? $tasks_by_day[$day] : array();
                ?>
                <td class="<?= implode(' ', $classes) ?>">
                    <div class="due-calendar-daynum">
                        <?= (int) substr($day, 8, 2) ?>
                        <?php if (count($dayTasks) > 0): ?><span class="due-calendar-badge"><?= count($dayTasks) ?></span><?php endif ?>
                    </div>
                    <?php foreach ($dayTasks as $task): ?>
                        <?= $this->render('DueCalendar:calendar/task', array('task' => $task, 'colors' => $colors, 'show_project' => $show_project)) ?>
                    <?php endforeach ?>
                </td>
            <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>

    <div class="due-calendar-agenda">
        <h3><?= t('Release schedule') ?></h3>
        <?php if ($monthTotal === 0): ?>
            <p class="alert"><?= t('No tasks with a due date this month.') ?></p>
        <?php else: ?>
            <table class="table-striped table-scrolling">
                <tr>
                    <th class="column-15"><?= t('Due date') ?></th>
                    <?php if ($show_project): ?><th class="column-15"><?= t('Project') ?></th><?php endif ?>
                    <th><?= t('Task') ?></th>
                    <th class="column-12"><?= t('Column') ?></th>
                    <th class="column-12"><?= t('Assignee') ?></th>
                    <th class="column-10"><?= t('Status') ?></th>
                </tr>
                <?php foreach ($tasks_by_day as $day => $list): ?>
                    <?php if (strpos($day, $currentMonth) !== 0) { continue; } ?>
                    <?php foreach ($list as $task): ?>
                    <tr class="<?= $task['is_overdue'] ? 'due-calendar-row-overdue' : '' ?>">
                        <td><?= $this->dt->date($task['date_due']) ?><?= $task['has_time'] ? ' '.date('H:i', $task['date_due']) : '' ?></td>
                        <?php if ($show_project): ?><td><?= $this->text->e($task['project_name']) ?></td><?php endif ?>
                        <td><?= $this->url->link('#'.$task['id'].' '.$this->text->e($task['title']), 'TaskViewController', 'show', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?></td>
                        <td><?= $this->text->e($task['column_name']) ?></td>
                        <td><?= $this->text->e($task['assignee_name'] ?: $task['assignee_username'] ?: '') ?></td>
                        <td>
                            <?php if ($task['is_active'] == 0): ?>
                                <span class="due-calendar-status due-calendar-status-done"><?= t('Done') ?></span>
                            <?php elseif ($task['is_overdue']): ?>
                                <span class="due-calendar-status due-calendar-status-overdue"><?= t('Overdue') ?></span>
                            <?php else: ?>
                                <span class="due-calendar-status"><?= t('Scheduled') ?></span>
                            <?php endif ?>
                        </td>
                    </tr>
                    <?php endforeach ?>
                <?php endforeach ?>
            </table>
        <?php endif ?>
    </div>
</div>
