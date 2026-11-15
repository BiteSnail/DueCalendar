<li <?= $this->app->checkMenuSelection('DueCalendarController', 'project') ?>>
    <?= $this->url->icon('rocket', t('Release calendar'), 'DueCalendarController', 'project', array('project_id' => $project['id'], 'search' => $filters['search'], 'plugin' => 'DueCalendar'), false, 'view-due-calendar') ?>
</li>
