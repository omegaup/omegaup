# Clarification

Controller for handling clarifications in contests and course assignments.
Clarifications allow users to ask questions about problems, and administrators can respond with answers.
Clarifications can be public (visible to all participants) or private (visible only to the author and receiver).

## `/api/clarification/create/`

### Description

Creates a new clarification for a problem in a contest or an assignment of a course.
Clarifications are created as private by default unless the receiver is the same as the author.
The user must be logged in and have access to the contest or course assignment.
Contest administrators or course administrators/teaching assistants will be notified of the clarification request.

### Parameters

| Name | Type | Description |
|------|------|-------------|
| `message` | `string` | Clarification content (1-200 characters) |
| `problem_alias` | `string` | Problem alias |
| `assignment_alias` | `string\|null` | Assignment alias (required if course_alias is provided) |
| `contest_alias` | `string\|null` | Contest alias (required if course_alias is not provided) |
| `course_alias` | `string\|null` | Course alias (required if contest_alias is not provided) |
| `username` | `null\|string` | Username of the intended receiver (optional, for private clarifications) |
### Returns

```typescript
types.Clarification
```

## `/api/clarification/details/`

### Description

Returns the details of a clarification for a problem in a contest or course assignment.
The user must be logged in and have access to view the clarification.
Private clarifications can only be viewed by the author, receiver, or contest/course administrators.

### Parameters

| Name | Type | Description |
|------|------|-------------|
| `clarification_id` | `int` | The ID of the clarification to retrieve |
### Returns

| Name | Type |
|------|------|
| `answer` | `string` |
| `message` | `string` |
| `problem_id` | `number` |
| `problemset_id` | `number` |
| `time` | `Date` |
## `/api/clarification/update/`

### Description

Updates the contents of a clarification for a problem in a contest or course assignment.
Only contest administrators, course administrators, or teaching assistants can update clarifications.
When an answer is provided, the clarification author will be notified.
The clarification timestamp is automatically updated to the current time.

### Parameters

| Name | Type | Description |
|------|------|-------------|
| `clarification_id` | `int` | The ID of the clarification to update |
| `answer` | `null\|string` | The answer to the clarification (optional) |
| `message` | `null\|string` | The clarification message content (optional) |
| `public` | `bool\|null` | Whether the clarification should be public (optional) |
### Returns

