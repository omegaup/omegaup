- [Admin](#admin)
  - [`/api/admin/platformReportStats/`](#apiadminplatformreportstats)
- [Authorization](#authorization)
  - [`/api/authorization/problem/`](#apiauthorizationproblem)
- [Badge](#badge)
  - [`/api/badge/badgeDetails/`](#apibadgebadgedetails)
  - [`/api/badge/list/`](#apibadgelist)
  - [`/api/badge/myBadgeAssignationTime/`](#apibadgemybadgeassignationtime)
  - [`/api/badge/myList/`](#apibadgemylist)
  - [`/api/badge/userList/`](#apibadgeuserlist)
- [Clarification](#clarification)
  - [`/api/clarification/create/`](#apiclarificationcreate)
  - [`/api/clarification/details/`](#apiclarificationdetails)
  - [`/api/clarification/update/`](#apiclarificationupdate)
- [Contest](#contest)
  - [`/api/contest/activityReport/`](#apicontestactivityreport)
  - [`/api/contest/addAdmin/`](#apicontestaddadmin)
  - [`/api/contest/addGroup/`](#apicontestaddgroup)
  - [`/api/contest/addGroupAdmin/`](#apicontestaddgroupadmin)
  - [`/api/contest/addProblem/`](#apicontestaddproblem)
  - [`/api/contest/addUser/`](#apicontestadduser)
  - [`/api/contest/adminDetails/`](#apicontestadmindetails)
  - [`/api/contest/adminList/`](#apicontestadminlist)
  - [`/api/contest/admins/`](#apicontestadmins)
  - [`/api/contest/arbitrateRequest/`](#apicontestarbitraterequest)
  - [`/api/contest/clarifications/`](#apicontestclarifications)
  - [`/api/contest/clone/`](#apicontestclone)
  - [`/api/contest/contestants/`](#apicontestcontestants)
  - [`/api/contest/create/`](#apicontestcreate)
  - [`/api/contest/createVirtual/`](#apicontestcreatevirtual)
  - [`/api/contest/details/`](#apicontestdetails)
  - [`/api/contest/list/`](#apicontestlist)
  - [`/api/contest/listParticipating/`](#apicontestlistparticipating)
  - [`/api/contest/myList/`](#apicontestmylist)
  - [`/api/contest/open/`](#apicontestopen)
  - [`/api/contest/problems/`](#apicontestproblems)
  - [`/api/contest/publicDetails/`](#apicontestpublicdetails)
  - [`/api/contest/registerForContest/`](#apicontestregisterforcontest)
  - [`/api/contest/removeAdmin/`](#apicontestremoveadmin)
  - [`/api/contest/removeGroup/`](#apicontestremovegroup)
  - [`/api/contest/removeGroupAdmin/`](#apicontestremovegroupadmin)
  - [`/api/contest/removeProblem/`](#apicontestremoveproblem)
  - [`/api/contest/removeUser/`](#apicontestremoveuser)
  - [`/api/contest/report/`](#apicontestreport)
  - [`/api/contest/requests/`](#apicontestrequests)
  - [`/api/contest/role/`](#apicontestrole)
  - [`/api/contest/runs/`](#apicontestruns)
  - [`/api/contest/runsDiff/`](#apicontestrunsdiff)
  - [`/api/contest/scoreboard/`](#apicontestscoreboard)
  - [`/api/contest/scoreboardEvents/`](#apicontestscoreboardevents)
  - [`/api/contest/scoreboardMerge/`](#apicontestscoreboardmerge)
  - [`/api/contest/setRecommended/`](#apicontestsetrecommended)
  - [`/api/contest/stats/`](#apiconteststats)
  - [`/api/contest/update/`](#apicontestupdate)
  - [`/api/contest/updateEndTimeForIdentity/`](#apicontestupdateendtimeforidentity)
  - [`/api/contest/users/`](#apicontestusers)
- [Course](#course)
  - [`/api/course/activityReport/`](#apicourseactivityreport)
  - [`/api/course/addAdmin/`](#apicourseaddadmin)
  - [`/api/course/addGroupAdmin/`](#apicourseaddgroupadmin)
  - [`/api/course/addProblem/`](#apicourseaddproblem)
  - [`/api/course/addStudent/`](#apicourseaddstudent)
  - [`/api/course/adminDetails/`](#apicourseadmindetails)
  - [`/api/course/admins/`](#apicourseadmins)
  - [`/api/course/arbitrateRequest/`](#apicoursearbitraterequest)
  - [`/api/course/assignmentDetails/`](#apicourseassignmentdetails)
  - [`/api/course/assignmentScoreboard/`](#apicourseassignmentscoreboard)
  - [`/api/course/assignmentScoreboardEvents/`](#apicourseassignmentscoreboardevents)
  - [`/api/course/clone/`](#apicourseclone)
  - [`/api/course/create/`](#apicoursecreate)
  - [`/api/course/createAssignment/`](#apicoursecreateassignment)
  - [`/api/course/details/`](#apicoursedetails)
  - [`/api/course/getProblemUsers/`](#apicoursegetproblemusers)
  - [`/api/course/introDetails/`](#apicourseintrodetails)
  - [`/api/course/listAssignments/`](#apicourselistassignments)
  - [`/api/course/listCourses/`](#apicourselistcourses)
  - [`/api/course/listSolvedProblems/`](#apicourselistsolvedproblems)
  - [`/api/course/listStudents/`](#apicourseliststudents)
  - [`/api/course/listUnsolvedProblems/`](#apicourselistunsolvedproblems)
  - [`/api/course/myProgress/`](#apicoursemyprogress)
  - [`/api/course/registerForCourse/`](#apicourseregisterforcourse)
  - [`/api/course/removeAdmin/`](#apicourseremoveadmin)
  - [`/api/course/removeGroupAdmin/`](#apicourseremovegroupadmin)
  - [`/api/course/removeProblem/`](#apicourseremoveproblem)
  - [`/api/course/removeStudent/`](#apicourseremovestudent)
  - [`/api/course/requests/`](#apicourserequests)
  - [`/api/course/runs/`](#apicourseruns)
  - [`/api/course/studentProgress/`](#apicoursestudentprogress)
  - [`/api/course/update/`](#apicourseupdate)
  - [`/api/course/updateAssignment/`](#apicourseupdateassignment)
  - [`/api/course/updateAssignmentsOrder/`](#apicourseupdateassignmentsorder)
  - [`/api/course/updateProblemsOrder/`](#apicourseupdateproblemsorder)
- [Grader](#grader)
  - [`/api/grader/status/`](#apigraderstatus)
- [Group](#group)
  - [`/api/group/addUser/`](#apigroupadduser)
  - [`/api/group/create/`](#apigroupcreate)
  - [`/api/group/createScoreboard/`](#apigroupcreatescoreboard)
  - [`/api/group/details/`](#apigroupdetails)
  - [`/api/group/list/`](#apigrouplist)
  - [`/api/group/members/`](#apigroupmembers)
  - [`/api/group/myList/`](#apigroupmylist)
  - [`/api/group/removeUser/`](#apigroupremoveuser)
- [GroupScoreboard](#groupscoreboard)
  - [`/api/groupScoreboard/addContest/`](#apigroupscoreboardaddcontest)
  - [`/api/groupScoreboard/details/`](#apigroupscoreboarddetails)
  - [`/api/groupScoreboard/list/`](#apigroupscoreboardlist)
  - [`/api/groupScoreboard/removeContest/`](#apigroupscoreboardremovecontest)
- [Identity](#identity)
  - [`/api/identity/bulkCreate/`](#apiidentitybulkcreate)
  - [`/api/identity/changePassword/`](#apiidentitychangepassword)
  - [`/api/identity/create/`](#apiidentitycreate)
  - [`/api/identity/update/`](#apiidentityupdate)
- [Interview](#interview)
  - [`/api/interview/addUsers/`](#apiinterviewaddusers)
  - [`/api/interview/create/`](#apiinterviewcreate)
  - [`/api/interview/details/`](#apiinterviewdetails)
  - [`/api/interview/list/`](#apiinterviewlist)
- [Notification](#notification)
  - [`/api/notification/myList/`](#apinotificationmylist)
  - [`/api/notification/readNotifications/`](#apinotificationreadnotifications)
- [Problem](#problem)
  - [`/api/problem/addAdmin/`](#apiproblemaddadmin)
  - [`/api/problem/addGroupAdmin/`](#apiproblemaddgroupadmin)
  - [`/api/problem/addTag/`](#apiproblemaddtag)
  - [`/api/problem/adminList/`](#apiproblemadminlist)
  - [`/api/problem/admins/`](#apiproblemadmins)
  - [`/api/problem/bestScore/`](#apiproblembestscore)
  - [`/api/problem/clarifications/`](#apiproblemclarifications)
  - [`/api/problem/create/`](#apiproblemcreate)
  - [`/api/problem/delete/`](#apiproblemdelete)
  - [`/api/problem/details/`](#apiproblemdetails)
  - [`/api/problem/list/`](#apiproblemlist)
  - [`/api/problem/myList/`](#apiproblemmylist)
  - [`/api/problem/rejudge/`](#apiproblemrejudge)
  - [`/api/problem/removeAdmin/`](#apiproblemremoveadmin)
  - [`/api/problem/removeGroupAdmin/`](#apiproblemremovegroupadmin)
  - [`/api/problem/removeTag/`](#apiproblemremovetag)
  - [`/api/problem/runs/`](#apiproblemruns)
  - [`/api/problem/runsDiff/`](#apiproblemrunsdiff)
  - [`/api/problem/selectVersion/`](#apiproblemselectversion)
  - [`/api/problem/solution/`](#apiproblemsolution)
  - [`/api/problem/stats/`](#apiproblemstats)
  - [`/api/problem/tags/`](#apiproblemtags)
  - [`/api/problem/update/`](#apiproblemupdate)
  - [`/api/problem/updateSolution/`](#apiproblemupdatesolution)
  - [`/api/problem/updateStatement/`](#apiproblemupdatestatement)
  - [`/api/problem/versions/`](#apiproblemversions)
- [ProblemForfeited](#problemforfeited)
  - [`/api/problemForfeited/getCounts/`](#apiproblemforfeitedgetcounts)
- [Problemset](#problemset)
  - [`/api/problemset/details/`](#apiproblemsetdetails)
  - [`/api/problemset/scoreboard/`](#apiproblemsetscoreboard)
  - [`/api/problemset/scoreboardEvents/`](#apiproblemsetscoreboardevents)
- [QualityNomination](#qualitynomination)
  - [`/api/qualityNomination/create/`](#apiqualitynominationcreate)
  - [`/api/qualityNomination/details/`](#apiqualitynominationdetails)
  - [`/api/qualityNomination/list/`](#apiqualitynominationlist)
  - [`/api/qualityNomination/myAssignedList/`](#apiqualitynominationmyassignedlist)
  - [`/api/qualityNomination/myList/`](#apiqualitynominationmylist)
  - [`/api/qualityNomination/resolve/`](#apiqualitynominationresolve)
- [Reset](#reset)
  - [`/api/reset/create/`](#apiresetcreate)
  - [`/api/reset/generateToken/`](#apiresetgeneratetoken)
  - [`/api/reset/update/`](#apiresetupdate)
- [Run](#run)
  - [`/api/run/counts/`](#apiruncounts)
  - [`/api/run/create/`](#apiruncreate)
  - [`/api/run/details/`](#apirundetails)
  - [`/api/run/disqualify/`](#apirundisqualify)
  - [`/api/run/list/`](#apirunlist)
  - [`/api/run/rejudge/`](#apirunrejudge)
  - [`/api/run/source/`](#apirunsource)
  - [`/api/run/status/`](#apirunstatus)
- [School](#school)
  - [`/api/school/create/`](#apischoolcreate)
  - [`/api/school/list/`](#apischoollist)
  - [`/api/school/monthlySolvedProblemsCount/`](#apischoolmonthlysolvedproblemscount)
  - [`/api/school/schoolCodersOfTheMonth/`](#apischoolschoolcodersofthemonth)
  - [`/api/school/selectSchoolOfTheMonth/`](#apischoolselectschoolofthemonth)
  - [`/api/school/users/`](#apischoolusers)
- [Scoreboard](#scoreboard)
  - [`/api/scoreboard/refresh/`](#apiscoreboardrefresh)
- [Session](#session)
  - [`/api/session/currentSession/`](#apisessioncurrentsession)
  - [`/api/session/googleLogin/`](#apisessiongooglelogin)
- [Submission](#submission)
  - [`/api/submission/latestSubmissions/`](#apisubmissionlatestsubmissions)
- [Tag](#tag)
  - [`/api/tag/list/`](#apitaglist)
- [Time](#time)
  - [`/api/time/get/`](#apitimeget)
- [User](#user)
  - [`/api/user/acceptPrivacyPolicy/`](#apiuseracceptprivacypolicy)
  - [`/api/user/addExperiment/`](#apiuseraddexperiment)
  - [`/api/user/addGroup/`](#apiuseraddgroup)
  - [`/api/user/addRole/`](#apiuseraddrole)
  - [`/api/user/associateIdentity/`](#apiuserassociateidentity)
  - [`/api/user/changePassword/`](#apiuserchangepassword)
  - [`/api/user/coderOfTheMonth/`](#apiusercoderofthemonth)
  - [`/api/user/coderOfTheMonthList/`](#apiusercoderofthemonthlist)
  - [`/api/user/contestStats/`](#apiuserconteststats)
  - [`/api/user/create/`](#apiusercreate)
  - [`/api/user/extraInformation/`](#apiuserextrainformation)
  - [`/api/user/generateGitToken/`](#apiusergenerategittoken)
  - [`/api/user/generateOmiUsers/`](#apiusergenerateomiusers)
  - [`/api/user/interviewStats/`](#apiuserinterviewstats)
  - [`/api/user/lastPrivacyPolicyAccepted/`](#apiuserlastprivacypolicyaccepted)
  - [`/api/user/list/`](#apiuserlist)
  - [`/api/user/listAssociatedIdentities/`](#apiuserlistassociatedidentities)
  - [`/api/user/listUnsolvedProblems/`](#apiuserlistunsolvedproblems)
  - [`/api/user/login/`](#apiuserlogin)
  - [`/api/user/mailingListBackfill/`](#apiusermailinglistbackfill)
  - [`/api/user/problemsCreated/`](#apiuserproblemscreated)
  - [`/api/user/problemsSolved/`](#apiuserproblemssolved)
  - [`/api/user/profile/`](#apiuserprofile)
  - [`/api/user/rankByProblemsSolved/`](#apiuserrankbyproblemssolved)
  - [`/api/user/removeExperiment/`](#apiuserremoveexperiment)
  - [`/api/user/removeGroup/`](#apiuserremovegroup)
  - [`/api/user/removeRole/`](#apiuserremoverole)
  - [`/api/user/selectCoderOfTheMonth/`](#apiuserselectcoderofthemonth)
  - [`/api/user/stats/`](#apiuserstats)
  - [`/api/user/statusVerified/`](#apiuserstatusverified)
  - [`/api/user/update/`](#apiuserupdate)
  - [`/api/user/updateBasicInfo/`](#apiuserupdatebasicinfo)
  - [`/api/user/updateMainEmail/`](#apiuserupdatemainemail)
  - [`/api/user/validateFilter/`](#apiuservalidatefilter)
  - [`/api/user/verifyEmail/`](#apiuserverifyemail)

# Admin

## `/api/admin/platformReportStats/`

### Description

Get stats for an overall platform report.

### Parameters

| Name         | Type    | Description |
| ------------ | ------- | ----------- |
| `end_time`   | `mixed` |             |
| `start_time` | `mixed` |             |

### Returns

| Name     | Type                                                                                                                                                                                                     |
| -------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `report` | `{ acceptedSubmissions: number; activeSchools: number; activeUsers: { [key: string]: number; }; courses: number; omiCourse: { attemptedUsers: number; completedUsers: number; passedUsers: number; }; }` |

# Authorization

AuthorizationController

## `/api/authorization/problem/`

### Description

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `problem_alias` | `mixed` |             |
| `token`         | `mixed` |             |
| `username`      | `mixed` |             |

### Returns

| Name         | Type      |
| ------------ | --------- |
| `has_solved` | `boolean` |
| `is_admin`   | `boolean` |
| `can_view`   | `boolean` |
| `can_edit`   | `boolean` |

# Badge

BadgesController

## `/api/badge/badgeDetails/`

### Description

Returns the number of owners and the first
assignation timestamp for a certain badge

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `badge_alias` | `mixed` |             |

### Returns

```typescript
types.Badge;
```

## `/api/badge/list/`

### Description

Returns a list of existing badges

### Returns

```typescript
string[]
```

## `/api/badge/myBadgeAssignationTime/`

### Description

Returns a the assignation timestamp of a badge
for current user.

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `badge_alias` | `mixed` |             |

### Returns

| Name               | Type   |
| ------------------ | ------ |
| `assignation_time` | `Date` |

## `/api/badge/myList/`

### Description

Returns a list of badges owned by current user

### Returns

| Name     | Type            |
| -------- | --------------- |
| `badges` | `types.Badge[]` |

## `/api/badge/userList/`

### Description

Returns a list of badges owned by a certain user

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `target_username` | `mixed` |             |

### Returns

| Name     | Type            |
| -------- | --------------- |
| `badges` | `types.Badge[]` |

# Clarification

Description of ClarificationController

## `/api/clarification/create/`

### Description

Creates a Clarification

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `message`       | `mixed` |             |
| `problem_alias` | `mixed` |             |
| `username`      | `mixed` |             |

### Returns

| Name               | Type     |
| ------------------ | -------- |
| `clarification_id` | `number` |

## `/api/clarification/details/`

### Description

API for getting a clarification

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `clarification_id` | `mixed` |             |

### Returns

| Name            | Type     |
| --------------- | -------- |
| `message`       | `string` |
| `answer`        | `string` |
| `time`          | `number` |
| `problem_id`    | `number` |
| `problemset_id` | `number` |

## `/api/clarification/update/`

### Description

Update a clarification

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `answer`           | `mixed` |             |
| `clarification_id` | `mixed` |             |
| `message`          | `mixed` |             |

### Returns

_Nothing_

# Contest

ContestController

## `/api/contest/activityReport/`

### Description

Returns a report with all user activity for a contest.

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `token`         | `mixed` |             |

### Returns

| Name     | Type                                                                                    |
| -------- | --------------------------------------------------------------------------------------- |
| `events` | `{ username: string; ip: number; time: number; classname?: string; alias?: string; }[]` |

## `/api/contest/addAdmin/`

### Description

Adds an admin to a contest

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `contest_alias`   | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/addGroup/`

### Description

Adds an group to a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `group`         | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/addGroupAdmin/`

### Description

Adds an group admin to a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `group`         | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/addProblem/`

### Description

Adds a problem to a contest

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `commit`           | `mixed` |             |
| `contest_alias`    | `mixed` |             |
| `order_in_contest` | `mixed` |             |
| `points`           | `mixed` |             |
| `problem_alias`    | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/addUser/`

### Description

Adds a user to a contest.
By default, any user can view details of public contests.
Only users added through this API can view private contests

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `contest_alias`   | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/adminDetails/`

### Description

Returns details of a Contest, for administrators. This differs from
apiDetails in the sense that it does not attempt to calculate the
remaining time from the contest, or register the opened time.

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `token`         | `mixed` |             |

### Returns

| Name                        | Type                                                                                                                                                                                                                                                        |
| --------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `admin`                     | `boolean`                                                                                                                                                                                                                                                   |
| `admission_mode`            | `string`                                                                                                                                                                                                                                                    |
| `alias`                     | `string`                                                                                                                                                                                                                                                    |
| `available_languages`       | `{ [key: string]: string; }`                                                                                                                                                                                                                                |
| `description`               | `string`                                                                                                                                                                                                                                                    |
| `director`                  | `string`                                                                                                                                                                                                                                                    |
| `feedback`                  | `string`                                                                                                                                                                                                                                                    |
| `finish_time`               | `number`                                                                                                                                                                                                                                                    |
| `languages`                 | `string[]`                                                                                                                                                                                                                                                  |
| `needs_basic_information`   | `boolean`                                                                                                                                                                                                                                                   |
| `partial_score`             | `boolean`                                                                                                                                                                                                                                                   |
| `opened`                    | `boolean`                                                                                                                                                                                                                                                   |
| `original_contest_alias`    | `string`                                                                                                                                                                                                                                                    |
| `original_problemset_id`    | `number`                                                                                                                                                                                                                                                    |
| `penalty`                   | `number`                                                                                                                                                                                                                                                    |
| `penalty_calc_policy`       | `string`                                                                                                                                                                                                                                                    |
| `penalty_type`              | `string`                                                                                                                                                                                                                                                    |
| `problems`                  | `{ accepted: number; alias: string; commit: string; difficulty: number; languages: string; letter: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]` |
| `points_decay_factor`       | `number`                                                                                                                                                                                                                                                    |
| `problemset_id`             | `number`                                                                                                                                                                                                                                                    |
| `requests_user_information` | `string`                                                                                                                                                                                                                                                    |
| `rerun_id`                  | `number`                                                                                                                                                                                                                                                    |
| `scoreboard`                | `number`                                                                                                                                                                                                                                                    |
| `scoreboard_url`            | `string`                                                                                                                                                                                                                                                    |
| `scoreboard_url_admin`      | `string`                                                                                                                                                                                                                                                    |
| `show_scoreboard_after`     | `boolean`                                                                                                                                                                                                                                                   |
| `start_time`                | `number`                                                                                                                                                                                                                                                    |
| `submissions_gap`           | `number`                                                                                                                                                                                                                                                    |
| `title`                     | `string`                                                                                                                                                                                                                                                    |
| `window_length`             | `number`                                                                                                                                                                                                                                                    |

## `/api/contest/adminList/`

### Description

Returns a list of contests where current user has admin rights (or is
the director).

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `page`      | `mixed` |             |
| `page_size` | `mixed` |             |

### Returns

| Name       | Type                                                                                                                                                                       |
| ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `contests` | `{ admission_mode: string; alias: string; finish_time: Date; rerun_id: number; scoreboard_url: string; scoreboard_url_admin: string; start_time: Date; title: string; }[]` |

## `/api/contest/admins/`

### Description

Returns all contest administrators

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |

### Returns

| Name           | Type                                               |
| -------------- | -------------------------------------------------- |
| `admins`       | `{ role: string; username: string; }[]`            |
| `group_admins` | `{ alias: string; name: string; role: string; }[]` |

## `/api/contest/arbitrateRequest/`

### Description

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `note`          | `mixed` |             |
| `resolution`    | `mixed` |             |
| `username`      | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/clarifications/`

### Description

Get clarifications of a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `offset`        | `mixed` |             |
| `rowcount`      | `mixed` |             |

### Returns

| Name             | Type                    |
| ---------------- | ----------------------- |
| `clarifications` | `types.Clarification[]` |

## `/api/contest/clone/`

### Description

Clone a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `alias`         | `mixed` |             |
| `auth_token`    | `mixed` |             |
| `contest_alias` | `mixed` |             |
| `description`   | `mixed` |             |
| `start_time`    | `mixed` |             |
| `title`         | `mixed` |             |

### Returns

| Name    | Type     |
| ------- | -------- |
| `alias` | `string` |

## `/api/contest/contestants/`

### Description

Return users who participate in a contest, as long as contest admin
has chosen to ask for users information and contestants have
previously agreed to share their information.

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |

### Returns

| Name          | Type                                                                                                   |
| ------------- | ------------------------------------------------------------------------------------------------------ |
| `contestants` | `{ name: string; username: string; email: string; state: string; country: string; school: string; }[]` |

## `/api/contest/create/`

### Description

Creates a new contest

### Parameters

| Name                        | Type    | Description |
| --------------------------- | ------- | ----------- |
| `admission_mode`            | `mixed` |             |
| `alias`                     | `mixed` |             |
| `basic_information`         | `mixed` |             |
| `description`               | `mixed` |             |
| `feedback`                  | `mixed` |             |
| `finish_time`               | `mixed` |             |
| `languages`                 | `mixed` |             |
| `partial_score`             | `mixed` |             |
| `penalty`                   | `mixed` |             |
| `penalty_calc_policy`       | `mixed` |             |
| `penalty_type`              | `mixed` |             |
| `points_decay_factor`       | `mixed` |             |
| `problems`                  | `mixed` |             |
| `requests_user_information` | `mixed` |             |
| `scoreboard`                | `mixed` |             |
| `show_scoreboard_after`     | `mixed` |             |
| `start_time`                | `mixed` |             |
| `submissions_gap`           | `mixed` |             |
| `title`                     | `mixed` |             |
| `window_length`             | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/createVirtual/`

### Description

### Parameters

| Name         | Type    | Description |
| ------------ | ------- | ----------- |
| `alias`      | `mixed` |             |
| `start_time` | `mixed` |             |

### Returns

| Name    | Type     |
| ------- | -------- |
| `alias` | `string` |

## `/api/contest/details/`

### Description

Returns details of a Contest. Requesting the details of a contest will
not start the current user into that contest. In order to participate
in the contest, \OmegaUp\Controllers\Contest::apiOpen() must be used.

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `token`         | `mixed` |             |

### Returns

| Name                        | Type                                                                                                                                                                                                                                                        |
| --------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `admin`                     | `boolean`                                                                                                                                                                                                                                                   |
| `admission_mode`            | `string`                                                                                                                                                                                                                                                    |
| `alias`                     | `string`                                                                                                                                                                                                                                                    |
| `description`               | `string`                                                                                                                                                                                                                                                    |
| `director`                  | `string`                                                                                                                                                                                                                                                    |
| `feedback`                  | `string`                                                                                                                                                                                                                                                    |
| `finish_time`               | `number`                                                                                                                                                                                                                                                    |
| `languages`                 | `string[]`                                                                                                                                                                                                                                                  |
| `needs_basic_information`   | `boolean`                                                                                                                                                                                                                                                   |
| `opened`                    | `boolean`                                                                                                                                                                                                                                                   |
| `partial_score`             | `boolean`                                                                                                                                                                                                                                                   |
| `original_contest_alias`    | `string`                                                                                                                                                                                                                                                    |
| `original_problemset_id`    | `number`                                                                                                                                                                                                                                                    |
| `penalty`                   | `number`                                                                                                                                                                                                                                                    |
| `penalty_calc_policy`       | `string`                                                                                                                                                                                                                                                    |
| `penalty_type`              | `string`                                                                                                                                                                                                                                                    |
| `problems`                  | `{ accepted: number; alias: string; commit: string; difficulty: number; languages: string; letter: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]` |
| `points_decay_factor`       | `number`                                                                                                                                                                                                                                                    |
| `problemset_id`             | `number`                                                                                                                                                                                                                                                    |
| `requests_user_information` | `string`                                                                                                                                                                                                                                                    |
| `scoreboard`                | `number`                                                                                                                                                                                                                                                    |
| `show_scoreboard_after`     | `boolean`                                                                                                                                                                                                                                                   |
| `start_time`                | `number`                                                                                                                                                                                                                                                    |
| `submissions_gap`           | `number`                                                                                                                                                                                                                                                    |
| `submission_deadline`       | `number`                                                                                                                                                                                                                                                    |
| `title`                     | `string`                                                                                                                                                                                                                                                    |
| `window_length`             | `number`                                                                                                                                                                                                                                                    |

## `/api/contest/list/`

### Description

Returns a list of contests

### Parameters

| Name             | Type    | Description |
| ---------------- | ------- | ----------- |
| `active`         | `mixed` |             |
| `admission_mode` | `mixed` |             |
| `page`           | `mixed` |             |
| `page_size`      | `mixed` |             |
| `participating`  | `mixed` |             |
| `query`          | `mixed` |             |
| `recommended`    | `mixed` |             |

### Returns

| Name                | Type                                                                                                                                                                                                                                                                              |
| ------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `number_of_results` | `number`                                                                                                                                                                                                                                                                          |
| `results`           | `{ admission_mode: string; alias: string; contest_id: number; description: string; finish_time: Date; last_updated: Date; original_finish_time: Date; problemset_id: number; recommended: boolean; rerun_id: number; start_time: Date; title: string; window_length: number; }[]` |

## `/api/contest/listParticipating/`

### Description

Returns a list of contests where current user is participating in

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `page`      | `mixed` |             |
| `page_size` | `mixed` |             |
| `query`     | `mixed` |             |

### Returns

| Name       | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| ---------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `contests` | `{ acl_id?: number; admission_mode: string; alias: string; contest_id: number; description: string; feedback?: string; finish_time: Date; languages?: string; last_updated: Date; original_finish_time?: Date; partial_score?: number; penalty?: number; penalty_calc_policy?: string; penalty_type?: string; points_decay_factor?: number; problemset_id: number; recommended: boolean; rerun_id: number; scoreboard?: number; scoreboard_url: string; scoreboard_url_admin: string; show_scoreboard_after?: number; start_time: Date; submissions_gap?: number; title: string; urgent?: number; window_length: number; }[]` |

## `/api/contest/myList/`

### Description

Returns a list of contests where current user is the director

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `page`      | `mixed` |             |
| `page_size` | `mixed` |             |
| `query`     | `mixed` |             |

### Returns

| Name       | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| ---------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `contests` | `{ acl_id?: number; admission_mode: string; alias: string; contest_id: number; description: string; feedback?: string; finish_time: Date; languages?: string; last_updated: Date; original_finish_time?: Date; partial_score?: number; penalty?: number; penalty_calc_policy?: string; penalty_type?: string; points_decay_factor?: number; problemset_id: number; recommended: boolean; rerun_id: number; scoreboard?: number; scoreboard_url: string; scoreboard_url_admin: string; show_scoreboard_after?: number; start_time: Date; submissions_gap?: number; title: string; urgent?: number; window_length: number; }[]` |

## `/api/contest/open/`

### Description

Joins a contest - explicitly adds a identity to a contest.

### Parameters

| Name                     | Type    | Description |
| ------------------------ | ------- | ----------- |
| `contest_alias`          | `mixed` |             |
| `privacy_git_object_id`  | `mixed` |             |
| `share_user_information` | `mixed` |             |
| `statement_type`         | `mixed` |             |
| `token`                  | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/problems/`

### Description

Gets the problems from a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |

### Returns

| Name       | Type                                                                                                                                                                                                                                        |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `problems` | `{ accepted: number; alias: string; commit: string; difficulty: number; languages: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]` |

## `/api/contest/publicDetails/`

### Description

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |

### Returns

| Name                          | Type      |
| ----------------------------- | --------- |
| `admission_mode`              | `string`  |
| `alias`                       | `string`  |
| `description`                 | `string`  |
| `feedback`                    | `string`  |
| `finish_time`                 | `number`  |
| `languages`                   | `string`  |
| `partial_score`               | `boolean` |
| `penalty`                     | `number`  |
| `penalty_calc_policy`         | `string`  |
| `penalty_type`                | `string`  |
| `points_decay_factor`         | `number`  |
| `problemset_id`               | `number`  |
| `rerun_id`                    | `number`  |
| `scoreboard`                  | `number`  |
| `show_scoreboard_after`       | `boolean` |
| `start_time`                  | `number`  |
| `submissions_gap`             | `number`  |
| `title`                       | `string`  |
| `window_length`               | `number`  |
| `user_registration_requested` | `boolean` |
| `user_registration_answered`  | `boolean` |
| `user_registration_accepted`  | `boolean` |

## `/api/contest/registerForContest/`

### Description

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/removeAdmin/`

### Description

Removes an admin from a contest

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `contest_alias`   | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/removeGroup/`

### Description

Removes a group from a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `group`         | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/removeGroupAdmin/`

### Description

Removes a group admin from a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `group`         | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/removeProblem/`

### Description

Removes a problem from a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `problem_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/removeUser/`

### Description

Remove a user from a private contest

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `contest_alias`   | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/report/`

### Description

Returns a detailed report of the contest

### Returns

| Name          | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| ------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `finish_time` | `number`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `problems`    | `{ alias: string; order: number; }[]`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| `ranking`     | `{ country: string; is_invited: boolean; name: string; place?: number; problems: { alias: string; penalty: number; percent: number; place?: number; points: number; run_details?: { cases?: { contest_score: number; max_score: number; meta: { status: string; }; name: string; out_diff: string; score: number; verdict: string; }[]; details: { groups: { cases: { meta: { memory: number; time: number; wall_time: number; }; }[]; }[]; }; }; runs: number; }[]; total: { penalty: number; points: number; }; username: string; }[]` |
| `start_time`  | `number`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `time`        | `number`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `title`       | `string`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |

## `/api/contest/requests/`

### Description

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |

### Returns

| Name            | Type                                                                                                                                 |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| `users`         | `{ accepted: boolean; admin?: { username?: string; }; country: string; last_update: Date; request_time: Date; username: string; }[]` |
| `contest_alias` | `string`                                                                                                                             |

## `/api/contest/role/`

### Description

Given a contest_alias and user_id, returns the role of the user within
the context of a contest.

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `token`         | `mixed` |             |

### Returns

| Name    | Type      |
| ------- | --------- |
| `admin` | `boolean` |

## `/api/contest/runs/`

### Description

Returns all runs for a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `language`      | `mixed` |             |
| `offset`        | `mixed` |             |
| `problem_alias` | `mixed` |             |
| `rowcount`      | `mixed` |             |
| `status`        | `mixed` |             |
| `username`      | `mixed` |             |
| `verdict`       | `mixed` |             |

### Returns

| Name   | Type          |
| ------ | ------------- |
| `runs` | `types.Run[]` |

## `/api/contest/runsDiff/`

### Description

Return a report of which runs would change due to a version change.

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `problem_alias` | `mixed` |             |
| `version`       | `mixed` |             |

### Returns

| Name   | Type                                                                                                                                                                                   |
| ------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `diff` | `{ guid: string; new_score: number; new_status: string; new_verdict: string; old_score: number; old_status: string; old_verdict: string; problemset_id: number; username: string; }[]` |

## `/api/contest/scoreboard/`

### Description

Returns the Scoreboard

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `token`         | `mixed` |             |

### Returns

| Name          | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| ------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `finish_time` | `number`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `problems`    | `{ alias: string; order: number; }[]`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| `ranking`     | `{ country: string; is_invited: boolean; name: string; place?: number; problems: { alias: string; penalty: number; percent: number; place?: number; points: number; run_details?: { cases?: { contest_score: number; max_score: number; meta: { status: string; }; name: string; out_diff: string; score: number; verdict: string; }[]; details: { groups: { cases: { meta: { memory: number; time: number; wall_time: number; }; }[]; }[]; }; }; runs: number; }[]; total: { penalty: number; points: number; }; username: string; }[]` |
| `start_time`  | `number`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `time`        | `number`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `title`       | `string`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |

## `/api/contest/scoreboardEvents/`

### Description

Returns the Scoreboard events

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `token`         | `mixed` |             |

### Returns

| Name     | Type                                                                                                                                                                                                    |
| -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `events` | `{ country: string; delta: number; is_invited: boolean; total: { points: number; penalty: number; }; name: string; username: string; problem: { alias: string; points: number; penalty: number; }; }[]` |

## `/api/contest/scoreboardMerge/`

### Description

Gets the accomulative scoreboard for an array of contests

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `contest_aliases`  | `mixed` |             |
| `contest_params`   | `mixed` |             |
| `usernames_filter` | `mixed` |             |

### Returns

| Name      | Type                                                                                                                                                     |
| --------- | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `ranking` | `{ name: string; username: string; contests: { [key: string]: { points: number; penalty: number; }; }; total: { points: number; penalty: number; }; }[]` |

## `/api/contest/setRecommended/`

### Description

Given a contest_alias, sets the recommended flag on/off.
Only omegaUp admins can call this API.

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `value`         | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/stats/`

### Description

Stats of a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |

### Returns

| Name                 | Type                         |
| -------------------- | ---------------------------- |
| `total_runs`         | `number`                     |
| `pending_runs`       | `string[]`                   |
| `max_wait_time`      | `number`                     |
| `max_wait_time_guid` | `string`                     |
| `verdict_counts`     | `{ [key: string]: number; }` |
| `distribution`       | `{ [key: number]: number; }` |
| `size_of_bucket`     | `number`                     |
| `total_points`       | `number`                     |

## `/api/contest/update/`

### Description

Update a Contest

### Parameters

| Name                        | Type    | Description |
| --------------------------- | ------- | ----------- |
| `admission_mode`            | `mixed` |             |
| `alias`                     | `mixed` |             |
| `basic_information`         | `mixed` |             |
| `contest_alias`             | `mixed` |             |
| `description`               | `mixed` |             |
| `feedback`                  | `mixed` |             |
| `finish_time`               | `mixed` |             |
| `languages`                 | `mixed` |             |
| `penalty_calc_policy`       | `mixed` |             |
| `penalty_type`              | `mixed` |             |
| `problems`                  | `mixed` |             |
| `requests_user_information` | `mixed` |             |
| `start_time`                | `mixed` |             |
| `submissions_gap`           | `mixed` |             |
| `title`                     | `mixed` |             |
| `window_length`             | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/updateEndTimeForIdentity/`

### Description

Update Contest end time for an identity when window_length
option is turned on

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `end_time`      | `mixed` |             |
| `username`      | `mixed` |             |

### Returns

_Nothing_

## `/api/contest/users/`

### Description

Returns ALL identities participating in a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |

### Returns

| Name     | Type                                                                                                   |
| -------- | ------------------------------------------------------------------------------------------------------ |
| `users`  | `{ access_time: number; country_id: string; end_time: number; is_owner: number; username: string; }[]` |
| `groups` | `{ alias: string; name: string; }[]`                                                                   |

# Course

CourseController

## `/api/course/activityReport/`

### Description

Returns a report with all user activity for a course.

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `course_alias` | `mixed` |             |

### Returns

| Name     | Type                                                                                    |
| -------- | --------------------------------------------------------------------------------------- |
| `events` | `{ username: string; ip: number; time: number; classname?: string; alias?: string; }[]` |

## `/api/course/addAdmin/`

### Description

Adds an admin to a course

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `course_alias`    | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

## `/api/course/addGroupAdmin/`

### Description

Adds an group admin to a course

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `course_alias` | `mixed` |             |
| `group`        | `mixed` |             |

### Returns

_Nothing_

## `/api/course/addProblem/`

### Description

Adds a problem to an assignment

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `assignment_alias` | `mixed` |             |
| `commit`           | `mixed` |             |
| `course_alias`     | `mixed` |             |
| `points`           | `mixed` |             |
| `problem_alias`    | `mixed` |             |

### Returns

_Nothing_

## `/api/course/addStudent/`

### Description

Add Student to Course.

### Parameters

| Name                           | Type    | Description |
| ------------------------------ | ------- | ----------- |
| `accept_teacher`               | `mixed` |             |
| `accept_teacher_git_object_id` | `mixed` |             |
| `course_alias`                 | `mixed` |             |
| `privacy_git_object_id`        | `mixed` |             |
| `share_user_information`       | `mixed` |             |
| `statement_type`               | `mixed` |             |
| `usernameOrEmail`              | `mixed` |             |

### Returns

_Nothing_

## `/api/course/adminDetails/`

### Description

Returns all details of a given Course

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `alias` | `mixed` |             |

### Returns

```typescript
types.CourseDetails;
```

## `/api/course/admins/`

### Description

Returns all course administrators

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `course_alias` | `mixed` |             |

### Returns

| Name           | Type                                               |
| -------------- | -------------------------------------------------- |
| `admins`       | `{ role: string; username: string; }[]`            |
| `group_admins` | `{ alias: string; name: string; role: string; }[]` |

## `/api/course/arbitrateRequest/`

### Description

Stores the resolution given to a certain request made by a contestant
interested to join the course.

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |
| `resolution`   | `bool`   |             |
| `username`     | `string` |             |

### Returns

_Nothing_

## `/api/course/assignmentDetails/`

### Description

Returns details of a given assignment

### Parameters

| Name         | Type    | Description |
| ------------ | ------- | ----------- |
| `assignment` | `mixed` |             |
| `course`     | `mixed` |             |
| `token`      | `mixed` |             |
| `username`   | `mixed` |             |

### Returns

| Name              | Type                                                                                                                                                                                                                                        |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `name`            | `string`                                                                                                                                                                                                                                    |
| `description`     | `string`                                                                                                                                                                                                                                    |
| `assignment_type` | `string`                                                                                                                                                                                                                                    |
| `start_time`      | `number`                                                                                                                                                                                                                                    |
| `finish_time`     | `number`                                                                                                                                                                                                                                    |
| `problems`        | `{ accepted: number; alias: string; commit: string; difficulty: number; languages: string; order: number; points: number; problem_id: number; submissions: number; title: string; version: string; visibility: number; visits: number; }[]` |
| `director`        | `string`                                                                                                                                                                                                                                    |
| `problemset_id`   | `number`                                                                                                                                                                                                                                    |
| `admin`           | `boolean`                                                                                                                                                                                                                                   |

## `/api/course/assignmentScoreboard/`

### Description

Gets Scoreboard for an assignment

### Parameters

| Name         | Type    | Description |
| ------------ | ------- | ----------- |
| `assignment` | `mixed` |             |
| `course`     | `mixed` |             |
| `token`      | `mixed` |             |

### Returns

| Name          | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| ------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `finish_time` | `number`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `problems`    | `{ alias: string; order: number; }[]`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| `ranking`     | `{ country: string; is_invited: boolean; name: string; place?: number; problems: { alias: string; penalty: number; percent: number; place?: number; points: number; run_details?: { cases?: { contest_score: number; max_score: number; meta: { status: string; }; name: string; out_diff: string; score: number; verdict: string; }[]; details: { groups: { cases: { meta: { memory: number; time: number; wall_time: number; }; }[]; }[]; }; }; runs: number; }[]; total: { penalty: number; points: number; }; username: string; }[]` |
| `start_time`  | `number`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `time`        | `number`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| `title`       | `string`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |

## `/api/course/assignmentScoreboardEvents/`

### Description

Returns the Scoreboard events

### Parameters

| Name         | Type    | Description |
| ------------ | ------- | ----------- |
| `assignment` | `mixed` |             |
| `course`     | `mixed` |             |
| `token`      | `mixed` |             |

### Returns

| Name     | Type                                                                                                                                                                                                    |
| -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `events` | `{ country: string; delta: number; is_invited: boolean; name: string; problem: { alias: string; penalty: number; points: number; }; total: { penalty: number; points: number; }; username: string; }[]` |

## `/api/course/clone/`

### Description

Clone a course

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `alias`        | `mixed` |             |
| `course_alias` | `mixed` |             |
| `name`         | `mixed` |             |
| `start_time`   | `mixed` |             |

### Returns

| Name    | Type     |
| ------- | -------- |
| `alias` | `string` |

## `/api/course/create/`

### Description

Create new course API

### Parameters

| Name                        | Type    | Description |
| --------------------------- | ------- | ----------- |
| `admission_mode`            | `mixed` |             |
| `alias`                     | `mixed` |             |
| `description`               | `mixed` |             |
| `finish_time`               | `mixed` |             |
| `name`                      | `mixed` |             |
| `needs_basic_information`   | `mixed` |             |
| `public`                    | `mixed` |             |
| `requests_user_information` | `mixed` |             |
| `school_id`                 | `mixed` |             |
| `show_scoreboard`           | `mixed` |             |
| `start_time`                | `mixed` |             |
| `unlimited_duration`        | `mixed` |             |

### Returns

_Nothing_

## `/api/course/createAssignment/`

### Description

API to Create an assignment

### Parameters

| Name                 | Type    | Description |
| -------------------- | ------- | ----------- |
| `alias`              | `mixed` |             |
| `assignment_type`    | `mixed` |             |
| `course_alias`       | `mixed` |             |
| `description`        | `mixed` |             |
| `finish_time`        | `mixed` |             |
| `name`               | `mixed` |             |
| `publish_time_delay` | `mixed` |             |
| `start_time`         | `mixed` |             |
| `unlimited_duration` | `mixed` |             |

### Returns

_Nothing_

## `/api/course/details/`

### Description

Returns details of a given course

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `alias` | `mixed` |             |

### Returns

```typescript
types.CourseDetails;
```

## `/api/course/getProblemUsers/`

### Description

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `course_alias`  | `mixed` |             |
| `problem_alias` | `mixed` |             |

### Returns

| Name         | Type       |
| ------------ | ---------- |
| `identities` | `string[]` |

## `/api/course/introDetails/`

### Description

Show course intro only on public courses when user is not yet registered

### Returns

| Name                      | Type                                                                                                                                                                |
| ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `name`                    | `string`                                                                                                                                                            |
| `description`             | `string`                                                                                                                                                            |
| `alias`                   | `string`                                                                                                                                                            |
| `currentUsername`         | `string`                                                                                                                                                            |
| `needsBasicInformation`   | `boolean`                                                                                                                                                           |
| `requestsUserInformation` | `string`                                                                                                                                                            |
| `shouldShowAcceptTeacher` | `boolean`                                                                                                                                                           |
| `statements`              | `{ privacy: { markdown: string; gitObjectId: string; statementType: string; }; acceptTeacher: { gitObjectId: string; markdown: string; statementType: string; }; }` |
| `isFirstTimeAccess`       | `boolean`                                                                                                                                                           |
| `shouldShowResults`       | `boolean`                                                                                                                                                           |

## `/api/course/listAssignments/`

### Description

List course assignments

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `course_alias` | `mixed` |             |

### Returns

| Name          | Type                                                                                                                                                                                                                |
| ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `assignments` | `{ alias: string; assignment_type: string; description: string; finish_time: number; has_runs: boolean; name: string; order: number; scoreboard_url: string; scoreboard_url_admin: string; start_time: number; }[]` |

## `/api/course/listCourses/`

### Description

Lists all the courses this user is associated with.

Returns courses for which the current user is an admin and
for in which the user is a student.

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `page`      | `mixed` |             |
| `page_size` | `mixed` |             |

### Returns

| Name      | Type                                                                                                              |
| --------- | ----------------------------------------------------------------------------------------------------------------- |
| `admin`   | `{ alias: string; counts: { [key: string]: number; }; finish_time: number; name: string; start_time: number; }[]` |
| `public`  | `{ alias: string; counts: { [key: string]: number; }; finish_time: number; name: string; start_time: number; }[]` |
| `student` | `{ alias: string; counts: { [key: string]: number; }; finish_time: number; name: string; start_time: number; }[]` |

## `/api/course/listSolvedProblems/`

### Description

Get Problems solved by users of a course

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `course_alias` | `mixed` |             |

### Returns

| Name            | Type                                                                        |
| --------------- | --------------------------------------------------------------------------- |
| `user_problems` | `{ [key: string]: { alias: string; title: string; username: string; }[]; }` |

## `/api/course/listStudents/`

### Description

List students in a course

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `course_alias` | `mixed` |             |

### Returns

| Name       | Type                                                                          |
| ---------- | ----------------------------------------------------------------------------- |
| `students` | `{ name: string; progress: { [key: string]: number; }; username: string; }[]` |

## `/api/course/listUnsolvedProblems/`

### Description

Get Problems unsolved by users of a course

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `course_alias` | `mixed` |             |

### Returns

| Name            | Type                                                                        |
| --------------- | --------------------------------------------------------------------------- |
| `user_problems` | `{ [key: string]: { alias: string; title: string; username: string; }[]; }` |

## `/api/course/myProgress/`

### Description

Returns details of a given course

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `alias` | `mixed` |             |

### Returns

| Name          | Type                       |
| ------------- | -------------------------- |
| `assignments` | `types.AssignmentProgress` |

## `/api/course/registerForCourse/`

### Description

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `course_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/course/removeAdmin/`

### Description

Removes an admin from a course

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `course_alias`    | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

## `/api/course/removeGroupAdmin/`

### Description

Removes a group admin from a course

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `course_alias` | `mixed` |             |
| `group`        | `mixed` |             |

### Returns

_Nothing_

## `/api/course/removeProblem/`

### Description

Remove a problem from an assignment

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `assignment_alias` | `mixed` |             |
| `course_alias`     | `mixed` |             |
| `problem_alias`    | `mixed` |             |

### Returns

_Nothing_

## `/api/course/removeStudent/`

### Description

Remove Student from Course

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `course_alias`    | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

## `/api/course/requests/`

### Description

Returns the list of requests made by participants who are interested to
join the course

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |

### Returns

| Name    | Type                                                                                                                                                                  |
| ------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `users` | `{ accepted: boolean; admin?: { name: string; username: string; }; country: string; country_id: string; last_update: Date; request_time: Date; username: string; }[]` |

## `/api/course/runs/`

### Description

Returns all runs for a course

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `assignment_alias` | `mixed` |             |
| `course_alias`     | `mixed` |             |
| `language`         | `mixed` |             |
| `offset`           | `mixed` |             |
| `problem_alias`    | `mixed` |             |
| `rowcount`         | `mixed` |             |
| `status`           | `mixed` |             |
| `username`         | `mixed` |             |
| `verdict`          | `mixed` |             |

### Returns

| Name   | Type                                                                                                                                                                                                                                                                                                                                               |
| ------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `runs` | `{ run_id: number; guid: string; language: string; status: string; verdict: string; runtime: number; penalty: number; memory: number; score: number; contest_score: number; judged_by: string; time: Date; submit_delay: number; type: string; username: string; classname: string; alias: string; country_id: string; contest_alias: string; }[]` |

## `/api/course/studentProgress/`

### Description

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `assignment_alias` | `mixed` |             |
| `course_alias`     | `mixed` |             |
| `usernameOrEmail`  | `mixed` |             |

### Returns

| Name       | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| ---------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `problems` | `{ accepted: number; alias: string; commit: string; difficulty: number; languages: string; letter: string; order: number; points: number; submissions: number; title: string; version: string; visibility: number; visits: number; runs: { guid: string; language: string; source?: string; status: string; verdict: string; runtime: number; penalty: number; memory: number; score: number; contest_score: number; time: Date; submit_delay: number; }[]; }[]` |

## `/api/course/update/`

### Description

Edit Course contents

### Parameters

| Name                        | Type    | Description |
| --------------------------- | ------- | ----------- |
| `admission_mode`            | `mixed` |             |
| `alias`                     | `mixed` |             |
| `course_alias`              | `mixed` |             |
| `description`               | `mixed` |             |
| `finish_time`               | `mixed` |             |
| `name`                      | `mixed` |             |
| `requests_user_information` | `mixed` |             |
| `school_id`                 | `mixed` |             |
| `start_time`                | `mixed` |             |
| `unlimited_duration`        | `mixed` |             |

### Returns

_Nothing_

## `/api/course/updateAssignment/`

### Description

Update an assignment

### Parameters

| Name                 | Type    | Description |
| -------------------- | ------- | ----------- |
| `assignment`         | `mixed` |             |
| `course`             | `mixed` |             |
| `finish_time`        | `mixed` |             |
| `start_time`         | `mixed` |             |
| `unlimited_duration` | `mixed` |             |

### Returns

_Nothing_

## `/api/course/updateAssignmentsOrder/`

### Description

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `assignments`  | `mixed` |             |
| `course_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/course/updateProblemsOrder/`

### Description

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `assignment_alias` | `mixed` |             |
| `course_alias`     | `mixed` |             |
| `order`            | `mixed` |             |
| `problems`         | `mixed` |             |

### Returns

_Nothing_

# Grader

Description of GraderController

## `/api/grader/status/`

### Description

Calls to /status grader

### Returns

| Name     | Type                 |
| -------- | -------------------- |
| `grader` | `types.GraderStatus` |

# Group

GroupController

## `/api/group/addUser/`

### Description

Add identity to group

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `group_alias`     | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

## `/api/group/create/`

### Description

New group

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `alias`       | `mixed` |             |
| `description` | `mixed` |             |
| `name`        | `mixed` |             |

### Returns

_Nothing_

## `/api/group/createScoreboard/`

### Description

Create a scoreboard set to a group

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `alias`       | `mixed` |             |
| `description` | `mixed` |             |
| `group_alias` | `mixed` |             |
| `name`        | `mixed` |             |

### Returns

_Nothing_

## `/api/group/details/`

### Description

Details of a group (scoreboards)

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `group_alias` | `mixed` |             |

### Returns

| Name          | Type                                                                           |
| ------------- | ------------------------------------------------------------------------------ |
| `exists`      | `boolean`                                                                      |
| `group`       | `{ create_time: number; alias: string; name: string; description: string; }`   |
| `scoreboards` | `{ alias: string; create_time: string; description: string; name: string; }[]` |

## `/api/group/list/`

### Description

Returns a list of groups that match a partial name. This returns an
array instead of an object since it is used by typeahead.

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `query` | `mixed` |             |

### Returns

```typescript
{
  label: string;
  value: string;
}
[];
```

## `/api/group/members/`

### Description

Members of a group (usernames only).

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `group_alias` | `mixed` |             |

### Returns

| Name         | Type                                                                                                                                                                       |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `identities` | `{ classname: string; country?: string; country_id?: string; name?: string; school?: string; school_id?: number; state?: string; state_id?: string; username: string; }[]` |

## `/api/group/myList/`

### Description

Returns a list of groups by owner

### Returns

| Name     | Type                                                                           |
| -------- | ------------------------------------------------------------------------------ |
| `groups` | `{ alias: string; create_time: number; description: string; name: string; }[]` |

## `/api/group/removeUser/`

### Description

Remove user from group

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `group_alias`     | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

# GroupScoreboard

GroupScoreboardController

## `/api/groupScoreboard/addContest/`

### Description

Add contest to a group scoreboard

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `contest_alias`    | `mixed` |             |
| `group_alias`      | `mixed` |             |
| `only_ac`          | `mixed` |             |
| `scoreboard_alias` | `mixed` |             |
| `weight`           | `mixed` |             |

### Returns

_Nothing_

## `/api/groupScoreboard/details/`

### Description

Details of a scoreboard. Returns a list with all contests that belong to
the given scoreboard_alias

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `group_alias`      | `mixed` |             |
| `scoreboard_alias` | `mixed` |             |

### Returns

| Name         | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| ------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `ranking`    | `{ name: string; username: string; contests: { [key: string]: { points: number; penalty: number; }; }; total: { points: number; penalty: number; }; }[]`                                                                                                                                                                                                                                                                                                                                                                                              |
| `scoreboard` | `{ group_scoreboard_id: number; group_id: number; create_time: number; alias: string; name: string; description: string; }`                                                                                                                                                                                                                                                                                                                                                                                                                           |
| `contests`   | `{ contest_id: number; problemset_id: number; acl_id: number; title: string; description: string; start_time: number; finish_time: number; last_updated: number; window_length: number; rerun_id: number; admission_mode: string; alias: string; scoreboard: number; points_decay_factor: number; partial_score: boolean; submissions_gap: number; feedback: string; penalty: string; penalty_calc_policy: string; show_scoreboard_after: boolean; urgent: boolean; languages: string; recommended: boolean; only_ac?: boolean; weight?: number; }[]` |

## `/api/groupScoreboard/list/`

### Description

Details of a scoreboard

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `group_alias` | `mixed` |             |

### Returns

| Name          | Type                                                                                                                          |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `scoreboards` | `{ group_scoreboard_id: number; group_id: number; create_time: number; alias: string; name: string; description: string; }[]` |

## `/api/groupScoreboard/removeContest/`

### Description

Add contest to a group scoreboard

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `contest_alias`    | `mixed` |             |
| `group_alias`      | `mixed` |             |
| `scoreboard_alias` | `mixed` |             |

### Returns

_Nothing_

# Identity

IdentityController

## `/api/identity/bulkCreate/`

### Description

Entry point for Create bulk Identities API

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `group_alias` | `mixed` |             |
| `identities`  | `mixed` |             |
| `name`        | `mixed` |             |
| `username`    | `mixed` |             |

### Returns

_Nothing_

## `/api/identity/changePassword/`

### Description

Entry point for change passowrd of an identity

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `group_alias` | `mixed` |             |
| `identities`  | `mixed` |             |
| `name`        | `mixed` |             |
| `password`    | `mixed` |             |
| `username`    | `mixed` |             |

### Returns

_Nothing_

## `/api/identity/create/`

### Description

Entry point for Create an Identity API

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `country_id`  | `mixed` |             |
| `gender`      | `mixed` |             |
| `group_alias` | `mixed` |             |
| `identities`  | `mixed` |             |
| `name`        | `mixed` |             |
| `password`    | `mixed` |             |
| `school_name` | `mixed` |             |
| `state_id`    | `mixed` |             |
| `username`    | `mixed` |             |

### Returns

| Name       | Type     |
| ---------- | -------- |
| `username` | `string` |

## `/api/identity/update/`

### Description

Entry point for Update an Identity API

### Parameters

| Name                | Type    | Description |
| ------------------- | ------- | ----------- |
| `country_id`        | `mixed` |             |
| `gender`            | `mixed` |             |
| `group_alias`       | `mixed` |             |
| `identities`        | `mixed` |             |
| `name`              | `mixed` |             |
| `original_username` | `mixed` |             |
| `school_name`       | `mixed` |             |
| `state_id`          | `mixed` |             |
| `username`          | `mixed` |             |

### Returns

_Nothing_

# Interview

## `/api/interview/addUsers/`

### Description

### Parameters

| Name                  | Type    | Description |
| --------------------- | ------- | ----------- |
| `interview_alias`     | `mixed` |             |
| `usernameOrEmailsCSV` | `mixed` |             |

### Returns

_Nothing_

## `/api/interview/create/`

### Description

### Parameters

| Name          | Type    | Description |
| ------------- | ------- | ----------- |
| `alias`       | `mixed` |             |
| `description` | `mixed` |             |
| `duration`    | `mixed` |             |
| `title`       | `mixed` |             |

### Returns

_Nothing_

## `/api/interview/details/`

### Description

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `interview_alias` | `mixed` |             |

### Returns

| Name            | Type                                                                                                                     |
| --------------- | ------------------------------------------------------------------------------------------------------------------------ |
| `description`   | `string`                                                                                                                 |
| `contest_alias` | `string`                                                                                                                 |
| `problemset_id` | `number`                                                                                                                 |
| `users`         | `{ user_id: number; username: string; access_time: Date; email: string; opened_interview: boolean; country: string; }[]` |
| `exists`        | `boolean`                                                                                                                |

## `/api/interview/list/`

### Description

### Returns

| Name     | Type                                                                                                                                           |
| -------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| `result` | `{ acl_id: number; alias: string; description: string; interview_id: number; problemset_id: number; title: string; window_length: number; }[]` |

# Notification

BadgesController

## `/api/notification/myList/`

### Description

Returns a list of unread notifications for user

### Returns

| Name            | Type                   |
| --------------- | ---------------------- |
| `notifications` | `types.Notification[]` |

## `/api/notification/readNotifications/`

### Description

Updates notifications as read in database

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `notifications` | `mixed` |             |

### Returns

_Nothing_

# Problem

ProblemsController

## `/api/problem/addAdmin/`

### Description

Adds an admin to a problem

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `problem_alias`   | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

## `/api/problem/addGroupAdmin/`

### Description

Adds a group admin to a problem

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `group`         | `mixed` |             |
| `problem_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/problem/addTag/`

### Description

Adds a tag to a problem

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `name`          | `mixed` |             |
| `problem_alias` | `mixed` |             |
| `public`        | `mixed` |             |

### Returns

| Name   | Type     |
| ------ | -------- |
| `name` | `string` |

## `/api/problem/adminList/`

### Description

Returns a list of problems where current user has admin rights (or is
the owner).

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `page`      | `mixed` |             |
| `page_size` | `mixed` |             |

### Returns

| Name         | Type                                               |
| ------------ | -------------------------------------------------- |
| `pagerItems` | `types.PageItem[]`                                 |
| `problems`   | `{ tags: { name: string; source: string; }[]; }[]` |

## `/api/problem/admins/`

### Description

Returns all problem administrators

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `problem_alias` | `mixed` |             |

### Returns

| Name           | Type                                               |
| -------------- | -------------------------------------------------- |
| `admins`       | `{ role: string; username: string; }[]`            |
| `group_admins` | `{ alias: string; name: string; role: string; }[]` |

## `/api/problem/bestScore/`

### Description

Returns the best score for a problem

### Parameters

| Name             | Type    | Description |
| ---------------- | ------- | ----------- |
| `contest_alias`  | `mixed` |             |
| `lang`           | `mixed` |             |
| `problem_alias`  | `mixed` |             |
| `problemset_id`  | `mixed` |             |
| `statement_type` | `mixed` |             |
| `username`       | `mixed` |             |

### Returns

| Name    | Type     |
| ------- | -------- |
| `score` | `number` |

## `/api/problem/clarifications/`

### Description

Entry point for Problem clarifications API

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `offset`        | `mixed` |             |
| `problem_alias` | `mixed` |             |
| `rowcount`      | `mixed` |             |

### Returns

| Name             | Type                                                                                                                                   |
| ---------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| `clarifications` | `{ clarification_id: number; contest_alias: string; author: string; message: string; time: Date; answer: string; public: boolean; }[]` |

## `/api/problem/create/`

### Description

Create a new problem

### Parameters

| Name                      | Type     | Description |
| ------------------------- | -------- | ----------- |
| `allow_user_add_tags`     | `bool`   |             |
| `email_clarifications`    | `mixed`  |             |
| `extra_wall_time`         | `mixed`  |             |
| `input_limit`             | `mixed`  |             |
| `languages`               | `mixed`  |             |
| `memory_limit`            | `mixed`  |             |
| `output_limit`            | `mixed`  |             |
| `overall_wall_time_limit` | `mixed`  |             |
| `problem_alias`           | `mixed`  |             |
| `selected_tags`           | `mixed`  |             |
| `show_diff`               | `string` |             |
| `source`                  | `mixed`  |             |
| `time_limit`              | `mixed`  |             |
| `title`                   | `mixed`  |             |
| `update_published`        | `mixed`  |             |
| `validator`               | `mixed`  |             |
| `validator_time_limit`    | `mixed`  |             |
| `visibility`              | `mixed`  |             |

### Returns

_Nothing_

## `/api/problem/delete/`

### Description

Removes a problem whether user is the creator

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `problem_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/problem/details/`

### Description

Entry point for Problem Details API

### Parameters

| Name                      | Type    | Description |
| ------------------------- | ------- | ----------- |
| `contest_alias`           | `mixed` |             |
| `lang`                    | `mixed` |             |
| `prevent_problemset_open` | `mixed` |             |
| `problem_alias`           | `mixed` |             |
| `problemset_id`           | `mixed` |             |
| `show_solvers`            | `mixed` |             |
| `statement_type`          | `mixed` |             |

### Returns

| Name                   | Type                                                                                                                                                                                                                                |
| ---------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `accepted`             | `number`                                                                                                                                                                                                                            |
| `admin`                | `boolean`                                                                                                                                                                                                                           |
| `alias`                | `string`                                                                                                                                                                                                                            |
| `allow_user_add_tags`  | `boolean`                                                                                                                                                                                                                           |
| `commit`               | `string`                                                                                                                                                                                                                            |
| `creation_date`        | `number`                                                                                                                                                                                                                            |
| `difficulty`           | `number`                                                                                                                                                                                                                            |
| `email_clarifications` | `boolean`                                                                                                                                                                                                                           |
| `exists`               | `boolean`                                                                                                                                                                                                                           |
| `input_limit`          | `number`                                                                                                                                                                                                                            |
| `languages`            | `string[]`                                                                                                                                                                                                                          |
| `order`                | `string`                                                                                                                                                                                                                            |
| `points`               | `number`                                                                                                                                                                                                                            |
| `preferred_language`   | `string`                                                                                                                                                                                                                            |
| `problemsetter`        | `{ creation_date: number; name: string; username: string; }`                                                                                                                                                                        |
| `quality_seal`         | `boolean`                                                                                                                                                                                                                           |
| `runs`                 | `{ alias: string; contest_score: number; guid: string; language: string; memory: number; penalty: number; runtime: number; score: number; status: string; submit_delay: number; time: Date; username: string; verdict: string; }[]` |
| `score`                | `number`                                                                                                                                                                                                                            |
| `settings`             | `{ cases: { [key: string]: { in: string; out: string; weight?: number; }; }; limits: { MemoryLimit: number|string; OverallWallTimeLimit: string; TimeLimit: string; }; validator?: { name: string; tolerance?: number; }; }`        |
| `show_diff`            | `string`                                                                                                                                                                                                                            |
| `solvers`              | `{ language: string; memory: number; runtime: number; time: number; username: string; }[]`                                                                                                                                          |
| `source`               | `string`                                                                                                                                                                                                                            |
| `statement`            | `{ images: { [key: string]: string; }; language: string; markdown: string; }`                                                                                                                                                       |
| `submissions`          | `number`                                                                                                                                                                                                                            |
| `title`                | `string`                                                                                                                                                                                                                            |
| `version`              | `string`                                                                                                                                                                                                                            |
| `visibility`           | `number`                                                                                                                                                                                                                            |
| `visits`               | `number`                                                                                                                                                                                                                            |

## `/api/problem/list/`

### Description

List of public and user's private problems

### Parameters

| Name                    | Type    | Description |
| ----------------------- | ------- | ----------- |
| `difficulty_range`      | `mixed` |             |
| `language`              | `mixed` |             |
| `max_difficulty`        | `mixed` |             |
| `min_difficulty`        | `mixed` |             |
| `min_visibility`        | `mixed` |             |
| `mode`                  | `mixed` |             |
| `offset`                | `mixed` |             |
| `only_karel`            | `mixed` |             |
| `order_by`              | `mixed` |             |
| `page`                  | `mixed` |             |
| `programming_languages` | `mixed` |             |
| `query`                 | `mixed` |             |
| `require_all_tags`      | `mixed` |             |
| `rowcount`              | `mixed` |             |
| `some_tags`             | `mixed` |             |

### Returns

| Name      | Type                      |
| --------- | ------------------------- |
| `results` | `types.ProblemListItem[]` |
| `total`   | `number`                  |

## `/api/problem/myList/`

### Description

Gets a list of problems where current user is the owner

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `offset`   | `mixed` |             |
| `page`     | `mixed` |             |
| `rowcount` | `mixed` |             |

### Returns

| Name         | Type                                               |
| ------------ | -------------------------------------------------- |
| `pagerItems` | `types.PageItem[]`                                 |
| `problems`   | `{ tags: { name: string; source: string; }[]; }[]` |

## `/api/problem/rejudge/`

### Description

Rejudge problem

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `problem_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/problem/removeAdmin/`

### Description

Removes an admin from a problem

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `problem_alias`   | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_

## `/api/problem/removeGroupAdmin/`

### Description

Removes a group admin from a problem

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `group`         | `mixed` |             |
| `problem_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/problem/removeTag/`

### Description

Removes a tag from a contest

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `name`          | `mixed` |             |
| `problem_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/problem/runs/`

### Description

Entry point for Problem runs API

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `language`      | `mixed` |             |
| `offset`        | `mixed` |             |
| `problem_alias` | `mixed` |             |
| `rowcount`      | `mixed` |             |
| `show_all`      | `mixed` |             |
| `status`        | `mixed` |             |
| `username`      | `mixed` |             |
| `verdict`       | `mixed` |             |

### Returns

| Name   | Type                                                                                                                                                                                                                                                                                                                                                     |
| ------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `runs` | `{ alias: string; classname?: string; contest_alias?: string; contest_score: number; country_id?: string; guid: string; judged_by?: string; language: string; memory: number; penalty: number; run_id?: number; runtime: number; score: number; status: string; submit_delay: number; time: Date; type?: string; username: string; verdict: string; }[]` |

## `/api/problem/runsDiff/`

### Description

Return a report of which runs would change due to a version change.

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `problem_alias` | `mixed` |             |
| `version`       | `mixed` |             |

### Returns

| Name   | Type                                                                                                                                                                                   |
| ------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `diff` | `{ username: string; guid: string; problemset_id: number; old_status: string; old_verdict: string; old_score: number; new_status: string; new_verdict: string; new_score: number; }[]` |

## `/api/problem/selectVersion/`

### Description

Change the version of the problem.

### Parameters

| Name               | Type    | Description |
| ------------------ | ------- | ----------- |
| `commit`           | `mixed` |             |
| `problem_alias`    | `mixed` |             |
| `update_published` | `mixed` |             |

### Returns

_Nothing_

## `/api/problem/solution/`

### Description

Returns the solution for a problem if conditions are satisfied.

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `contest_alias`   | `mixed` |             |
| `forfeit_problem` | `mixed` |             |
| `lang`            | `mixed` |             |
| `problem_alias`   | `mixed` |             |
| `problemset_id`   | `mixed` |             |
| `statement_type`  | `mixed` |             |

### Returns

| Name       | Type                                                                          |
| ---------- | ----------------------------------------------------------------------------- |
| `exists`   | `boolean`                                                                     |
| `solution` | `{ language: string; markdown: string; images: { [key: string]: string; }; }` |

## `/api/problem/stats/`

### Description

Stats of a problem

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `problem_alias` | `mixed` |             |

### Returns

| Name             | Type                         |
| ---------------- | ---------------------------- |
| `cases_stats`    | `{ [key: string]: number; }` |
| `pending_runs`   | `string[]`                   |
| `total_runs`     | `number`                     |
| `verdict_counts` | `{ [key: string]: number; }` |

## `/api/problem/tags/`

### Description

Returns every tag associated to a given problem.

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `include_voted` | `mixed` |             |
| `problem_alias` | `mixed` |             |

### Returns

| Name   | Type                                   |
| ------ | -------------------------------------- |
| `tags` | `{ name: string; public: boolean; }[]` |

## `/api/problem/update/`

### Description

Update problem contents

### Parameters

| Name                      | Type     | Description |
| ------------------------- | -------- | ----------- |
| `allow_user_add_tags`     | `bool`   |             |
| `email_clarifications`    | `mixed`  |             |
| `extra_wall_time`         | `mixed`  |             |
| `input_limit`             | `mixed`  |             |
| `languages`               | `mixed`  |             |
| `memory_limit`            | `mixed`  |             |
| `message`                 | `mixed`  |             |
| `output_limit`            | `mixed`  |             |
| `overall_wall_time_limit` | `mixed`  |             |
| `problem_alias`           | `mixed`  |             |
| `redirect`                | `mixed`  |             |
| `selected_tags`           | `mixed`  |             |
| `show_diff`               | `string` |             |
| `source`                  | `mixed`  |             |
| `time_limit`              | `mixed`  |             |
| `title`                   | `mixed`  |             |
| `update_published`        | `mixed`  |             |
| `validator`               | `mixed`  |             |
| `validator_time_limit`    | `mixed`  |             |
| `visibility`              | `mixed`  |             |

### Returns

| Name       | Type      |
| ---------- | --------- |
| `rejudged` | `boolean` |

## `/api/problem/updateSolution/`

### Description

Updates problem solution only

### Parameters

| Name                      | Type     | Description |
| ------------------------- | -------- | ----------- |
| `allow_user_add_tags`     | `bool`   |             |
| `email_clarifications`    | `mixed`  |             |
| `extra_wall_time`         | `mixed`  |             |
| `input_limit`             | `mixed`  |             |
| `lang`                    | `mixed`  |             |
| `languages`               | `mixed`  |             |
| `memory_limit`            | `mixed`  |             |
| `message`                 | `mixed`  |             |
| `output_limit`            | `mixed`  |             |
| `overall_wall_time_limit` | `mixed`  |             |
| `problem_alias`           | `mixed`  |             |
| `selected_tags`           | `mixed`  |             |
| `show_diff`               | `string` |             |
| `solution`                | `mixed`  |             |
| `source`                  | `mixed`  |             |
| `time_limit`              | `mixed`  |             |
| `title`                   | `mixed`  |             |
| `update_published`        | `mixed`  |             |
| `validator`               | `mixed`  |             |
| `validator_time_limit`    | `mixed`  |             |
| `visibility`              | `mixed`  |             |

### Returns

_Nothing_

## `/api/problem/updateStatement/`

### Description

Updates problem statement only

### Parameters

| Name                      | Type     | Description |
| ------------------------- | -------- | ----------- |
| `allow_user_add_tags`     | `bool`   |             |
| `email_clarifications`    | `mixed`  |             |
| `extra_wall_time`         | `mixed`  |             |
| `input_limit`             | `mixed`  |             |
| `lang`                    | `mixed`  |             |
| `languages`               | `mixed`  |             |
| `memory_limit`            | `mixed`  |             |
| `message`                 | `mixed`  |             |
| `output_limit`            | `mixed`  |             |
| `overall_wall_time_limit` | `mixed`  |             |
| `problem_alias`           | `mixed`  |             |
| `selected_tags`           | `mixed`  |             |
| `show_diff`               | `string` |             |
| `source`                  | `mixed`  |             |
| `statement`               | `mixed`  |             |
| `time_limit`              | `mixed`  |             |
| `title`                   | `mixed`  |             |
| `update_published`        | `mixed`  |             |
| `validator`               | `mixed`  |             |
| `validator_time_limit`    | `mixed`  |             |
| `visibility`              | `mixed`  |             |

### Returns

_Nothing_

## `/api/problem/versions/`

### Description

Entry point for Problem Versions API

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `problem_alias` | `mixed` |             |

### Returns

| Name        | Type                                                                                                                                                                                                                                                  |
| ----------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `published` | `string`                                                                                                                                                                                                                                              |
| `log`       | `{ commit: string; tree: { [key: string]: string; }; parents?: string[]; author: { name?: string; email?: string; time: number|string; }; committer: { name?: string; email?: string; time: number|string; }; message?: string; version: string; }[]` |

# ProblemForfeited

ProblemForfeitedController

## `/api/problemForfeited/getCounts/`

### Description

Returns the number of solutions allowed
and the number of solutions already seen

### Returns

| Name      | Type     |
| --------- | -------- |
| `allowed` | `number` |
| `seen`    | `number` |

# Problemset

## `/api/problemset/details/`

### Description

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `assignment`      | `mixed` |             |
| `auth_token`      | `mixed` |             |
| `contest_alias`   | `mixed` |             |
| `course`          | `mixed` |             |
| `interview_alias` | `mixed` |             |
| `problemset_id`   | `mixed` |             |
| `token`           | `mixed` |             |
| `tokens`          | `mixed` |             |
| `username`        | `mixed` |             |

### Returns

| Name                        | Type                                                                                                                     |
| --------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| `admin`                     | `boolean`                                                                                                                |
| `admission_mode`            | `string`                                                                                                                 |
| `alias`                     | `string`                                                                                                                 |
| `assignment_type`           | `string`                                                                                                                 |
| `contest_alias`             | `string`                                                                                                                 |
| `description`               | `string`                                                                                                                 |
| `director`                  | `string|dao.Identities`                                                                                                  |
| `exists`                    | `boolean`                                                                                                                |
| `feedback`                  | `string`                                                                                                                 |
| `finish_time`               | `number`                                                                                                                 |
| `languages`                 | `string[]`                                                                                                               |
| `name`                      | `string`                                                                                                                 |
| `needs_basic_information`   | `boolean`                                                                                                                |
| `opened`                    | `boolean`                                                                                                                |
| `original_contest_alias`    | `string`                                                                                                                 |
| `original_problemset_id`    | `number`                                                                                                                 |
| `partial_score`             | `boolean`                                                                                                                |
| `penalty`                   | `number`                                                                                                                 |
| `penalty_calc_policy`       | `string`                                                                                                                 |
| `penalty_type`              | `string`                                                                                                                 |
| `points_decay_factor`       | `number`                                                                                                                 |
| `problems`                  | `types.ProblemsetProblem[]`                                                                                              |
| `problemset_id`             | `number`                                                                                                                 |
| `requests_user_information` | `string`                                                                                                                 |
| `scoreboard`                | `number`                                                                                                                 |
| `show_scoreboard_after`     | `boolean`                                                                                                                |
| `start_time`                | `number`                                                                                                                 |
| `submission_deadline`       | `number`                                                                                                                 |
| `submissions_gap`           | `number`                                                                                                                 |
| `title`                     | `string`                                                                                                                 |
| `users`                     | `{ access_time: Date; country: string; email: string; opened_interview: boolean; user_id: number; username: string; }[]` |
| `window_length`             | `number`                                                                                                                 |

## `/api/problemset/scoreboard/`

### Description

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `assignment`    | `mixed` |             |
| `auth_token`    | `mixed` |             |
| `contest_alias` | `mixed` |             |
| `course`        | `mixed` |             |
| `problemset_id` | `mixed` |             |
| `token`         | `mixed` |             |
| `tokens`        | `mixed` |             |

### Returns

```typescript
types.Scoreboard;
```

## `/api/problemset/scoreboardEvents/`

### Description

Returns the Scoreboard events

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `assignment`    | `mixed` |             |
| `auth_token`    | `mixed` |             |
| `contest_alias` | `mixed` |             |
| `course`        | `mixed` |             |
| `problemset_id` | `mixed` |             |
| `token`         | `mixed` |             |
| `tokens`        | `mixed` |             |

### Returns

| Name     | Type                                                                                                                                                                                                    |
| -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `events` | `{ country: string; delta: number; is_invited: boolean; total: { points: number; penalty: number; }; name: string; username: string; problem: { alias: string; points: number; penalty: number; }; }[]` |

# QualityNomination

## `/api/qualityNomination/create/`

### Description

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

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contents`      | `mixed` |             |
| `nomination`    | `mixed` |             |
| `problem_alias` | `mixed` |             |

### Returns

| Name                   | Type     |
| ---------------------- | -------- |
| `qualitynomination_id` | `number` |

## `/api/qualityNomination/details/`

### Description

Displays the details of a nomination. The user needs to be either the
nominator or a member of the reviewer group.

### Parameters

| Name                   | Type    | Description |
| ---------------------- | ------- | ----------- |
| `qualitynomination_id` | `mixed` |             |

### Returns

| Name                   | Type                                                                                                                                                                           |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `author`               | `{ name: string; username: string; }`                                                                                                                                          |
| `contents`             | `{ before_ac?: boolean; difficulty?: number; quality?: number; rationale?: string; reason?: string; statements?: { [key: string]: string; }; tags?: string[]; }`               |
| `nomination`           | `string`                                                                                                                                                                       |
| `nomination_status`    | `string`                                                                                                                                                                       |
| `nominator`            | `{ name: string; username: string; }`                                                                                                                                          |
| `original_contents`    | `{ source: string; statements: { [key: string]: { language: string; markdown: string; images: { [key: string]: string; }; }; }; tags?: { source: string; name: string; }[]; }` |
| `problem`              | `{ alias: string; title: string; }`                                                                                                                                            |
| `qualitynomination_id` | `number`                                                                                                                                                                       |
| `reviewer`             | `boolean`                                                                                                                                                                      |
| `time`                 | `number`                                                                                                                                                                       |
| `votes`                | `{ time: number; user: { name: string; username: string; }; vote: number; }[]`                                                                                                 |

## `/api/qualityNomination/list/`

### Description

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `offset`   | `mixed` |             |
| `status`   | `mixed` |             |
| `rowcount` | `mixed` |             |

### Returns

| Name          | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `nominations` | `{ author: { name: string; username: string; }; contents?: { before_ac?: boolean; difficulty?: number; quality?: number; rationale?: string; reason?: string; statements?: { [key: string]: string; }; tags?: string[]; }; nomination: string; nominator: { name: string; username: string; }; problem: { alias: string; title: string; }; qualitynomination_id: number; status: string; time: number; votes: { time: number; user: { name: string; username: string; }; vote: number; }[]; }|null[]` |
| `pager_items` | `{ class: string; label: string; page: number; }[]`                                                                                                                                                                                                                                                                                                                                                                                                                                                   |

## `/api/qualityNomination/myAssignedList/`

### Description

Displays the nominations that this user has been assigned.

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `page`      | `mixed` |             |
| `page_size` | `mixed` |             |

### Returns

| Name          | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `nominations` | `{ author: { name: string; username: string; }; contents?: { before_ac?: boolean; difficulty?: number; quality?: number; rationale?: string; reason?: string; statements?: { [key: string]: string; }; tags?: string[]; }; nomination: string; nominator: { name: string; username: string; }; problem: { alias: string; title: string; }; qualitynomination_id: number; status: string; time: number; votes: { time: number; user: { name: string; username: string; }; vote: number; }[]; }|null[]` |

## `/api/qualityNomination/myList/`

### Description

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `offset`   | `mixed` |             |
| `rowcount` | `mixed` |             |

### Returns

| Name          | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `nominations` | `{ author: { name: string; username: string; }; contents?: { before_ac?: boolean; difficulty?: number; quality?: number; rationale?: string; reason?: string; statements?: { [key: string]: string; }; tags?: string[]; }; nomination: string; nominator: { name: string; username: string; }; problem: { alias: string; title: string; }; qualitynomination_id: number; status: string; time: number; votes: { time: number; user: { name: string; username: string; }; vote: number; }[]; }|null[]` |
| `pager_items` | `{ class: string; label: string; page: number; }[]`                                                                                                                                                                                                                                                                                                                                                                                                                                                   |

## `/api/qualityNomination/resolve/`

### Description

Marks a problem of a nomination (only the demotion type supported for now) as (resolved, banned, warning).

### Parameters

| Name                   | Type    | Description |
| ---------------------- | ------- | ----------- |
| `problem_alias`        | `mixed` |             |
| `qualitynomination_id` | `mixed` |             |
| `rationale`            | `mixed` |             |
| `status`               | `mixed` |             |

### Returns

_Nothing_

# Reset

## `/api/reset/create/`

### Description

Creates a reset operation, the first of two steps needed to reset a
password. The first step consist of sending an email to the user with
instructions to reset he's password, if and only if the email is valid.

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `email` | `mixed` |             |

### Returns

| Name      | Type     |
| --------- | -------- |
| `message` | `string` |
| `token`   | `string` |

## `/api/reset/generateToken/`

### Description

Creates a reset operation, support team members can generate a valid
token and then they can send it to end user

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `email` | `mixed` |             |

### Returns

| Name    | Type     |
| ------- | -------- |
| `link`  | `string` |
| `token` | `string` |

## `/api/reset/update/`

### Description

Updates the password of a given user, this is the second and last step
in order to reset the password. This operation is done if and only if
the correct parameters are suplied.

### Parameters

| Name                    | Type    | Description |
| ----------------------- | ------- | ----------- |
| `email`                 | `mixed` |             |
| `password`              | `mixed` |             |
| `password_confirmation` | `mixed` |             |
| `reset_token`           | `mixed` |             |

### Returns

| Name      | Type     |
| --------- | -------- |
| `message` | `string` |

# Run

RunController

## `/api/run/counts/`

### Description

Get total of last 6 months

### Returns

| Name    | Type                         |
| ------- | ---------------------------- |
| `total` | `{ [key: string]: number; }` |
| `ac`    | `{ [key: string]: number; }` |

## `/api/run/create/`

### Description

Create a new run

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `contest_alias` | `mixed` |             |
| `language`      | `mixed` |             |
| `problem_alias` | `mixed` |             |
| `problemset_id` | `mixed` |             |
| `source`        | `mixed` |             |

### Returns

| Name                      | Type     |
| ------------------------- | -------- |
| `guid`                    | `string` |
| `submission_deadline`     | `number` |
| `nextSubmissionTimestamp` | `number` |

## `/api/run/details/`

### Description

Gets the details of a run. Includes admin details if admin.

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `run_alias` | `mixed` |             |

### Returns

```typescript
types.RunDetails;
```

## `/api/run/disqualify/`

### Description

Disqualify a submission

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `run_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/run/list/`

### Description

Gets a list of latest runs overall

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `language`      | `mixed` |             |
| `offset`        | `mixed` |             |
| `problem_alias` | `mixed` |             |
| `rowcount`      | `mixed` |             |
| `status`        | `mixed` |             |
| `username`      | `mixed` |             |
| `verdict`       | `mixed` |             |

### Returns

| Name   | Type                                                                                                                                                                                                                                                                                                                               |
| ------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `runs` | `{ alias: string; classname: string; contest_alias: string; contest_score: number; country_id: string; guid: string; judged_by: string; language: string; memory: number; penalty: number; run_id: number; runtime: number; score: number; submit_delay: number; time: Date; type: string; username: string; verdict: string; }[]` |

## `/api/run/rejudge/`

### Description

Re-sends a problem to Grader.

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `debug`     | `mixed` |             |
| `run_alias` | `mixed` |             |

### Returns

_Nothing_

## `/api/run/source/`

### Description

Given the run alias, returns the source code and any compile errors if any
Used in the arena, any contestant can view its own codes and compile errors

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `run_alias` | `mixed` |             |

### Returns

| Name            | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `compile_error` | `string`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| `details`       | `{ compile_meta?: { [key: string]: { memory: number; sys_time: number; time: number; verdict: string; wall_time: number; }; }; contest_score: number; groups?: { cases: { contest_score: number; max_score: number; meta: { verdict: string; }; name: string; score: number; verdict: string; }[]; contest_score: number; group: string; max_score: number; score: number; }[]; judged_by: string; max_score?: number; memory?: number; score: number; time?: number; verdict: string; wall_time?: number; }` |
| `source`        | `string`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |

## `/api/run/status/`

### Description

Get basic details of a run

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `run_alias` | `mixed` |             |

### Returns

| Name            | Type     |
| --------------- | -------- |
| `contest_score` | `number` |
| `memory`        | `number` |
| `penalty`       | `number` |
| `runtime`       | `number` |
| `score`         | `number` |
| `submit_delay`  | `number` |
| `time`          | `Date`   |

# School

SchoolController

## `/api/school/create/`

### Description

Api to create new school

### Parameters

| Name         | Type    | Description |
| ------------ | ------- | ----------- |
| `country_id` | `mixed` |             |
| `name`       | `mixed` |             |
| `state_id`   | `mixed` |             |

### Returns

| Name        | Type     |
| ----------- | -------- |
| `school_id` | `number` |

## `/api/school/list/`

### Description

Gets a list of schools

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `query` | `mixed` |             |
| `term`  | `mixed` |             |

### Returns

```typescript
{
  id: number;
  label: string;
  value: string;
}
[];
```

## `/api/school/monthlySolvedProblemsCount/`

### Description

Returns the number of solved problems on the last
months (including the current one)

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `school_id` | `mixed` |             |

### Returns

| Name                       | Type                                                          |
| -------------------------- | ------------------------------------------------------------- |
| `distinct_problems_solved` | `{ month: number; problems_solved: number; year: number; }[]` |

## `/api/school/schoolCodersOfTheMonth/`

### Description

Returns rank of best schools in last month

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `school_id` | `mixed` |             |

### Returns

| Name     | Type                                                       |
| -------- | ---------------------------------------------------------- |
| `coders` | `{ time: string; username: string; classname: string; }[]` |

## `/api/school/selectSchoolOfTheMonth/`

### Description

Selects a certain school as school of the month

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `school_id` | `mixed` |             |

### Returns

_Nothing_

## `/api/school/users/`

### Description

Returns the list of current students registered in a certain school
with the number of created problems, solved problems and organized contests.

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `school_id` | `mixed` |             |

### Returns

| Name    | Type                                                                                                                        |
| ------- | --------------------------------------------------------------------------------------------------------------------------- |
| `users` | `{ username: string; classname: string; created_problems: number; solved_problems: number; organized_contests: number; }[]` |

# Scoreboard

ScoreboardController

## `/api/scoreboard/refresh/`

### Description

Returns a list of contests

### Parameters

| Name           | Type    | Description |
| -------------- | ------- | ----------- |
| `alias`        | `mixed` |             |
| `course_alias` | `mixed` |             |
| `token`        | `mixed` |             |

### Returns

_Nothing_

# Session

Session controller handles sessions.

## `/api/session/currentSession/`

### Description

Returns information about current session. In order to avoid one full
server roundtrip (about ~100msec on each pageload), it also returns the
current time to be able to calculate the time delta between the
contestant's machine and the server.

### Returns

| Name      | Type                                                                                                                   |
| --------- | ---------------------------------------------------------------------------------------------------------------------- |
| `session` | `{ valid: boolean; email: string; user: dao.Users; identity: dao.Identities; auth_token: string; is_admin: boolean; }` |
| `time`    | `number`                                                                                                               |

## `/api/session/googleLogin/`

### Description

### Parameters

| Name         | Type     | Description |
| ------------ | -------- | ----------- |
| `storeToken` | `string` |             |

### Returns

```typescript
{ [key: string]: string; }
```

# Submission

SubmissionController

## `/api/submission/latestSubmissions/`

### Description

Returns the latest submissions

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `offset`   | `mixed` |             |
| `rowcount` | `mixed` |             |
| `username` | `mixed` |             |

### Returns

| Name          | Type                                                                                                                                                                              |
| ------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `submissions` | `{ time: number; username: string; school_id: number; school_name: string; alias: string; title: string; language: string; verdict: string; runtime: number; memory: number; }[]` |
| `totalRows`   | `number`                                                                                                                                                                          |

# Tag

TagController

## `/api/tag/list/`

### Description

Gets a list of tags

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `query` | `mixed` |             |
| `term`  | `mixed` |             |

### Returns

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

### Description

Entry point for /time API

### Returns

| Name   | Type     |
| ------ | -------- |
| `time` | `number` |

# User

UserController

## `/api/user/acceptPrivacyPolicy/`

### Description

Keeps a record of a user who accepts the privacy policy

### Parameters

| Name                    | Type    | Description |
| ----------------------- | ------- | ----------- |
| `privacy_git_object_id` | `mixed` |             |
| `statement_type`        | `mixed` |             |
| `username`              | `mixed` |             |

### Returns

_Nothing_

## `/api/user/addExperiment/`

### Description

Adds the experiment to the user.

### Parameters

| Name         | Type    | Description |
| ------------ | ------- | ----------- |
| `experiment` | `mixed` |             |

### Returns

_Nothing_

## `/api/user/addGroup/`

### Description

Adds the identity to the group.

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `group` | `mixed` |             |

### Returns

_Nothing_

## `/api/user/addRole/`

### Description

Adds the role to the user.

### Parameters

| Name   | Type    | Description |
| ------ | ------- | ----------- |
| `role` | `mixed` |             |

### Returns

_Nothing_

## `/api/user/associateIdentity/`

### Description

Associates an identity to the logged user given the username

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `password` | `mixed` |             |
| `username` | `mixed` |             |

### Returns

_Nothing_

## `/api/user/changePassword/`

### Description

Changes the password of a user

### Parameters

| Name             | Type    | Description |
| ---------------- | ------- | ----------- |
| `old_password`   | `mixed` |             |
| `password`       | `mixed` |             |
| `permission_key` | `mixed` |             |
| `username`       | `mixed` |             |

### Returns

_Nothing_

## `/api/user/coderOfTheMonth/`

### Description

Get coder of the month by trying to find it in the table using the first
day of the current month. If there's no coder of the month for the given
date, calculate it and save it.

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `category` | `mixed` |             |
| `date`     | `mixed` |             |

### Returns

| Name        | Type                                                                                                                                                                                                                                                                                                                                                                                    |
| ----------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `coderinfo` | `{ birth_date: number; country: string; country_id: string; email: string; gender: string; graduation_date: number; gravatar_92: string; hide_problem_tags: boolean; is_private: boolean; locale: string; name: string; preferred_language: string; scholar_degree: string; school: string; school_id: number; state: string; state_id: string; username: string; verified: boolean; }` |

## `/api/user/coderOfTheMonthList/`

### Description

Returns the list of coders of the month

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `category` | `mixed` |             |
| `date`     | `mixed` |             |

### Returns

| Name     | Type                                                                                                |
| -------- | --------------------------------------------------------------------------------------------------- |
| `coders` | `{ username: string; country_id: string; gravatar_32: string; date: string; classname: string; }[]` |

## `/api/user/contestStats/`

### Description

Get Contests which a certain user has participated in

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `auth_token`    | `mixed` |             |
| `contest_alias` | `mixed` |             |
| `token`         | `mixed` |             |
| `username`      | `mixed` |             |

### Returns

| Name       | Type                                                                                                                                       |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| `contests` | `{ [key: string]: { data: { alias: string; title: string; start_time: Date; finish_time: Date; last_updated: Date; }; place: number; }; }` |

## `/api/user/create/`

### Description

Entry point for Create a User API

### Returns

| Name       | Type     |
| ---------- | -------- |
| `username` | `string` |

## `/api/user/extraInformation/`

### Description

Gets extra information of the identity:

- last password change request
- verify status

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `email` | `mixed` |             |

### Returns

| Name              | Type      |
| ----------------- | --------- |
| `within_last_day` | `boolean` |
| `verified`        | `boolean` |
| `username`        | `string`  |
| `last_login`      | `number`  |

## `/api/user/generateGitToken/`

### Description

Generate a new gitserver token. This token can be used to authenticate
against the gitserver.

### Returns

| Name    | Type     |
| ------- | -------- |
| `token` | `string` |

## `/api/user/generateOmiUsers/`

### Description

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `auth_token`      | `mixed` |             |
| `change_password` | `mixed` |             |
| `contest_alias`   | `mixed` |             |
| `contest_type`    | `mixed` |             |
| `id`              | `mixed` |             |
| `old_password`    | `mixed` |             |
| `password`        | `mixed` |             |
| `permission_key`  | `mixed` |             |
| `username`        | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

```typescript
{ [key: string]: string; }
```

## `/api/user/interviewStats/`

### Description

Get the results for this user in a given interview

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `interview` | `mixed` |             |
| `username`  | `mixed` |             |

### Returns

| Name               | Type      |
| ------------------ | --------- |
| `user_verified`    | `boolean` |
| `interview_url`    | `string`  |
| `name_or_username` | `string`  |
| `opened_interview` | `boolean` |
| `finished`         | `boolean` |

## `/api/user/lastPrivacyPolicyAccepted/`

### Description

Gets the last privacy policy accepted by user

### Returns

| Name          | Type      |
| ------------- | --------- |
| `hasAccepted` | `boolean` |

## `/api/user/list/`

### Description

Gets a list of users. This returns an array instead of an object since
it is used by typeahead.

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `query` | `mixed` |             |
| `term`  | `mixed` |             |

### Returns

```typescript
types.UserListItem[]
```

## `/api/user/listAssociatedIdentities/`

### Description

Get the identities that have been associated to the logged user

### Returns

| Name         | Type                                        |
| ------------ | ------------------------------------------- |
| `identities` | `{ username: string; default: boolean; }[]` |

## `/api/user/listUnsolvedProblems/`

### Description

Get Problems unsolved by user

### Returns

| Name       | Type              |
| ---------- | ----------------- |
| `problems` | `types.Problem[]` |

## `/api/user/login/`

### Description

Exposes API /user/login
Expects in request:
user
password

### Returns

| Name         | Type     |
| ------------ | -------- |
| `auth_token` | `string` |

## `/api/user/mailingListBackfill/`

### Description

Registers to the mailing list all users that have not been added before. Admin only

### Returns

| Name    | Type                          |
| ------- | ----------------------------- |
| `users` | `{ [key: string]: boolean; }` |

## `/api/user/problemsCreated/`

### Description

Get Problems created by user

### Returns

| Name       | Type              |
| ---------- | ----------------- |
| `problems` | `types.Problem[]` |

## `/api/user/problemsSolved/`

### Description

Get Problems solved by user

### Returns

| Name       | Type              |
| ---------- | ----------------- |
| `problems` | `types.Problem[]` |

## `/api/user/profile/`

### Description

Get general user info

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `category`  | `mixed` |             |
| `omit_rank` | `mixed` |             |
| `username`  | `mixed` |             |

### Returns

| Name                 | Type                                                          |
| -------------------- | ------------------------------------------------------------- |
| `birth_date`         | `number`                                                      |
| `classname`          | `string`                                                      |
| `country`            | `string`                                                      |
| `country_id`         | `string`                                                      |
| `email`              | `string`                                                      |
| `gender`             | `string`                                                      |
| `graduation_date`    | `number`                                                      |
| `gravatar_92`        | `string`                                                      |
| `hide_problem_tags`  | `boolean`                                                     |
| `is_private`         | `boolean`                                                     |
| `locale`             | `string`                                                      |
| `name`               | `string`                                                      |
| `preferred_language` | `string`                                                      |
| `rankinfo`           | `{ name?: string; problems_solved?: number; rank?: number; }` |
| `scholar_degree`     | `string`                                                      |
| `school`             | `string`                                                      |
| `school_id`          | `number`                                                      |
| `state`              | `string`                                                      |
| `state_id`           | `string`                                                      |
| `username`           | `string`                                                      |
| `verified`           | `boolean`                                                     |

## `/api/user/rankByProblemsSolved/`

### Description

If no username provided: Gets the top N users who have solved more problems
If username provided: Gets rank for username provided

### Parameters

| Name         | Type          | Description |
| ------------ | ------------- | ----------- |
| `auth_token` | `null|string` |             |
| `filter`     | `mixed`       |             |
| `offset`     | `mixed`       |             |
| `rowcount`   | `mixed`       |             |
| `username`   | `mixed`       |             |

### Returns

| Name              | Type                                                                                                                                                            |
| ----------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `rank`            | `{ classname: string; country_id: string; name: string; problems_solved: number; ranking: number; score: number; user_id: number; username: string; }[]|number` |
| `total`           | `number`                                                                                                                                                        |
| `name`            | `string`                                                                                                                                                        |
| `problems_solved` | `number`                                                                                                                                                        |

## `/api/user/removeExperiment/`

### Description

Removes the experiment from the user.

### Parameters

| Name         | Type    | Description |
| ------------ | ------- | ----------- |
| `experiment` | `mixed` |             |

### Returns

_Nothing_

## `/api/user/removeGroup/`

### Description

Removes the user to the group.

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `group` | `mixed` |             |

### Returns

_Nothing_

## `/api/user/removeRole/`

### Description

Removes the role from the user.

### Parameters

| Name   | Type    | Description |
| ------ | ------- | ----------- |
| `role` | `mixed` |             |

### Returns

_Nothing_

## `/api/user/selectCoderOfTheMonth/`

### Description

Selects coder of the month for next month.

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `category` | `mixed` |             |
| `username` | `mixed` |             |

### Returns

_Nothing_

## `/api/user/stats/`

### Description

Get stats

### Returns

| Name   | Type                                                 |
| ------ | ---------------------------------------------------- |
| `runs` | `{ date: string; runs: number; verdict: string; }[]` |

## `/api/user/statusVerified/`

### Description

Gets verify status of a user

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `email` | `mixed` |             |

### Returns

| Name       | Type      |
| ---------- | --------- |
| `username` | `string`  |
| `verified` | `boolean` |

## `/api/user/update/`

### Description

Update user profile

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `auth_token`      | `mixed` |             |
| `birth_date`      | `mixed` |             |
| `country_id`      | `mixed` |             |
| `gender`          | `mixed` |             |
| `graduation_date` | `mixed` |             |
| `locale`          | `mixed` |             |
| `name`            | `mixed` |             |
| `scholar_degree`  | `mixed` |             |
| `school_id`       | `mixed` |             |
| `school_name`     | `mixed` |             |
| `state_id`        | `mixed` |             |
| `username`        | `mixed` |             |

### Returns

_Nothing_

## `/api/user/updateBasicInfo/`

### Description

Update basic user profile info when logged with fb/gool

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `password` | `mixed` |             |
| `username` | `mixed` |             |

### Returns

_Nothing_

## `/api/user/updateMainEmail/`

### Description

Updates the main email of the current user

### Parameters

| Name    | Type    | Description |
| ------- | ------- | ----------- |
| `email` | `mixed` |             |

### Returns

_Nothing_

## `/api/user/validateFilter/`

### Description

Parses and validates a filter string to be used for event notification
filtering.

The Request must have a 'filter' key with comma-delimited URI paths
representing the resources the caller is interested in receiving events
for. If the caller has enough privileges to receive notifications for
ALL the requested filters, the request will return successfully,
otherwise an exception will be thrown.

This API does not need authentication to be used. This allows to track
contest updates with an access token.

### Parameters

| Name            | Type    | Description |
| --------------- | ------- | ----------- |
| `auth_token`    | `mixed` |             |
| `contest_admin` | `mixed` |             |
| `contest_alias` | `mixed` |             |
| `filter`        | `mixed` |             |
| `problemset_id` | `mixed` |             |
| `token`         | `mixed` |             |
| `tokens`        | `mixed` |             |

### Returns

| Name               | Type       |
| ------------------ | ---------- |
| `user`             | `string`   |
| `admin`            | `boolean`  |
| `problem_admin`    | `string[]` |
| `contest_admin`    | `string[]` |
| `problemset_admin` | `number[]` |

## `/api/user/verifyEmail/`

### Description

Verifies the user given its verification id

### Parameters

| Name              | Type    | Description |
| ----------------- | ------- | ----------- |
| `id`              | `mixed` |             |
| `usernameOrEmail` | `mixed` |             |

### Returns

_Nothing_
