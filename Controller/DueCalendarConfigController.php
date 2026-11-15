<?php

namespace Kanboard\Plugin\DueCalendar\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Plugin\DueCalendar\Model\DueCalendarModel;

/**
 * 설정 > 배포 캘린더 (관리자 전용)
 *
 * @property \Kanboard\Plugin\DueCalendar\Model\DueCalendarModel $dueCalendarModel
 */
class DueCalendarConfigController extends BaseController
{
    public function show()
    {
        $this->response->html($this->helper->layout->config('DueCalendar:config/settings', array(
            'title' => t('Settings').' &gt; '.t('Release calendar'),
            'values' => array(
                DueCalendarModel::CONFIG_WEEK_START => $this->dueCalendarModel->getWeekStart(),
                DueCalendarModel::CONFIG_DEFAULT_VIEW => $this->dueCalendarModel->getDefaultView(),
            ),
        )));
    }

    public function save()
    {
        $this->checkCSRFForm();
        $values = $this->request->getValues();

        $weekStart = isset($values[DueCalendarModel::CONFIG_WEEK_START]) && (string) $values[DueCalendarModel::CONFIG_WEEK_START] === '0' ? '0' : '1';
        $defaultView = isset($values[DueCalendarModel::CONFIG_DEFAULT_VIEW]) && $values[DueCalendarModel::CONFIG_DEFAULT_VIEW] === 'week' ? 'week' : 'month';

        if ($this->configModel->save(array(
            DueCalendarModel::CONFIG_WEEK_START => $weekStart,
            DueCalendarModel::CONFIG_DEFAULT_VIEW => $defaultView,
        ))) {
            $this->flash->success(t('Settings saved successfully.'));
        } else {
            $this->flash->failure(t('Unable to save your settings.'));
        }

        $this->response->redirect($this->helper->url->to('DueCalendarConfigController', 'show', array('plugin' => 'DueCalendar')));
    }
}
