omegaup.OmegaUp.on('ready', function() {
  // facebook button
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.id = id;
    js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.12';
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));

  // twitter button
  twttr = (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0], t = window.twttr || {};
    if (d.getElementById(id)) return t;
    js = d.createElement(s);
    js.id = id;
    js.src = 'https://platform.twitter.com/widgets.js';
    fjs.parentNode.insertBefore(js, fjs);

    t._e = [];
    t.ready = function(f) { t._e.push(f); };

    return t;
  }(document, 'script', 'twitter-wjs'));
  twttr.ready(function() {
    twttr.widgets.createFollowButton('omegaup',
                                     document.getElementById('twitter-follow'),
                                     {width: '300px', height: '20'});
  });
});
