omegaup.OmegaUp.on('ready', function() {
  Date.setLocale(omegaup.T.locale);

  var contestListConfigs = [
    // List Id, Active, Recommended, List, Public header
    [
      '#recommended-current-contests',
      'ACTIVE',
      'RECOMMENDED',
      'NO',
      'NO',
      omegaup.T.arenaRecommendedCurrentContests,
    ],
    [
      '#current-contests',
      'ACTIVE',
      'NOT_RECOMMENDED',
      'NO',
      'NO',
      omegaup.T.arenaCurrentContests,
    ],
    [
      '#list-current-public-contest',
      'ACTIVE',
      'NOT_RECOMMENDED',
      'NO',
      'YES',
      omegaup.T.arenaCurrentPublicContests,
    ],
    [
      '#future-contests',
      'FUTURE',
      'NOT_RECOMMENDED',
      'NO',
      'NO',
      omegaup.T.arenaFutureContests,
    ],
    [
      '#recommended-past-contests',
      'PAST',
      'RECOMMENDED',
      'NO',
      'NO',
      omegaup.T.arenaRecommendedOldContests,
    ],
    [
      '#past-contests',
      'PAST',
      'NOT_RECOMMENDED',
      'NO',
      'NO',
      omegaup.T.arenaOldContests,
    ],
    [
      '#participating-current-contests',
      'ACTIVE',
      'NOT_RECOMMENDED',
      'YES',
      'NO',
      omegaup.T.arenaMyActiveContests,
    ],
  ];

  var requests = [];
  var contestLists = [];
  var query = document.querySelector('input[name=query]').value;

  for (var i = 0, len = contestListConfigs.length; i < len; i++) {
    var config = contestListConfigs[i];
    var contestList = new omegaup.arena.ContestList(
        document.querySelector(config[0]),
        {
          active: config[1],
          recommended: config[2],
          participating: config[3], public: config[4],
          query: query,
        },
        {header: config[5]});
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
