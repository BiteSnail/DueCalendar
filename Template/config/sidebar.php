<li <?= $this->app->checkMenuSelection('DueCalendarConfigController') ?>>
    <?= $this->url->link(t('Release calendar'), 'DueCalendarConfigController', 'show', array('plugin' => 'DueCalendar')) ?>
</li>
