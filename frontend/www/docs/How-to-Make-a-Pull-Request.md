## Contents

- [Development Process](#development-process)
- [Issue Assignment Requirement](#issue-assignment-requirement)
- [Setting Up the omegaUp Fork and Remotes](#setting-up-the-omegaup-fork-and-remotes)
- [Updating Your main Branch](#updating-your-main-branch)
- [Additional Settings](#additional-settings)
- [Starting a New Change](#starting-a-new-change)
- [Upload your changes and make a new Pull Request](#upload-your-changes-and-make-a-new-pull-request)
- [Deleting a Branch](#deleting-a-branch)
- [What Happens After Submitting My Pull Request](#what-happens-after-submitting-my-pull-request)

# Development process

After forking the omegaUp repository, the `main` branch in your repository should always be kept up to date with the `main` branch of the omegaUp repository, which contains the latest changes approved by the review team. For this reason, you should avoid committing directly to `main`. Instead, create a separate branch for each change you plan to submit via a Pull Request.

Once you have [set up your development environment](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md) follow these steps:

# Issue Assignment Requirement

**Important:** Before opening a Pull Request, you must ensure that:

1. **Your PR is linked to an existing issue**: Every PR must reference an issue using closing keywords (e.g., `Fixes #1234` or `Closes #1234`) in the PR description.

2. **The issue is assigned to you**: The linked issue must be explicitly assigned to you (the PR author) before you open the PR.

If either of these conditions is not met, an automated check will fail on your PR. To resolve this:

- If you don't have an issue to link, first [create a new issue](https://github.com/omegaup/omegaup/issues/new) describing the bug fix or feature you want to implement.
- Comment on the issue expressing your interest in working on it and wait for a maintainer to assign it to you.
- Once the issue is assigned to you, you can proceed to open your Pull Request.

This process helps us track contributions effectively and ensures that work is properly coordinated among contributors.

# Setting Up the omegaUp Fork and Remotes

You only need to perform these configurations once.

- Visit the URL https://github.com/omegaup/omegaup and click on the following button to fork your own copy of `omegaup/omegaup`:![Screenshot from 2025-03-13 20-18-14](https://github.com/user-attachments/assets/967a0cfe-6ef0-49c2-9b77-3c24b939ef51)

- Then, you can clone the repository into your development environment. To do this, copy the cloning URL as follows:![Screenshot from 2025-03-13 20-18-46](https://github.com/user-attachments/assets/d53c2a8c-5354-4b15-86e9-69b7a13afe55)

- From your virtual machine, run the following command:

```bash
git clone https://github.com/omegaup/omegaup.git
```

- Now you can navigate to the directory where the repository was cloned:

```bash
cd omegaup
```

- Check if you have already configured the omegaUp remote repository:

```bash
git remote -v
```

- You should see something similar to this:

```bash
origin        https://github.com/omegaup/omegaup.git (fetch)
origin        https://github.com/omegaup/omegaup.git (push)
```

- If not, you only need to run the following command once to configure origin and be able to fetch changes:

```bash
git remote add origin https://github.com/omegaup/omegaup.git
```

- Now you can add the remote for your forked repository:

```bash
git remote add [remote-name] https://github.com/[username]/omegaup

```

- Finally you should see something similar to:

```bash
$ git remote -v
origin	https://github.com/omegaup/omegaup.git (fetch)
origin	https://github.com/omegaup/omegaup.git (push)
upstream	https://github.com/[username]/omegaup.git (fetch)
upstream	https://github.com/[username]/omegaup.git (push)
```

# Updating Your main Branch

It is recommended that you avoid making changes directly in the `main` branch, as it is very difficult to return it to a clean state once your changes have been merged. However, it is still a good idea to update it from time to time:

```bash
git checkout main  # Switch back to main if you were on another branch
git fetch origin   # Fetch the latest changes from omegaup/main
git pull --rebase origin main  # Sync your main branch with omegaup/main
git push upstream
```

If `git push upstream` fails, it means you broke the rule of not making changes in `main`. Try running `git push upstream -f`.

# Additional Settings

- The virtual machine does not have `en_US.UTF-8` as the default language. To update it you have to do what is described in [this link](https://askubuntu.com/questions/881742/locale-cannot-set-lc-ctype-to-default-locale-no-such-file-or-directory-locale/893586#893586).

- At first, there are many missing dependencies. You need to run `composer install` inside the VM to install them.

- If you encounter the following error when trying to push changes:

```bash
FileNotFoundError: [Errno 2] No such file or directory: 'mysql'
error: failed to push some refs to 'https://github.com/[username]/omegaup.git'
```

Install `mysql-client` and `mysql-server` outside the container using:

```bash
sudo apt install mysql-client mysql-server
```

Then, add the following configuration:

```bash
cat > ~/.mysql.docker.cnf <<EOF
[client]
port=13306
host=127.0.0.1
protocol=tcp
user=root
password=omegaup
EOF
ln -sf ~/.mysql.docker.cnf .my.cnf
```

# Starting a New Change

We invite you to follow our [Coding Guidelines](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Coding-guidelines.md) for contributing to our project. By adhering to them, your changes will be easier to review and integrate into production.

**Before making any modifications** run the following commands to create a new branch that is synchronized with `omegaup`:

```bash
git checkout -b feature-name origin/main # Creates a new branch and syncs it with omegaUp
git push upstream feature-name # Pushes your changes to GitHub
```

# Upload your changes and make a new Pull Request

Once you have made the changes you want to propose, follow these steps:

- Add your changes to a new commit:

```bash
git add .
```

- Commit your changes:

```bash
git commit -m "Write a description for your committed changes."
```

- **Run the validators**: Before pushing your changes, we strongly recommend running the following command:

```bash
./stuff/lint.sh
```

This command will align elements, remove unnecessary lines, and perform extra validations for each of the languages used in omegaUp.

While this command should not be necessary if you follow the development process correctly, it is particularly useful if you are using external tools that might bypass the pre-push hooks. These hooks include this script, so running it manually ensures your changes meet the project's standards.

- Set up your user information (You only need to enter this information the first time you push):

```bash
    git config --global user.email "your-email@domain.com"`
    git config --global user.name "<your-username>"`
```

- Push from your commit to your fork:

```bash
git push -u upstream
```

- Go to https://github.com/[username]/omegaup, click on the button _Branch_ [0] and select the branch you made changes _newfeaturename_[1.1]. After click on _Pull request_[2]:

![Select Branch and Pull Request](https://i.ibb.co/0Dd1ngf/Select-Branch-Own-Repository.png)
Alternatively, if you are in the omegaUp repository or yours, you can press the [1.2] button and then press the _Pull request_ [2] button.

- Fill in the required information and press the "Create Pull request" button
  ![Create Pull Request](https://i.ibb.co/KzJYC2D/Create-Pull-Request.png)
  It's important you mention the issue ID this change is fixing in the PR description:

```bash
Fixes: #1234
```

This way, once the change is approved and merged with the main branch, the issue will be automatically closed.

If you have to make more changes after making the Pull Request, follow this steps:

- Add your changes to a commit:

```bash
git add .
```

- Add your commit or commits:

```bash
git commit -m" Put a description of the changes here. "
```

- Push from your commit to your fork:

```bash
git push
```

As you can see, in the last command, the `-u` flag is no longer needed because your branch is now linked to a defined upstream.

# Deleting a Branch

You can see the branches that you have in your local copy by running

```bash
git branch
```

You can switch between branches by doing

```bash
git checkout branchname
```

If we have already merged a Pull Request, you can clean your local branch with

```bash
git branch -D branchname
```

But you also have to go to GitHub and delete the branch there (by clicking on" Branches ") or directly on the Pull Request.

![Delete Branch](https://i.ibb.co/99PMQC6/Delete-Branch-Git.png)

After this, you will still see the remote branch if you run the command `git branch -a`. You will find a line similar to:

```
remotes/upstream/newfeaturename
```

You can delete it in the following way:

- Enter the following command

```bash
git remote prune upstream --dry-run
```

Then it will ask you to enter your credentials. You will see something similar to this:

```bash
[would prune] upstream/newfeaturename
```

- Now enter the command

```bash
git remote prune upstream
```

Re-enter your credentials if needed. You will see something similar to this:

```
Pruning upstream
URL: git@github.com: [username]/omegaup.git
* [pruned] upstream/newfeaturename
```

If you run the command

```bash
git branch -a
```

Again you will notice that everything related to `newfeaturename` has already removed.

# What Happens After Submitting My Pull Request

- Make sure all tests have passed.
- Wait for a member of omegaUp to review your change.
- Address any comments made during the review.
- Has your PR been merged? Wait for the weekend deployment to see your changes in production.

You might be interested in the following topics:

- [Coding guidelines](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Coding-guidelines.md).
- [Useful development commands](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Useful-Commands-for-Development.md).
- [How to use Cypress in omegaUp](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-use-Cypress-in-omegaUp.md).
