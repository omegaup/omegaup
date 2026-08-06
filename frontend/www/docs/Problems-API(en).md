# Problem API Documentation

## POST `problem/create`

### Description
Creates a new problem.

### Privileges
- Must be a logged-in user.

### Parameters

| Parameter          | Type   | Description                                                                                                 | Optional? |
|--------------------|--------|-------------------------------------------------------------------------------------------------------------|-----------|
| `author_username`  | string | Username of the user who originally wrote the problem                                                       | No        |
| `title`            | string | Title of the problem                                                                                        | No        |
| `alias`            | string | Short alias for the problem                                                                                 | No        |
| `source`           | string | Source of the problem (UVA, OMI, etc.)                                                                       | No        |
| `public`           | int    | `0` for private problem, `1` for public problem                                                              | No        |
| `validator`        | string | Defines how contestants’ outputs are compared to the official outputs. See **Validators** table below.      | No        |
| `time_limit`       | int    | Time limit in milliseconds for each case of the problem (TLE)                                               | No        |
| `memory_limit`     | int    | Memory limit in KB for each case of the problem (MLE)                                                        | No        |
| `order`            | string | (Reserved for ordering information)                                                                         | Yes       |
| `problem_contents` | FILE   | A ZIP file containing the problem’s contents ([How to write problems for omegaup](Cómo-escribir-problemas-para-Omegaup)) | No        |

#### Validators

| Type              | Description |
|-------------------|-------------|
| `literal`         |             |
| `token`           |             |
| `token-caseless`  |             |
| `token-numeric`   |             |

### Returns

| Parameter         | Type         | Description                                         |
|-------------------|--------------|-----------------------------------------------------|
| `status`          | string       | If successful, returns `ok`                         |
| `uploaded_files`  | array[string]| List of files that were unpacked from the ZIP file  |


---

## GET `problems/:problem_alias`

### Description
Returns the details of a problem **inside a contest**.

### Privileges
- Must be a logged-in user.
- If the contest is private, the user must be invited.

### Parameters

| Parameter        | Type   | Description                             | Optional? |
|------------------|--------|-----------------------------------------|-----------|
| `contest_alias`  | string | Contest alias                           | No        |
| `lang`           | string | Contest language (default: `es`)        | Yes       |

### Returns

| Parameter       | Type     | Description                                                                 |
|-----------------|----------|-----------------------------------------------------------------------------|
| `title`         | string   | Title of the problem                                                        |
| `author_id`     | int      | Problem author ID                                                           |
| `validator`     | string   | Validator type (see **Validators** table)                                   |
| `time_limit`    | int      | Time limit in milliseconds                                                  |
| `memory_limit`  | int      | Memory limit in KB                                                           |
| `visits`        | int      | Total visits to this problem                                                 |
| `submissions`   | int      | Total submissions across all contests                                        |
| `accepted`      | int      | Total accepted submissions (AC) across all contests                          |
| `difficulty`    | int      | Problem difficulty as determined by Omegaup                                 |
| `creation_date` | datetime | Date when the problem was created                                            |
| `source`        | string   | Source of the problem                                                        |
| `runs`          | array    | List of all runs by the contestant for this problem (see **Runs** table)     |

#### Runs

| Parameter       | Type     | Description                                                                                   |
|-----------------|----------|-----------------------------------------------------------------------------------------------|
| `guid`          | string   | Run ID                                                                                        |
| `language`      | string   | Submission language                                                                           |
| `status`        | string   | Status in the grading process (`new`, `waiting`, `compiling`, `running`, `ready`)             |
| `veredict`      | string   | Judge's verdict (`AC`, `PA`, `PE`, `WA`, `TLE`, `OLE`, `MLE`, `RTE`, `RFE`, `CE`, `JE`)        |
| `runtime`       | int      | Total execution time in milliseconds                                                          |
| `memory`        | int      | Total memory used in KB                                                                       |
| `score`         | double   | Between `0` and `1` — proportion of test cases solved (`1` = all cases solved)                 |
| `contest_score` | int      | Weighted score shown on the scoreboard                                                        |
| `time`          | datetime | Time of submission                                                                            |
| `submit_delay`  | int      | Minutes since the contest started until the run was submitted                                 |
