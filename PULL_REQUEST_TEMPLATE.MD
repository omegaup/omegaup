# Description

Please include a description of the changes you have made. The text you put in this section will be used as the **commit message** when this PR is merged.

If you modify the **User Interface**, please include a screenshot or a video.

Fixes: #(issue)

# Comments

Add any comments for the reviewers (e.g., indicating the beginning of the revision, something where the reviewer needs to pay special attention, etc.). If  there are no extra comments this section can be deleted.

# Checklist:

- [ ] The code follows the [coding guidelines](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Coding-guidelines.md) of omegaUp.
- [ ] The tests were executed and all of them passed.
- [ ] If you are creating a feature, the new tests were added.
- [ ] If the change is large (> 300 lines), this PR was split into various Pull Requests. It's preferred to create one PR for changes in controllers + unit tests in PHPUnit,  and then another Pull Request for UI + tests in Jest, Cypress or both.
