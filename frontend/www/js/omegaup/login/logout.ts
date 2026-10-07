import { OmegaUp } from '../omegaup';
import { broadcastLogout, clearSessionStorageForLogout } from '../logoutSync';

OmegaUp.on('ready', () => {
  if (OmegaUp._cleanupLogoutListener) {
    OmegaUp._cleanupLogoutListener();
    OmegaUp._cleanupLogoutListener = null;
  }

  // Clear sessionStorage while preserving ephemeral sources.
  clearSessionStorageForLogout();

  // Notify all other open tabs after the local cleanup has finished so they
  // observe the cleared state before redirecting.
  broadcastLogout();

  // Just in case we need redirect when user logs out
  const params = new URL(document.location.toString()).searchParams;
  let pathname = params.get('redirect');
  if (!pathname || !pathname.startsWith('/') || pathname.startsWith('//')) {
    pathname = '/';
  }
  window.location.href = pathname;
});
