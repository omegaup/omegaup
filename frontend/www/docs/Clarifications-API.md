## POST `clarification/create`

### Description
Creates a new clarification for a problem **in a contest**. Clarifications are created as private by default.

### Privileges
Logged-in user.

### Parameters

| Parameter | Type | Description | Optional? |
| ---------- |:-------------:| :---------- | :--------- |
| `contest_alias` | string | Alias of the contest |  |
| `problem_alias` | string | Alias of the problem |  |
| `message` | string | Content of the clarification |  |

### Returns

| Parameter | Type | Description |
| ---------- |:-------------:| :---------- |
| `status` | string | If the request was successful, returns `ok` | 
| `clarification_id` | int | ID of the newly created clarification |


## GET `clarifications/:clarification_id`

### Description
Returns the details of a clarification for a problem **in a contest**.

### Privileges
Logged-in user with access to the contest.

### Parameters
None

### Returns

| Parameter | Type | Description |
| ---------- |:-------------:| :---------- |
| `message` | string | The message of the clarification | 
| `answer` | string | The answer to the clarification |
| `time` | datetime | The date and time of the last modification to the clarification |
| `problem_id` | int | Problem ID |
| `contest_id` | int | Contest ID |


## POST `clarifications/:clarification_id/update`

### Description
Updates the content of a clarification for a problem **in a contest**.

### Privileges
Contest administrator or higher.

### Parameters

| Parameter | Type | Description | Optional? |
| ---------- |:-------------:| :---------- | :--------- |
| `contest_alias` | string | Alias of the contest | Optional |
| `problem_alias` | string | Alias of the problem | Optional |
| `message` | string | Content of the clarification | Optional |

### Returns

| Parameter | Type | Description |
| ---------- |:-------------:| :---------- |
| `status` | string | If the request was successful, returns `ok` |
