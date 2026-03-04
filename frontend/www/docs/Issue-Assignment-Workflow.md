## Issue Assignment Workflow

This repository uses the [`takanome-dev/assign-issue-action`](https://github.com/takanome-dev/assign-issue-action) to keep issue assignments fair and active.

### Commands
- `/assign`: self-assign the issue.
- `/unassign`: remove yourself from the issue.
- The bot may auto-suggest assignment if you comment showing interest.

### Limits and eligibility
- Maximum concurrent assignments per contributor: **2**.
- `/assign` is enabled only when the issue creator has repository association `OWNER`, `MEMBER`, or `COLLABORATOR`.
- New contributors (`FIRST_TIMER`, `FIRST_TIME_CONTRIBUTOR`, `NONE`) can use `/assign` only after they have at least one merged PR in this repository.

### Tips for New Contributors

- Comment `/assign` on eligible issues to self-assign.
- Use `/unassign` if you can’t continue, so others can pick it up.
- If `/assign` is rejected, ask a maintainer to assign the issue manually.
