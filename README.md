# DueCalendar – Kanboard 배포 캘린더 플러그인

태스크를 **마감일(date_due) 기준으로만** 월간 캘린더에 표시합니다.
시작일·생성일은 무시하고, 마감일이 없는 태스크는 표시하지 않으므로 배포/릴리즈 일정표로 쓰기 좋습니다.

## 기능
- **프로젝트 뷰**: 상단 `개요 / 보드 / 목록` 옆에 **배포 캘린더** 탭이 추가됩니다.
  - 상단 검색창 필터(`assignee:`, `category:`, `column:`, `swimlane:`, `tag:` 등)가 그대로 적용됩니다.
  - 기본값은 Kanboard 기본 필터(`status:open`)이며, **완료된 태스크 포함** 링크로 `status:all` 전환이 됩니다.
- **대시보드 뷰**: 대시보드 왼쪽 메뉴의 **배포 캘린더**에서 내가 속한 모든 활성 프로젝트를 한 번에 봅니다.
  - 프로젝트 선택, 상태(전체/진행 중/완료) 필터가 있습니다.
- 월요일 시작, 주말 표시, 오늘 강조, 이전/다음 달 이동
- 태스크 색상 그대로 표시, 마감 시간이 있으면 `14:00`처럼 표시
- 지난 마감일인데 아직 열려 있으면 **빨간색(지연)**, 완료된 태스크는 취소선
- 달력 아래 **배포 일정** 표(마감일·프로젝트·태스크·컬럼·담당자·상태)

## 설치
1. `DueCalendar` 폴더를 Kanboard의 `plugins/` 디렉터리에 복사합니다.
   (폴더 이름은 반드시 `DueCalendar`이어야 합니다.)
   ```
   kanboard/plugins/DueCalendar/Plugin.php
   ```
2. 별도 DB 마이그레이션은 없습니다. 페이지를 새로고침하면 바로 적용됩니다.
3. Docker 이미지라면 `/var/www/app/plugins` 볼륨에 넣으면 됩니다.

## 호환성
- Kanboard 1.2.x 이상 (최신 master에서 테스트, PHP 8.3)
- 공식 Calendar 플러그인과 같이 써도 충돌하지 않습니다.

## URL
- 프로젝트: `/?controller=DueCalendarController&action=project&project_id=1&plugin=DueCalendar&month=2026-11`
- 전체: `/?controller=DueCalendarController&action=dashboard&plugin=DueCalendar`
  (URL 리라이트를 쓰면 `/release-calendar/project/1`, `/release-calendar`)
