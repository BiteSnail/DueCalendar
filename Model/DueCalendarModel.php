<?php

namespace Kanboard\Plugin\DueCalendar\Model;

use Kanboard\Core\Base;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\TaskModel;
use PicoDb\Table;

/**
 * 월간 그리드 계산 + 마감일 기준 태스크 조회
 */
class DueCalendarModel extends Base
{
    /**
     * 'YYYY-MM' 문자열을 검증하고 해당 월 1일 00:00 타임스탬프를 반환
     */
    public function parseMonth($month)
    {
        if (! is_string($month) || ! preg_match('/^(\d{4})-(\d{2})$/', $month, $m) || (int) $m[2] < 1 || (int) $m[2] > 12) {
            $month = date('Y-m');
            preg_match('/^(\d{4})-(\d{2})$/', $month, $m);
        }

        return mktime(0, 0, 0, (int) $m[2], 1, (int) $m[1]);
    }

    /**
     * 월요일 시작 6주(또는 5주) 그리드
     *
     * @return array [ 'start' => ts, 'end' => ts(exclusive), 'weeks' => [[ 'Y-m-d', ... x7 ], ...] ]
     */
    public function buildGrid($monthStart)
    {
        $weekday = (int) date('N', $monthStart); // 1 = 월요일
        $gridStart = strtotime('-'.($weekday - 1).' days', $monthStart);
        $monthEnd = strtotime('+1 month', $monthStart);

        $weeks = array();
        $day = $gridStart;

        while ($day < $monthEnd || count($weeks) === 0) {
            $week = array();
            for ($i = 0; $i < 7; $i++) {
                $week[] = date('Y-m-d', $day);
                $day = strtotime('+1 day', $day);
            }
            $weeks[] = $week;
        }

        return array(
            'start' => $gridStart,
            'end' => $day,
            'weeks' => $weeks,
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
