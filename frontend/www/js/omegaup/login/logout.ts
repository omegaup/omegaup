import { OmegaUp } from '../omegaup';
import { broadcastLogout } from '../logoutSync';

OmegaUp.on('ready', () => {
  // Notify all other open tabs that this session is being terminated so
  // they redirect immediately instead of waiting for the next API failure.
  broadcastLogout();

  // Remove ephemeral sources
  for (const key of Object.keys(sessionStorage)) {
    if (key.startsWith('ephemeral-sources-')) continue;
    sessionStorage.removeItem(key);
  }

  // Just in case we need redirect when user logs out
  const params = new URL(document.location.toString()).searchParams;
  let pathname = params.get('redirect');
  if (!pathname || !pathname.startsWith('/') || pathname.startsWith('//')) {
    pathname = '/';
  }
  window.location.href = pathname;
});
