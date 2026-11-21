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

If you plan to use AI tools to assist with your contributions, please review:

📘 **AI Contribution Policy**  
- [`AI_CONTRIBUTION_POLICY.md`](AI_CONTRIBUTION_POLICY.md)

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
- Follow the [AI Contribution Policy](AI_CONTRIBUTION_POLICY.md) if your changes involved AI assistance
- Use Draft PRs for work in progress
- Ensure CI checks pass before requesting review
- Keep PRs focused and avoid unrelated refactors

For more information on Pull Requests, see:
- https://docs.github.com/en/pull-requests

---

## AI Contribution Policy

Contributors are welcome to use AI tools responsibly to assist with their contributions:

- AI should be treated as an assistance tool, not as a replacement for your own judgment or responsibility
- Contributors remain fully responsible for the code, documentation, and communication they submit
- Disclose significant AI assistance in your pull request description
- Human review, testing, and evidence expectations apply equally to all contributions

📘 For full details, see:  
- [`AI_CONTRIBUTION_POLICY.md`](AI_CONTRIBUTION_POLICY.md)

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
