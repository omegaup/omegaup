# Admin

## `/api/admin/platformReportStats/`

### Descripción

Get stats for an overall platform report.

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

Returns the number of owners and the first
assignation timestamp for a certain badge

### Parámetros

_Por documentar_

### Regresa

```typescript
types.Badge;
```

## `/api/badge/list/`

### Descripción

Returns a list of existing badges

### Parámetros

_Por documentar_

### Regresa

```typescript
string[]
```

## `/api/badge/myBadgeAssignationTime/`

### Descripción

Returns a the assignation timestamp of a badge
for current user.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ assignation_time?: number; }
```

## `/api/badge/myList/`

### Descripción

Returns a list of badges owned by current user

### Parámetros

_Por documentar_

### Regresa

```typescript
{ badges: types.Badge[]; }
```

## `/api/badge/userList/`

### Descripción

Returns a list of badges owned by a certain user

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

Creates a Clarification

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

API for getting a clarification

### Parámetros

_Por documentar_

### Regresa

```typescript
{ message: string; answer?: string; time: number; problem_id: number; problemset_id?: number; }
```

## `/api/clarification/update/`

### Descripción

Update a clarification

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

# Contest

ContestController

## `/api/contest/activityReport/`

### Descripción

Returns a report with all user activity for a contest.

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

Adds an admin to a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/addGroup/`

### Descripción

Adds an group to a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/addGroupAdmin/`

### Descripción

Adds an group admin to a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/addProblem/`

### Descripción

Adds a problem to a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/addUser/`

### Descripción

Adds a user to a contest.
By default, any user can view details of public contests.
Only users added through this API can view private contests

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/adminDetails/`

### Descripción

Returns details of a Contest, for administrators. This differs from
apiDetails in the sense that it does not attempt to calculate the
remaining time from the contest, or register the opened time.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ admin: boolean; admission_mode: string; alias: string; available_languages: { [key: string]: string; }; description: string; director?: string; feedback: string; finish_time: number; languages: string[]; needs_basic_information: boolean; partial_score: boolean; opened: boolean; original_contest_alias?: string; original_problemset_id?: number; penalty: number; penalty_calc_policy: string; penalty_type: string; problems: { accepted: number; alias: string; commit: string; difficulty: number; languages: string; letter: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]; points_decay_factor: number; problemset_id: number; requests_user_information: string; rerun_id: number; scoreboard: number; scoreboard_url: string; scoreboard_url_admin: string; show_scoreboard_after: boolean; start_time: number; submissions_gap: number; title: string; window_length?: number; }
```

## `/api/contest/adminList/`

### Descripción

Returns a list of contests where current user has admin rights (or is
the director).

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

Returns all contest administrators

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
}
```

## `/api/contest/clarifications/`

### Descripción

Get clarifications of a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{ clarifications: { answer?: string; author: string; clarification_id: number; message: string; problem_alias: string; public: boolean; receiver?: string; time: number; }[]; }
```

## `/api/contest/clone/`

### Descripción

Clone a contest

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

Return users who participate in a contest, as long as contest admin
has chosen to ask for users information and contestants have
previously agreed to share their information.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ contestants: { name?: string; username: string; email?: string; state?: string; country?: string; school?: string; }[]; }
```

## `/api/contest/create/`

### Descripción

Creates a new contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
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

