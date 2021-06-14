import { OmegaUp } from '../omegaup';

function redirect() {
  const params = new URL(document.location.toString()).searchParams;
  let pathname = params.get('redirect');
  if (!pathname || pathname.indexOf('/') !== 0) {
    pathname = '/';
  }
  window.location.href = pathname;
}

OmegaUp.on('ready', () => {
  const clientId = document.querySelector(
    'meta[name="google-signin-client_id"]',
  );
  if (!clientId || !window.gapi) {
    redirect();
    return;
  }

  // All possible paths need to end with redirect().
  window.gapi.load('auth2', () => {
    // ['then'] is used instead of .then(), since these are not real ES6
    // Promise objects, therefore they don't have an .else() or .finally().
    // That trips up the linter.
    window.gapi.auth2.init({})['then'](
      (auth: gapi.auth2.GoogleAuth) => {
        auth.signOut()['then'](
          () => redirect(),
          // eslint-disable-next-line @typescript-eslint/no-unused-vars
          (error: Promise<string>) => {
            redirect();
          },
        );
      },
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      (error: { error: string; details: string }) => {
        redirect();
      },
    );
  });
});
