# Admin

## `/api/admin/platformReportStats/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ report: { acceptedSubmissions: number; activeSchools: number; activeUsers: { [key: string]: number; }; courses: number; omiCourse: { attemptedUsers: number; completedUsers: number; passedUsers: number; }; }; }
```

# Authorization

AuthorizationController

## `/api/authorization/problem/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  has_solved: boolean;
  is_admin: boolean;
  can_view: boolean;
  can_edit: boolean;
}
```

# Badge

BadgesController

## `/api/badge/badgeDetails/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
types.Badge;
```

## `/api/badge/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
string[]
```

## `/api/badge/myBadgeAssignationTime/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ assignation_time?: number; }
```

## `/api/badge/myList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ badges: types.Badge[]; }
```

## `/api/badge/userList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ badges: types.Badge[]; }
```

# Clarification

Description of ClarificationController

## `/api/clarification/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  clarification_id: number;
}
```

## `/api/clarification/details/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ message: string; answer?: string; time: number; problem_id: number; problemset_id?: number; }
```

## `/api/clarification/update/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

# Contest

ContestController

## `/api/contest/activityReport/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  events: {
    username: string;
    ip: number;
    time: number;
    classname: string;
    alias: string;
  }
  [];
}
```

## `/api/contest/addAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/addGroup/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/addGroupAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/addProblem/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/addUser/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/adminDetails/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ admin: boolean; admission_mode: string; alias: string; available_languages: { [key: string]: string; }; description: string; director?: string; feedback: string; finish_time: number; languages: string[]; needs_basic_information: boolean; partial_score: boolean; opened: boolean; original_contest_alias?: string; original_problemset_id?: number; penalty: number; penalty_calc_policy: string; penalty_type: string; problems: { accepted: number; alias: string; commit: string; difficulty: number; languages: string; letter: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]; points_decay_factor: number; problemset_id: number; requests_user_information: string; rerun_id: number; scoreboard: number; scoreboard_url: string; scoreboard_url_admin: string; show_scoreboard_after: boolean; start_time: number; submissions_gap: number; title: string; window_length?: number; }
```

## `/api/contest/adminList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  contests: {
    admission_mode: string;
    alias: string;
    finish_time: number;
    rerun_id: number;
    scoreboard_url: string;
    scoreboard_url_admin: string;
    start_time: number;
    title: string;
  }
  [];
}
```

## `/api/contest/admins/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  admins: {
    role: string;
    username: string;
  }
  [];
  group_admins: {
    alias: string;
    name: string;
    role: string;
  }
  [];
}
```

## `/api/contest/arbitrateRequest/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/clarifications/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ clarifications: { answer?: string; author: string; clarification_id: number; message: string; problem_alias: string; public: boolean; receiver?: string; time: number; }[]; }
```

## `/api/contest/clone/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  alias: string;
}
```

## `/api/contest/contestants/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ contestants: { name?: string; username: string; email?: string; state?: string; country?: string; school?: string; }[]; }
```

## `/api/contest/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/createVirtual/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  alias: string;
}
```

## `/api/contest/details/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ admin: boolean; admission_mode: string; alias: string; description: string; director?: string; feedback: string; finish_time: number; languages: string[]; needs_basic_information: boolean; opened: boolean; partial_score: boolean; original_contest_alias?: string; original_problemset_id?: number; penalty: number; penalty_calc_policy: string; penalty_type: string; problems: { accepted: number; alias: string; commit: string; difficulty: number; languages: string; letter: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]; points_decay_factor: number; problemset_id: number; requests_user_information: string; scoreboard: number; show_scoreboard_after: boolean; start_time: number; submissions_gap: number; submission_deadline: number; title: string; window_length?: number; }
```

## `/api/contest/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ number_of_results: number; results: { admission_mode: string; alias: string; contest_id: number; description: string; finish_time: number; last_updated: number; original_finish_time: Date; problemset_id: number; recommended: boolean; rerun_id: number; start_time: number; title: string; window_length?: number; }[]; }
```

## `/api/contest/listParticipating/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ contests: { acl_id: number; admission_mode: string; alias: string; contest_id: number; description: string; feedback: string; finish_time: number; languages?: string; last_updated: number; original_finish_time: Date; partial_score: number; penalty: number; penalty_calc_policy: string; penalty_type: string; points_decay_factor: number; problemset_id: number; recommended: boolean; rerun_id: number; scoreboard: number; scoreboard_url: string; scoreboard_url_admin: string; show_scoreboard_after: number; start_time: number; submissions_gap: number; title: string; urgent: number; window_length?: number; }[]; }
```

## `/api/contest/myList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ contests: { acl_id: number; admission_mode: string; alias: string; contest_id: number; description: string; feedback: string; finish_time: number; languages?: string; last_updated: number; original_finish_time: Date; partial_score: number; penalty: number; penalty_calc_policy: string; penalty_type: string; points_decay_factor: number; problemset_id: number; recommended: boolean; rerun_id: number; scoreboard: number; scoreboard_url: string; scoreboard_url_admin: string; show_scoreboard_after: number; start_time: number; submissions_gap: number; title: string; urgent: number; window_length?: number; }[]; }
```

## `/api/contest/open/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/problems/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  problems: {
    accepted: number;
    alias: string;
    commit: string;
    difficulty: number;
    languages: string;
    order: number;
    points: number;
    problem_id: number;
    submissions: number;
    title: string;
    version: string;
    visibility: number;
    visits: number;
  }
  [];
}
```

## `/api/contest/publicDetails/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ admission_mode: string; alias: string; description: string; feedback: string; finish_time: number; languages: string; partial_score: boolean; penalty: number; penalty_calc_policy: string; penalty_type: string; points_decay_factor: number; problemset_id: number; rerun_id: number; scoreboard: number; show_scoreboard_after: boolean; start_time: number; submissions_gap: number; title: string; window_length?: number; user_registration_requested: boolean; user_registration_answered: boolean; user_registration_accepted?: boolean; }
```

## `/api/contest/registerForContest/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/removeAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/removeGroup/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/removeGroupAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/removeProblem/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/removeUser/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/report/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ finish_time?: number; problems: { alias: string; order: number; }[]; ranking: { country?: string; is_invited: boolean; name?: string; place: number; problems: { alias: string; penalty: number; percent: number; place: number; points: number; run_details: { cases: { contest_score: number; max_score: number; meta: { status: string; }; name?: string; out_diff: string; score: number; verdict: string; }[]; details: { groups: { cases: { meta: { memory: number; time: number; wall_time: number; }; }[]; }[]; }; }; runs: number; }[]; total: { penalty: number; points: number; }; username: string; }[]; start_time: number; time: number; title: string; }
```

## `/api/contest/requests/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ users: { accepted?: boolean; admin: { username?: string; }; country?: string; last_update?: Date; request_time: Date; username: string; }[]; contest_alias: string; }
```

## `/api/contest/role/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  admin: boolean;
}
```

## `/api/contest/runs/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ runs: { run_id: number; guid: string; language: string; status: string; verdict: string; runtime: number; penalty: number; memory: number; score: number; contest_score: number; judged_by?: string; time: number; submit_delay: number; type?: string; username: string; alias: string; country_id?: string; contest_alias?: string; }[]; }
```

## `/api/contest/runsDiff/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ diff: { guid: string; new_score?: number; new_status?: string; new_verdict?: string; old_score?: number; old_status?: string; old_verdict?: string; problemset_id?: number; username: string; }[]; }
```

## `/api/contest/scoreboard/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ finish_time?: number; problems: { alias: string; order: number; }[]; ranking: { country?: string; is_invited: boolean; name?: string; place: number; problems: { alias: string; penalty: number; percent: number; place: number; points: number; run_details: { cases: { contest_score: number; max_score: number; meta: { status: string; }; name?: string; out_diff: string; score: number; verdict: string; }[]; details: { groups: { cases: { meta: { memory: number; time: number; wall_time: number; }; }[]; }[]; }; }; runs: number; }[]; total: { penalty: number; points: number; }; username: string; }[]; start_time: number; time: number; title: string; }
```

## `/api/contest/scoreboardEvents/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ events: { country?: string; delta: number; is_invited: boolean; total: { points: number; penalty: number; }; name?: string; username: string; problem: { alias: string; points: number; penalty: number; }; }[]; }
```

## `/api/contest/scoreboardMerge/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ ranking: { name?: string; username: string; contests: { [key: string]: { points: number; penalty: number; }; }; total: { points: number; penalty: number; }; }[]; }
```

## `/api/contest/setRecommended/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/stats/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ total_runs: number; pending_runs: string[]; max_wait_time: number; max_wait_time_guid?: string; verdict_counts: { [key: string]: number; }; distribution: { [key: number]: number; }; size_of_bucket: number; total_points: number; }
```

## `/api/contest/update/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/updateEndTimeForIdentity/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/contest/users/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ users: { access_time?: number; country_id?: string; end_time?: number; is_owner?: number; username: string; }[]; groups: { alias: string; name: string; }[]; }
```

# Course

CourseController

## `/api/course/activityReport/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  events: {
    username: string;
    ip: number;
    time: number;
    classname: string;
    alias: string;
  }
  [];
}
```

## `/api/course/addAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/addGroupAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/addProblem/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/addStudent/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/adminDetails/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ name: string; description: string; alias: string; basic_information_required: boolean; requests_user_information: string; assignments: { name: string; description: string; alias: string; publish_time_delay?: number; assignment_type: string; start_time: number; finish_time?: number; max_points: number; order: number; scoreboard_url: string; scoreboard_url_admin: string; }[]; school_id?: number; start_time: number; finish_time?: number; is_admin: boolean; public: boolean; show_scoreboard: boolean; student_count: number; school_name?: string; }
```

## `/api/course/admins/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  admins: {
    role: string;
    username: string;
  }
  [];
  group_admins: {
    alias: string;
    name: string;
    role: string;
  }
  [];
}
```

## `/api/course/arbitrateRequest/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/assignmentDetails/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ name?: string; description?: string; assignment_type?: string; start_time: number; finish_time?: number; problems: { accepted: number; alias: string; commit: string; difficulty: number; languages: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]; director: string; problemset_id: number; admin: boolean; }
```

## `/api/course/assignmentScoreboard/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ finish_time?: number; problems: { alias: string; order: number; }[]; ranking: { country?: string; is_invited: boolean; name?: string; place: number; problems: { alias: string; penalty: number; percent: number; place: number; points: number; run_details: { cases: { contest_score: number; max_score: number; meta: { status: string; }; name?: string; out_diff: string; score: number; verdict: string; }[]; details: { groups: { cases: { meta: { memory: number; time: number; wall_time: number; }; }[]; }[]; }; }; runs: number; }[]; total: { penalty: number; points: number; }; username: string; }[]; start_time: number; time: number; title: string; }
```

## `/api/course/assignmentScoreboardEvents/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ events: { country?: string; delta: number; is_invited: boolean; name?: string; problem: { alias: string; penalty: number; points: number; }; total: { penalty: number; points: number; }; username: string; }[]; }
```

## `/api/course/clone/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  alias: string;
}
```

## `/api/course/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/createAssignment/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/details/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ name: string; description: string; alias: string; basic_information_required: boolean; requests_user_information: string; assignments: { name: string; description: string; alias: string; publish_time_delay?: number; assignment_type: string; start_time: number; finish_time?: number; max_points: number; order: number; scoreboard_url: string; scoreboard_url_admin: string; }[]; school_id?: number; start_time: number; finish_time?: number; is_admin: boolean; public: boolean; show_scoreboard: boolean; student_count: number; school_name?: string; }
```

## `/api/course/getProblemUsers/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ identities: string[]; }
```

## `/api/course/introDetails/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ name: string; description: string; alias: string; currentUsername: string; needsBasicInformation: boolean; requestsUserInformation: string; shouldShowAcceptTeacher: boolean; statements: { privacy: { markdown?: string; gitObjectId?: string; statementType?: string; }; acceptTeacher: { gitObjectId?: string; markdown: string; statementType: string; }; }; isFirstTimeAccess: boolean; shouldShowResults: boolean; }
```

## `/api/course/listAssignments/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ assignments: { alias: string; assignment_type: string; description: string; finish_time?: number; has_runs: boolean; name: string; order: number; scoreboard_url: string; scoreboard_url_admin: string; start_time: number; }[]; }
```

## `/api/course/listCourses/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ admin: { alias: string; counts: { [key: string]: number; }; finish_time?: number; name: string; start_time: number; }[]; public: { alias: string; counts: { [key: string]: number; }; finish_time?: number; name: string; start_time: number; }[]; student: { alias: string; counts: { [key: string]: number; }; finish_time?: number; name: string; start_time: number; }[]; }
```

## `/api/course/listSolvedProblems/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ user_problems: { [key: string]: { alias: string; title: string; username: string; }[]; }; }
```

## `/api/course/listStudents/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ students: { name?: string; progress: { [key: string]: number; }; username: string; }[]; }
```

## `/api/course/listUnsolvedProblems/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ user_problems: { [key: string]: { alias: string; title: string; username: string; }[]; }; }
```

## `/api/course/myProgress/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  assignments: types.AssignmentProgress;
}
```

## `/api/course/registerForCourse/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/removeAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/removeGroupAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/removeProblem/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/removeStudent/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/requests/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ users: { accepted?: boolean; admin: { name?: string; username: string; }; country?: string; country_id?: string; last_update?: string; request_time: string; username: string; }[]; }
```

## `/api/course/runs/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ runs: { run_id: number; guid: string; language: string; status: string; verdict: string; runtime: number; penalty: number; memory: number; score: number; contest_score: number; judged_by?: string; time: number; submit_delay: number; type?: string; username: string; alias: string; country_id?: string; contest_alias?: string; }[]; }
```

## `/api/course/studentProgress/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ problems: { accepted: number; alias: string; commit: string; difficulty: number; languages: string; letter: string; order: number; points: number; submissions: number; title: string; version: string; visibility: number; visits: number; runs: { guid: string; language: string; source: string; status: string; verdict: string; runtime: number; penalty: number; memory: number; score: number; contest_score?: number; time: number; submit_delay: number; }[]; }[]; }
```

## `/api/course/update/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/updateAssignment/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/updateAssignmentsOrder/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/course/updateProblemsOrder/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

