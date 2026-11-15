<?php

namespace Kanboard\Plugin\DueCalendar\Model;

use Kanboard\Core\Base;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\TaskModel;
use PicoDb\Table;

/**
 * 달력 범위 계산 + 마감일 기준 태스크 조회
 */
class DueCalendarModel extends Base
{
    const CONFIG_WEEK_START = 'due_calendar_week_start';   // 0 = 일요일, 1 = 월요일
    const CONFIG_DEFAULT_VIEW = 'due_calendar_default_view'; // month | week
    const SESSION_VIEW = 'dueCalendarView';

    /**
     * 설정된 한 주의 시작 요일 (0 = 일요일, 1 = 월요일)
     */
    public function getWeekStart()
    {
        return (int) $this->configModel->get(self::CONFIG_WEEK_START, 1) === 0 ? 0 : 1;
    }

    public function getDefaultView()
    {
        return $this->configModel->get(self::CONFIG_DEFAULT_VIEW, 'month') === 'week' ? 'week' : 'month';
    }

    /**
     * 요청 파라미터 > 세션 > 설정 기본값 순으로 보기 모드 결정
     */
    public function resolveView($requested)
    {
        if ($requested === 'month' || $requested === 'week') {
            session_set(self::SESSION_VIEW, $requested);
            return $requested;
        }

        $saved = session_get(self::SESSION_VIEW);

        return $saved === 'month' || $saved === 'week' ? $saved : $this->getDefaultView();
    }

    /**
     * 기준 날짜 결정: date=YYYY-MM-DD 또는 (하위호환) month=YYYY-MM, 없으면 오늘
     */
    public function resolveDate($date, $month)
    {
        if (is_string($date) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return mktime(0, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1]);
        }

        if (is_string($month) && preg_match('/^(\d{4})-(\d{2})$/', $month, $m) && (int) $m[2] >= 1 && (int) $m[2] <= 12) {
            return mktime(0, 0, 0, (int) $m[2], 1, (int) $m[1]);
        }

        return mktime(0, 0, 0);
    }

    /**
     * 해당 날짜가 속한 주의 첫날
     */
    public function startOfWeek($day, $weekStart)
    {
        $offset = ((int) date('w', $day) - $weekStart + 7) % 7;
        return mktime(0, 0, 0, (int) date('n', $day), (int) date('j', $day) - $offset, (int) date('Y', $day));
    }

    private function addDays($day, $n)
    {
        return mktime(0, 0, 0, (int) date('n', $day), (int) date('j', $day) + $n, (int) date('Y', $day));
    }

    /**
     * 보기 모드에 맞는 그리드와 이동용 날짜 계산
     *
     * @return array
     */
    public function buildCalendar($view, $anchor, $weekStart)
    {
        if ($view === 'week') {
            $periodStart = $this->startOfWeek($anchor, $weekStart);
            $periodEnd = $this->addDays($periodStart, 7);
            $gridStart = $periodStart;
            $gridEnd = $periodEnd;
            $prev = $this->addDays($periodStart, -7);
            $next = $periodEnd;
            $last = $this->addDays($periodEnd, -1);
            $title = date('Y. m. d', $periodStart).' – '.(date('Y', $last) !== date('Y', $periodStart) ? date('Y. ', $last) : '').date('m. d', $last);
        } else {
            $periodStart = mktime(0, 0, 0, (int) date('n', $anchor), 1, (int) date('Y', $anchor));
            $periodEnd = mktime(0, 0, 0, (int) date('n', $anchor) + 1, 1, (int) date('Y', $anchor));
            $gridStart = $this->startOfWeek($periodStart, $weekStart);
            $gridEnd = $this->startOfWeek($this->addDays($periodEnd, -1), $weekStart);
            $gridEnd = $this->addDays($gridEnd, 7);
            $prev = mktime(0, 0, 0, (int) date('n', $anchor) - 1, 1, (int) date('Y', $anchor));
            $next = $periodEnd;
            $title = date('Y. m', $periodStart);
        }

        $weeks = array();
        for ($day = $gridStart; $day < $gridEnd;) {
            $week = array();
            for ($i = 0; $i < 7; $i++) {
                $week[] = date('Y-m-d', $day);
                $day = $this->addDays($day, 1);
            }
            $weeks[] = $week;
        }

        $today = mktime(0, 0, 0);

        return array(
            'view' => $view,
            'week_start' => $weekStart,
            'title' => $title,
            'start' => $gridStart,
            'end' => $gridEnd,
            'period_start' => date('Y-m-d', $periodStart),
            'period_end' => date('Y-m-d', $periodEnd), // exclusive
            'weeks' => $weeks,
            'weekdays' => $weekStart === 0 ? array(0, 1, 2, 3, 4, 5, 6) : array(1, 2, 3, 4, 5, 6, 0),
            'anchor' => date('Y-m-d', $anchor),
            'prev' => date('Y-m-d', $prev),
            'next' => date('Y-m-d', $next),
            // 보기 전환 시 기준일: 오늘이 현재 기간 안이면 오늘, 아니면 기간 첫날
            'switch_anchor' => ($today >= $periodStart && $today < $periodEnd) ? date('Y-m-d', $today) : date('Y-m-d', $periodStart),
        );
    }

    /**
     * 주어진 쿼리에 마감일 범위 조건을 붙여 날짜별로 묶어 반환
     * 마감일이 없는 태스크는 자연스럽게 제외됨
     */
    public function getTasksByDueDate(Table $query, $start, $end)
    {
        $tasks = $query
            ->gte(TaskModel::TABLE.'.date_due', $start)
            ->lt(TaskModel::TABLE.'.date_due', $end)
            ->asc(TaskModel::TABLE.'.date_due')
            ->asc(TaskModel::TABLE.'.position')
            ->findAll();

        $byDay = array();
        $now = time();

        foreach ($tasks as $task) {
            $task['is_overdue'] = $task['is_active'] == TaskModel::STATUS_OPEN && $task['date_due'] < $now;
            $task['has_time'] = date('H:i', $task['date_due']) !== '00:00';
            $byDay[date('Y-m-d', $task['date_due'])][] = $task;
        }

        return $byDay;
    }

    /**
     * 대시보드용: 사용자가 접근 가능한 활성 프로젝트 전체의 태스크 쿼리
     */
    public function getDashboardQuery($user_id, $project_id = 0, $status = 'open')
    {
        $project_ids = $this->projectPermissionModel->getActiveProjectIds($user_id);

        if ($project_id > 0) {
            $project_ids = in_array($project_id, $project_ids) ? array($project_id) : array();
        }

        $query = $this->taskFinderModel->getExtendedQuery()
            ->eq(ProjectModel::TABLE.'.is_active', ProjectModel::ACTIVE);

        if (empty($project_ids)) {
            $query->eq(TaskModel::TABLE.'.project_id', -1);
        } else {
            $query->in(TaskModel::TABLE.'.project_id', $project_ids);
        }

        if ($status === 'open') {
            $query->eq(TaskModel::TABLE.'.is_active', TaskModel::STATUS_OPEN);
        } elseif ($status === 'closed') {
            $query->eq(TaskModel::TABLE.'.is_active', TaskModel::STATUS_CLOSED);
        }

        return $query;
    }
}
