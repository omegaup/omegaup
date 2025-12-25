# Description

Add frontend components for enhanced user profile statistics display.

This PR introduces:
- **ProblemSolvingProgress.vue** - Circular progress chart showing:
  - Total solved problems count
  - Difficulty breakdown (easy/medium/hard/unlabelled) with color-coded segments
  - Interactive hover states with tooltips
- **TagsSolvedChart.vue** - Bar chart showing:
  - Tag distribution for solved problems
  - Problem counts per tag with hover tooltips
- **Translation keys** for all profile statistics labels (10 keys in 4 languages)
- **API integration** via `api.ts` and `api_types.ts`
- **ViewProfile.vue updates** to include the new components

![Profile Statistics UI](Add screenshot here)

**Part 2 of 2:** Frontend components and translations. Depends on the backend API from PR 1.

Fixes: #8656

# Comments

This is the second of two PRs to implement the Enhanced User Profile Statistics feature:

1. **PR 1 (merged/pending):** Backend API + DAO + Tests + Docs
2. **PR 2 (this one):** Frontend Vue components + Translation keys

> **Note:** This PR should be rebased on `main` after PR 1 is merged:
> ```bash
> git fetch origin && git rebase origin/main && git push --force-with-lease
> ```

# Checklist:

- [x] The code follows the [coding guidelines](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Coding-guidelines.md) of omegaUp.
- [x] The tests were executed and all of them passed.
- [x] If you are creating a feature, the new tests were added.
- [x] If the change is large (> 200 lines), this PR was split into various Pull Requests. It's preferred to create one PR for changes in controllers + unit tests in PHPUnit, and then another Pull Request for UI + tests in Jest, Cypress or both.
