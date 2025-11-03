## GET `runs/:run_alias`

### Description
Returns the details of a specific run.

### Privileges
Logged-in user.

### Parameters
None

### Returns

| Parameter | Type | Description |
| ---------- |:----:| :----------- |
| `guid` | string | Unique identifier of the run. |
| `language` | string | Programming language of the submission. |
| `status` | string | Status of the problem during the grading process. Possible values: `new`, `waiting`, `compiling`, `running`, `ready`. |
| `veredict` | string | Judge’s verdict on the problem. Possible verdicts: `AC`, `PA`, `PE`, `WA`, `TLE`, `OLE`, `MLE`, `RTE`, `RFE`, `CE`, `JE`. |
| `runtime` | int | Total execution time in milliseconds taken by the submission to solve the problem’s test cases. |
| `memory` | int | Total memory used by the run to solve the test cases. |
| `score` | double | A double between `0` and `1` indicating the total number of cases solved, where `1` means all cases were solved. |
| `contest_score` | int | Weighted score of the run (the score shown on the scoreboard). |
| `time` | int | Submission time of the run in UNIX timestamp format. |
| `submit_delay` | int | Number of minutes elapsed from the start of the contest until the run was submitted. |
| `source` | string | Source code of the corresponding run. |

---

## GET `runs/:run_alias/adminDetails`

### Description
Returns complete details of a run for the contest administrator, including a diff between the official test cases and the outputs produced by the run.

### Privileges
Contest administrator or higher.

### Parameters
None

### Returns
**Pending**

---

## POST `runs/create`

### Description
Creates a new run for a problem **within a contest**.

### Privileges
Logged-in user.

### Parameters

| Parameter | Type | Description | Optional? |
| ---------- |:----:| :----------- | :---------: |
| `problem_alias` | string | Alias of the problem. |  |
| `contest_alias` | string | Alias of the contest. |  |
| `language` | string | Programming language used for the solution. Possible values: `kp`, `kj`, `c`, `cpp`, `java`, `py`, `rb`, `pl`, `cs`, `p`. |  |
| `source` | string | Source code of the solution. |  |

### Returns

| Parameter | Type | Description |
| ---------- |:----:| :----------- |
| `status` | string | If the request was successful, returns `ok`. |

---

## GET `runs/:run_alias/source`

### Description
Returns the source code of a run. If the code failed to compile, it returns the compilation error.

### Privileges
Logged-in user.

### Parameters
None

### Returns

| Parameter | Type | Description |
| ---------- |:----:| :----------- |
| `status` | string | If the request was successful, returns `ok`. |
| `source` | string | Source code of the problem. |
| `compile_error` | string | Compilation error, if it exists. |
