# GitHub OAuth Setup

This is a quick guide to enable “Sign in with GitHub” in a local omegaUp instance.

## Create the OAuth App

1. Go to https://github.com/settings/developers and choose **OAuth Apps → New OAuth App**.
2. Use a descriptive name such as `omegaUp local`.
3. Homepage URL: `http://localhost:8001/`.
4. Authorization callback URL: `http://localhost:8001/login?third_party_login=github`.
5. Save and note the **Client ID** and **Client Secret**.

## Configure omegaUp

1. Copy `frontend/server/config.default.php` to `frontend/server/config.php` if you have not already.
2. Set these values in `frontend/server/config.php`:
   - `OMEGAUP_GITHUB_CLIENT_ID`
   - `OMEGAUP_GITHUB_CLIENT_SECRET`
3. Restart your local stack so the new configuration is loaded (e.g., `docker-compose up -d --force-recreate frontend`).

## Tips

- Keep the secret private; never commit it.
- If you change the port or host, update the callback URL in the GitHub app to match.
- The login button is disabled when no client ID is configured, so double-check the values if it remains inactive.