Returns details of a Contest. Requesting the details of a contest will
not start the current user into that contest. In order to participate
in the contest, \OmegaUp\Controllers\Contest::apiOpen() must be used.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ admin: boolean; admission_mode: string; alias: string; description: string; director?: string; feedback: string; finish_time: number; languages: string[]; needs_basic_information: boolean; opened: boolean; partial_score: boolean; original_contest_alias?: string; original_problemset_id?: number; penalty: number; penalty_calc_policy: string; penalty_type: string; problems: { accepted: number; alias: string; commit: string; difficulty: number; languages: string; letter: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]; points_decay_factor: number; problemset_id: number; requests_user_information: string; scoreboard: number; show_scoreboard_after: boolean; start_time: number; submissions_gap: number; submission_deadline: number; title: string; window_length?: number; }
```

## `/api/contest/list/`

### Descripción

Returns a list of contests

### Parámetros

_Por documentar_

### Regresa

```typescript
{ number_of_results: number; results: { admission_mode: string; alias: string; contest_id: number; description: string; finish_time: number; last_updated: number; original_finish_time: Date; problemset_id: number; recommended: boolean; rerun_id: number; start_time: number; title: string; window_length?: number; }[]; }
```

## `/api/contest/listParticipating/`

### Descripción

Returns a list of contests where current user is participating in

### Parámetros

_Por documentar_

### Regresa

```typescript
{ contests: { acl_id: number; admission_mode: string; alias: string; contest_id: number; description: string; feedback: string; finish_time: number; languages?: string; last_updated: number; original_finish_time: Date; partial_score: number; penalty: number; penalty_calc_policy: string; penalty_type: string; points_decay_factor: number; problemset_id: number; recommended: boolean; rerun_id: number; scoreboard: number; scoreboard_url: string; scoreboard_url_admin: string; show_scoreboard_after: number; start_time: number; submissions_gap: number; title: string; urgent: number; window_length?: number; }[]; }
```

## `/api/contest/myList/`

### Descripción

Returns a list of contests where current user is the director

### Parámetros

_Por documentar_

### Regresa

```typescript
{ contests: { acl_id: number; admission_mode: string; alias: string; contest_id: number; description: string; feedback: string; finish_time: number; languages?: string; last_updated: number; original_finish_time: Date; partial_score: number; penalty: number; penalty_calc_policy: string; penalty_type: string; points_decay_factor: number; problemset_id: number; recommended: boolean; rerun_id: number; scoreboard: number; scoreboard_url: string; scoreboard_url_admin: string; show_scoreboard_after: number; start_time: number; submissions_gap: number; title: string; urgent: number; window_length?: number; }[]; }
```

## `/api/contest/open/`

### Descripción

Joins a contest - explicitly adds a identity to a contest.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/problems/`

### Descripción

Gets the problems from a contest

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
}
```

## `/api/contest/removeAdmin/`

### Descripción

Removes an admin from a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/removeGroup/`

### Descripción

Removes a group from a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/removeGroupAdmin/`

### Descripción

Removes a group admin from a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/removeProblem/`

### Descripción

Removes a problem from a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/removeUser/`

### Descripción

Remove a user from a private contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/report/`

### Descripción

Returns a detailed report of the contest

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

Given a contest_alias and user_id, returns the role of the user within
the context of a contest.

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

Returns all runs for a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{ runs: { run_id: number; guid: string; language: string; status: string; verdict: string; runtime: number; penalty: number; memory: number; score: number; contest_score: number; judged_by?: string; time: number; submit_delay: number; type?: string; username: string; alias: string; country_id?: string; contest_alias?: string; }[]; }
```

## `/api/contest/runsDiff/`

### Descripción

Return a report of which runs would change due to a version change.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ diff: { guid: string; new_score?: number; new_status?: string; new_verdict?: string; old_score?: number; old_status?: string; old_verdict?: string; problemset_id?: number; username: string; }[]; }
```

## `/api/contest/scoreboard/`

### Descripción

Returns the Scoreboard

### Parámetros

_Por documentar_

### Regresa

```typescript
{ finish_time?: number; problems: { alias: string; order: number; }[]; ranking: { country?: string; is_invited: boolean; name?: string; place: number; problems: { alias: string; penalty: number; percent: number; place: number; points: number; run_details: { cases: { contest_score: number; max_score: number; meta: { status: string; }; name?: string; out_diff: string; score: number; verdict: string; }[]; details: { groups: { cases: { meta: { memory: number; time: number; wall_time: number; }; }[]; }[]; }; }; runs: number; }[]; total: { penalty: number; points: number; }; username: string; }[]; start_time: number; time: number; title: string; }
```

## `/api/contest/scoreboardEvents/`

### Descripción

Returns the Scoreboard events

### Parámetros

_Por documentar_

### Regresa

```typescript
{ events: { country?: string; delta: number; is_invited: boolean; total: { points: number; penalty: number; }; name?: string; username: string; problem: { alias: string; points: number; penalty: number; }; }[]; }
```

## `/api/contest/scoreboardMerge/`

### Descripción

Gets the accomulative scoreboard for an array of contests

### Parámetros

_Por documentar_

### Regresa

```typescript
{ ranking: { name?: string; username: string; contests: { [key: string]: { points: number; penalty: number; }; }; total: { points: number; penalty: number; }; }[]; }
```

