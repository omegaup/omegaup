You only have to execute this once to configure `upstream` and that you can download the changes:

`git remote add upstream https://github.com/omegaup/omegaup.git`

# Update your `main` branch 

It is recommended that you do not make changes in `main`, because it is very difficult to return it to a decent state once your changes have been merged. However, it's a good idea that from time to time you update:
-   `git checkout main # returns you to main, if you have been in another branch`
-   `git fetch upstream # download the repository omegaup/main`
-   `git pull --rebase upstream main # synchronize your main copy with omegaup/main`
-   `git push`

If `git push` fails, it is because you violated the rule of not making changes to` main`. Try doing `git push -f`.