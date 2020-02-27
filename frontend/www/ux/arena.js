omegaup.OmegaUp.on('ready', function() {
  Date.setLocale(omegaup.T.locale);

  var contestListConfigs = [
    {
      id: '#participating-current-contests',
      header: omegaup.T.arenaMyActiveContests,
      requestParams: {
        participating: 'YES',
      },
    },
    {
      id: '#recommended-current-contests',
      header: omegaup.T.arenaRecommendedCurrentContests,
      requestParams: {
        active: 'ACTIVE',
        recommended: 'RECOMMENDED',
        participating: 'NO',
        public: 'NO',
      },
    },
    {
      id: '#current-contests',
      header: omegaup.T.arenaCurrentContests,
      requestParams: {
        active: 'ACTIVE',
        recommended: 'NOT_RECOMMENDED',
        participating: 'NO',
        public: 'NO',
      },
    },
    {
      id: '#list-current-public-contest',
      header: omegaup.T.arenaCurrentPublicContests,
      requestParams: {
        active: 'ACTIVE',
        recommended: 'NOT_RECOMMENDED',
        participating: 'NO',
        public: 'YES',
      },
    },
    {
      id: '#future-contests',
      header: omegaup.T.arenaFutureContests,
      requestParams: {
        active: 'FUTURE',
        recommended: 'NOT_RECOMMENDED',
        participating: 'NO',
        public: 'NO',
      },
    },
    {
      id: '#recommended-past-contests',
      header: omegaup.T.arenaRecommendedOldContests,
      requestParams: {
        active: 'PAST',
        recommended: 'RECOMMENDED',
        participating: 'NO',
        public: 'NO',
      },
    },
    {
      id: '#past-contests',
      header: omegaup.T.arenaOldContests,
      requestParams: {
        active: 'PAST',
        recommended: 'NOT_RECOMMENDED',
        participating: 'NO',
        public: 'NO',
      },
    },
  ];

  var requests = [];
  var contestLists = [];
  var query = document.querySelector('input[name=query]').value;

  for (var i = 0, len = contestListConfigs.length; i < len; i++) {
    var config = contestListConfigs[i];
    var request = {
      query: query,
    };
    for (var name in config.requestParams) {
      if (!config.requestParams.hasOwnProperty(name)) {
        continue;
      }
      request[name] = config.requestParams[name];
    }
    var contestList = new omegaup.arena.ContestList(
      document.querySelector(config.id),
      request,
      { header: config.header },
    );
    contestLists.push(contestList);
    requests.push(contestList.deferred);
  }

  // Wait until all of the calls above finish before showing the contents.
  $.when.apply($, requests).done(function() {
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
