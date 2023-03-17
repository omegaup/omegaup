import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  const params = new URL(document.location.toString()).searchParams;
  let pathname = params.get('redirect');
  if (!pathname || pathname.indexOf('/') !== 0) {
    pathname = '/';
  }
  window.location.href = pathname;
});
