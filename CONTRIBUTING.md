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

omegaUp uses an automated issue assignment workflow to keep contributions fair and active.

- Use `/assign` to self-assign an issue
- You may have up to **3 active assignments** at a time
- A Pull Request (**Draft PR is acceptable**) must be opened within **7 days** of assignment
- Issues without PR activity may be automatically unassigned

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
