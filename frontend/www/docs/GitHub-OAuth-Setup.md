# GitHub OAuth Setup

This is a quick guide to enable “Sign in with GitHub” in a local omegaUp instance.

## Create the OAuth App

1. Sign in to GitHub and open https://github.com/settings/developers.
2. Click **OAuth Apps → New OAuth App**.
3. Fill the form:
   - Application name: `omegaUp local` (or any name you prefer)
   - Homepage URL: `http://localhost:8001/`
   - Authorization callback URL: `http://localhost:8001/login?third_party_login=github`
4. Click **Register application**.
5. On the app page, copy the **Client ID** and generate/copy the **Client Secret** (you will not see the secret again).

## Configure omegaUp

1. Open `frontend/server/config.default.php` and find the GitHub section:
   ```php
   try_define('OMEGAUP_GITHUB_CLIENT_ID', 'xxxxx');
   try_define('OMEGAUP_GITHUB_CLIENT_SECRET', 'xxxxx');
   ```
2. Replace the placeholder values with your actual credentials:
   ```php
   try_define('OMEGAUP_GITHUB_CLIENT_ID', 'your_real_client_id_here');
   try_define('OMEGAUP_GITHUB_CLIENT_SECRET', 'your_real_client_secret_here');
   ```
3. Restart your local stack:
   ```bash
   docker-compose restart frontend
   ```

### ⚠️ IMPORTANT: Before Committing

**You MUST revert your credentials before pushing to git:**

1. Before `git add` or `git commit`, restore the placeholders:
   ```php
   try_define('OMEGAUP_GITHUB_CLIENT_ID', 'xxxxx');
   try_define('OMEGAUP_GITHUB_CLIENT_SECRET', 'xxxxx');
   ```
2. Or use `git checkout frontend/server/config.default.php` to discard changes.
3. **Never commit real secrets to git** — they will be permanently in the repository history.

## Tips

- Keep the secret private; never commit it.
- If you change the port or host, update the callback URL in the GitHub app to match.
- The login button is disabled when no client ID is configured, so double-check the values if it remains inactive.
