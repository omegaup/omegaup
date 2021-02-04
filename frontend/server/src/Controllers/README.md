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
  - [`/api/contest/searchUsers/`](#apicontestsearchusers)
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
  - [`/api/course/generateTokenForCloneCourse/`](#apicoursegeneratetokenforclonecourse)
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
  - [`/api/course/removeAssignment/`](#apicourseremoveassignment)
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
  - [`/api/group/update/`](#apigroupupdate)
- [GroupScoreboard](#groupscoreboard)
  - [`/api/groupScoreboard/addContest/`](#apigroupscoreboardaddcontest)
  - [`/api/groupScoreboard/details/`](#apigroupscoreboarddetails)
  - [`/api/groupScoreboard/list/`](#apigroupscoreboardlist)
  - [`/api/groupScoreboard/removeContest/`](#apigroupscoreboardremovecontest)
- [Identity](#identity)
  - [`/api/identity/bulkCreate/`](#apiidentitybulkcreate)
  - [`/api/identity/changePassword/`](#apiidentitychangepassword)
  - [`/api/identity/create/`](#apiidentitycreate)
  - [`/api/identity/selectIdentity/`](#apiidentityselectidentity)
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
  - [`/api/problem/randomKarelProblem/`](#apiproblemrandomkarelproblem)
  - [`/api/problem/randomLanguageProblem/`](#apiproblemrandomlanguageproblem)
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
  - [`/api/problem/updateProblemLevel/`](#apiproblemupdateproblemlevel)
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
  - [`/api/school/selectSchoolOfTheMonth/`](#apischoolselectschoolofthemonth)
- [Scoreboard](#scoreboard)
  - [`/api/scoreboard/refresh/`](#apiscoreboardrefresh)
- [Session](#session)
  - [`/api/session/currentSession/`](#apisessioncurrentsession)
  - [`/api/session/googleLogin/`](#apisessiongooglelogin)
- [Tag](#tag)
  - [`/api/tag/frequentTags/`](#apitagfrequenttags)
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

| Name         | Type        | Description |
| ------------ | ----------- | ----------- |
| `end_time`   | `int\|null` |             |
| `start_time` | `int\|null` |             |

### Returns

| Name     | Type                                                                                                                                                                                                     |
| -------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `report` | `{ acceptedSubmissions: number; activeSchools: number; activeUsers: { [key: string]: number; }; courses: number; omiCourse: { attemptedUsers: number; completedUsers: number; passedUsers: number; }; }` |

# Authorization

AuthorizationController

## `/api/authorization/problem/`

### Description

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `problem_alias` | `string` |             |
| `token`         | `string` |             |
| `username`      | `mixed`  |             |

### Returns

| Name         | Type      |
| ------------ | --------- |
| `can_edit`   | `boolean` |
| `can_view`   | `boolean` |
| `has_solved` | `boolean` |
| `is_admin`   | `boolean` |

# Badge

BadgesController

## `/api/badge/badgeDetails/`

### Description

Returns the number of owners and the first
assignation timestamp for a certain badge

### Parameters

| Name          | Type           | Description |
| ------------- | -------------- | ----------- |
| `badge_alias` | `null\|string` |             |

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

| Name          | Type           | Description |
| ------------- | -------------- | ----------- |
| `badge_alias` | `null\|string` |             |

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

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `problem_alias` | `string`       |             |
| `message`       | `null\|string` |             |
| `username`      | `null\|string` |             |

### Returns

| Name               | Type     |
| ------------------ | -------- |
| `clarification_id` | `number` |

## `/api/clarification/details/`

### Description

API for getting a clarification

### Parameters

| Name               | Type  | Description |
| ------------------ | ----- | ----------- |
| `clarification_id` | `int` |             |

### Returns

| Name            | Type     |
| --------------- | -------- |
| `answer`        | `string` |
| `message`       | `string` |
| `problem_id`    | `number` |
| `problemset_id` | `number` |
| `time`          | `number` |

## `/api/clarification/update/`

### Description

Update a clarification

### Parameters

| Name               | Type           | Description |
| ------------------ | -------------- | ----------- |
| `clarification_id` | `int`          |             |
| `answer`           | `null\|string` |             |
| `message`          | `null\|string` |             |
| `public`           | `bool\|null`   |             |

### Returns

_Nothing_

# Contest

ContestController

## `/api/contest/activityReport/`

### Description

Returns a report with all user activity for a contest.

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `token`         | `null\|string` |             |

### Returns

| Name     | Type                                                                                  |
| -------- | ------------------------------------------------------------------------------------- |
| `events` | `{ alias?: string; classname?: string; ip: number; time: Date; username: string; }[]` |

## `/api/contest/addAdmin/`

### Description

Adds an admin to a contest

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `contest_alias`   | `string` |             |
| `usernameOrEmail` | `string` |             |

### Returns

_Nothing_

## `/api/contest/addGroup/`

### Description

Adds an group to a contest

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |
| `group`         | `string` |             |

### Returns

_Nothing_

## `/api/contest/addGroupAdmin/`

### Description

Adds an group admin to a contest

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |
| `group`         | `string` |             |

### Returns

_Nothing_

## `/api/contest/addProblem/`

### Description

Adds a problem to a contest

### Parameters

| Name               | Type           | Description |
| ------------------ | -------------- | ----------- |
| `contest_alias`    | `string`       |             |
| `order_in_contest` | `int`          |             |
| `points`           | `float`        |             |
| `problem_alias`    | `string`       |             |
| `commit`           | `null\|string` |             |

### Returns

_Nothing_

## `/api/contest/addUser/`

### Description

Adds a user to a contest.
By default, any user can view details of public contests.
Only users added through this API can view private contests

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `contest_alias`   | `string` |             |
| `usernameOrEmail` | `string` |             |

### Returns

_Nothing_

## `/api/contest/adminDetails/`

### Description

Returns details of a Contest, for administrators. This differs from
apiDetails in the sense that it does not attempt to calculate the
remaining time from the contest, or register the opened time.

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `token`         | `null\|string` |             |

### Returns

```typescript
types.ContestAdminDetails;
```

## `/api/contest/adminList/`

### Description

Returns a list of contests where current user has admin rights (or is
the director).

### Parameters

| Name        | Type  | Description |
| ----------- | ----- | ----------- |
| `page`      | `int` |             |
| `page_size` | `int` |             |

### Returns

| Name       | Type              |
| ---------- | ----------------- |
| `contests` | `types.Contest[]` |

## `/api/contest/admins/`

### Description

Returns all contest administrators

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |

### Returns

| Name           | Type                                               |
| -------------- | -------------------------------------------------- |
| `admins`       | `{ role: string; username: string; }[]`            |
| `group_admins` | `{ alias: string; name: string; role: string; }[]` |

## `/api/contest/arbitrateRequest/`

### Description

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `username`      | `string`       |             |
| `note`          | `null\|string` |             |
| `resolution`    | `mixed`        |             |

### Returns

_Nothing_

## `/api/contest/clarifications/`

### Description

Get clarifications of a contest

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |
| `offset`        | `int`    |             |
| `rowcount`      | `int`    |             |

### Returns

| Name             | Type                    |
| ---------------- | ----------------------- |
| `clarifications` | `types.Clarification[]` |

## `/api/contest/clone/`

### Description

Clone a contest

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `description`   | `string`       |             |
| `start_time`    | `int`          |             |
| `title`         | `string`       |             |
| `alias`         | `null\|string` |             |
| `auth_token`    | `null\|string` |             |

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

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |

### Returns

| Name          | Type                                                                                                   |
| ------------- | ------------------------------------------------------------------------------------------------------ |
| `contestants` | `{ country: string; email: string; name: string; school: string; state: string; username: string; }[]` |

## `/api/contest/create/`

### Description

Creates a new contest

### Parameters

| Name                        | Type           | Description |
| --------------------------- | -------------- | ----------- |
| `admission_mode`            | `mixed`        |             |
| `alias`                     | `mixed`        |             |
| `description`               | `mixed`        |             |
| `feedback`                  | `mixed`        |             |
| `finish_time`               | `mixed`        |             |
| `languages`                 | `mixed`        |             |
| `needs_basic_information`   | `bool\|null`   |             |
| `partial_score`             | `bool\|null`   |             |
| `penalty`                   | `mixed`        |             |
| `penalty_calc_policy`       | `mixed`        |             |
| `penalty_type`              | `mixed`        |             |
| `points_decay_factor`       | `mixed`        |             |
| `problems`                  | `null\|string` |             |
| `requests_user_information` | `mixed`        |             |
| `scoreboard`                | `mixed`        |             |
| `show_scoreboard_after`     | `mixed`        |             |
| `start_time`                | `mixed`        |             |
| `submissions_gap`           | `mixed`        |             |
| `title`                     | `mixed`        |             |
| `window_length`             | `int\|null`    |             |

### Returns

_Nothing_

## `/api/contest/createVirtual/`

### Description

### Parameters

| Name         | Type     | Description |
| ------------ | -------- | ----------- |
| `alias`      | `string` |             |
| `start_time` | `int`    |             |

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

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `token`         | `null\|string` |             |

### Returns

```typescript
types.ContestDetails;
```

## `/api/contest/list/`

### Description

Returns a list of contests

### Parameters

| Name             | Type        | Description |
| ---------------- | ----------- | ----------- |
| `page`           | `int`       |             |
| `page_size`      | `int`       |             |
| `query`          | `string`    |             |
| `active`         | `int\|null` |             |
| `admission_mode` | `mixed`     |             |
| `participating`  | `int\|null` |             |
| `recommended`    | `int\|null` |             |

### Returns

| Name                | Type                                                                                                                                                                                                                                                                              |
| ------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `number_of_results` | `number`                                                                                                                                                                                                                                                                          |
| `results`           | `{ admission_mode: string; alias: string; contest_id: number; description: string; finish_time: Date; last_updated: Date; original_finish_time: Date; problemset_id: number; recommended: boolean; rerun_id: number; start_time: Date; title: string; window_length: number; }[]` |

## `/api/contest/listParticipating/`

### Description

Returns a list of contests where current user is participating in

### Parameters

| Name        | Type     | Description |
| ----------- | -------- | ----------- |
| `page`      | `int`    |             |
| `page_size` | `int`    |             |
| `query`     | `string` |             |

### Returns

| Name       | Type              |
| ---------- | ----------------- |
| `contests` | `types.Contest[]` |

## `/api/contest/myList/`

### Description

Returns a list of contests where current user is the director

### Parameters

| Name        | Type     | Description |
| ----------- | -------- | ----------- |
| `page`      | `int`    |             |
| `page_size` | `int`    |             |
| `query`     | `string` |             |

### Returns

| Name       | Type              |
| ---------- | ----------------- |
| `contests` | `types.Contest[]` |

## `/api/contest/open/`

### Description

Joins a contest - explicitly adds a identity to a contest.

### Parameters

| Name                     | Type           | Description |
| ------------------------ | -------------- | ----------- |
| `contest_alias`          | `string`       |             |
| `privacy_git_object_id`  | `string`       |             |
| `statement_type`         | `string`       |             |
| `share_user_information` | `bool\|null`   |             |
| `token`                  | `null\|string` |             |

### Returns

_Nothing_

## `/api/contest/problems/`

### Description

Gets the problems from a contest

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |

### Returns

| Name       | Type                     |
| ---------- | ------------------------ |
| `problems` | `types.ContestProblem[]` |

## `/api/contest/publicDetails/`

### Description

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |

### Returns

```typescript
types.ContestPublicDetails;
```

## `/api/contest/registerForContest/`

### Description

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |

### Returns

_Nothing_

## `/api/contest/removeAdmin/`

### Description

Removes an admin from a contest

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `contest_alias`   | `string` |             |
| `usernameOrEmail` | `string` |             |

### Returns

_Nothing_

## `/api/contest/removeGroup/`

### Description

Removes a group from a contest

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |
| `group`         | `string` |             |

### Returns

_Nothing_

## `/api/contest/removeGroupAdmin/`

### Description

Removes a group admin from a contest

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |
| `group`         | `string` |             |

### Returns

_Nothing_

## `/api/contest/removeProblem/`

### Description

Removes a problem from a contest

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |
| `problem_alias` | `string` |             |

### Returns

_Nothing_

## `/api/contest/removeUser/`

### Description

Remove a user from a private contest

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `contest_alias`   | `string` |             |
| `usernameOrEmail` | `string` |             |

### Returns

_Nothing_

## `/api/contest/report/`

### Description

Returns a detailed report of the contest

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `auth_token`    | `null\|string` |             |
| `filterBy`      | `null\|string` |             |

### Returns

| Name          | Type                                                                                                                                                                                                                                                                                                                                                                          |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `finish_time` | `Date`                                                                                                                                                                                                                                                                                                                                                                        |
| `problems`    | `{ alias: string; order: number; }[]`                                                                                                                                                                                                                                                                                                                                         |
| `ranking`     | `{ country: string; is_invited: boolean; name: string; place?: number; problems: { alias: string; penalty: number; percent: number; place?: number; points: number; run_details?: { cases?: types.CaseResult[]; details: { groups: { cases: { meta: types.RunMetadata; }[]; }[]; }; }; runs: number; }[]; total: { penalty: number; points: number; }; username: string; }[]` |
| `start_time`  | `Date`                                                                                                                                                                                                                                                                                                                                                                        |
| `time`        | `Date`                                                                                                                                                                                                                                                                                                                                                                        |
| `title`       | `string`                                                                                                                                                                                                                                                                                                                                                                      |

## `/api/contest/requests/`

### Description

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |

### Returns

| Name            | Type                                                                                                                                 |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| `contest_alias` | `string`                                                                                                                             |
| `users`         | `{ accepted: boolean; admin?: { username?: string; }; country: string; last_update: Date; request_time: Date; username: string; }[]` |

## `/api/contest/role/`

### Description

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `token`         | `null\|string` |             |

### Returns

| Name    | Type      |
| ------- | --------- |
| `admin` | `boolean` |

## `/api/contest/runs/`

### Description

Returns all runs for a contest

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `problem_alias` | `string`       |             |
| `language`      | `mixed`        |             |
| `offset`        | `int\|null`    |             |
| `rowcount`      | `int\|null`    |             |
| `status`        | `mixed`        |             |
| `username`      | `null\|string` |             |
| `verdict`       | `mixed`        |             |

### Returns

| Name   | Type          |
| ------ | ------------- |
| `runs` | `types.Run[]` |

## `/api/contest/runsDiff/`

### Description

Return a report of which runs would change due to a version change.

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `version`       | `string`       |             |
| `problem_alias` | `null\|string` |             |

### Returns

| Name   | Type                                                                                                                                                                                   |
| ------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `diff` | `{ guid: string; new_score: number; new_status: string; new_verdict: string; old_score: number; old_status: string; old_verdict: string; problemset_id: number; username: string; }[]` |

## `/api/contest/scoreboard/`

### Description

Returns the Scoreboard

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `token`         | `null\|string` |             |

### Returns

```typescript
types.Scoreboard;
```

## `/api/contest/scoreboardEvents/`

### Description

Returns the Scoreboard events

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `token`         | `null\|string` |             |

### Returns

| Name     | Type                      |
| -------- | ------------------------- |
| `events` | `types.ScoreboardEvent[]` |

## `/api/contest/scoreboardMerge/`

### Description

Gets the accomulative scoreboard for an array of contests

### Parameters

| Name               | Type           | Description |
| ------------------ | -------------- | ----------- |
| `contest_aliases`  | `string`       |             |
| `contest_params`   | `mixed`        |             |
| `usernames_filter` | `null\|string` |             |

### Returns

| Name      | Type                                                                                                                                                     |
| --------- | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `ranking` | `{ contests: { [key: string]: { penalty: number; points: number; }; }; name: string; total: { penalty: number; points: number; }; username: string; }[]` |

## `/api/contest/searchUsers/`

### Description

Search users in contest

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |
| `query`         | `mixed`  |             |

### Returns

```typescript
{
  label: string;
  value: string;
}
[];
```

## `/api/contest/setRecommended/`

### Description

Given a contest_alias, sets the recommended flag on/off.
Only omegaUp admins can call this API.

### Parameters

| Name            | Type         | Description |
| --------------- | ------------ | ----------- |
| `contest_alias` | `string`     |             |
| `value`         | `bool\|null` |             |

### Returns

_Nothing_

## `/api/contest/stats/`

### Description

Stats of a contest

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `null\|string` |             |

### Returns

| Name                 | Type                         |
| -------------------- | ---------------------------- |
| `distribution`       | `{ [key: number]: number; }` |
| `max_wait_time`      | `Date`                       |
| `max_wait_time_guid` | `string`                     |
| `pending_runs`       | `string[]`                   |
| `size_of_bucket`     | `number`                     |
| `total_points`       | `number`                     |
| `total_runs`         | `number`                     |
| `verdict_counts`     | `{ [key: string]: number; }` |

## `/api/contest/update/`

### Description

Update a Contest

### Parameters

| Name                        | Type                      | Description |
| --------------------------- | ------------------------- | ----------- |
| `contest_alias`             | `string`                  |             |
| `finish_time`               | `int`                     |             |
| `submissions_gap`           | `int`                     |             |
| `window_length`             | `int`                     |             |
| `admission_mode`            | `mixed`                   |             |
| `alias`                     | `null\|string`            |             |
| `description`               | `null\|string`            |             |
| `feedback`                  | `mixed`                   |             |
| `languages`                 | `mixed`                   |             |
| `needs_basic_information`   | `bool\|null`              |             |
| `partial_score`             | `bool\|null`              |             |
| `penalty`                   | `int\|null`               |             |
| `penalty_calc_policy`       | `mixed`                   |             |
| `penalty_type`              | `mixed`                   |             |
| `points_decay_factor`       | `float\|null`             |             |
| `problems`                  | `null\|string`            |             |
| `requests_user_information` | `mixed`                   |             |
| `scoreboard`                | `float\|null`             |             |
| `show_scoreboard_after`     | `bool\|null`              |             |
| `start_time`                | `OmegaUp\Timestamp\|null` |             |
| `title`                     | `null\|string`            |             |

### Returns

_Nothing_

## `/api/contest/updateEndTimeForIdentity/`

### Description

Update Contest end time for an identity when window_length
option is turned on

### Parameters

| Name            | Type                | Description |
| --------------- | ------------------- | ----------- |
| `contest_alias` | `string`            |             |
| `end_time`      | `OmegaUp\Timestamp` |             |
| `username`      | `string`            |             |

### Returns

_Nothing_

## `/api/contest/users/`

### Description

Returns ALL identities participating in a contest

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |

### Returns

| Name     | Type                                 |
| -------- | ------------------------------------ |
| `groups` | `{ alias: string; name: string; }[]` |
| `users`  | `types.ContestUser[]`                |

# Course

CourseController

## `/api/course/activityReport/`

### Description

Returns a report with all user activity for a course.

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |

### Returns

| Name     | Type                                                                                  |
| -------- | ------------------------------------------------------------------------------------- |
| `events` | `{ alias?: string; classname?: string; ip: number; time: Date; username: string; }[]` |

## `/api/course/addAdmin/`

### Description

Adds an admin to a course

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `course_alias`    | `string` |             |
| `usernameOrEmail` | `string` |             |

### Returns

_Nothing_

## `/api/course/addGroupAdmin/`

### Description

Adds an group admin to a course

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |
| `group`        | `string` |             |

### Returns

_Nothing_

## `/api/course/addProblem/`

### Description

Adds a problem to an assignment

### Parameters

| Name               | Type           | Description |
| ------------------ | -------------- | ----------- |
| `assignment_alias` | `string`       |             |
| `course_alias`     | `string`       |             |
| `points`           | `float`        |             |
| `problem_alias`    | `string`       |             |
| `commit`           | `null\|string` |             |

### Returns

_Nothing_

## `/api/course/addStudent/`

### Description

Add Student to Course.

### Parameters

| Name                           | Type         | Description |
| ------------------------------ | ------------ | ----------- |
| `accept_teacher_git_object_id` | `string`     |             |
| `course_alias`                 | `string`     |             |
| `privacy_git_object_id`        | `string`     |             |
| `share_user_information`       | `bool`       |             |
| `statement_type`               | `string`     |             |
| `usernameOrEmail`              | `string`     |             |
| `accept_teacher`               | `bool\|null` |             |

### Returns

_Nothing_

## `/api/course/adminDetails/`

### Description

Returns all details of a given Course

### Parameters

| Name    | Type     | Description |
| ------- | -------- | ----------- |
| `alias` | `string` |             |

### Returns

```typescript
types.CourseDetails;
```

## `/api/course/admins/`

### Description

Returns all course administrators

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |

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

| Name         | Type           | Description |
| ------------ | -------------- | ----------- |
| `assignment` | `string`       |             |
| `course`     | `string`       |             |
| `token`      | `null\|string` |             |

### Returns

| Name                | Type                        |
| ------------------- | --------------------------- |
| `admin`             | `boolean`                   |
| `alias`             | `string`                    |
| `assignment_type`   | `string`                    |
| `courseAssignments` | `types.CourseAssignment[]`  |
| `description`       | `string`                    |
| `director`          | `string`                    |
| `finish_time`       | `Date`                      |
| `name`              | `string`                    |
| `problems`          | `types.ProblemsetProblem[]` |
| `problemset_id`     | `number`                    |
| `start_time`        | `Date`                      |

## `/api/course/assignmentScoreboard/`

### Description

Gets Scoreboard for an assignment

### Parameters

| Name         | Type           | Description |
| ------------ | -------------- | ----------- |
| `assignment` | `string`       |             |
| `course`     | `string`       |             |
| `token`      | `null\|string` |             |

### Returns

```typescript
types.Scoreboard;
```

## `/api/course/assignmentScoreboardEvents/`

### Description

Returns the Scoreboard events

### Parameters

| Name         | Type           | Description |
| ------------ | -------------- | ----------- |
| `assignment` | `string`       |             |
| `course`     | `string`       |             |
| `token`      | `null\|string` |             |

### Returns

| Name     | Type                      |
| -------- | ------------------------- |
| `events` | `types.ScoreboardEvent[]` |

## `/api/course/clone/`

### Description

Clone a course

### Parameters

| Name           | Type                | Description |
| -------------- | ------------------- | ----------- |
| `alias`        | `string`            |             |
| `course_alias` | `string`            |             |
| `name`         | `string`            |             |
| `start_time`   | `OmegaUp\Timestamp` |             |
| `token`        | `null\|string`      |             |

### Returns

| Name    | Type     |
| ------- | -------- |
| `alias` | `string` |

## `/api/course/create/`

### Description

Create new course API

### Parameters

| Name                        | Type         | Description |
| --------------------------- | ------------ | ----------- |
| `admission_mode`            | `mixed`      |             |
| `alias`                     | `mixed`      |             |
| `description`               | `mixed`      |             |
| `finish_time`               | `mixed`      |             |
| `languages`                 | `mixed`      |             |
| `name`                      | `mixed`      |             |
| `needs_basic_information`   | `mixed`      |             |
| `public`                    | `mixed`      |             |
| `requests_user_information` | `mixed`      |             |
| `school_id`                 | `mixed`      |             |
| `show_scoreboard`           | `mixed`      |             |
| `start_time`                | `mixed`      |             |
| `unlimited_duration`        | `bool\|null` |             |

### Returns

_Nothing_

## `/api/course/createAssignment/`

### Description

API to Create an assignment

### Parameters

| Name                 | Type           | Description |
| -------------------- | -------------- | ----------- |
| `course_alias`       | `string`       |             |
| `alias`              | `mixed`        |             |
| `assignment_type`    | `mixed`        |             |
| `description`        | `mixed`        |             |
| `finish_time`        | `mixed`        |             |
| `name`               | `mixed`        |             |
| `order`              | `int\|null`    |             |
| `problems`           | `null\|string` |             |
| `publish_time_delay` | `mixed`        |             |
| `start_time`         | `mixed`        |             |
| `unlimited_duration` | `bool\|null`   |             |

### Returns

_Nothing_

## `/api/course/details/`

### Description

Returns details of a given course

### Parameters

| Name    | Type     | Description |
| ------- | -------- | ----------- |
| `alias` | `string` |             |

### Returns

```typescript
types.CourseDetails;
```

## `/api/course/generateTokenForCloneCourse/`

### Description

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |

### Returns

| Name    | Type     |
| ------- | -------- |
| `token` | `string` |

## `/api/course/getProblemUsers/`

### Description

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `course_alias`  | `string` |             |
| `problem_alias` | `string` |             |

### Returns

| Name         | Type       |
| ------------ | ---------- |
| `identities` | `string[]` |

## `/api/course/introDetails/`

### Description

Show course intro only on public courses when user is not yet registered

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |

### Returns

```typescript
types.IntroDetailsPayload;
```

## `/api/course/listAssignments/`

### Description

List course assignments

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |

### Returns

| Name          | Type                       |
| ------------- | -------------------------- |
| `assignments` | `types.CourseAssignment[]` |

## `/api/course/listCourses/`

### Description

Lists all the courses this user is associated with.

Returns courses for which the current user is an admin and
for in which the user is a student.

### Parameters

| Name        | Type  | Description |
| ----------- | ----- | ----------- |
| `page`      | `int` |             |
| `page_size` | `int` |             |

### Returns

```typescript
types.CoursesList;
```

## `/api/course/listSolvedProblems/`

### Description

Get Problems solved by users of a course

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |

### Returns

| Name            | Type                                                                        |
| --------------- | --------------------------------------------------------------------------- |
| `user_problems` | `{ [key: string]: { alias: string; title: string; username: string; }[]; }` |

## `/api/course/listStudents/`

### Description

List students in a course

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |

### Returns

| Name       | Type                    |
| ---------- | ----------------------- |
| `students` | `types.CourseStudent[]` |

## `/api/course/listUnsolvedProblems/`

### Description

Get Problems unsolved by users of a course

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |

### Returns

| Name            | Type                                                                        |
| --------------- | --------------------------------------------------------------------------- |
| `user_problems` | `{ [key: string]: { alias: string; title: string; username: string; }[]; }` |

## `/api/course/myProgress/`

### Description

Returns details of a given course

### Parameters

| Name    | Type     | Description |
| ------- | -------- | ----------- |
| `alias` | `string` |             |

### Returns

| Name          | Type                       |
| ------------- | -------------------------- |
| `assignments` | `types.AssignmentProgress` |

## `/api/course/registerForCourse/`

### Description

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |

### Returns

_Nothing_

## `/api/course/removeAdmin/`

### Description

Removes an admin from a course

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `course_alias`    | `string` |             |
| `usernameOrEmail` | `string` |             |

### Returns

_Nothing_

## `/api/course/removeAssignment/`

### Description

Remove an assignment from a course

### Parameters

| Name               | Type     | Description |
| ------------------ | -------- | ----------- |
| `assignment_alias` | `string` |             |
| `course_alias`     | `string` |             |

### Returns

_Nothing_

## `/api/course/removeGroupAdmin/`

### Description

Removes a group admin from a course

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `course_alias` | `string` |             |
| `group`        | `string` |             |

### Returns

_Nothing_

## `/api/course/removeProblem/`

### Description

Remove a problem from an assignment

### Parameters

| Name               | Type     | Description |
| ------------------ | -------- | ----------- |
| `assignment_alias` | `string` |             |
| `course_alias`     | `string` |             |
| `problem_alias`    | `string` |             |

### Returns

_Nothing_

## `/api/course/removeStudent/`

### Description

Remove Student from Course

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `course_alias`    | `string` |             |
| `usernameOrEmail` | `string` |             |

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

| Name    | Type                      |
| ------- | ------------------------- |
| `users` | `types.IdentityRequest[]` |

## `/api/course/runs/`

### Description

Returns all runs for a course

### Parameters

| Name               | Type                                                                                                                                                            | Description |
| ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------- |
| `assignment_alias` | `string`                                                                                                                                                        |             |
| `course_alias`     | `string`                                                                                                                                                        |             |
| `language`         | `'c11-clang'\|'c11-gcc'\|'cat'\|'cpp11-clang'\|'cpp11-gcc'\|'cpp17-clang'\|'cpp17-gcc'\|'cs'\|'hs'\|'java'\|'kj'\|'kp'\|'lua'\|'pas'\|'py2'\|'py3'\|'rb'\|null` |             |
| `offset`           | `mixed`                                                                                                                                                         |             |
| `problem_alias`    | `null\|string`                                                                                                                                                  |             |
| `rowcount`         | `mixed`                                                                                                                                                         |             |
| `status`           | `'compiling'\|'new'\|'ready'\|'running'\|'waiting'\|null`                                                                                                       |             |
| `username`         | `null\|string`                                                                                                                                                  |             |
| `verdict`          | `'AC'\|'CE'\|'JE'\|'MLE'\|'NO-AC'\|'OLE'\|'PA'\|'RFE'\|'RTE'\|'TLE'\|'VE'\|'WA'\|null`                                                                          |             |

### Returns

| Name   | Type          |
| ------ | ------------- |
| `runs` | `types.Run[]` |

## `/api/course/studentProgress/`

### Description

### Parameters

| Name               | Type     | Description |
| ------------------ | -------- | ----------- |
| `assignment_alias` | `string` |             |
| `course_alias`     | `string` |             |
| `usernameOrEmail`  | `string` |             |

### Returns

| Name       | Type                    |
| ---------- | ----------------------- |
| `problems` | `types.CourseProblem[]` |

## `/api/course/update/`

### Description

Edit Course contents

### Parameters

| Name                        | Type                                        | Description |
| --------------------------- | ------------------------------------------- | ----------- |
| `alias`                     | `string`                                    |             |
| `languages`                 | `string`                                    |             |
| `school_id`                 | `int`                                       |             |
| `admission_mode`            | `'private'\|'public'\|'registration'\|null` |             |
| `description`               | `null\|string`                              |             |
| `finish_time`               | `OmegaUp\Timestamp\|null`                   |             |
| `name`                      | `null\|string`                              |             |
| `needs_basic_information`   | `bool\|null`                                |             |
| `requests_user_information` | `'no'\|'optional'\|'required'\|null`        |             |
| `show_scoreboard`           | `bool\|null`                                |             |
| `start_time`                | `OmegaUp\Timestamp\|null`                   |             |
| `unlimited_duration`        | `bool\|null`                                |             |

### Returns

_Nothing_

## `/api/course/updateAssignment/`

### Description

Update an assignment

### Parameters

| Name                 | Type                | Description |
| -------------------- | ------------------- | ----------- |
| `assignment`         | `string`            |             |
| `course`             | `string`            |             |
| `finish_time`        | `OmegaUp\Timestamp` |             |
| `start_time`         | `OmegaUp\Timestamp` |             |
| `unlimited_duration` | `bool\|null`        |             |

### Returns

_Nothing_

## `/api/course/updateAssignmentsOrder/`

### Description

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `assignments`  | `string` |             |
| `course_alias` | `string` |             |

### Returns

_Nothing_

## `/api/course/updateProblemsOrder/`

### Description

### Parameters

| Name               | Type     | Description |
| ------------------ | -------- | ----------- |
| `assignment_alias` | `string` |             |
| `course_alias`     | `string` |             |
| `problems`         | `string` |             |

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

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `group_alias`     | `string` |             |
| `usernameOrEmail` | `string` |             |

### Returns

_Nothing_

## `/api/group/create/`

### Description

New group

### Parameters

| Name          | Type           | Description |
| ------------- | -------------- | ----------- |
| `description` | `string`       |             |
| `name`        | `string`       |             |
| `alias`       | `null\|string` |             |

### Returns

_Nothing_

## `/api/group/createScoreboard/`

### Description

Create a scoreboard set to a group

### Parameters

| Name          | Type           | Description |
| ------------- | -------------- | ----------- |
| `group_alias` | `string`       |             |
| `name`        | `string`       |             |
| `alias`       | `null\|string` |             |
| `description` | `null\|string` |             |

### Returns

_Nothing_

## `/api/group/details/`

### Description

Details of a group (scoreboards)

### Parameters

| Name          | Type     | Description |
| ------------- | -------- | ----------- |
| `group_alias` | `string` |             |

### Returns

| Name          | Type                                                                         |
| ------------- | ---------------------------------------------------------------------------- |
| `group`       | `{ alias: string; create_time: number; description: string; name: string; }` |
| `scoreboards` | `types.GroupScoreboard[]`                                                    |

## `/api/group/list/`

### Description

Returns a list of groups that match a partial name. This returns an
array instead of an object since it is used by typeahead.

### Parameters

| Name    | Type           | Description |
| ------- | -------------- | ----------- |
| `query` | `null\|string` |             |

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

| Name          | Type     | Description |
| ------------- | -------- | ----------- |
| `group_alias` | `string` |             |

### Returns

| Name         | Type                                                                                                                                                                       |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `identities` | `{ classname: string; country?: string; country_id?: string; name?: string; school?: string; school_id?: number; state?: string; state_id?: string; username: string; }[]` |

## `/api/group/myList/`

### Description

Returns a list of groups by owner

### Returns

| Name     | Type                                                                         |
| -------- | ---------------------------------------------------------------------------- |
| `groups` | `{ alias: string; create_time: Date; description: string; name: string; }[]` |

## `/api/group/removeUser/`

### Description

Remove user from group

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `group_alias`     | `string` |             |
| `usernameOrEmail` | `string` |             |

### Returns

_Nothing_

## `/api/group/update/`

### Description

Update an existing group

### Parameters

| Name          | Type     | Description |
| ------------- | -------- | ----------- |
| `alias`       | `string` |             |
| `description` | `string` |             |
| `name`        | `string` |             |

### Returns

_Nothing_

# GroupScoreboard

GroupScoreboardController

## `/api/groupScoreboard/addContest/`

### Description

Add contest to a group scoreboard

### Parameters

| Name               | Type         | Description |
| ------------------ | ------------ | ----------- |
| `contest_alias`    | `string`     |             |
| `group_alias`      | `string`     |             |
| `scoreboard_alias` | `string`     |             |
| `weight`           | `float`      |             |
| `only_ac`          | `bool\|null` |             |

### Returns

_Nothing_

## `/api/groupScoreboard/details/`

### Description

Details of a scoreboard. Returns a list with all contests that belong to
the given scoreboard_alias

### Parameters

| Name               | Type     | Description |
| ------------------ | -------- | ----------- |
| `group_alias`      | `string` |             |
| `scoreboard_alias` | `string` |             |

### Returns

| Name         | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
| ------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `contests`   | `{ acl_id: number; admission_mode: string; alias: string; contest_id: number; description: string; feedback: string; finish_time: Date; languages: string; last_updated: number; only_ac?: boolean; partial_score: boolean; penalty: string; penalty_calc_policy: string; points_decay_factor: number; problemset_id: number; recommended: boolean; rerun_id: number; scoreboard: number; show_scoreboard_after: boolean; start_time: Date; submissions_gap: number; title: string; urgent: boolean; weight?: number; window_length: number; }[]` |
| `ranking`    | `{ contests: { [key: string]: { penalty: number; points: number; }; }; name: string; total: { penalty: number; points: number; }; username: string; }[]`                                                                                                                                                                                                                                                                                                                                                                                          |
| `scoreboard` | `{ alias: string; create_time: number; description: string; group_id: number; group_scoreboard_id: number; name: string; }`                                                                                                                                                                                                                                                                                                                                                                                                                       |

## `/api/groupScoreboard/list/`

### Description

Details of a scoreboard

### Parameters

| Name          | Type           | Description |
| ------------- | -------------- | ----------- |
| `group_alias` | `null\|string` |             |

### Returns

| Name          | Type                                                                                                                          |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `scoreboards` | `{ alias: string; create_time: number; description: string; group_id: number; group_scoreboard_id: number; name: string; }[]` |

## `/api/groupScoreboard/removeContest/`

### Description

Add contest to a group scoreboard

### Parameters

| Name               | Type     | Description |
| ------------------ | -------- | ----------- |
| `contest_alias`    | `string` |             |
| `group_alias`      | `string` |             |
| `scoreboard_alias` | `string` |             |

### Returns

_Nothing_

# Identity

IdentityController

## `/api/identity/bulkCreate/`

### Description

Entry point for Create bulk Identities API

### Parameters

| Name          | Type           | Description |
| ------------- | -------------- | ----------- |
| `identities`  | `string`       |             |
| `group_alias` | `null\|string` |             |
| `name`        | `mixed`        |             |
| `username`    | `mixed`        |             |

### Returns

_Nothing_

## `/api/identity/changePassword/`

### Description

Entry point for change passowrd of an identity

### Parameters

| Name          | Type     | Description |
| ------------- | -------- | ----------- |
| `group_alias` | `string` |             |
| `password`    | `string` |             |
| `username`    | `string` |             |
| `identities`  | `mixed`  |             |
| `name`        | `mixed`  |             |

### Returns

_Nothing_

## `/api/identity/create/`

### Description

Entry point for Create an Identity API

### Parameters

| Name          | Type           | Description |
| ------------- | -------------- | ----------- |
| `gender`      | `string`       |             |
| `name`        | `string`       |             |
| `password`    | `string`       |             |
| `school_name` | `string`       |             |
| `username`    | `string`       |             |
| `country_id`  | `null\|string` |             |
| `group_alias` | `null\|string` |             |
| `identities`  | `mixed`        |             |
| `state_id`    | `null\|string` |             |

### Returns

| Name       | Type     |
| ---------- | -------- |
| `username` | `string` |

## `/api/identity/selectIdentity/`

### Description

Entry point for switching between associated identities for a user

### Parameters

| Name              | Type           | Description |
| ----------------- | -------------- | ----------- |
| `usernameOrEmail` | `string`       |             |
| `auth_token`      | `null\|string` |             |

### Returns

_Nothing_

## `/api/identity/update/`

### Description

Entry point for Update an Identity API

### Parameters

| Name                | Type           | Description |
| ------------------- | -------------- | ----------- |
| `gender`            | `string`       |             |
| `group_alias`       | `string`       |             |
| `name`              | `string`       |             |
| `original_username` | `string`       |             |
| `school_name`       | `string`       |             |
| `username`          | `string`       |             |
| `country_id`        | `null\|string` |             |
| `identities`        | `mixed`        |             |
| `state_id`          | `null\|string` |             |

### Returns

_Nothing_

# Interview

## `/api/interview/addUsers/`

### Description

### Parameters

| Name                  | Type     | Description |
| --------------------- | -------- | ----------- |
| `interview_alias`     | `string` |             |
| `usernameOrEmailsCSV` | `string` |             |

### Returns

_Nothing_

## `/api/interview/create/`

### Description

### Parameters

| Name          | Type           | Description |
| ------------- | -------------- | ----------- |
| `duration`    | `int`          |             |
| `title`       | `string`       |             |
| `alias`       | `null\|string` |             |
| `description` | `null\|string` |             |

### Returns

_Nothing_

## `/api/interview/details/`

### Description

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `interview_alias` | `string` |             |

### Returns

| Name            | Type                                                                                                                     |
| --------------- | ------------------------------------------------------------------------------------------------------------------------ |
| `contest_alias` | `string`                                                                                                                 |
| `description`   | `string`                                                                                                                 |
| `problemset_id` | `number`                                                                                                                 |
| `users`         | `{ access_time: Date; country: string; email: string; opened_interview: boolean; user_id: number; username: string; }[]` |

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

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `problem_alias`   | `string` |             |
| `usernameOrEmail` | `string` |             |

### Returns

_Nothing_

## `/api/problem/addGroupAdmin/`

### Description

Adds a group admin to a problem

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `group`         | `string` |             |
| `problem_alias` | `string` |             |

### Returns

_Nothing_

## `/api/problem/addTag/`

### Description

Adds a tag to a problem

### Parameters

| Name            | Type         | Description |
| --------------- | ------------ | ----------- |
| `name`          | `string`     |             |
| `problem_alias` | `string`     |             |
| `public`        | `bool\|null` |             |

### Returns

| Name   | Type     |
| ------ | -------- |
| `name` | `string` |

## `/api/problem/adminList/`

### Description

Returns a list of problems where current user has admin rights (or is
the owner).

### Parameters

| Name        | Type  | Description |
| ----------- | ----- | ----------- |
| `page`      | `int` |             |
| `page_size` | `int` |             |

### Returns

| Name         | Type                      |
| ------------ | ------------------------- |
| `pagerItems` | `types.PageItem[]`        |
| `problems`   | `types.ProblemListItem[]` |

## `/api/problem/admins/`

### Description

Returns all problem administrators

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `problem_alias` | `string` |             |

### Returns

| Name           | Type                        |
| -------------- | --------------------------- |
| `admins`       | `types.ProblemAdmin[]`      |
| `group_admins` | `types.ProblemGroupAdmin[]` |

## `/api/problem/bestScore/`

### Description

Returns the best score for a problem

### Parameters

| Name             | Type           | Description |
| ---------------- | -------------- | ----------- |
| `username`       | `string`       |             |
| `contest_alias`  | `null\|string` |             |
| `problem_alias`  | `null\|string` |             |
| `problemset_id`  | `mixed`        |             |
| `statement_type` | `null\|string` |             |

### Returns

| Name    | Type     |
| ------- | -------- |
| `score` | `number` |

## `/api/problem/clarifications/`

### Description

Entry point for Problem clarifications API

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `problem_alias` | `string` |             |
| `offset`        | `mixed`  |             |
| `rowcount`      | `mixed`  |             |

### Returns

| Name             | Type                    |
| ---------------- | ----------------------- |
| `clarifications` | `types.Clarification[]` |

## `/api/problem/create/`

### Description

Create a new problem

### Parameters

| Name                      | Type           | Description |
| ------------------------- | -------------- | ----------- |
| `problem_alias`           | `string`       |             |
| `visibility`              | `string`       |             |
| `allow_user_add_tags`     | `bool\|null`   |             |
| `email_clarifications`    | `bool\|null`   |             |
| `extra_wall_time`         | `mixed`        |             |
| `input_limit`             | `mixed`        |             |
| `languages`               | `mixed`        |             |
| `memory_limit`            | `mixed`        |             |
| `output_limit`            | `mixed`        |             |
| `overall_wall_time_limit` | `mixed`        |             |
| `problem_level`           | `null\|string` |             |
| `selected_tags`           | `null\|string` |             |
| `show_diff`               | `null\|string` |             |
| `source`                  | `null\|string` |             |
| `time_limit`              | `mixed`        |             |
| `title`                   | `null\|string` |             |
| `update_published`        | `null\|string` |             |
| `validator`               | `null\|string` |             |
| `validator_time_limit`    | `mixed`        |             |

### Returns

_Nothing_

## `/api/problem/delete/`

### Description

Removes a problem whether user is the creator

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `problem_alias` | `string` |             |

### Returns

_Nothing_

## `/api/problem/details/`

### Description

Entry point for Problem Details API

### Parameters

| Name                      | Type           | Description |
| ------------------------- | -------------- | ----------- |
| `problem_alias`           | `string`       |             |
| `contest_alias`           | `null\|string` |             |
| `lang`                    | `null\|string` |             |
| `prevent_problemset_open` | `bool\|null`   |             |
| `problemset_id`           | `mixed`        |             |
| `show_solvers`            | `bool\|null`   |             |
| `statement_type`          | `null\|string` |             |

### Returns

```typescript
types.ProblemDetails;
```

## `/api/problem/list/`

### Description

List of public and user's private problems

### Parameters

| Name                    | Type           | Description |
| ----------------------- | -------------- | ----------- |
| `only_quality_seal`     | `bool`         |             |
| `difficulty`            | `null\|string` |             |
| `difficulty_range`      | `null\|string` |             |
| `language`              | `mixed`        |             |
| `level`                 | `null\|string` |             |
| `max_difficulty`        | `int\|null`    |             |
| `min_difficulty`        | `int\|null`    |             |
| `min_visibility`        | `int\|null`    |             |
| `offset`                | `mixed`        |             |
| `only_karel`            | `mixed`        |             |
| `order_by`              | `mixed`        |             |
| `page`                  | `mixed`        |             |
| `programming_languages` | `null\|string` |             |
| `query`                 | `null\|string` |             |
| `require_all_tags`      | `mixed`        |             |
| `rowcount`              | `mixed`        |             |
| `some_tags`             | `mixed`        |             |
| `sort_order`            | `mixed`        |             |

### Returns

| Name      | Type                      |
| --------- | ------------------------- |
| `results` | `types.ProblemListItem[]` |
| `total`   | `number`                  |

## `/api/problem/myList/`

### Description

Gets a list of problems where current user is the owner

### Parameters

| Name        | Type    | Description |
| ----------- | ------- | ----------- |
| `page`      | `int`   |             |
| `page_size` | `int`   |             |
| `offset`    | `mixed` |             |
| `rowcount`  | `mixed` |             |

### Returns

| Name         | Type                      |
| ------------ | ------------------------- |
| `pagerItems` | `types.PageItem[]`        |
| `problems`   | `types.ProblemListItem[]` |

## `/api/problem/randomKarelProblem/`

### Description

### Returns

| Name    | Type     |
| ------- | -------- |
| `alias` | `string` |

## `/api/problem/randomLanguageProblem/`

### Description

### Returns

| Name    | Type     |
| ------- | -------- |
| `alias` | `string` |

## `/api/problem/rejudge/`

### Description

Rejudge problem

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `problem_alias` | `string` |             |

### Returns

_Nothing_

## `/api/problem/removeAdmin/`

### Description

Removes an admin from a problem

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `problem_alias`   | `string` |             |
| `usernameOrEmail` | `string` |             |

### Returns

_Nothing_

## `/api/problem/removeGroupAdmin/`

### Description

Removes a group admin from a problem

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `group`         | `string` |             |
| `problem_alias` | `string` |             |

### Returns

_Nothing_

## `/api/problem/removeTag/`

### Description

Removes a tag from a contest

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `name`          | `string` |             |
| `problem_alias` | `string` |             |

### Returns

_Nothing_

## `/api/problem/runs/`

### Description

Entry point for Problem runs API

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `language`      | `null\|string` |             |
| `offset`        | `int\|null`    |             |
| `problem_alias` | `null\|string` |             |
| `rowcount`      | `int\|null`    |             |
| `show_all`      | `bool\|null`   |             |
| `status`        | `null\|string` |             |
| `username`      | `null\|string` |             |
| `verdict`       | `null\|string` |             |

### Returns

| Name   | Type          |
| ------ | ------------- |
| `runs` | `types.Run[]` |

## `/api/problem/runsDiff/`

### Description

Return a report of which runs would change due to a version change.

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `version`       | `string`       |             |
| `problem_alias` | `null\|string` |             |

### Returns

| Name   | Type               |
| ------ | ------------------ |
| `diff` | `types.RunsDiff[]` |

## `/api/problem/selectVersion/`

### Description

Change the version of the problem.

### Parameters

| Name               | Type           | Description |
| ------------------ | -------------- | ----------- |
| `commit`           | `null\|string` |             |
| `problem_alias`    | `null\|string` |             |
| `update_published` | `null\|string` |             |

### Returns

_Nothing_

## `/api/problem/solution/`

### Description

Returns the solution for a problem if conditions are satisfied.

### Parameters

| Name              | Type           | Description |
| ----------------- | -------------- | ----------- |
| `contest_alias`   | `null\|string` |             |
| `forfeit_problem` | `bool\|null`   |             |
| `lang`            | `null\|string` |             |
| `problem_alias`   | `null\|string` |             |
| `problemset_id`   | `mixed`        |             |
| `statement_type`  | `null\|string` |             |

### Returns

| Name       | Type                     |
| ---------- | ------------------------ |
| `solution` | `types.ProblemStatement` |

## `/api/problem/stats/`

### Description

Stats of a problem

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `problem_alias` | `string` |             |

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

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `problem_alias` | `string` |             |
| `include_voted` | `mixed`  |             |

### Returns

| Name   | Type                                   |
| ------ | -------------------------------------- |
| `tags` | `{ name: string; public: boolean; }[]` |

## `/api/problem/update/`

### Description

Update problem contents

### Parameters

| Name                      | Type           | Description |
| ------------------------- | -------------- | ----------- |
| `message`                 | `string`       |             |
| `problem_alias`           | `string`       |             |
| `allow_user_add_tags`     | `bool\|null`   |             |
| `email_clarifications`    | `bool\|null`   |             |
| `extra_wall_time`         | `mixed`        |             |
| `input_limit`             | `mixed`        |             |
| `languages`               | `mixed`        |             |
| `memory_limit`            | `mixed`        |             |
| `output_limit`            | `mixed`        |             |
| `overall_wall_time_limit` | `mixed`        |             |
| `problem_level`           | `null\|string` |             |
| `redirect`                | `mixed`        |             |
| `selected_tags`           | `null\|string` |             |
| `show_diff`               | `null\|string` |             |
| `source`                  | `null\|string` |             |
| `time_limit`              | `mixed`        |             |
| `title`                   | `null\|string` |             |
| `update_published`        | `null\|string` |             |
| `validator`               | `null\|string` |             |
| `validator_time_limit`    | `mixed`        |             |
| `visibility`              | `null\|string` |             |

### Returns

| Name       | Type      |
| ---------- | --------- |
| `rejudged` | `boolean` |

## `/api/problem/updateProblemLevel/`

### Description

Updates the problem level of a problem

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `problem_alias` | `string`       |             |
| `level_tag`     | `null\|string` |             |

### Returns

_Nothing_

## `/api/problem/updateSolution/`

### Description

Updates problem solution only

### Parameters

| Name                      | Type           | Description |
| ------------------------- | -------------- | ----------- |
| `message`                 | `string`       |             |
| `problem_alias`           | `string`       |             |
| `solution`                | `string`       |             |
| `visibility`              | `string`       |             |
| `allow_user_add_tags`     | `bool\|null`   |             |
| `email_clarifications`    | `bool\|null`   |             |
| `extra_wall_time`         | `mixed`        |             |
| `input_limit`             | `mixed`        |             |
| `lang`                    | `null\|string` |             |
| `languages`               | `mixed`        |             |
| `memory_limit`            | `mixed`        |             |
| `output_limit`            | `mixed`        |             |
| `overall_wall_time_limit` | `mixed`        |             |
| `problem_level`           | `null\|string` |             |
| `selected_tags`           | `null\|string` |             |
| `show_diff`               | `null\|string` |             |
| `source`                  | `null\|string` |             |
| `time_limit`              | `mixed`        |             |
| `title`                   | `null\|string` |             |
| `update_published`        | `null\|string` |             |
| `validator`               | `null\|string` |             |
| `validator_time_limit`    | `mixed`        |             |

### Returns

_Nothing_

## `/api/problem/updateStatement/`

### Description

Updates problem statement only

### Parameters

| Name                      | Type           | Description |
| ------------------------- | -------------- | ----------- |
| `message`                 | `string`       |             |
| `problem_alias`           | `string`       |             |
| `statement`               | `string`       |             |
| `visibility`              | `string`       |             |
| `allow_user_add_tags`     | `bool\|null`   |             |
| `email_clarifications`    | `bool\|null`   |             |
| `extra_wall_time`         | `mixed`        |             |
| `input_limit`             | `mixed`        |             |
| `lang`                    | `mixed`        |             |
| `languages`               | `mixed`        |             |
| `memory_limit`            | `mixed`        |             |
| `output_limit`            | `mixed`        |             |
| `overall_wall_time_limit` | `mixed`        |             |
| `problem_level`           | `null\|string` |             |
| `selected_tags`           | `null\|string` |             |
| `show_diff`               | `null\|string` |             |
| `source`                  | `null\|string` |             |
| `time_limit`              | `mixed`        |             |
| `title`                   | `null\|string` |             |
| `update_published`        | `null\|string` |             |
| `validator`               | `null\|string` |             |
| `validator_time_limit`    | `mixed`        |             |

### Returns

_Nothing_

## `/api/problem/versions/`

### Description

Entry point for Problem Versions API

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `problem_alias` | `null\|string` |             |

### Returns

| Name        | Type                     |
| ----------- | ------------------------ |
| `log`       | `types.ProblemVersion[]` |
| `published` | `string`                 |

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

| Name              | Type           | Description |
| ----------------- | -------------- | ----------- |
| `assignment`      | `string`       |             |
| `contest_alias`   | `string`       |             |
| `course`          | `string`       |             |
| `interview_alias` | `string`       |             |
| `problemset_id`   | `int`          |             |
| `auth_token`      | `mixed`        |             |
| `token`           | `null\|string` |             |
| `tokens`          | `mixed`        |             |

### Returns

```typescript
types.Problemset;
```

## `/api/problemset/scoreboard/`

### Description

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `assignment`    | `string` |             |
| `contest_alias` | `string` |             |
| `course`        | `string` |             |
| `problemset_id` | `int`    |             |
| `auth_token`    | `mixed`  |             |
| `token`         | `mixed`  |             |
| `tokens`        | `mixed`  |             |

### Returns

```typescript
types.Scoreboard;
```

## `/api/problemset/scoreboardEvents/`

### Description

Returns the Scoreboard events

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `assignment`    | `string` |             |
| `contest_alias` | `string` |             |
| `course`        | `string` |             |
| `problemset_id` | `int`    |             |
| `auth_token`    | `mixed`  |             |
| `token`         | `mixed`  |             |
| `tokens`        | `mixed`  |             |

### Returns

| Name     | Type                      |
| -------- | ------------------------- |
| `events` | `types.ScoreboardEvent[]` |

# QualityNomination

QualityNominationController

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

| Name            | Type                                                                | Description |
| --------------- | ------------------------------------------------------------------- | ----------- |
| `contents`      | `string`                                                            |             |
| `nomination`    | `'demotion'\|'dismissal'\|'promotion'\|'quality_tag'\|'suggestion'` |             |
| `problem_alias` | `string`                                                            |             |

### Returns

| Name                   | Type     |
| ---------------------- | -------- |
| `qualitynomination_id` | `number` |

## `/api/qualityNomination/details/`

### Description

Displays the details of a nomination. The user needs to be either the
nominator or a member of the reviewer group.

### Parameters

| Name                   | Type  | Description |
| ---------------------- | ----- | ----------- |
| `qualitynomination_id` | `int` |             |

### Returns

| Name                   | Type                                                                                                                                                             |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `author`               | `{ name: string; username: string; }`                                                                                                                            |
| `contents`             | `{ before_ac?: boolean; difficulty?: number; quality?: number; rationale?: string; reason?: string; statements?: { [key: string]: string; }; tags?: string[]; }` |
| `nomination`           | `string`                                                                                                                                                         |
| `nomination_status`    | `string`                                                                                                                                                         |
| `nominator`            | `{ name: string; username: string; }`                                                                                                                            |
| `original_contents`    | `{ source: string; statements: { [key: string]: types.ProblemStatement; }; tags?: { name: string; source: string; }[]; }`                                        |
| `problem`              | `{ alias: string; title: string; }`                                                                                                                              |
| `qualitynomination_id` | `number`                                                                                                                                                         |
| `reviewer`             | `boolean`                                                                                                                                                        |
| `time`                 | `Date`                                                                                                                                                           |
| `votes`                | `{ time: Date; user: { name: string; username: string; }; vote: number; }[]`                                                                                     |

## `/api/qualityNomination/list/`

### Description

### Parameters

| Name       | Type                                                             | Description |
| ---------- | ---------------------------------------------------------------- | ----------- |
| `offset`   | `int`                                                            |             |
| `rowcount` | `int`                                                            |             |
| `column`   | `'author_username'\|'nominator_username'\|'problem_alias'\|null` |             |
| `query`    | `null\|string`                                                   |             |
| `status`   | `mixed`                                                          |             |

### Returns

| Name          | Type                         |
| ------------- | ---------------------------- |
| `nominations` | `types.NominationListItem[]` |
| `pager_items` | `types.PageItem[]`           |

## `/api/qualityNomination/myAssignedList/`

### Description

Displays the nominations that this user has been assigned.

### Parameters

| Name        | Type  | Description |
| ----------- | ----- | ----------- |
| `page`      | `int` |             |
| `page_size` | `int` |             |

### Returns

| Name          | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| ------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `nominations` | `{ author: { name: string; username: string; }; contents?: { before_ac?: boolean; difficulty?: number; quality?: number; rationale?: string; reason?: string; statements?: { [key: string]: string; }; tags?: string[]; }; nomination: string; nominator: { name: string; username: string; }; problem: { alias: string; title: string; }; qualitynomination_id: number; status: string; time: Date; votes: { time: Date; user: { name: string; username: string; }; vote: number; }[]; }[]` |

## `/api/qualityNomination/myList/`

### Description

### Parameters

| Name       | Type  | Description |
| ---------- | ----- | ----------- |
| `offset`   | `int` |             |
| `rowcount` | `int` |             |

### Returns

| Name          | Type                         |
| ------------- | ---------------------------- |
| `nominations` | `types.NominationListItem[]` |
| `pager_items` | `types.PageItem[]`           |

## `/api/qualityNomination/resolve/`

### Description

Marks a problem of a nomination (only the demotion type supported for now) as (resolved, banned, warning).

### Parameters

| Name                   | Type                                      | Description |
| ---------------------- | ----------------------------------------- | ----------- |
| `problem_alias`        | `string`                                  |             |
| `qualitynomination_id` | `int`                                     |             |
| `rationale`            | `string`                                  |             |
| `status`               | `'banned'\|'open'\|'resolved'\|'warning'` |             |
| `all`                  | `bool\|null`                              |             |

### Returns

_Nothing_

# Reset

## `/api/reset/create/`

### Description

Creates a reset operation, the first of two steps needed to reset a
password. The first step consist of sending an email to the user with
instructions to reset he's password, if and only if the email is valid.

### Parameters

| Name    | Type     | Description |
| ------- | -------- | ----------- |
| `email` | `string` |             |

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

| Name    | Type     | Description |
| ------- | -------- | ----------- |
| `email` | `string` |             |

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

| Name                    | Type     | Description |
| ----------------------- | -------- | ----------- |
| `email`                 | `string` |             |
| `password`              | `string` |             |
| `password_confirmation` | `string` |             |
| `reset_token`           | `string` |             |

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
| `ac`    | `{ [key: string]: number; }` |
| `total` | `{ [key: string]: number; }` |

## `/api/run/create/`

### Description

Create a new run

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `contest_alias` | `string` |             |
| `problem_alias` | `string` |             |
| `source`        | `string` |             |
| `language`      | `mixed`  |             |
| `problemset_id` | `mixed`  |             |

### Returns

| Name                      | Type     |
| ------------------------- | -------- |
| `guid`                    | `string` |
| `nextSubmissionTimestamp` | `Date`   |
| `submission_deadline`     | `Date`   |
| `submit_delay`            | `number` |

## `/api/run/details/`

### Description

Gets the details of a run. Includes admin details if admin.

### Parameters

| Name        | Type     | Description |
| ----------- | -------- | ----------- |
| `run_alias` | `string` |             |

### Returns

```typescript
types.RunDetails;
```

## `/api/run/disqualify/`

### Description

Disqualify a submission

### Parameters

| Name        | Type     | Description |
| ----------- | -------- | ----------- |
| `run_alias` | `string` |             |

### Returns

_Nothing_

## `/api/run/list/`

### Description

Gets a list of latest runs overall

### Parameters

| Name            | Type     | Description |
| --------------- | -------- | ----------- |
| `offset`        | `int`    |             |
| `problem_alias` | `string` |             |
| `rowcount`      | `int`    |             |
| `username`      | `string` |             |
| `language`      | `mixed`  |             |
| `status`        | `mixed`  |             |
| `verdict`       | `mixed`  |             |

### Returns

| Name   | Type          |
| ------ | ------------- |
| `runs` | `types.Run[]` |

## `/api/run/rejudge/`

### Description

Re-sends a problem to Grader.

### Parameters

| Name        | Type     | Description |
| ----------- | -------- | ----------- |
| `run_alias` | `string` |             |
| `debug`     | `mixed`  |             |

### Returns

_Nothing_

## `/api/run/source/`

### Description

Given the run alias, returns the source code and any compile errors if any
Used in the arena, any contestant can view its own codes and compile errors

### Parameters

| Name        | Type     | Description |
| ----------- | -------- | ----------- |
| `run_alias` | `string` |             |

### Returns

| Name            | Type                                                                                                                                                                                                                                                                                                                             |
| --------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `compile_error` | `string`                                                                                                                                                                                                                                                                                                                         |
| `details`       | `{ compile_meta?: { [key: string]: types.RunMetadata; }; contest_score: number; groups?: { cases: types.CaseResult[]; contest_score: number; group: string; max_score: number; score: number; }[]; judged_by: string; max_score?: number; memory?: number; score: number; time?: number; verdict: string; wall_time?: number; }` |
| `source`        | `string`                                                                                                                                                                                                                                                                                                                         |

## `/api/run/status/`

### Description

Get basic details of a run

### Parameters

| Name        | Type     | Description |
| ----------- | -------- | ----------- |
| `run_alias` | `string` |             |

### Returns

```typescript
types.Run;
```

# School

SchoolController

## `/api/school/create/`

### Description

Api to create new school

### Parameters

| Name         | Type           | Description |
| ------------ | -------------- | ----------- |
| `name`       | `string`       |             |
| `country_id` | `null\|string` |             |
| `state_id`   | `null\|string` |             |

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

## `/api/school/selectSchoolOfTheMonth/`

### Description

Selects a certain school as school of the month

### Parameters

| Name        | Type  | Description |
| ----------- | ----- | ----------- |
| `school_id` | `int` |             |

### Returns

_Nothing_

# Scoreboard

ScoreboardController

## `/api/scoreboard/refresh/`

### Description

Returns a list of contests

### Parameters

| Name           | Type           | Description |
| -------------- | -------------- | ----------- |
| `alias`        | `string`       |             |
| `course_alias` | `null\|string` |             |
| `token`        | `mixed`        |             |

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

### Parameters

| Name         | Type           | Description |
| ------------ | -------------- | ----------- |
| `auth_token` | `null\|string` |             |

### Returns

| Name      | Type                   |
| --------- | ---------------------- |
| `session` | `types.CurrentSession` |
| `time`    | `number`               |

## `/api/session/googleLogin/`

### Description

### Parameters

| Name         | Type     | Description |
| ------------ | -------- | ----------- |
| `storeToken` | `string` |             |

### Returns

| Name                | Type      |
| ------------------- | --------- |
| `isAccountCreation` | `boolean` |

# Tag

TagController

## `/api/tag/frequentTags/`

### Description

Return most frequent public tags of a certain level

### Parameters

| Name           | Type     | Description |
| -------------- | -------- | ----------- |
| `problemLevel` | `string` |             |
| `rows`         | `int`    |             |

### Returns

| Name            | Type                          |
| --------------- | ----------------------------- |
| `frequent_tags` | `types.TagWithProblemCount[]` |

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

| Name                    | Type     | Description |
| ----------------------- | -------- | ----------- |
| `privacy_git_object_id` | `string` |             |
| `statement_type`        | `string` |             |
| `username`              | `string` |             |

### Returns

_Nothing_

## `/api/user/addExperiment/`

### Description

Adds the experiment to the user.

### Parameters

| Name         | Type     | Description |
| ------------ | -------- | ----------- |
| `experiment` | `string` |             |

### Returns

_Nothing_

## `/api/user/addGroup/`

### Description

Adds the identity to the group.

### Parameters

| Name    | Type     | Description |
| ------- | -------- | ----------- |
| `group` | `string` |             |

### Returns

_Nothing_

## `/api/user/addRole/`

### Description

Adds the role to the user.

### Parameters

| Name   | Type     | Description |
| ------ | -------- | ----------- |
| `role` | `string` |             |

### Returns

_Nothing_

## `/api/user/associateIdentity/`

### Description

Associates an identity to the logged user given the username

### Parameters

| Name       | Type     | Description |
| ---------- | -------- | ----------- |
| `password` | `string` |             |
| `username` | `string` |             |

### Returns

_Nothing_

## `/api/user/changePassword/`

### Description

Changes the password of a user

### Parameters

| Name             | Type           | Description |
| ---------------- | -------------- | ----------- |
| `old_password`   | `string`       |             |
| `username`       | `string`       |             |
| `password`       | `null\|string` |             |
| `permission_key` | `mixed`        |             |

### Returns

_Nothing_

## `/api/user/coderOfTheMonth/`

### Description

Get coder of the month by trying to find it in the table using the first
day of the current month. If there's no coder of the month for the given
date, calculate it and save it.

### Parameters

| Name       | Type           | Description |
| ---------- | -------------- | ----------- |
| `category` | `mixed`        |             |
| `date`     | `null\|string` |             |

### Returns

| Name        | Type                |
| ----------- | ------------------- |
| `coderinfo` | `types.UserProfile` |

## `/api/user/coderOfTheMonthList/`

### Description

Returns the list of coders of the month

### Parameters

| Name       | Type           | Description |
| ---------- | -------------- | ----------- |
| `category` | `mixed`        |             |
| `date`     | `null\|string` |             |

### Returns

| Name     | Type                        |
| -------- | --------------------------- |
| `coders` | `types.CoderOfTheMonthList` |

## `/api/user/contestStats/`

### Description

Get Contests which a certain user has participated in

### Parameters

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `contest_alias` | `string`       |             |
| `auth_token`    | `mixed`        |             |
| `token`         | `null\|string` |             |
| `username`      | `mixed`        |             |

### Returns

| Name       | Type                                                                                                                                        |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| `contests` | `{ [key: string]: { data: { alias: string; finish_time: Date; last_updated: Date; start_time: Date; title: string; }; place?: number; }; }` |

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

| Name    | Type     | Description |
| ------- | -------- | ----------- |
| `email` | `string` |             |

### Returns

| Name              | Type      |
| ----------------- | --------- |
| `last_login`      | `Date`    |
| `username`        | `string`  |
| `verified`        | `boolean` |
| `within_last_day` | `boolean` |

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

| Name              | Type           | Description |
| ----------------- | -------------- | ----------- |
| `auth_token`      | `string`       |             |
| `contest_alias`   | `string`       |             |
| `contest_type`    | `string`       |             |
| `id`              | `string`       |             |
| `old_password`    | `string`       |             |
| `permission_key`  | `string`       |             |
| `username`        | `string`       |             |
| `change_password` | `mixed`        |             |
| `password`        | `null\|string` |             |
| `usernameOrEmail` | `null\|string` |             |

### Returns

```typescript
{ [key: string]: string; }
```

## `/api/user/interviewStats/`

### Description

Get the results for this user in a given interview

### Parameters

| Name        | Type     | Description |
| ----------- | -------- | ----------- |
| `interview` | `string` |             |
| `username`  | `string` |             |

### Returns

| Name               | Type      |
| ------------------ | --------- |
| `finished`         | `boolean` |
| `interview_url`    | `string`  |
| `name_or_username` | `string`  |
| `opened_interview` | `boolean` |
| `user_verified`    | `boolean` |

## `/api/user/lastPrivacyPolicyAccepted/`

### Description

Gets the last privacy policy accepted by user

### Parameters

| Name       | Type     | Description |
| ---------- | -------- | ----------- |
| `username` | `string` |             |

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

| Name         | Type                         |
| ------------ | ---------------------------- |
| `identities` | `types.AssociatedIdentity[]` |

## `/api/user/listUnsolvedProblems/`

### Description

Get Problems unsolved by user

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `username` | `mixed` |             |

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

### Parameters

| Name              | Type     | Description |
| ----------------- | -------- | ----------- |
| `password`        | `string` |             |
| `usernameOrEmail` | `string` |             |

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

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `username` | `mixed` |             |

### Returns

| Name       | Type              |
| ---------- | ----------------- |
| `problems` | `types.Problem[]` |

## `/api/user/problemsSolved/`

### Description

Get Problems solved by user

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `username` | `mixed` |             |

### Returns

| Name       | Type              |
| ---------- | ----------------- |
| `problems` | `types.Problem[]` |

## `/api/user/profile/`

### Description

Get general user info

### Parameters

| Name        | Type         | Description |
| ----------- | ------------ | ----------- |
| `category`  | `mixed`      |             |
| `omit_rank` | `bool\|null` |             |
| `username`  | `mixed`      |             |

### Returns

```typescript
types.UserProfileInfo;
```

## `/api/user/removeExperiment/`

### Description

Removes the experiment from the user.

### Parameters

| Name         | Type     | Description |
| ------------ | -------- | ----------- |
| `experiment` | `string` |             |

### Returns

_Nothing_

## `/api/user/removeGroup/`

### Description

Removes the user to the group.

### Parameters

| Name    | Type     | Description |
| ------- | -------- | ----------- |
| `group` | `string` |             |

### Returns

_Nothing_

## `/api/user/removeRole/`

### Description

Removes the role from the user.

### Parameters

| Name   | Type     | Description |
| ------ | -------- | ----------- |
| `role` | `string` |             |

### Returns

_Nothing_

## `/api/user/selectCoderOfTheMonth/`

### Description

Selects coder of the month for next month.

### Parameters

| Name       | Type     | Description |
| ---------- | -------- | ----------- |
| `username` | `string` |             |
| `category` | `mixed`  |             |

### Returns

_Nothing_

## `/api/user/stats/`

### Description

Get stats

### Parameters

| Name       | Type    | Description |
| ---------- | ------- | ----------- |
| `username` | `mixed` |             |

### Returns

| Name   | Type                                                 |
| ------ | ---------------------------------------------------- |
| `runs` | `{ date: string; runs: number; verdict: string; }[]` |

## `/api/user/statusVerified/`

### Description

Gets verify status of a user

### Parameters

| Name    | Type     | Description |
| ------- | -------- | ----------- |
| `email` | `string` |             |

### Returns

| Name       | Type      |
| ---------- | --------- |
| `username` | `string`  |
| `verified` | `boolean` |

## `/api/user/update/`

### Description

Update user profile

### Parameters

| Name                | Type                                         | Description |
| ------------------- | -------------------------------------------- | ----------- |
| `birth_date`        | `string`                                     |             |
| `country_id`        | `string`                                     |             |
| `graduation_date`   | `string`                                     |             |
| `locale`            | `string`                                     |             |
| `state_id`          | `string`                                     |             |
| `auth_token`        | `mixed`                                      |             |
| `gender`            | `'decline'\|'female'\|'male'\|'other'\|null` |             |
| `hide_problem_tags` | `bool\|null`                                 |             |
| `is_private`        | `bool\|null`                                 |             |
| `name`              | `null\|string`                               |             |
| `scholar_degree`    | `null\|string`                               |             |
| `school_id`         | `int\|null`                                  |             |
| `school_name`       | `mixed`                                      |             |
| `username`          | `mixed`                                      |             |

### Returns

_Nothing_

## `/api/user/updateBasicInfo/`

### Description

Update basic user profile info when logged with fb/gool

### Parameters

| Name       | Type     | Description |
| ---------- | -------- | ----------- |
| `password` | `string` |             |
| `username` | `string` |             |

### Returns

_Nothing_

## `/api/user/updateMainEmail/`

### Description

Updates the main email of the current user

### Parameters

| Name    | Type     | Description |
| ------- | -------- | ----------- |
| `email` | `string` |             |

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

| Name            | Type           | Description |
| --------------- | -------------- | ----------- |
| `filter`        | `string`       |             |
| `problemset_id` | `int`          |             |
| `auth_token`    | `null\|string` |             |
| `contest_admin` | `null\|string` |             |
| `contest_alias` | `null\|string` |             |
| `token`         | `null\|string` |             |
| `tokens`        | `mixed`        |             |

### Returns

| Name               | Type       |
| ------------------ | ---------- |
| `admin`            | `boolean`  |
| `contest_admin`    | `string[]` |
| `problem_admin`    | `string[]` |
| `problemset_admin` | `number[]` |
| `user`             | `string`   |

## `/api/user/verifyEmail/`

### Description

Verifies the user given its verification id

### Parameters

| Name              | Type           | Description |
| ----------------- | -------------- | ----------- |
| `id`              | `string`       |             |
| `usernameOrEmail` | `null\|string` |             |

### Returns

_Nothing_
