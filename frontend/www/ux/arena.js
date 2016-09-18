// This really should be a Knowckout component.
omegaup.UI.ContestList = function(element, api_params, ui_params) {
    var self = this;
    self.element = $(element);

    var actual_api_params = $.extend({
        active: 'ALL',
        recommended: 'ALL',
        // TODO: Make this match ui_params.page_size and do smaller requests.
        page_size: 1000,
    }, api_params);
    var actual_ui_params = $.extend({
        header: omegaup.T.wordsContests,
        page_size: 10,
        show_times: (actual_api_params.active == 'ACTIVE'),
        show_practice: (actual_api_params.active == 'PAST'),
    }, ui_params);

    // Contest list view model.
    self.header = actual_ui_params.header;
    self.showTimes = actual_ui_params.show_times;
    self.showPractice = actual_ui_params.show_practice;
    self.contests = ko.observableArray([]);

    // Pagination.
    self.pageNumber = ko.observable(1);
    // TODO: Make this an observable and link it to an input field.
    self.pageSize = actual_ui_params.page_size;
    self.totalPages = ko.computed(function() {
        return Math.ceil(self.contests().length / self.pageSize);
    });
    self.page = ko.computed(function() {
        var first = (self.pageNumber() - 1) * self.pageSize;
        return self.contests.slice(first, first + self.pageSize);
    });
    self.hasPrevious = ko.computed(function() {
        return self.pageNumber() > 1;
    });
    self.hasNext = ko.computed(function() {
        return self.pageNumber() < self.totalPages();
    });
    self.pagerColumns = ko.computed(function() {
        var cols = 2;
        if (self.showPractice) cols += 1;
        if (self.showTimes) cols += 3;
        return cols;
    });
    // Click handlers.
    self.next = function() {
        if(self.pageNumber() < self.totalPages()) {
            self.pageNumber(self.pageNumber() + 1);
        }
    }
    self.previous = function() {
        if(self.pageNumber() != 0) {
            self.pageNumber(self.pageNumber() - 1);
        }
    }
    self.deferred = omegaup.API.getContests(actual_api_params).then(function(data) {
        // Create contest view model from contest data model.
        data.results.each(function(contest) {
            contest.duration = omegaup.UI.toHHMM(contest.duration);
            self.contests.push(contest);
        });
        ko.applyBindings(self, self.element[0]);
    });
};

$(document).ready(function() {
	Date.setLocale(omegaup.T.locale);

    ko.bindingProvider.instance = new ko.secureBindingsProvider({attribute: 'data-bind'});
    var contestLists = [
        // List Id, Active, Recommended, List header
        ['#current-contests', 'ACTIVE', 'NOT_RECOMMENDED', omegaup.T.arenaCurrentContests ],
        ['#recommended-current-contests', 'ACTIVE', 'RECOMMENDED',
            omegaup.T.arenaRecommendedCurrentContests],
        ['#past-contests', 'PAST', 'NOT_RECOMMENDED', omegaup.T.arenaOldContests],
        ['#recommended-past-contests', 'PAST', 'RECOMMENDED',
            omegaup.T.arenaRecommendedOldContests],
    ];

    var requests = [];
    for (var i = 0, len = contestLists.length; i < len; i++) {
        var contest_list = new omegaup.UI.ContestList(
            contestLists[i][0],
            { active: contestLists[i][1], recommended: contestLists[i][2] },
            { header: contestLists[i][3] });
        requests.push(contest_list.deferred);
    }

    // Wait until all of the calls above finish before showing the contents.
    $.when.apply($, requests).done(function() {
        $('#root').show();
        $('#loading').fadeOut('slow');
    });
});