## `/api/contest/setRecommended/`

### Descripción

Given a contest_alias, sets the recommended flag on/off.
Only omegaUp admins can call this API.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/stats/`

### Descripción

Stats of a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{ total_runs: number; pending_runs: string[]; max_wait_time: number; max_wait_time_guid?: string; verdict_counts: { [key: string]: number; }; distribution: { [key: number]: number; }; size_of_bucket: number; total_points: number; }
```

## `/api/contest/update/`

### Descripción

Update a Contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/updateEndTimeForIdentity/`

### Descripción

Update Contest end time for an identity when window_length
option is turned on

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/contest/users/`

### Descripción

Returns ALL identities participating in a contest

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

Returns a report with all user activity for a course.

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

Adds an admin to a course

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/addGroupAdmin/`

### Descripción

Adds an group admin to a course

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/addProblem/`

### Descripción

Adds a problem to an assignment

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/addStudent/`

### Descripción

Add Student to Course.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/adminDetails/`

### Descripción

Returns all details of a given Course

### Parámetros

_Por documentar_

### Regresa

```typescript
{ name: string; description: string; alias: string; basic_information_required: boolean; requests_user_information: string; assignments: { name: string; description: string; alias: string; publish_time_delay?: number; assignment_type: string; start_time: number; finish_time?: number; max_points: number; order: number; scoreboard_url: string; scoreboard_url_admin: string; }[]; school_id?: number; start_time: number; finish_time?: number; is_admin: boolean; public: boolean; show_scoreboard: boolean; student_count: number; school_name?: string; }
```

## `/api/course/admins/`

### Descripción

Returns all course administrators

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

Stores the resolution given to a certain request made by a contestant
interested to join the course.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/assignmentDetails/`

### Descripción

Returns details of a given assignment

### Parámetros

_Por documentar_

### Regresa

```typescript
{ name?: string; description?: string; assignment_type?: string; start_time: number; finish_time?: number; problems: { accepted: number; alias: string; commit: string; difficulty: number; languages: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]; director: string; problemset_id: number; admin: boolean; }
```

## `/api/course/assignmentScoreboard/`

### Descripción

Gets Scoreboard for an assignment

### Parámetros

_Por documentar_

### Regresa

```typescript
{ finish_time?: number; problems: { alias: string; order: number; }[]; ranking: { country?: string; is_invited: boolean; name?: string; place: number; problems: { alias: string; penalty: number; percent: number; place: number; points: number; run_details: { cases: { contest_score: number; max_score: number; meta: { status: string; }; name?: string; out_diff: string; score: number; verdict: string; }[]; details: { groups: { cases: { meta: { memory: number; time: number; wall_time: number; }; }[]; }[]; }; }; runs: number; }[]; total: { penalty: number; points: number; }; username: string; }[]; start_time: number; time: number; title: string; }
```

## `/api/course/assignmentScoreboardEvents/`

### Descripción

Returns the Scoreboard events

### Parámetros

_Por documentar_

### Regresa

```typescript
{ events: { country?: string; delta: number; is_invited: boolean; name?: string; problem: { alias: string; penalty: number; points: number; }; total: { penalty: number; points: number; }; username: string; }[]; }
```

## `/api/course/clone/`

### Descripción

Clone a course

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

Create new course API

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/createAssignment/`

### Descripción

API to Create an assignment

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/details/`

### Descripción

Returns details of a given course

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

Show course intro only on public courses when user is not yet registered

### Parámetros

_Por documentar_

### Regresa

```typescript
{ name: string; description: string; alias: string; currentUsername: string; needsBasicInformation: boolean; requestsUserInformation: string; shouldShowAcceptTeacher: boolean; statements: { privacy: { markdown?: string; gitObjectId?: string; statementType?: string; }; acceptTeacher: { gitObjectId?: string; markdown: string; statementType: string; }; }; isFirstTimeAccess: boolean; shouldShowResults: boolean; }
```

## `/api/course/listAssignments/`

### Descripción

List course assignments

### Parámetros

_Por documentar_

### Regresa

```typescript
{ assignments: { alias: string; assignment_type: string; description: string; finish_time?: number; has_runs: boolean; name: string; order: number; scoreboard_url: string; scoreboard_url_admin: string; start_time: number; }[]; }
```

## `/api/course/listCourses/`

### Descripción

Lists all the courses this user is associated with.

Returns courses for which the current user is an admin and
for in which the user is a student.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ admin: { alias: string; counts: { [key: string]: number; }; finish_time?: number; name: string; start_time: number; }[]; public: { alias: string; counts: { [key: string]: number; }; finish_time?: number; name: string; start_time: number; }[]; student: { alias: string; counts: { [key: string]: number; }; finish_time?: number; name: string; start_time: number; }[]; }
```

