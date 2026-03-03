## Issue Assignment Workflow

This repository uses the [`takanome-dev/assign-issue-action`](https://github.com/takanome-dev/assign-issue-action) to keep issue assignments fair and active.

### Commands
- `/assign`: self-assign the issue.
- `/unassign`: remove yourself from the issue.
- The bot may auto-suggest assignment if you comment showing interest.

### Limits and deadlines
- Maximum concurrent assignments per contributor: **5**.
- You must open a PR (draft is fine) within **7 days** of assignment.
- A reminder is posted roughly halfway (~3.5 days) before auto-unassignment.
- If no PR is opened by day 7, you are automatically unassigned and blocked from self-reassigning; ask a maintainer if you need it back.

### Experienced Issue Creators

- Contributors who create an issue and have at least **10 merged PRs** may self-assign the issue without any assignment limit.
- The existing **7-day inactivity auto-unassign rule** remains unchanged.
- When assigning themselves to issues created by others, the standard maximum of **5 active assignments** still applies.

### Tips for New Contributors

- Comment `/assign`, then open a PR (draft is OK) within **7 days** to keep the slot.
- Use `/unassign` if you can’t continue, so others can pick it up.
- If you need more time, ask a maintainer to add the `📌 Pinned` label.
