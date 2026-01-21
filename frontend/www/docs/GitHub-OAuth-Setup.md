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

1. Create `frontend/server/config.php` if it doesn't exist, or edit it if it already exists. Add your GitHub OAuth credentials at the end of the file:
   ```php
      <?php
      define('OMEGAUP_GITHUB_CLIENT_ID', 'your_real_client_id_here');
      define('OMEGAUP_GITHUB_CLIENT_SECRET', 'your_real_client_secret_here');
   ```
2. No Docker Compose restart is required.

### Important Notes

**You MUST revert your credentials before pushing to git:**

- **Never commit OAuth credentials** - `config.php` is not versioned for this reason.
- **Do not edit `frontend/server/config.default.php`** - use `config.php` for local configuration.
- **`config.php` is deleted when Docker Compose is restarted** - store your credentials securely or regenerate them as needed.
- **If the GitHub login button is disabled**, verify the client ID is properly defined in `config.php`.

## Tips

- Keep the secret private; never commit it.
- If you change the port or host, update the callback URL in the GitHub app to match.
- The login button is disabled when no client ID is configured, so double-check the values if it remains inactive.
