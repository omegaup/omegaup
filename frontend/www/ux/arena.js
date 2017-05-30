omegaup.OmegaUp.on('ready', function() {
  Date.setLocale(omegaup.T.locale);

  var contestListConfigs = [
    // List Id, Active, Recommended, List header
    [
      '#recommended-current-contests',
      'ACTIVE',
      'RECOMMENDED',
      omegaup.T.arenaRecommendedCurrentContests
    ],
    [
      '#current-contests',
      'ACTIVE',
      'NOT_RECOMMENDED',
      omegaup.T.arenaCurrentContests
    ],
    [
      '#recommended-past-contests',
      'PAST',
      'RECOMMENDED',
      omegaup.T.arenaRecommendedOldContests
    ],
    ['#past-contests', 'PAST', 'NOT_RECOMMENDED', omegaup.T.arenaOldContests],
  ];

  var requests = [];
  var contestLists = [];
  for (var i = 0, len = contestListConfigs.length; i < len; i++) {
    var config = contestListConfigs[i];
    var contestList = new omegaup.arena.ContestList(
        config[0], {active: config[1], recommended: config[2]},
        {header: config[3]});
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