## `/api/course/listSolvedProblems/`

### Descripción

Get Problems solved by users of a course

### Parámetros

_Por documentar_

### Regresa

```typescript
{ user_problems: { [key: string]: { alias: string; title: string; username: string; }[]; }; }
```

## `/api/course/listStudents/`

### Descripción

List students in a course

### Parámetros

_Por documentar_

### Regresa

```typescript
{ students: { name?: string; progress: { [key: string]: number; }; username: string; }[]; }
```

## `/api/course/listUnsolvedProblems/`

### Descripción

Get Problems unsolved by users of a course

### Parámetros

_Por documentar_

### Regresa

```typescript
{ user_problems: { [key: string]: { alias: string; title: string; username: string; }[]; }; }
```

## `/api/course/myProgress/`

### Descripción

Returns details of a given course

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
}
```

## `/api/course/removeAdmin/`

### Descripción

Removes an admin from a course

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/removeGroupAdmin/`

### Descripción

Removes a group admin from a course

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/removeProblem/`

### Descripción

Remove a problem from an assignment

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/removeStudent/`

### Descripción

Remove Student from Course

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/requests/`

### Descripción

Returns the list of requests made by participants who are interested to
join the course

### Parámetros

_Por documentar_

### Regresa

```typescript
{ users: { accepted?: boolean; admin: { name?: string; username: string; }; country?: string; country_id?: string; last_update?: Date; request_time: Date; username: string; }[]; }
```

## `/api/course/runs/`

### Descripción

Returns all runs for a course

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

Edit Course contents

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/updateAssignment/`

### Descripción

Update an assignment

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/updateAssignmentsOrder/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/course/updateProblemsOrder/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

# Grader

Description of GraderController

## `/api/grader/status/`

### Descripción

Calls to /status grader

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

Add identity to group

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/group/create/`

### Descripción

New group

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/group/createScoreboard/`

### Descripción

Create a scoreboard set to a group

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/group/details/`

### Descripción

Details of a group (scoreboards)

### Parámetros

_Por documentar_

### Regresa

```typescript
{ exists: boolean; group: { create_time: number; alias?: string; name?: string; description?: string; }; scoreboards: { alias: string; create_time: string; description?: string; name: string; }[]; }
```

## `/api/group/list/`

### Descripción

Returns a list of groups that match a partial name. This returns an
array instead of an object since it is used by typeahead.

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

Members of a group (usernames only).

### Parámetros

_Por documentar_

### Regresa

```typescript
{ identities: { classname: string; country?: string; country_id?: string; name?: string; school?: string; school_id?: number; state?: string; state_id?: string; username: string; }[]; }
```

## `/api/group/myList/`

### Descripción

Returns a list of groups by owner

### Parámetros

_Por documentar_

### Regresa

```typescript
{ groups: { alias: string; create_time: number; description?: string; name: string; }[]; }
```

## `/api/group/removeUser/`

### Descripción

Remove user from group

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

# GroupScoreboard

GroupScoreboardController

## `/api/groupScoreboard/addContest/`

### Descripción

Add contest to a group scoreboard

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/groupScoreboard/details/`

### Descripción

Details of a scoreboard. Returns a list with all contests that belong to
the given scoreboard_alias

### Parámetros

_Por documentar_

### Regresa

```typescript
{ ranking: { name?: string; username: string; contests: { [key: string]: { points: number; penalty: number; }; }; total: { points: number; penalty: number; }; }[]; scoreboard: { group_scoreboard_id: number; group_id: number; create_time: number; alias: string; name: string; description: string; }; contests: { contest_id: number; problemset_id: number; acl_id: number; title: string; description: string; start_time: number; finish_time: number; last_updated: number; window_length?: number; rerun_id: number; admission_mode: string; alias: string; scoreboard: number; points_decay_factor: number; partial_score: boolean; submissions_gap: number; feedback: string; penalty: string; penalty_calc_policy: string; show_scoreboard_after: boolean; urgent: boolean; languages: string; recommended: boolean; only_ac: boolean; weight: number; }[]; }
```