# Grader

Description of GraderController

## `/api/grader/status/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ grader: { status: string; broadcaster_sockets: number; embedded_runner: boolean; queue: { running: { name: string; id: number; }[]; run_queue_length: number; runner_queue_length: number; runners: string[]; }; }; }
```

# Group

GroupController

## `/api/group/addUser/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/group/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/group/createScoreboard/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/group/details/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ exists: boolean; group: { create_time: number; alias?: string; name?: string; description?: string; }; scoreboards: { alias: string; create_time: string; description?: string; name: string; }[]; }
```

## `/api/group/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  label: string;
  value: string;
}
[];
```

## `/api/group/members/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ identities: { classname: string; country?: string; country_id?: string; name?: string; school?: string; school_id?: number; state?: string; state_id?: string; username: string; }[]; }
```

## `/api/group/myList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ groups: { alias: string; create_time: number; description?: string; name: string; }[]; }
```

## `/api/group/removeUser/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

# GroupScoreboard

GroupScoreboardController

## `/api/groupScoreboard/addContest/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/groupScoreboard/details/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ ranking: { name?: string; username: string; contests: { [key: string]: { points: number; penalty: number; }; }; total: { points: number; penalty: number; }; }[]; scoreboard: { group_scoreboard_id: number; group_id: number; create_time: number; alias: string; name: string; description: string; }; contests: { contest_id: number; problemset_id: number; acl_id: number; title: string; description: string; start_time: number; finish_time: number; last_updated: number; window_length?: number; rerun_id: number; admission_mode: string; alias: string; scoreboard: number; points_decay_factor: number; partial_score: boolean; submissions_gap: number; feedback: string; penalty: string; penalty_calc_policy: string; show_scoreboard_after: boolean; urgent: boolean; languages: string; recommended: boolean; only_ac: boolean; weight: number; }[]; }
```

## `/api/groupScoreboard/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  scoreboards: {
    group_scoreboard_id: number;
    group_id: number;
    create_time: number;
    alias: string;
    name: string;
    description: string;
  }
  [];
}
```

## `/api/groupScoreboard/removeContest/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

