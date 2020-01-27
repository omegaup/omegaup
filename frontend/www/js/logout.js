function redirect() {
  var params = new URL(document.location).searchParams;
  var pathname = params.get('redirect');
  if (!pathname || pathname.indexOf('/') !== 0) {
    pathname = '/';
  }
  window.location = pathname;
}

omegaup.OmegaUp.on('ready', function() {
  // This needs to be able to work with or without the Google API loaded. We
  // can sniff that out by the presence / abssence of the
  // google-signin-client_id meta field and the global `gapi` object.
  var clientId = document.querySelector('meta[name="google-signin-client_id"]');
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
      function(auth) {
        auth.signOut()['then'](
          function() {
            redirect();
          },
          function(error) {
            console.error(error);
            redirect();
          },
        );
      },
      function(error) {
        console.error(error);
        redirect();
      },
    );
  });
});
