<li <?= $this->app->checkMenuSelection('DueCalendarController', 'dashboard') ?>>
    <?= $this->url->link(t('Release calendar'), 'DueCalendarController', 'dashboard', array('user_id' => $user['id'], 'plugin' => 'DueCalendar')) ?>
</li>
