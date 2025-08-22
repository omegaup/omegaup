**NOTE**: This documentation is outdated.

For a more recent version, visit the following link: [apiContest](https://github.com/omegaup/omegaup/blob/master/frontend/server/src/Controllers/README.md#contest)

## GET `contests/`

### Description
Returns the 10 most recent contests that the logged-in user can view. Non-logged-in users can consume this API.

### Privileges
None required.

### Parameters
None

### Returns
Returns an array with the following information for each contest:

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`alias`|string|Contest alias|
|`contest_id`|int|Contest ID|
|`title`|string|Contest Title|
|`description`|string|Contest Description|
|`start_time`|int|Contest start time in UNIX timestamp format|
|`finish_time`|int|Contest end time in UNIX timestamp format|
|`public`|int|If `0`, the contest is private. If `1`, the contest is public|
|`director_id`|int|ID of the user who is the contest director|
|`window_length`|int| If not null, the contest duration will be `window_length` in minutes, and the contest timer will be specific to each user instead of general to all. The timer will start when the contestant first enters the contest. `start_time` will then determine the time at which users can start opening the contest (USACO style). The default is `null`. |`duration`|int|The duration of the contest, taking into account the value of `window_length`|

## GET `contests/:contest_alias/`

### Description
Returns the details of the contest `:contest_alias`.

### Privileges
If the contest is private, the user must be on the list of private contestants. If the contest is public, any user can access this API.

### Parameters
None

### Returns

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`title`|string|Contest title|
|`description`|string|Contest description|
|`alias`|string|Contest alias|
|`start_time`|int|Contest start time in UNIX timestamp format|
|`finish_time`|int|Contest end time in UNIX timestamp format|
|`window_length`|int| If not null, the contest duration will be `window_length` in minutes, and the contest timer will be specific to each user instead of general to all. The timer will start when the contestant first enters the contest. `start_time` will then determine the time at which users can begin opening the contest (USACO style). The default is `null`.|
|`scoreboard`|int|An integer between `0` and `100` (inclusive) that determines the percentage of time the contest scoreboard will be viewable by contestants. When the percentage is exceeded, the scoreboard returned is the last version that could have been made public. Administrators will always see the full scoreboard.|
|`points_decay_factor`|double|Double between `0` and `1` inclusive. If this number is non-zero, the score obtained for correctly solving a problem decays over time. The score value is given by `(1 - points_decay_factor)` `+ points_decay_factor * TT^2` `/ (10 * PT^2 + TT^2)`, where `PT` is the penalty in minutes for the submission and `TT` is the total contest time, in minutes. |
|`partial_score`|int|Integer between `0` and `1` |
|`submissions_gap`|int| Number of seconds the contestant must wait before resubmitting a solution. |
|`feedback`|string|Options: `yes`, `no`, `partial` |
|`penalty_time_start`|string|Determines how the penalty is calculated. Options: `contest`, `problem`, `none`. In the case of a `contest`, the penalty for a submission starts counting from the start of the contest. `problem` indicates that the penalty is taken into account from the moment a problem is opened. `none` indicates that there will be no penalties in the contest. |
|`penalty_calc_policy`|string|Options: `sum`, `max`. Default:|
|`submission_deadline`|int|Time remaining in seconds until the end of the contest. If `window_length` is not `NULL`, this value can be different for each contestant. |
|`problems`|array|Array containing information about the contest problems ordered as desired by the contest director. See the `problems` table for more details. |

#### `problems`

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`title`|string|Problem title|
|`alias`|string|Issue alias|
|`validator`|string|Type of the problem validator. Options: `remote`, `literal`, `token`, `token-caseless`, `token-numeric`|
|`time_limit`|int|Time limit in seconds for each submission (TLE)|
|`memory_limit`|int|Memory limit in KB (MLE)|
|`submissions`|int|Total number of submissions to this problem system-wide, not just in the contest|
|`accepted`|int|Number of solutions that fully solved the problem system-wide, not just in the given contest|
|`difficulty`|string|Problem difficulty calculated based on system statistics|

## POST `contests/create`

### Description
Creates a new contest. The contest leader will be the currently logged in user.

### Privileges
Any logged in user.

### Parameters
| Parameter | Type | Description | Optional? |
| -------- |:-------------:| :-----|:-----|
| `title` |string | Contest title | |
|`description` | string | Short description of the contest | |
| `alias` | string | Contest alias. Its main use is to build contest URLs (see other APIs). | |
| `start_time`| int | Contest start time in UNIX timestamp format if `window_length` is null. | |
| `finish_time` | int | Contest end time in UNIX timestamp format if `window_length` is null. | |
| `window_length` | int | If not null, the contest duration will be `window_length` in minutes, and the contest timer will be specific to each user instead of general. The timer will start when the contestant first enters the contest. `start_time` will then determine the time at which users can begin opening the contest (USACO style). The default is `null`. | Optional |
| `public` | int | Determines whether the contest is public or private (`0` for private, `1` for public) | |
| `scoreboard` | int | Integer between `0` and `100` (inclusive) that determines the percentage of time the contest scoreboard will be viewable by contestants. When the percentage is exceeded, the scoreboard returned is the last version that could have been public. Administrators will always see the full scoreboard. | |
| `points_decay_factor` | double | Double between `0` and `1` inclusive. If this number is non-zero, the score obtained for correctly solving a problem decays over time. The score value is given by `(1 - points_decay_factor)` `+ points_decay_factor * TT^2` `/ (10 * PT^2 + TT^2)`, where `PT` is the penalty in minutes for the submission and `TT` is the total contest time, in minutes. | |
| `partial_score` | int | Integer between `0` and `1` | |
| `submissions_gap` | int | Number of seconds the contestant needs to wait before resubmitting a solution. | |
| `feedback` | string | Options: `yes`, `no`, `partial` | |
| `penalty_time_start` | string | Determines how the penalty is calculated. Options: `contest`, `problem`, `none`. In the case of `contest`, the penalty for a submission starts counting from the start of the contest. `problem` indicates that the penalty is taken into account from the moment a problem is opened. `none` indicates that there will be no penalties in the contest. | |
| `penalty_calc_policy` | string | Options: `sum`, `max`. Default: | Optional |
| `private_users` | json_array[int] | Array of `user_id` of participants who can enter a private contest. | Optional |
| `problems` | array[string] | Array of `problem_alias` with the aliases of existing problems that will be used in the contest. | Optional |
| `show_scoreboard_after` | int | If `1`, the final scoreboard will be shown immediately at the end of the contest. If `0`, the scoreboard will be frozen from the time specified by the `scoreboard` parameter, even after the end of the contest. | Optional |

### Returns

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
| `status` | string | If the request was successful, returns `ok` |

## POST `contests/:contest_alias/addProblem/`

### Description
Adds a problem to a contest. The problem must have been previously created.

### Privileges
Contest director or higher.

### Parameters
| Parameter | Type | Description | Optional? |
| -------- |:-------------:| :-----|:-----|
|`problem_alias`|string|Alias ​​of the problem to add||
|`points`|int|Value of a complete solution to this problem. Typically, it is `100`, however, it can be different for each problem||
|`order_in_contest`|int|Index used to order problems relative to others in the same contest|Optional|

### Returns
| Parameter | Type | Description |
| -------- |:-------------:| :-----|
| `status` | string | If the request was successful, returns `ok`|

## POST `contests/:contest_alias/addUser/`

### Description
Adds a user to a private contest. If the contest is private and the user is not on this list, they will not be able to enter the contest.

### Privileges
Contest director or higher.

### Parameters

| Parameter | Type | Description | Optional? |
| -------- |:-------------:| :-----|:-----|
|`user_id`|int|User ID to add||

### Returns

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
| `status` | string | If the request was successful, returns `ok` |

## GET `contests/:contest_alias/clarifications/`

### Description
Returns the clarifications for a contest. If the user is a contestant, only the clarifications marked as public plus their own

Private clarifications. If the user is a contest director or admin, this API will also return private clarifications.

### Privileges
If the contest is private, the user must be on the contest's private contestant list. If the contest is public, any user can access this API.

### Parameters

| Parameter | Type | Description | Optional? |
| -------- |:-------------:| :-----|:-----|
|`offset`|int|Determines which element the request will be processed from, relative to the total number of elements. Commonly used for pagination (determines the start of the page). | Optional |
|`rowcount`|int|Determines how many elements are returned. | Optional |

### Returns
| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`clarification_id`|int|Clarification ID|
|`problem_alias`|string|Alias ​​of the problem the clarification corresponds to|
|`message`|string|Text of the clarification|
|`answer`|string|Response to the clarification|
|`time`|int|Timestamp of the last update to the clarification|
|`public`|int|`0` or `1` depending on whether the clarification is private or public|

## GET `contests/:contest_alias/scoreboard/`

### Description
Returns the contest scoreboard. If the user is a contestant, the scoreboard will be frozen as dictated by the `scoreboard` parameter when creating the contest (can be modified via Update). If the user is an administrator, they will always see the updated scoreboard.

### Privileges
If the contest is private, the user must be on the contest's private contestant list. If the contest is public, any user can access this API.

### Parameters
None

### Returns
| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`ranking`|array|Array of scoreboard details for each user, see `ranking` table|

### `ranking`
The ranking table is an ordered array with integer indices, where index 0 is the best contestant.

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`username`|string|Contestant's username|
|`total[points]`|int|Contestant's total points|
|`total[penalty]`|int|Contestant's total penalty|
|`problems`|array|Array with scoreboard details by problem. See `problems` table |

### `problems`
The problems table contains detailed score information per problem per contestant. The array index is a string corresponding to the `:problem_alias` of the problem in question. For each problem, the following information is displayed:

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`points`|int|Points for the particular problem|
|`penalty`|int|Total penalty for the particular problem|
|`wrong_runs_count`|int|Total incorrect submissions for the particular problem|

## GET `contests/:contest_alias/users/`

### Description
Returns a list of users who have entered the contest.

### Privileges
Contest director or higher.

### Parameters
None

### Returns

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`user_id`|int|User ID|
|`username`|string|User username|

## POST `contests/:contest_alias/update`

### Description
Updates the contents of a contest.

### Privileges
Contest director or higher.

### Parameters
| Parameter | Type | Description | Optional? |
| -------- |:-------------:| :-----|:-----|
| `title` |string | Contest title |Optional|
|`description` | string | Short description of the contest | Optional|
| `alias` | string | Contest alias. Its main use is to build contest URLs (see other APIs). | Optional |
| `start_time`| int | Contest start time in UNIX timestamp format if `window_length` is null. | Optional |
| `finish_time` | int | Contest end time in UNIX timestamp format if `window_length` is null. | Optional |
| `window_length` | int | If not null, the contest duration will be `window_length` in minutes, and the contest timer will be specific to each user instead of general. The timer will start when the contestant first enters the contest. `start_time` will then determine the time at which users can start opening the contest (USACO style). The default is `null`. | Optional |
| `public` | int | Determines whether the contest is public or private (`0` for private, `1` for public) | |
| `scoreboard` | int | An integer between `0` and `100` (inclusive) that determines the percentage of time the contest scoreboard will be visible to contestants. When this percentage is exceeded, the scoreboard returned is the last version that could have been made public. Administrators will always see the full scoreboard. |Optional |
| `points_decay_factor` | double | A double between `0` and `1` inclusive. If this number is non-zero, the score obtained for correctly solving a problem decays over time. The score valuewill be given by `(1 - points_decay_factor)` `+ points_decay_factor * TT^2` `/ (10 * PT^2 + TT^2)`, where `PT` is the penalty in minutes of the submission and `TT` is the total contest time, in minutes. |Optional |
| `partial_score` | int | Integer between `0` and `1` | Optional|
| `submissions_gap` | int | Number of seconds the contestant needs to wait before resubmitting a solution. |Optional |
| `feedback` | string | Options: `yes`, `no`, `partial` |Optional |
| `penalty_time_start` | string | Determines how the penalty is calculated. Options: `contest`, `problem`, `none`. In the case of a `contest`, the penalty for a submission starts counting from the start of the contest. `problem` indicates that the penalty is taken into account from the moment a problem is opened. `none` indicates that there will be no penalties in the contest. |Optional|
| `penalty_calc_policy` | string | Options: `sum`, `max`. Default: | Optional|
| `private_users` | json_array[int] | Array of `user_id` of participants who can enter a private contest. | Optional|
| `problems` | array[string] | Array of `problem_alias` with the aliases of existing problems that will be used in the contest. | Optional|
| `show_scoreboard_after` | int | If `1`, the final scoreboard will be shown immediately after the end of the contest. If `0`, the scoreboard will remain frozen from the time specified by the `scoreboard` parameter, even after the end of the contest. | Optional |

### Returns

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
| `status` | string | If the request was successful, returns `ok` |