# Identity

IdentityController

## `/api/identity/bulkCreate/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/identity/changePassword/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/identity/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  username: string;
}
```

## `/api/identity/update/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

# Interview

## `/api/interview/addUsers/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/interview/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/interview/details/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ description?: string; contest_alias?: string; problemset_id?: number; users: { user_id?: number; username: string; access_time?: Date; email?: string; opened_interview: boolean; country?: string; }[]; exists: boolean; }
```

## `/api/interview/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  result: {
    acl_id: number;
    alias: string;
    description: string;
    interview_id: number;
    problemset_id: number;
    title: string;
    window_length: number;
  }
  [];
}
```

# Notification

BadgesController

## `/api/notification/myList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  notifications: {
    contents: string;
    notification_id: number;
    timestamp: number;
  }
  [];
}
```

## `/api/notification/readNotifications/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

# Problem

ProblemsController

## `/api/problem/addAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/addGroupAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/addTag/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  name: string;
}
```

## `/api/problem/adminList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ pagerItems: types.PageItem[]; problems: { tags: { name: string; source: string; }[]; }[]; }
```

## `/api/problem/admins/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  admins: {
    role: string;
    username: string;
  }
  [];
  group_admins: {
    alias: string;
    name: string;
    role: string;
  }
  [];
}
```

## `/api/problem/bestScore/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  score: number;
}
```

## `/api/problem/clarifications/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ clarifications: { clarification_id: number; contest_alias: string; author?: string; message: string; time: number; answer?: string; public: boolean; }[]; }
```

## `/api/problem/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/delete/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/details/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ accepted: number; admin: boolean; alias: string; commit: string; creation_date: number; difficulty?: number; email_clarifications: boolean; exists: boolean; input_limit: number; languages: string[]; order: string; points: number; preferred_language: string; problemsetter: { creation_date: number; name: string; username: string; }; quality_seal: boolean; runs: { alias: string; contest_score?: number; guid: string; language: string; memory: number; penalty: number; runtime: number; score: number; status: string; submit_delay: number; time: number; username: string; verdict: string; }[]; score: number; settings: { cases: { [key: string]: { in: string; out: string; weight: number; }; }; limits: { MemoryLimit: number|string; OverallWallTimeLimit: string; TimeLimit: string; }; validator: { name: string; tolerance: number; }; }; solvers: { language: string; memory: number; runtime: number; time: number; username: string; }[]; source: string; statement: { images: { [key: string]: string; }; language: string; markdown: string; }; submissions: number; title: string; version: string; visibility: number; visits: number; }
```

## `/api/problem/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ results: { alias: string; difficulty?: number; difficulty_histogram: number[]; points: number; quality?: number; quality_histogram: number[]; ratio: number; score: number; tags: { source: string; name: string; }[]; title: string; visibility: number; quality_seal: boolean; }[]; total: number; }
```

## `/api/problem/myList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ pagerItems: types.PageItem[]; problems: { tags: { name: string; source: string; }[]; }[]; }
```

## `/api/problem/rejudge/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/removeAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/removeGroupAdmin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/removeTag/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/runs/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ runs: { guid: string; language: string; status: string; verdict: string; runtime: number; penalty: number; memory: number; score: number; contest_score?: number; time: number; submit_delay: number; alias: string; username: string; run_id: number; judged_by?: string; type?: string; country_id?: string; contest_alias?: string; }[]; }
```

## `/api/problem/runsDiff/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ diff: { username: string; guid: string; problemset_id?: number; old_status?: string; old_verdict?: string; old_score?: number; new_status?: string; new_verdict?: string; new_score?: number; }[]; }
```

## `/api/problem/selectVersion/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/solution/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ exists: boolean; solution: { language: string; markdown: string; images: { [key: string]: string; }; }; }
```

## `/api/problem/stats/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ cases_stats: { [key: string]: number; }; pending_runs: { guid: string; }[]; total_runs: number; verdict_counts: { [key: string]: number; }; }
```

## `/api/problem/tags/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  tags: {
    name: string;
    public: boolean;
  }
  [];
}
```

## `/api/problem/update/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  rejudged: boolean;
}
```

## `/api/problem/updateSolution/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/updateStatement/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/problem/versions/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ published?: string; log: { commit: string; tree?: { [key: string]: string; }; parents: string[]; author: { name: string; email: string; time?: number|string; }; committer: { name: string; email: string; time?: number|string; }; message: string; version?: string; }[]; }
```

# ProblemForfeited

ProblemForfeitedController

## `/api/problemForfeited/getCounts/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  allowed: number;
  seen: number;
}
```

# Problemset

## `/api/problemset/details/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ admin: boolean; admission_mode: string; alias: string; assignment_type?: string; contest_alias?: string; description?: string; director?: string|dao.Identities; exists: boolean; feedback: string; finish_time?: number; languages: string[]; name?: string; needs_basic_information: boolean; opened: boolean; original_contest_alias?: string; original_problemset_id?: number; partial_score: boolean; penalty: number; penalty_calc_policy: string; penalty_type: string; points_decay_factor: number; problems: { accepted: number; alias: string; commit: string; difficulty: number; languages: string; letter: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]; problemset_id?: number; requests_user_information: string; scoreboard: number; show_scoreboard_after: boolean; start_time: number; submission_deadline: number; submissions_gap: number; title: string; users: { access_time?: Date; country?: string; email?: string; opened_interview: boolean; user_id?: number; username: string; }[]; window_length?: number; }
```

## `/api/problemset/scoreboard/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ finish_time?: number; problems: { alias: string; order: number; }[]; ranking: { country?: string; is_invited: boolean; name?: string; place: number; problems: { alias: string; penalty: number; percent: number; place: number; points: number; run_details: { cases: { contest_score: number; max_score: number; meta: { status: string; }; name?: string; out_diff: string; score: number; verdict: string; }[]; details: { groups: { cases: { meta: { memory: number; time: number; wall_time: number; }; }[]; }[]; }; }; runs: number; }[]; total: { penalty: number; points: number; }; username: string; }[]; start_time: number; time: number; title: string; }
```

## `/api/problemset/scoreboardEvents/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ events: { country?: string; delta: number; is_invited: boolean; total: { points: number; penalty: number; }; name?: string; username: string; problem: { alias: string; points: number; penalty: number; }; }[]; }
```

# QualityNomination

## `/api/qualityNomination/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  qualitynomination_id: number;
}
```

## `/api/qualityNomination/details/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ author: { name?: string; username: string; }; contents: { before_ac: boolean; difficulty: number; quality: number; rationale: string; reason: string; statements: { [key: string]: string; }; tags: string[]; }; nomination: string; nomination_status: string; nominator: { name?: string; username: string; }; original_contents: { source?: string; statements: { [key: string]: { language: string; markdown: string; images: { [key: string]: string; }; }; }; tags: { source: string; name: string; }[]; }; problem: { alias: string; title: string; }; qualitynomination_id: number; reviewer: boolean; time: number; votes: { time?: number; user: { name?: string; username: string; }; vote: number; }[]; }
```

## `/api/qualityNomination/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ totalRows: number; nominations: { author: { name?: string; username: string; }; contents: { before_ac: boolean; difficulty: number; quality: number; rationale: string; reason: string; statements: { [key: string]: string; }; tags: string[]; }; nomination: string; nominator: { name?: string; username: string; }; problem: { alias: string; title: string; }; qualitynomination_id: number; status: string; time: number; votes: { time?: number; user: { name?: string; username: string; }; vote: number; }[]; }|null[]; }
```

## `/api/qualityNomination/myAssignedList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ nominations: { author: { name?: string; username: string; }; contents: { before_ac: boolean; difficulty: number; quality: number; rationale: string; reason: string; statements: { [key: string]: string; }; tags: string[]; }; nomination: string; nominator: { name?: string; username: string; }; problem: { alias: string; title: string; }; qualitynomination_id: number; status: string; time: number; votes: { time?: number; user: { name?: string; username: string; }; vote: number; }[]; }|null[]; }
```

## `/api/qualityNomination/myList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ totalRows: number; nominations: { author: { name?: string; username: string; }; contents: { before_ac: boolean; difficulty: number; quality: number; rationale: string; reason: string; statements: { [key: string]: string; }; tags: string[]; }; nomination: string; nominator: { name?: string; username: string; }; problem: { alias: string; title: string; }; qualitynomination_id: number; status: string; time: number; votes: { time?: number; user: { name?: string; username: string; }; vote: number; }[]; }|null[]; }
```

## `/api/qualityNomination/resolve/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

