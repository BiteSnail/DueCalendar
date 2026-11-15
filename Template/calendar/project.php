<?= $this->projectHeader->render($project, 'DueCalendarController', 'project', false, 'DueCalendar') ?>

<?php
    $base = array('project_id' => $project['id'], 'plugin' => 'DueCalendar', 'search' => $search);
    $includesClosed = preg_match('/status:\s*(all|closed)/i', $search);
    $toggleSearch = $includesClosed
        ? trim(preg_replace('/status:\s*(all|closed)/i', 'status:open', $search))
        : (preg_match('/status:\s*open/i', $search) ? trim(preg_replace('/status:\s*open/i', 'status:all', $search)) : trim($search.' status:all'));
?>

<?= $this->render('DueCalendar:calendar/grid', array(
    'controller' => 'DueCalendarController',
    'action' => 'project',
    'base_params' => $base,
    'month_start' => $month_start,
    'grid' => $grid,
    'tasks_by_day' => $tasks_by_day,
    'colors' => $colors,
    'show_project' => false,
    'toolbar' => $this->url->link(
        $includesClosed ? t('Hide closed tasks') : t('Include closed tasks'),
        'DueCalendarController', 'project',
        array_merge($base, array('search' => $toggleSearch, 'month' => date('Y-m', $month_start)))
    ),
)) ?>
