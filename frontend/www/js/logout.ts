import { OmegaUp } from '../js/omegaup/omegaup';
/// <reference path="../../node_modules/@types/gapi.auth2/index.d.ts" />
declare let gapi: any;

OmegaUp.on('ready', () => {
  let clientId = document.querySelector('meta[name="google-signin-client_id"]');
  if (!clientId || !window.gapi) {
    redirect();
    return;
  }

  // All possible paths need to end with redirect().
  gapi.load('auth2', function() {
    // ['then'] is used instead of .then(), since these are not real ES6
    // Promise objects, therefore they don't have an .else() or .finally().
    // That trips up the linter.
    gapi.auth2.init({})['then'](
      function(auth: any) {
        auth.signOut()['then'](
          function() {
            redirect();
          },
          function(error: any) {
            console.error(error);
            redirect();
          },
        );
      },
      function(error: any) {
        console.error(error);
        redirect();
      },
    );
  });
});

function redirect() {
  let params = new URL(document.location.toString()).searchParams;
  let pathname = params.get('redirect');
  if (!pathname || pathname.indexOf('/') !== 0) {
    pathname = '/';
  }
  window.location.href = pathname;
}
