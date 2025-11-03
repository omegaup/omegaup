## POST `clarification/create`

### Description
Creates a new clarification for a problem **in a contest**. Clarifications are created as private by default.

### Privileges
Logged-in user.

### Parameters

| Parameter | Type | Description  | Optional? |
| -------- |:-------------:| :-----|:-----|
|`contest_alias`|string|Contest alias||
|`problem_alias`|string|Problem alias||
|`message`|string|Clarification content||

### Returns

| Parameter | Type | Description  |
| -------- |:-------------:| :-----|
|`status`|string|If the request was successful, returns `ok`| 
|`clarification_id`|int|ID of the newly submitted clarification|

## GET clarifications/:clarification_id

#### Description
Returns the details of a clarification for a problem **in a contest**.

#### Privileges
Logged-in user with access to the contest.

### Parameters
None

### Returns

| Parameter | Type | Description  |
| -------- |:-------------:| :-----|
|`message`|string|Clarification message| 
|`answer`|string|Clarification answer|
|`time`|datetime|Date of the last modification to the clarification|
|`problem_id`|int|Problem ID|
|`contest_id`|int|Contest ID|


## POST clarifications/:clarification_id/update

### Description
Update the contents of a clarification for a problem **in a contest**.

### Privileges
Contest administrator or higher

### Parameters
| Parameter | Type | Description  | Optional? |
| -------- |:-------------:| :-----|:-----|
|`contest_alias`|string|Contest alias|Optional|
|`problem_alias`|string|Problem alias|Optional|
|`message`|string|Clarification content|Optional|

### Returns

| Parameter | Type | Description  |
| -------- |:-------------:| :-----|
|`status`|string|If the request was successful, returns `ok`| 