# Reset

## `/api/reset/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  message: string;
  token: string;
}
```

## `/api/reset/generateToken/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  link: string;
  token: string;
}
```

## `/api/reset/update/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  message: string;
}
```

# Run

RunController

## `/api/run/counts/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ total: { [key: string]: number; }; ac: { [key: string]: number; }; }
```

## `/api/run/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  guid: string;
  submission_deadline: number;
  nextSubmissionTimestamp: number;
}
```

## `/api/run/details/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ admin: boolean; compile_error: string; details: { compile_meta: { [key: string]: { memory: number; sys_time: number; time: number; verdict: string; wall_time: number; }; }; contest_score: number; groups: { cases: { contest_score: number; max_score: number; meta: { verdict: string; }; name: string; score: number; verdict: string; }[]; contest_score: number; group: string; max_score: number; score: number; }[]; judged_by: string; max_score: number; memory: number; score: number; time: number; verdict: string; wall_time: number; }; guid: string; judged_by: string; language: string; logs: string; source: string; }
```

## `/api/run/disqualify/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/run/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ runs: { alias: string; contest_alias?: string; contest_score?: number; country_id?: string; guid: string; judged_by?: string; language: string; memory: number; penalty: number; run_id: number; runtime: number; score: number; submit_delay: number; time: number; type?: string; username: string; verdict: string; }[]; }
```

## `/api/run/rejudge/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/run/source/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ compile_error: string; details: { compile_meta: { [key: string]: { memory: number; sys_time: number; time: number; verdict: string; wall_time: number; }; }; contest_score: number; groups: { cases: { contest_score: number; max_score: number; meta: { verdict: string; }; name: string; score: number; verdict: string; }[]; contest_score: number; group: string; max_score: number; score: number; }[]; judged_by: string; max_score: number; memory: number; score: number; time: number; verdict: string; wall_time: number; }; source: string; }
```

## `/api/run/status/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ contest_score?: number; memory: number; penalty: number; runtime: number; score: number; submit_delay: number; time: number; }
```

# School

Description of SchoolController

## `/api/school/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  school_id: number;
}
```

## `/api/school/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  id: number;
  label: string;
  value: string;
}
[];
```

## `/api/school/monthlySolvedProblemsCount/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  distinct_problems_solved: {
    month: number;
    problems_solved: number;
    year: number;
  }
  [];
}
```

## `/api/school/rank/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ rank: { country_id?: string; name: string; ranking?: number; school_id: number; score: number; }[]; totalRows: number; }
```

## `/api/school/schoolCodersOfTheMonth/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  coders: {
    time: string;
    username: string;
    classname: string;
  }
  [];
}
```

## `/api/school/selectSchoolOfTheMonth/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/school/users/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  users: {
    username: string;
    classname: string;
    created_problems: number;
    solved_problems: number;
    organized_contests: number;
  }
  [];
}
```

# Scoreboard

ScoreboardController

## `/api/scoreboard/refresh/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

