<div class="page-header">
    <h2><?= t('Release calendar settings') ?></h2>
</div>
<form method="post" action="<?= $this->url->href('DueCalendarConfigController', 'save', array('plugin' => 'DueCalendar')) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <fieldset>
        <legend><?= t('First day of the week') ?></legend>
        <?= $this->form->radios('due_calendar_week_start', array(
            '0' => t('Sunday'),
            '1' => t('Monday'),
        ), $values) ?>
    </fieldset>

    <fieldset>
        <legend><?= t('Default view') ?></legend>
        <?= $this->form->radios('due_calendar_default_view', array(
            'month' => t('Month view'),
            'week' => t('Week view'),
        ), $values) ?>
        <p class="form-help"><?= t('Users can switch between month and week view at any time; their last choice is remembered for the session.') ?></p>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= t('Save') ?></button>
    </div>
</form>
