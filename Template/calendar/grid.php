<?php
    $isWeek = $calendar['view'] === 'week';
    $today = date('Y-m-d');
    $currentMonth = substr($calendar['period_start'], 0, 7);
    $dayNames = array(t('Sun'), t('Mon'), t('Tue'), t('Wed'), t('Thu'), t('Fri'), t('Sat'));
    $inPeriod = function ($day) use ($calendar) {
        return $day >= $calendar['period_start'] && $day < $calendar['period_end'];
    };
    $total = 0;
    foreach ($tasks_by_day as $day => $list) {
        if ($inPeriod($day)) {
            $total += count($list);
        }
    }
    $nav = function ($params) use ($base_params, $calendar) {
        return array_merge($base_params, array('view' => $calendar['view']), $params);
    };
?>
<div class="due-calendar due-calendar-view-<?= $calendar['view'] ?>">
    <div class="due-calendar-header">
        <div class="due-calendar-nav">
            <?= $this->url->link('<i class="fa fa-chevron-left"></i>', $controller, $action, $nav(array('date' => $calendar['prev'])), false, 'btn', $isWeek ? t('Previous week') : t('Previous month')) ?>
            <?= $this->url->link(t('Today'), $controller, $action, $nav(array('date' => $today)), false, 'btn') ?>
            <?= $this->url->link('<i class="fa fa-chevron-right"></i>', $controller, $action, $nav(array('date' => $calendar['next'])), false, 'btn', $isWeek ? t('Next week') : t('Next month')) ?>
            <h3 class="due-calendar-title"><?= $calendar['title'] ?></h3>
            <span class="due-calendar-count"><?= $isWeek ? t('%d release(s) this week', $total) : t('%d release(s) this month', $total) ?></span>
        </div>
        <div class="due-calendar-actions">
            <div class="due-calendar-toolbar"><?= $toolbar ?></div>
            <div class="due-calendar-switch">
                <?= $this->url->link(t('Month'), $controller, $action, array_merge($base_params, array('view' => 'month', 'date' => $calendar['switch_anchor'])), false, $isWeek ? '' : 'active') ?>
                <?= $this->url->link(t('Week'), $controller, $action, array_merge($base_params, array('view' => 'week', 'date' => $calendar['switch_anchor'])), false, $isWeek ? 'active' : '') ?>
            </div>
        </div>
    </div>

    <table class="due-calendar-grid">
        <thead>
            <tr>
                <?php foreach ($calendar['weekdays'] as $i => $dow): ?>
                    <th class="<?= $dow === 0 ? 'due-calendar-sunday' : ($dow === 6 ? 'due-calendar-saturday' : '') ?>">
                        <?= $dayNames[$dow] ?>
                        <?php if ($isWeek): ?>
                            <span class="due-calendar-th-date"><?= (int) substr($calendar['weeks'][0][$i], 5, 2) ?>/<?= (int) substr($calendar['weeks'][0][$i], 8, 2) ?></span>
                        <?php endif ?>
                    </th>
                <?php endforeach ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($calendar['weeks'] as $week): ?>
            <tr>
            <?php foreach ($week as $i => $day): ?>
                <?php
                    $dow = $calendar['weekdays'][$i];
                    $classes = array('due-calendar-day');
                    if (! $isWeek && strpos($day, $currentMonth) !== 0) { $classes[] = 'due-calendar-other-month'; }
                    if ($day === $today) { $classes[] = 'due-calendar-today'; }
                    if ($dow === 0 || $dow === 6) { $classes[] = 'due-calendar-weekend'; }
                    $dayTasks = isset($tasks_by_day[$day]) ? $tasks_by_day[$day] : array();
                ?>
                <td class="<?= implode(' ', $classes) ?>">
                    <div class="due-calendar-daynum">
                        <?php if ($isWeek): ?>
                            <span><?= $day === $today ? t('Today') : '' ?></span>
                        <?php else: ?>
                            <?= $this->url->link((string) (int) substr($day, 8, 2), $controller, $action, array_merge($base_params, array('view' => 'week', 'date' => $day)), false, 'due-calendar-daylink', t('Week view')) ?>
                        <?php endif ?>
                        <?php if (count($dayTasks) > 0): ?><span class="due-calendar-badge"><?= count($dayTasks) ?></span><?php endif ?>
                    </div>
                    <?php foreach ($dayTasks as $task): ?>
                        <?= $this->render('DueCalendar:calendar/task', array('task' => $task, 'colors' => $colors, 'show_project' => $show_project, 'detailed' => $isWeek)) ?>
                    <?php endforeach ?>
                </td>
            <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>

    <div class="due-calendar-agenda">
        <h3><?= t('Release schedule') ?></h3>
        <?php if ($total === 0): ?>
            <p class="alert"><?= $isWeek ? t('No tasks with a due date this week.') : t('No tasks with a due date this month.') ?></p>
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
                    <?php if (! $inPeriod($day)) { continue; } ?>
                    <?php foreach ($list as $task): ?>
                    <tr class="<?= $task['is_overdue'] ? 'due-calendar-row-overdue' : '' ?>">
                        <td><?= $this->dt->date($task['date_due']) ?> (<?= $dayNames[(int) date('w', $task['date_due'])] ?>)<?= $task['has_time'] ? ' '.date('H:i', $task['date_due']) : '' ?></td>
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
