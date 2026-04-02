# Contributing to omegaUp

Thank you for your interest in contributing to omegaUp!   
This document explains how to get started as a contributor, follow project workflows, and submit changes for review.

---

## Before You Start

If you are not confident using Git, we recommend reading an introductory Git tutorial before contributing:

- https://docs.github.com/en/get-started/using-git

To set up the development environment, run the project locally, and troubleshoot issues, please refer to:

📘 **Development Environment Setup Guide**  
- [`frontend/www/docs/Development-Environment-Setup-Process.md`](frontend/www/docs/Development-Environment-Setup-Process.md)

This guide contains detailed instructions for Docker setup, running tests, authentication, and troubleshooting.

---

## Issue Assignment Workflow

omegaUp uses a custom automated issue assignment workflow (`.github/workflows/assign-issues.yml`) to keep contributions fair and active.

- Use `/assign` to self-assign an issue
- You may have up to **2 active assignments** at a time
- `/assign` is available only when the issue creator has repository association `OWNER`, `MEMBER`, or `COLLABORATOR`
- First-time contributors (`FIRST_TIMER`, `FIRST_TIME_CONTRIBUTOR`, `NONE`) can use `/assign` only after they have at least one merged PR in this repository
- Use `/unassign` to remove yourself from an issue

📘 For full details, see:  
- [`frontend/www/docs/Issue-Assignment-Workflow.md`](frontend/www/docs/Issue-Assignment-Workflow.md)

---

## Working on an Issue

1. Fork the repository  
   https://github.com/omegaup/omegaup
2. Create a new branch for your changes
3. Make focused, incremental commits
4. Open a Draft PR if work is in progress

Branch naming examples:
- `docs/add-contributing-md`
- `fix/login-validation`
- `feat/problem-editor-ui`

---

## Commit Message Guidelines

Use clear and descriptive commit messages:

- `docs: add CONTRIBUTING.md`
- `fix: correct submission validation`
- `feat: improve problem editor UX`

Follow conventional commit-style prefixes where possible.

---

## Pull Request Process

- Open Pull Requests against the `main` branch
- Link the related issue in the PR description (e.g. `Fixes #162`)
- Use Draft PRs for work in progress
- Ensure CI checks pass before requesting review
- Keep PRs focused and avoid unrelated refactors

For more information on Pull Requests, see:
- https://docs.github.com/en/pull-requests

---

## Documentation and Translations

- Documentation improvements are welcome
- Avoid duplicating existing documentation
- Translation changes should follow existing localization patterns found in:
  - `frontend/templates`

---

## Getting Help

If you need help:

- Ask questions in the issue you’re working on  
  https://github.com/omegaup/omegaup/issues
- Open a new issue if clarification is needed  
  https://github.com/omegaup/omegaup/issues/new

We appreciate your contributions and collaboration 



## First-Time Contributor Guide

Welcome to omegaUp! If you're contributing for the first time, follow this structured guide to avoid confusion.

1️⃣ Understand the Project
- Read `README.md` to understand the project overview.
- Carefully review this `CONTRIBUTING.md` file.
- Check open issues labeled `documentation`, `good first issue`, or `help wanted`.

2️⃣ Fork and Prepare
- Fork the repository to your GitHub account.
- It is recommended to create a new branch for your changes.
- Make sure your fork is up to date with the main repository.

3️⃣ Make Your Changes
- Keep changes focused on a single issue.
- Follow existing formatting and coding style.
- Write clear commit messages (example: `docs: improve onboarding clarity`).

4️⃣ Test and Review
- Re-read your changes before committing.
- Ensure links and formatting work correctly.

5️⃣ Open a Pull Request
- Go to your fork and click **Compare & Pull Request**.
- Clearly describe what you changed and why.
- Reference the issue number (example: `Closes #8766`).

6️⃣ Respond to Feedback
- Be open to suggestions.
- Make requested changes promptly.
- Engage respectfully in discussions.

Following these steps will help ensure a smooth and successful contribution experience 🎉
