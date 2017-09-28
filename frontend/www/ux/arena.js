omegaup.OmegaUp.on('ready', function() {
  Date.setLocale(omegaup.T.locale);

  var contestListConfigs = [
    // List Id, Active, Recommended, List header
    [
      '#recommended-current-contests',
      'ACTIVE',
      'RECOMMENDED',
      'NO',
      omegaup.T.arenaRecommendedCurrentContests
    ],
    [
      '#participating-current-contests',
      'ACTIVE',
      'NOT_RECOMMENDED',
      'YES',
      omegaup.T.arenaMyActiveContests
    ],
    [
      '#current-contests',
      'ACTIVE',
      'NOT_RECOMMENDED',
      'NO',
      omegaup.T.arenaCurrentContests
    ],
    [
      '#future-contests',
      'FUTURE',
      'NOT_RECOMMENDED',
      omegaup.T.arenaFutureContests
    ],
    [
      '#recommended-past-contests',
      'PAST',
      'RECOMMENDED',
      'NO',
      omegaup.T.arenaRecommendedOldContests
    ],
    [
      '#past-contests',
      'PAST',
      'NOT_RECOMMENDED',
      'NO',
      omegaup.T.arenaOldContests
    ],
  ];

  var requests = [];
  var contestLists = [];
  for (var i = 0, len = contestListConfigs.length; i < len; i++) {
    var config = contestListConfigs[i];
    var contestList = new omegaup.arena.ContestList(
        config[0],
        {active: config[1], recommended: config[2], participating: config[3]},
        {header: config[4]});
    contestLists.push(contestList);
    requests.push(contestList.deferred);
  }

  // Wait until all of the calls above finish before showing the contents.
  $.when.apply($, requests)
      .done(function() {
        for (var i = 0, len = contestLists.length; i < len; i++) {
          if (contestLists[i].totalPages()) {
            $('.nav-link', $('.nav-item')[i]).tab('show');
            break;
          }
        }
        $('#root').show();
        $('#loading').fadeOut('slow');
      });
});
