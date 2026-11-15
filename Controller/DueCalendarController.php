<?php

namespace Kanboard\Plugin\DueCalendar\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Filter\TaskProjectFilter;

/**
 * 배포 캘린더 (마감일 기준)
 *
 * @property \Kanboard\Plugin\DueCalendar\Model\DueCalendarModel $dueCalendarModel
 */
class DueCalendarController extends BaseController
{
    /**
     * 프로젝트 배포 캘린더 - 상단 검색 필터(status:, assignee:, category: ...)를 그대로 사용
     */
    public function project()
    {
        $project = $this->getProject();
        $search = $this->helper->projectHeader->getSearchQuery($project);
        $monthStart = $this->dueCalendarModel->parseMonth($this->request->getStringParam('month'));
        $grid = $this->dueCalendarModel->buildGrid($monthStart);

        $query = $this->taskLexer
            ->build($search)
            ->withFilter(new TaskProjectFilter($project['id']))
            ->getQuery();

        $this->response->html($this->helper->layout->app('DueCalendar:calendar/project', array(
            'project' => $project,
            'title' => $project['name'],
            'description' => $this->helper->projectHeader->getDescription($project),
            'search' => $search,
            'month_start' => $monthStart,
            'grid' => $grid,
            'tasks_by_day' => $this->dueCalendarModel->getTasksByDueDate($query, $grid['start'], $grid['end']),
            'colors' => $this->colorModel->getDefaultColors(),
        )));
    }

    /**
     * 내 전체 프로젝트 배포 캘린더 (대시보드)
     */
    public function dashboard()
    {
        $user = $this->getUser();
        $monthStart = $this->dueCalendarModel->parseMonth($this->request->getStringParam('month'));
        $grid = $this->dueCalendarModel->buildGrid($monthStart);
        $projectId = $this->request->getIntegerParam('filter_project_id');
        $status = $this->request->getStringParam('status', 'all');

        if (! in_array($status, array('open', 'closed', 'all'), true)) {
            $status = 'all';
        }

        $query = $this->dueCalendarModel->getDashboardQuery($user['id'], $projectId, $status);

        $this->response->html($this->helper->layout->dashboard('DueCalendar:calendar/dashboard', array(
            'title' => t('Release calendar'),
            'user' => $user,
            'month_start' => $monthStart,
            'grid' => $grid,
            'tasks_by_day' => $this->dueCalendarModel->getTasksByDueDate($query, $grid['start'], $grid['end']),
            'colors' => $this->colorModel->getDefaultColors(),
            'projects' => $this->projectUserRoleModel->getActiveProjectsByUser($user['id']),
            'filter_project_id' => $projectId,
            'status' => $status,
        )));
    }
}