## `/api/groupScoreboard/list/`

### Descripción

Details of a scoreboard

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

Add contest to a group scoreboard

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

# Identity

IdentityController

## `/api/identity/bulkCreate/`

### Descripción

Entry point for Create bulk Identities API

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/identity/changePassword/`

### Descripción

Entry point for change passowrd of an identity

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/identity/create/`

### Descripción

Entry point for Create an Identity API

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

Entry point for Update an Identity API

### Parámetros

_Por documentar_

### Regresa

```typescript
{
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
}
```

## `/api/interview/create/`

### Descripción

### Parámetros

_Por documentar_

### Regresa

```typescript
{
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

Returns a list of unread notifications for user

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

Updates notifications as read in database

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

# Problem

ProblemsController

## `/api/problem/addAdmin/`

### Descripción

Adds an admin to a problem

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/addGroupAdmin/`

### Descripción

Adds a group admin to a problem

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/addTag/`

### Descripción

Adds a tag to a problem

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

Returns a list of problems where current user has admin rights (or is
the owner).

### Parámetros

_Por documentar_

### Regresa

```typescript
{ pagerItems: types.PageItem[]; problems: { tags: { name: string; source: string; }[]; }[]; }
```

## `/api/problem/admins/`

### Descripción

Returns all problem administrators

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

Returns the best score for a problem

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

Entry point for Problem clarifications API

### Parámetros

_Por documentar_

### Regresa

```typescript
{ clarifications: { clarification_id: number; contest_alias: string; author?: string; message: string; time: number; answer?: string; public: boolean; }[]; }
```

## `/api/problem/create/`

### Descripción

Create a new problem

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/delete/`

### Descripción

Removes a problem whether user is the creator

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/details/`

### Descripción

Entry point for Problem Details API

### Parámetros

_Por documentar_

### Regresa

```typescript
{ accepted: number; admin: boolean; alias: string; commit: string; creation_date: number; difficulty?: number; email_clarifications: boolean; exists: boolean; input_limit: number; languages: string[]; order: string; points: number; preferred_language: string; problemsetter: { creation_date: number; name: string; username: string; }; quality_seal: boolean; runs: { alias: string; contest_score?: number; guid: string; language: string; memory: number; penalty: number; runtime: number; score: number; status: string; submit_delay: number; time: number; username: string; verdict: string; }[]; score: number; settings: { cases: { [key: string]: { in: string; out: string; weight: number; }; }; limits: { MemoryLimit: number|string; OverallWallTimeLimit: string; TimeLimit: string; }; validator: { name: string; tolerance: number; }; }; solvers: { language: string; memory: number; runtime: number; time: number; username: string; }[]; source: string; statement: { images: { [key: string]: string; }; language: string; markdown: string; }; submissions: number; title: string; version: string; visibility: number; visits: number; }
```

## `/api/problem/list/`

### Descripción

List of public and user's private problems

### Parámetros

_Por documentar_

### Regresa

```typescript
{ results: types.ProblemListItem[]; total: number; }
```

## `/api/problem/myList/`

### Descripción

Gets a list of problems where current user is the owner

### Parámetros

_Por documentar_

### Regresa

```typescript
{ pagerItems: types.PageItem[]; problems: { tags: { name: string; source: string; }[]; }[]; }
```

## `/api/problem/rejudge/`

### Descripción

Rejudge problem

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/removeAdmin/`

### Descripción

Removes an admin from a problem

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/removeGroupAdmin/`

### Descripción

Removes a group admin from a problem

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/removeTag/`

### Descripción

Removes a tag from a contest

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/runs/`

### Descripción

Entry point for Problem runs API

### Parámetros

_Por documentar_

### Regresa

```typescript
{ runs: { guid: string; language: string; status: string; verdict: string; runtime: number; penalty: number; memory: number; score: number; contest_score?: number; time: number; submit_delay: number; alias: string; username: string; run_id: number; judged_by?: string; type?: string; country_id?: string; contest_alias?: string; }[]; }
```

## `/api/problem/runsDiff/`

### Descripción

Return a report of which runs would change due to a version change.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ diff: { username: string; guid: string; problemset_id?: number; old_status?: string; old_verdict?: string; old_score?: number; new_status?: string; new_verdict?: string; new_score?: number; }[]; }
```

