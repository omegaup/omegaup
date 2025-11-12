# Changing the Last Commit Message
If you’ve already made a commit and want to update its message, use the following command:

```bash
git commit --amend
```
- This will open your default text editor, allowing you to modify the commit message.

```text
Commit message

# Please enter the commit message for your changes. Lines starting
# with '#' will be ignored, and an empty message aborts the commit.
```
Edit the message as needed, then save and close the editor.
To verify that the change was applied successfully, run:

```bash
git log
```
You should see something like this:

```text
commit <id>
Author: John Doe
Date:   <date>

    New commit message
```

If you’ve already pushed the commit to a remote repository, use a force push to update it:

```bash
git push -f
```
**Note:** Use `git commit --amend` only for your most recent commit.
For older commits, consider using `git rebase -i` to safely update messages.
