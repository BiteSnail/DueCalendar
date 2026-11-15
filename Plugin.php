<?php

namespace Kanboard\Plugin\DueCalendar;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;

/**
 * DueCalendar - 마감일(date_due) 기준으로만 태스크를 보여주는 배포 캘린더
 */
class Plugin extends Base
{
    public function initialize()
    {
        // 프로젝트 상단 뷰 전환 메뉴 (개요 / 보드 / 목록 / 배포 캘린더)
        $this->template->hook->attach('template:project-header:view-switcher', 'DueCalendar:project_header/view_switcher');

        // 대시보드 사이드바 (내 전체 프로젝트 배포 캘린더)
        $this->template->hook->attach('template:dashboard:sidebar', 'DueCalendar:dashboard/sidebar');

        $this->hook->on('template:layout:css', array('template' => 'plugins/DueCalendar/Assets/css/due-calendar.css'));

        $this->route->addRoute('/release-calendar/project/:project_id', 'DueCalendarController', 'project', 'DueCalendar');
        $this->route->addRoute('/release-calendar/project/:project_id/:month', 'DueCalendarController', 'project', 'DueCalendar');
        $this->route->addRoute('/release-calendar', 'DueCalendarController', 'dashboard', 'DueCalendar');
        $this->route->addRoute('/release-calendar/all/:month', 'DueCalendarController', 'dashboard', 'DueCalendar');

        $this->projectAccessMap->add('DueCalendarController', 'project', Role::PROJECT_VIEWER);
        $this->applicationAccessMap->add('DueCalendarController', 'dashboard', Role::APP_USER);
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getClasses()
    {
        return array(
            'Plugin\DueCalendar\Model' => array(
                'DueCalendarModel',
            ),
        );
    }

    public function getPluginName()
    {
        return 'DueCalendar';
    }

    public function getPluginDescription()
    {
        return t('Release calendar: shows tasks on their due date only');
    }

    public function getPluginAuthor()
    {
        return 'Claude';
    }

    public function getPluginVersion()
    {
        return '1.0.0';
    }

    public function getPluginHomepage()
    {
        return '';
    }

    public function getCompatibleVersion()
    {
        return '>=1.2.0';
    }
}