## `/api/problem/selectVersion/`

### Descripción

Change the version of the problem.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/solution/`

### Descripción

Returns the solution for a problem if conditions are satisfied.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ exists: boolean; solution: { language: string; markdown: string; images: { [key: string]: string; }; }; }
```

## `/api/problem/stats/`

### Descripción

Stats of a problem

### Parámetros

_Por documentar_

### Regresa

```typescript
{ cases_stats: { [key: string]: number; }; pending_runs: { guid: string; }[]; total_runs: number; verdict_counts: { [key: string]: number; }; }
```

## `/api/problem/tags/`

### Descripción

Returns every tag associated to a given problem.

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

Update problem contents

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

Updates problem solution only

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/updateStatement/`

### Descripción

Updates problem statement only

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/problem/versions/`

### Descripción

Entry point for Problem Versions API

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

Returns the number of solutions allowed
and the number of solutions already seen

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

Returns the Scoreboard events

### Parámetros

_Por documentar_

### Regresa

```typescript
{ events: { country?: string; delta: number; is_invited: boolean; total: { points: number; penalty: number; }; name?: string; username: string; problem: { alias: string; points: number; penalty: number; }; }[]; }
```

# QualityNomination

## `/api/qualityNomination/create/`

### Descripción

Creates a new QualityNomination

There are three ways in which users can interact with this:

# Suggestion

A user that has already solved a problem can make suggestions about a
problem. This expects the `nomination` field to be `suggestion` and the
`contents` field should be a JSON blob with at least one the following fields:

- `difficulty`: (Optional) A number in the range [0-4] indicating the
  difficulty of the problem.
- `quality`: (Optional) A number in the range [0-4] indicating the quality
  of the problem.
- `tags`: (Optional) An array of tag names that will be added to the
  problem upon promotion.
- `before_ac`: (Optional) Boolean indicating if the suggestion has been sent
  before receiving an AC verdict for problem run.

# Quality tag

A reviewer could send this type of nomination to make the user marked as
a quality problem or not. The reviewer could also specify which category
is the one the problem belongs to. The 'contents' field should have the
following subfields:

- tag: The name of the tag corresponding to the category of the problem
- quality_seal: A boolean that if activated, means that the problem is a
  quality problem

# Promotion

A user that has already solved a problem can nominate it to be promoted
as a Quality Problem. This expects the `nomination` field to be
`promotion` and the `contents` field should be a JSON blob with the
following fields:

- `statements`: A dictionary of languages to objects that contain a
  `markdown` field, which is the markdown-formatted
  problem statement for that language.
- `source`: A URL or string clearly documenting the source or full name
  of original author of the problem.
- `tags`: An array of tag names that will be added to the problem upon
  promotion.

# Demotion

A demoted problem is banned, and cannot be un-banned or added to any new
problemsets. This expects the `nomination` field to be `demotion` and
the `contents` field should be a JSON blob with the following fields:

- `rationale`: A small text explaining the rationale for demotion.
- `reason`: One of `['duplicate', 'no-problem-statement', 'offensive', 'other', 'spam']`.
- `original`: If the `reason` is `duplicate`, the alias of the original
  problem.

# Dismissal

A user that has already solved a problem can dismiss suggestions. The
`contents` field is empty.

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

Displays the details of a nomination. The user needs to be either the
nominator or a member of the reviewer group.

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

Displays the nominations that this user has been assigned.

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

Marks a nomination (only the demotion type supported for now) as resolved (approved or denied).

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

# Reset

## `/api/reset/create/`

### Descripción

Creates a reset operation, the first of two steps needed to reset a
password. The first step consist of sending an email to the user with
instructions to reset he's password, if and only if the email is valid.

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

Creates a reset operation, support team members can generate a valid
token and then they can send it to end user

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

Updates the password of a given user, this is the second and last step
in order to reset the password. This operation is done if and only if
the correct parameters are suplied.

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

Get total of last 6 months

### Parámetros

_Por documentar_

### Regresa

```typescript
{ total: { [key: string]: number; }; ac: { [key: string]: number; }; }
```

## `/api/run/create/`

### Descripción

Create a new run

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

