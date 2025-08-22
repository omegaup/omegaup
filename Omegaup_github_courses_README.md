# omegaUp GitHub Course Management

## Overview
This feature allows managing **public courses** on omegaUp through GitHub.

## Features
- Fetches course content from omegaUp API.
- Allows users to submit course improvements via PRs.
- Automatically updates courses using GitHub Actions.

## How It Works
1. The script `fetch_courses.py` pulls courses from the omegaUp API and saves them in Markdown format.
2. Contributors can suggest improvements via **GitHub Pull Requests**.
3. The **GitHub Actions workflow** runs daily to update course content.

## Running Locally
To test the scripts locally:
```sh
export OMEGAUP_TOKEN="your_api_key"
python scripts/fetch_courses.py
