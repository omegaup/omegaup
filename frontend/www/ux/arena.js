$(document)
    .ready(function() {
      Date.setLocale(omegaup.T.locale);

      ko.bindingProvider.instance =
          new ko.secureBindingsProvider({attribute: 'data-bind'});
      var contestLists = [
        // List Id, Active, Recommended, List header
        [
          '#current-contests',
          'ACTIVE',
          'NOT_RECOMMENDED',
          omegaup.T.arenaCurrentContests
        ],
        [
          '#recommended-current-contests',
          'ACTIVE',
          'RECOMMENDED',
          omegaup.T.arenaRecommendedCurrentContests
        ],
        [
          '#past-contests',
          'PAST',
          'NOT_RECOMMENDED',
          omegaup.T.arenaOldContests
        ],
        [
          '#recommended-past-contests',
          'PAST',
          'RECOMMENDED',
          omegaup.T.arenaRecommendedOldContests
        ],
      ];

      var requests = [];
      for (var i = 0, len = contestLists.length; i < len; i++) {
        var contestList = new omegaup.arena.ContestList(
            contestLists[i][0],
            {active: contestLists[i][1], recommended: contestLists[i][2]},
            {header: contestLists[i][3]});
        requests.push(contestList.deferred);
      }

      // Wait until all of the calls above finish before showing the contents.
      $.when.apply($, requests)
          .done(function() {
            $('#root').show();
            $('#loading').fadeOut('slow');
          });
    });