Gets the details of a run. Includes admin details if admin.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ admin: boolean; compile_error: string; details: { compile_meta: { [key: string]: { memory: number; sys_time: number; time: number; verdict: string; wall_time: number; }; }; contest_score: number; groups: { cases: { contest_score: number; max_score: number; meta: { verdict: string; }; name: string; score: number; verdict: string; }[]; contest_score: number; group: string; max_score: number; score: number; }[]; judged_by: string; max_score: number; memory: number; score: number; time: number; verdict: string; wall_time: number; }; guid: string; judged_by: string; language: string; logs: string; source: string; }
```

## `/api/run/disqualify/`

### Descripción

Disqualify a submission

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/run/list/`

### Descripción

Gets a list of latest runs overall

### Parámetros

_Por documentar_

### Regresa

```typescript
{ runs: { alias: string; contest_alias?: string; contest_score?: number; country_id?: string; guid: string; judged_by?: string; language: string; memory: number; penalty: number; run_id: number; runtime: number; score: number; submit_delay: number; time: number; type?: string; username: string; verdict: string; }[]; }
```

## `/api/run/rejudge/`

### Descripción

Re-sends a problem to Grader.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/run/source/`

### Descripción

Given the run alias, returns the source code and any compile errors if any
Used in the arena, any contestant can view its own codes and compile errors

### Parámetros

_Por documentar_

### Regresa

```typescript
{ compile_error: string; details: { compile_meta: { [key: string]: { memory: number; sys_time: number; time: number; verdict: string; wall_time: number; }; }; contest_score: number; groups: { cases: { contest_score: number; max_score: number; meta: { verdict: string; }; name: string; score: number; verdict: string; }[]; contest_score: number; group: string; max_score: number; score: number; }[]; judged_by: string; max_score: number; memory: number; score: number; time: number; verdict: string; wall_time: number; }; source: string; }
```

## `/api/run/status/`

### Descripción

Get basic details of a run

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

Api to create new school

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

Gets a list of schools

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

Returns the number of solved problems on the last
months (including the current one)

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

Returns the historical rank of schools

### Parámetros

_Por documentar_

### Regresa

```typescript
{ rank: { country_id?: string; name: string; ranking?: number; school_id: number; score: number; }[]; totalRows: number; }
```

## `/api/school/schoolCodersOfTheMonth/`

### Descripción

Returns rank of best schools in last month

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

Selects a certain school as school of the month

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/school/users/`

### Descripción

Returns the list of current students registered in a certain school
with the number of created problems, solved problems and organized contests.

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

Returns a list of contests

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

# Session

Session controller handles sessions.

## `/api/session/currentSession/`

### Descripción

Returns information about current session. In order to avoid one full
server roundtrip (about ~100msec on each pageload), it also returns the
current time to be able to calculate the time delta between the
contestant's machine and the server.

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

Returns the latest submissions

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

Gets a list of tags

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

Entry point for /time API

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

Keeps a record of a user who accepts the privacy policy

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/addExperiment/`

### Descripción

Adds the experiment to the user.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/addGroup/`

### Descripción

Adds the identity to the group.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/addRole/`

### Descripción

Adds the role to the user.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/associateIdentity/`

### Descripción

Associates an identity to the logged user given the username

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/changePassword/`

### Descripción

Changes the password of a user

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/coderOfTheMonth/`

### Descripción

