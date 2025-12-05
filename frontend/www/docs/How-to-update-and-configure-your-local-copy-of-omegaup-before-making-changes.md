Once you have **[installed the virtual machine](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md)**, follow these instructions.  
(Remote configurations only need to be done once.)

# Configuring the omegaUp Remote

* Fork the repository from `https://github.com/omegaup/omegaup` to `https://github.com/<your-username>/omegaup` by clicking the **"Fork"** button:![](https://image.ibb.co/k3Oh9v/Screenshot_from_2017_08_06_22_10_12.png)

Inside the virtual machine, go to the directory where the code is located:

`cd /opt/omegaup`

Check if you already have the `omegaUp` remote configured:

`git remote -v`

You should see something like this:
```
upstream        https://github.com/omegaup/omegaup.git (fetch)
upstream        https://github.com/omegaup/omegaup.git (push)
```

If not, run the following command **once** to set up the `upstream` remote so you can download updates:

`git remote add upstream https://github.com/omegaup/omegaup.git`


# Updating Your `main` Branch

It is recommended that you **do not** make changes directly in `main`, because it is hard to return it to a clean state after your changes are merged.  
Still, it’s a good idea to update it occasionally:

* `git checkout main` — switches back to `main` if you were on another branch  
* `git fetch upstream` — downloads the latest changes from `omegaup/main`  
* `git pull --rebase upstream main` — synchronizes your local `main` with `omegaup/main`  
* `git push pr`

If `git push` fails, it means you accidentally modified `main` 😅  
Try running: `git push -f`

# Additional Setup

- The virtual machine does not include `en_US.UTF-8` as the default locale. To fix this, follow the steps described in this link:  
  https://askubuntu.com/questions/881742/locale-cannot-set-lc-ctype-to-default-locale-no-such-file-or-directory-locale/893586#893586

- At the beginning, many dependencies are not installed yet, so you need to run: `composer install`.

- To avoid certain errors that may result in a blank page when visiting `localhost:8080`, it is important to run: `yarn install && yarn run dev`

# Before Making Changes

**Before starting any modifications**, run these commands to create a new branch synchronized with `omegaUp`:

* `git checkout main` — switch to the main branch  
* `git fetch upstream` — download updates from `omegaup/main`  
* `git checkout -b nameofthefeatureyouwanttodo upstream/main` — create a new branch synced with omegaUp  
* `git push pr nameofthefeatureyouwanttodo` — push your new branch to GitHub