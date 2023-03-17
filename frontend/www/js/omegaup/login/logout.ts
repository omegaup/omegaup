import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  // Remove ephemeral sources
  for (const key of Object.keys(sessionStorage)) {
    if (key.startsWith('ephemeral-sources-')) continue;
    sessionStorage.removeItem(key);
  }

  // Just in case we need redirect when user logs out
  const params = new URL(document.location.toString()).searchParams;
  let pathname = params.get('redirect');
  if (!pathname || pathname.indexOf('/') !== 0) {
    pathname = '/';
  }
  window.location.href = pathname;
});