Get coder of the month by trying to find it in the table using the first
day of the current month. If there's no coder of the month for the given
date, calculate it and save it.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ coderinfo?: { birth_date?: number; country?: string; country_id?: string; email?: string; gender?: string; graduation_date?: number; gravatar_92: string; hide_problem_tags?: boolean; is_private: boolean; locale: string; name?: string; preferred_language?: string; scholar_degree?: string; school?: string; school_id?: number; state?: string; state_id?: string; username?: string; verified: boolean; }; }
```

## `/api/user/coderOfTheMonthList/`

### Descripción

Returns the list of coders of the month

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

Get Contests which a certain user has participated in

### Parámetros

_Por documentar_

### Regresa

```typescript
{ contests: { [key: string]: { data: { alias: string; title: string; start_time: number; finish_time: number; last_updated: number; }; place?: number; }; }; }
```

## `/api/user/create/`

### Descripción

Entry point for Create a User API

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

Gets extra information of the identity:

- last password change request
- verify status

### Parámetros

_Por documentar_

### Regresa

```typescript
{ within_last_day: boolean; verified: boolean; username: string; last_login?: number; }
```

## `/api/user/generateGitToken/`

### Descripción

Generate a new gitserver token. This token can be used to authenticate
against the gitserver.

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

Get the results for this user in a given interview

### Parámetros

_Por documentar_

### Regresa

```typescript
{ user_verified: boolean; interview_url: string; name_or_username?: string; opened_interview: boolean; finished: boolean; }
```

## `/api/user/lastPrivacyPolicyAccepted/`

### Descripción

Gets the last privacy policy accepted by user

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

Gets a list of users. This returns an array instead of an object since
it is used by typeahead.

### Parámetros

_Por documentar_

### Regresa

```typescript
types.UserListItem[]
```

## `/api/user/listAssociatedIdentities/`

### Descripción

Get the identities that have been associated to the logged user

### Parámetros

_Por documentar_

### Regresa

```typescript
{ identities: { username: string; default: boolean; }[]; }
```

## `/api/user/listUnsolvedProblems/`

### Descripción

Get Problems unsolved by user

### Parámetros

_Por documentar_

### Regresa

```typescript
{ problems: types.Problem[]; }
```

## `/api/user/login/`

### Descripción

Exposes API /user/login
Expects in request:
user
password

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

Registers to the mailing list all users that have not been added before. Admin only

### Parámetros

_Por documentar_

### Regresa

```typescript
{ users: { [key: string]: boolean; }; }
```

## `/api/user/problemsCreated/`

### Descripción

Get Problems created by user

### Parámetros

_Por documentar_

### Regresa

```typescript
{ problems: types.Problem[]; }
```

## `/api/user/problemsSolved/`

### Descripción

Get Problems solved by user

### Parámetros

_Por documentar_

### Regresa

```typescript
{ problems: types.Problem[]; }
```

## `/api/user/profile/`

### Descripción

Get general user info

### Parámetros

_Por documentar_

### Regresa

```typescript
{ birth_date?: number; classname: string; country?: string; country_id?: string; email?: string; gender?: string; graduation_date?: number; gravatar_92?: string; hide_problem_tags?: boolean; is_private: boolean; locale?: string; name?: string; preferred_language?: string; rankinfo: { name?: string; problems_solved?: number; rank?: number; }; scholar_degree?: string; school?: string; school_id?: number; state?: string; state_id?: string; username?: string; verified?: boolean; }
```

## `/api/user/rankByProblemsSolved/`

### Descripción

If no username provided: Gets the top N users who have solved more problems
If username provided: Gets rank for username provided

### Parámetros

_Por documentar_

### Regresa

```typescript
{ rank: { classname: string; country_id?: string; name?: string; problems_solved: number; ranking: number; score: number; user_id: number; username: string; }[]|number; total: number; name: string; problems_solved: number; }
```

## `/api/user/removeExperiment/`

### Descripción

Removes the experiment from the user.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/removeGroup/`

### Descripción

Removes the user to the group.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/removeRole/`

### Descripción

Removes the role from the user.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/selectCoderOfTheMonth/`

### Descripción

Selects coder of the month for next month.

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/stats/`

### Descripción

Get stats

### Parámetros

_Por documentar_

### Regresa

```typescript
{ runs: { date?: string; runs: number; verdict: string; }[]; }
```

## `/api/user/statusVerified/`

### Descripción

Gets verify status of a user

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

Update user profile

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/updateBasicInfo/`

### Descripción

Update basic user profile info when logged with fb/gool

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/updateMainEmail/`

### Descripción

Updates the main email of the current user

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```

## `/api/user/validateFilter/`

### Descripción

Parses and validates a filter string to be used for event notification
filtering.

The Request must have a 'filter' key with comma-delimited URI paths
representing the resources the caller is interested in receiving events
for. If the caller has enough privileges to receive notifications for
ALL the requested filters, the request will return successfully,
otherwise an exception will be thrown.

This API does not need authentication to be used. This allows to track
contest updates with an access token.

### Parámetros

_Por documentar_

### Regresa

```typescript
{ user?: string; admin: boolean; problem_admin: string[]; contest_admin: string[]; problemset_admin: number[]; }
```

## `/api/user/verifyEmail/`

### Descripción

Verifies the user given its verification id

### Parámetros

_Por documentar_

### Regresa

```typescript
{
}
```
