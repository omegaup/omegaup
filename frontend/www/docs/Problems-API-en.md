## POST `problem/create`

### Description

Creates a new problem.

### Privileges

Logged-in user

### Parameters

| Parameter          |  Type  | Description                                                                                                        | Optional? |
| ------------------ | :----: | :----------------------------------------------------------------------------------------------------------------- | :-------- |
| `author_username`  | string | Username of the user who originally wrote the problem                                                              |           |
| `title`            | string | Title of the problem                                                                                               |           |
| `alias`            | string | Short alias of the problem                                                                                         |           |
| `source`           | string | Source of the problem (UVA, OMI, etc..)                                                                            |           |
| `public`           |  int   | `0` if the problem is private. `1` if the problem is public                                                        |           |
| `validator`        | string | Defines how contestants' outputs are going to be compared with the official outputs. See the **validators** table  |           |
| `time_limit`       |  int   | Execution time limit for each test case of the problem in milliseconds. (TLE)                                      |           |
| `memory_limit`     |  int   | Execution memory limit for each test case of the problem in KB (MLE)                                               |           |
| `order`            | string |                                                                                                                    |           |
| `problem_contents` |  FILE  | A ZIP file containing the problem contents: [How to write problems for omegaup](How-to-write-problems-for-Omegaup) |           |

#### Validators

| Type             | Description |
| ---------------- | :---------- |
| `literal`        |             |
| `token`          |             |
| `token-caseless` |             |
| `token-numeric`  |             |

### Returns

| Parameter        |     Type      | Description                                 |
| ---------------- | :-----------: | :------------------------------------------ | --- |
| `status`         |    string     | If the request was successful, returns `ok` |
| `uploaded_files` | array[]string | Array of files that were unpacked           |     |

## GET `problems/:problem_alias`

### Description

Returns the details of a problem **inside a contest**.

### Privileges

Logged-in user. If the contest is private, the user requires to be invited.

### Parameters

| Parameter       |  Type  | Description                              | Optional? |
| --------------- | :----: | :--------------------------------------- | :-------- |
| `contest_alias` | string | Alias of the contest                     |           |
| `lang`          | string | Language of the contest. Default is `es` | Optional  |

### Returns

| Parameter       |   Type   | Description                                                                        |
| --------------- | :------: | :--------------------------------------------------------------------------------- |
| `title`         |  string  | Title of the problem                                                               |
| `author_id`     |   int    | Author of the problem                                                              |
| `validator`     |  string  | Validator of the problem. See **validators** table                                 |
| `time_limit`    |   int    | Execution time limit in milliseconds                                               |
| `memory_limit`  |   int    | Memory limit in KB                                                                 |
| `visits`        |   int    | Total visits to this problem                                                       |
| `submissions`   |   int    | Total submissions for this problem across all contests                             |
| `accepted`      |   int    | Total accepted submissions (AC) for this problem across all contests               |
| `difficulty`    |   int    | Difficulty of the problem determined by Omegaup                                    |
| `creation_date` | datetime | Creation date of the problem                                                       |
| `source`        |  string  | Source of the problem.                                                             |
| `runs`          |  array   | Returns an array with all the contestant's runs for this problem. See `runs` table |

#### Runs

| Parameter       |   Type   | Description                                                                                                    |
| --------------- | :------: | :------------------------------------------------------------------------------------------------------------- |
| `guid`          |  string  | Run identification                                                                                             |
| `language`      |  string  | Language of the submission.                                                                                    |
| `status`        |  string  | Status of the problem in the grading process. Possible values: 'new','waiting','compiling','running','ready'   |
| `veredict`      |  string  | Judge's verdict on the problem. Possible verdicts: 'AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE' |
| `runtime`       |   int    | Total execution time in milliseconds that the submission took to solve the problem cases.                      |
| `memory`        |   int    | Total memory used by the run to solve the test cases.                                                          |
| `score`         |  double  | Double between `0` and `1` indicating the total solved cases, where `1` means all cases were solved.           |
| `contest_score` |   int    | Weighted score of the run. This is the score shown on the scoreboard.                                          |
| `time`          | datetime | Submission time of the run                                                                                     |
| `submit_delay`  |   int    | Minutes passed from the start of the contest until the run was submitted.                                      |
