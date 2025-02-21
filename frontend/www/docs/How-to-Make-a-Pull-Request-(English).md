[También disponible en Español](/docs/C%C3%B3mo-Hacer-un-Pull-Request.md)

Once you [installed your dev environment](/docs/How-to-Set-Up-Your-Development-Environment-(English.md)) and you made the changes you wish to propose, follow these steps:

# Previous settings

- The virtual machine does not have `en_US.UTF-8` as the default language. To update it you have to do what is described in [this link](https://askubuntu.com/questions/881742/locale-cannot-set-lc-ctype-to-default-locale-no-such-file-or-directory-locale/893586#893586).

- At first, there are many missing dependencies. You need to run `composer install` inside the VM to install them.

- If you made changes in the UI and you don't see them in `localhost:8080`, it is important to run: `yarn install && yarn run dev`

# Before a pull request

*  Clone the repository `https://github.com/omegaup/omegaup` onto `https://github.com/<your-username>/omegaup` by clicking on the "Fork" button:![](https://image.ibb.co/k3Oh9v/Screenshot_from_2017_08_06_22_10_12.png)

**Before starting to make modifications** run these commands to create a new branch that is synchronized with `omegaup` repository:

* `git checkout main` # Switch you to main branch.
* `git fetch upstream` # Download the  omegaup/main repository.
* `git checkout -b newfeaturename upstream/main` # Create new branch.
* `git push pr newfeaturename`  # Push the new branch to your fork.

# Upload your changes and made a new Pull Request

Follow the next steps:

* Add your changes to a new commit: 
`git add .`

* Commit your changes:
`git commit -m "Write your description."`

* Set up your user information (You only need to enter this information the first time you push):
    * `git config --global user.email "your-email@domain.com"`
    * `git config --global user.name "<your-username>"`

* Push from your commit to your fork: 
`git push -u pr`


* Go to https://github.com/your-username/omegaup, click on the button _Branch_ [0] and select the branch you made changes _newfeaturename_[1.1]. After click on _Pull request_[2]:

![Select Branch and Pull Request](https://i.ibb.co/0Dd1ngf/Select-Branch-Own-Repository.png)
Alternatively, if you are in the omegaUp repository or yours, you can press the [1.2] button and then press the _Pull request_ [2] button.

*Fill in the required information and press the "Create Pull request" button
![Create Pull Request](https://i.ibb.co/KzJYC2D/Create-Pull-Request.png)

If you have to make more changes after making the Pull Request, follow this steps:  
  
* Add your changes to a commit:  
`git add .`  
  
* Add your commit:  
`git commit -m" Put a description of the changes here. "`  
  
* Push from your commit to your fork:  
`git push`

# Delete a branch

You can see the branches that you have in your local copy by running `git branch`. You can switch between branches by doing `git checkout branchname`.  
  
If we have already merged a Pull Request, you can clean your local branch with `git branch -D branchname`, but you also have to go to GitHub and delete the branch there (by clicking on" Branches ") or directly on the Pull Request.

![Delete Branch](https://i.ibb.co/99PMQC6/Delete-Branch-Git.png)


After this, you will still see the remote branch if you run the command `git branch -a`. You will find a line similar to:  
```  
remotes/pr/newfeaturename 
```
You can delete it in the following way:  
  
* Enter the following command `git remote prune pr --dry-run`, then it will ask you to enter your credentials. You will see something similar to this:  
```
[would prune] pr/newfeaturename 
```
  
* Now enter the command `git remote prune pr`, re-enter your credentials. You will see something similar to this:  
```
Pruning pr  
URL: git@github.com: pabo99/omegaup.git  
* [pruned] pr/newfeaturename 
```
  
If you run the command `git branch -a` again you will notice that everything related to `newfeaturename` has already disappeared.
 
