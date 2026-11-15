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

        // 설정 사이드바 (주 시작 요일 / 기본 보기)
        $this->template->hook->attach('template:config:sidebar', 'DueCalendar:config/sidebar');

        $this->hook->on('template:layout:css', array('template' => 'plugins/DueCalendar/Assets/css/due-calendar.css'));

        $this->route->addRoute('/release-calendar/project/:project_id', 'DueCalendarController', 'project', 'DueCalendar');
        $this->route->addRoute('/release-calendar', 'DueCalendarController', 'dashboard', 'DueCalendar');
        $this->route->addRoute('/settings/release-calendar', 'DueCalendarConfigController', 'show', 'DueCalendar');

        $this->projectAccessMap->add('DueCalendarController', 'project', Role::PROJECT_VIEWER);
        $this->applicationAccessMap->add('DueCalendarController', 'dashboard', Role::APP_USER);
        $this->applicationAccessMap->add('DueCalendarConfigController', '*', Role::APP_ADMIN);
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
        return '1.1.0';
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
