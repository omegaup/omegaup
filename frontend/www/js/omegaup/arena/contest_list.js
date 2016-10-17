omegaup.arena = omegaup.arena || {};

// TODO: This really should be a Knockout component.
omegaup.arena.ContestList = function(element, apiParams, uiParams) {
  var self = this;
  self.jQueryElement = $(element);
  self.domElement = self.jQueryElement[0];

  var actualApiParams = $.extend(
      {
        active: 'ALL',
        recommended: 'ALL',
        // TODO: Make this match uiParams.pageSize and do smaller requests.
        page_size: 1000,
      },
      apiParams);
  var actualUiParams = $.extend(
      {
        header: omegaup.T.wordsContests,
        pageSize: 10,
        showTimes: (actualApiParams.active == 'ACTIVE'),
        showPractice: (actualApiParams.active == 'PAST'),
      },
      uiParams);

  // Contest list view model.
  self.header = actualUiParams.header;
  self.showTimes = actualUiParams.showTimes;
  self.showPractice = actualUiParams.showPractice;
  self.contests = ko.observableArray([]);

  // Pagination.
  self.pageNumber = ko.observable(1);
  // TODO: Make this an observable and link it to an input field.
  self.pageSize = actualUiParams.pageSize;
  self.totalPages = ko.computed(function() {
    return Math.ceil(self.contests().length / self.pageSize);
  });
  self.page = ko.computed(function() {
    var first = (self.pageNumber() - 1) * self.pageSize;
    return self.contests.slice(first, first + self.pageSize);
  });
  self.hasPrevious = ko.computed(function() { return self.pageNumber() > 1; });
  self.hasNext =
      ko.computed(function() { return self.pageNumber() < self.totalPages(); });
  self.pagerColumns = ko.computed(function() {
    var cols = 2;
    if (self.showPractice)
      cols += 1;
    if (self.showTimes)
      cols += 3;
    return cols;
  });
  // Click handlers.
  self.next = function() {
    // TODO: Update history so the back button works correctly.
    if (self.pageNumber() < self.totalPages()) {
      self.pageNumber(self.pageNumber() + 1);
    }
  };
  self.previous = function() {
    // TODO: Update history so the back button works correctly.
    if (self.pageNumber() != 0) {
      self.pageNumber(self.pageNumber() - 1);
    }
  };
  self.deferred =
      omegaup.API.getContests(actualApiParams)
          .then(function(data) {
            // Create contest view model from contest data model.
            data.results.each(function(contest) {
              contest.contestLink = '/arena/' + contest.alias;
              contest.practiceLink = contest.contestLink + '/practice/';
              contest.duration = omegaup.UI.toHHMM(contest.duration);
              contest.startLink =
                  'http://timeanddate.com/worldclock/fixedtime.html?iso=' +
                  contest.start_time.iso();
              contest.startText = contest.start_time.long();
              contest.finishLink =
                  'http://timeanddate.com/worldclock/fixedtime.html?iso=' +
                  contest.finish_time.iso();
              contest.finishText = contest.finish_time.long();
              self.contests.push(contest);
            });
            ko.applyBindings(self, self.domElement);
          });
};
