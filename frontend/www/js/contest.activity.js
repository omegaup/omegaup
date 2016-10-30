$('document')
    .ready(function() {
      var contestAlias = /\/contest\/([^\/]+)\/activity\/?.*/.exec(
          window.location.pathname)[1];

      var ActivityReport = function(report) {
        var self = this;
        self.events = report.events;
        for (var idx in self.events) {
          if (!self.events.hasOwnProperty(idx)) continue;
          self.events[idx].profile_url =
              '/profile/' + self.events[idx].username;
          self.events[idx].time =
              Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', self.events[idx].time);
          if (self.events[idx].event.problem) {
            self.events[idx].event.problem_url =
                '/arena/problem/' + self.events[idx].event.problem + '/';
          }
        }
      };

      omegaup.API.getContestActivityReport({'contest_alias': contestAlias})
          .then(function(report) {
            var options = {
              attribute: 'data-bind'  // default "data-sbind"
            };
            ko.bindingProvider.instance =
                new ko.secureBindingsProvider(options);
            ko.applyBindings(new ActivityReport(report), $('#report-table')[0]);
          });
    });