# Session

Session controller handles sessions.

## `/api/session/currentSession/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ session?: { valid: boolean; email?: string; user?: dao.Users; identity?: dao.Identities; auth_token?: string; is_admin: boolean; }; time: number; }
```

## `/api/session/googleLogin/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ [key: string]: string; }
```

# Submission

SubmissionController

## `/api/submission/latestSubmissions/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ submissions: { time: number; username: string; school_id?: number; school_name?: string; alias: string; title: string; language: string; verdict: string; runtime: number; memory: number; }[]; totalRows: number; }
```

# Tag

TagController

## `/api/tag/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  name: string;
}
[];
```

# Time

TimeController

Used by arena to sync time between client and server from time to time

## `/api/time/get/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  time: number;
}
```

# User

UserController

## `/api/user/acceptPrivacyPolicy/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/addExperiment/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/addGroup/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/addRole/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/associateIdentity/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/changePassword/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/coderOfTheMonth/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ coderinfo?: { birth_date?: number; country?: string; country_id?: string; email?: string; gender?: string; graduation_date?: number; gravatar_92: string; hide_problem_tags?: boolean; is_private: boolean; locale: string; name?: string; preferred_language?: string; scholar_degree?: string; school?: string; school_id?: number; state?: string; state_id?: string; username?: string; verified: boolean; }; }
```

## `/api/user/coderOfTheMonthList/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  coders: {
    username: string;
    country_id: string;
    gravatar_32: string;
    date: string;
    classname: string;
  }
  [];
}
```

## `/api/user/contestStats/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ contests: { [key: string]: { data: { alias: string; title: string; start_time: number; finish_time: number; last_updated: number; }; place?: number; }; }; }
```

## `/api/user/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  username: string;
}
```

## `/api/user/extraInformation/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ within_last_day: boolean; verified: boolean; username: string; last_login?: number; }
```

## `/api/user/generateGitToken/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  token: string;
}
```

## `/api/user/generateOmiUsers/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ [key: string]: string; }
```

## `/api/user/interviewStats/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ user_verified: boolean; interview_url: string; name_or_username?: string; opened_interview: boolean; finished: boolean; }
```

## `/api/user/lastPrivacyPolicyAccepted/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  hasAccepted: boolean;
}
```

## `/api/user/list/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
types.UserListItem[]
```

## `/api/user/listAssociatedIdentities/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ identities: { username: string; default: boolean; }[]; }
```

## `/api/user/listUnsolvedProblems/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ problems: types.Problem[]; }
```

## `/api/user/login/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  auth_token: string;
}
```

## `/api/user/mailingListBackfill/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ users: { [key: string]: boolean; }; }
```

## `/api/user/problemsCreated/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ problems: types.Problem[]; }
```

## `/api/user/problemsSolved/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ problems: types.Problem[]; }
```

## `/api/user/profile/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ birth_date?: number; classname: string; country?: string; country_id?: string; email?: string; gender?: string; graduation_date?: number; gravatar_92?: string; hide_problem_tags?: boolean; is_private: boolean; locale?: string; name?: string; preferred_language?: string; rankinfo: { name?: string; problems_solved?: number; rank?: number; }; scholar_degree?: string; school?: string; school_id?: number; state?: string; state_id?: string; username?: string; verified?: boolean; }
```

## `/api/user/rankByProblemsSolved/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ rank: { classname: string; country_id?: string; name?: string; problems_solved: number; ranking: number; score: number; user_id: number; username: string; }[]|number; total: number; name: string; problems_solved: number; }
```

## `/api/user/removeExperiment/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/removeGroup/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/removeRole/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/selectCoderOfTheMonth/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/stats/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ runs: { date?: string; runs: number; verdict: string; }[]; }
```

## `/api/user/statusVerified/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  username: string;
  verified: boolean;
}
```

## `/api/user/update/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/updateBasicInfo/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/updateMainEmail/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```

## `/api/user/validateFilter/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{ user?: string; admin: boolean; problem_admin: string[]; contest_admin: string[]; problemset_admin: number[]; }
```

## `/api/user/verifyEmail/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
  status: string;
}
```
