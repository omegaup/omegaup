## GET `runs/:run_alias`

### Description
Returns the details of a specific run.

### Privileges
Logged-in user. 

### Parameters
None

### Returns

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`guid`|string|Run identification|
|`language`|string|Language of the submission.|
|`status`|string|Status of the problem in the grading process. Possible values: 'new', 'waiting', 'compiling', 'running', 'ready'|
|`veredict`|string|Verdict of the judge on the problem. Possible verdicts: 'AC', 'PA', 'PE', 'WA', 'TLE', 'OLE', 'MLE', 'RTE', 'RFE', 'CE', 'JE'|
|`runtime`|int|Total execution time in milliseconds that the submission took to solve the problem's cases.|
|`memory`|int|Total memory used by the run to solve the test cases.|
|`score`|double|Double between `0` and `1` indicating the total cases solved, where `1` means all cases were solved.|
|`contest_score`|int|Weighted score of the run. It is the score shown on the scoreboard.|
|`time`|int|Run submission time in UNIX timestamp format|
|`submit_delay`|int|Minutes passed from the start of the contest until the run was submitted.|
|`source`|string|Source code of the run in question|


## GET `runs/:run_alias/adminDetails`

### Description
Returns the complete details of the run of interest for the contest administrator, including a diff between the official cases and the outputs produced by the run.

### Privileges
Contest administrator or higher.

### Parameters
None

### Returns
**Pending**

## POST `runs/create`

### Description
Creates a new run for a problem **in a contest**.

### Privileges
Logged-in user. 

### Parameters

| Parameter | Type | Description | Optional? |
| -------- |:-------------:| :-----|:-----|
|`problem_alias`|string|Problem alias||
|`contest_alias`|string|Contest alias||
|`language`|string|Programming language used for the solution. Possible values: 'kp', 'kj', 'c', 'cpp', 'java', 'py', 'rb', 'pl', 'cs', 'p'||
|`source`|string|Source code of the solution||

### Returns

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`status`|string|If the request was successful, returns `ok`| 

## GET `runs/:run_alias/source`

### Description
Returns the source code of a run. If the code did not compile, it returns the compilation error.

### Privileges
Logged-in user. 

### Parameters
None

### Returns

| Parameter | Type | Description |
| -------- |:-------------:| :-----|
|`status`|string|If the request was successful, returns `ok`| 
|`source`|string|Source code of the problem|
|`compile_error`|string|Compilation error, if it exists.|
