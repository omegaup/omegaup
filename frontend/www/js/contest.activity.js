omegaup.OmegaUp.on('ready', function() {
  var contestAlias =
      /\/contest\/([^\/]+)\/activity\/?.*/.exec(window.location.pathname)[1];

  function addMapping(mapping, key, value) {
    if (!mapping.hasOwnProperty(key)) {
      mapping[key] = {};
    }
    if (!mapping[key].hasOwnProperty(value)) {
      mapping[key][value] = true;
    }
  }

  var ActivityReport = function(report) {
    var self = this;
    self.events = report.events;
    var userMapping = {};
    var originMapping = {};
    for (var idx in self.events) {
      if (!self.events.hasOwnProperty(idx)) continue;
      addMapping(originMapping, self.events[idx].ip, self.events[idx].username);
      addMapping(userMapping, self.events[idx].username, self.events[idx].ip);
      self.events[idx].ip = '' + self.events[idx].ip;
      self.events[idx].profile_url = '/profile/' + self.events[idx].username;
      self.events[idx].time =
          Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', self.events[idx].time);
      if (self.events[idx].event.problem) {
        self.events[idx].event.problem_url =
            '/arena/problem/' + self.events[idx].event.problem + '/';
      }
    }

    self.users = [];
    var sortedUsers = Object.keys(userMapping);
    sortedUsers.sort();
    for (var i = 0; i < sortedUsers.length; i++) {
      var ips = Object.keys(userMapping[sortedUsers[i]]);
      if (ips.length == 1) continue;
      ips.sort();
      self.users.push({username: sortedUsers[i], ips: ips.join(' ')});
    }

    self.origins = [];
    var sortedOrigins = Object.keys(originMapping);
    sortedOrigins.sort();
    for (var i = 0; i < sortedOrigins.length; i++) {
      var users = Object.keys(originMapping[sortedOrigins[i]]);
      if (users.length == 1) continue;
      users.sort();
      for (var j = 0; j < users.length; j++) {
        users[j] = {username: users[j], profile_url: '/profile/' + users[j]};
      }
      self.origins.push({origin: sortedOrigins[i], usernames: users});
    }
  };

  omegaup.API.getContestActivityReport({'contest_alias': contestAlias})
      .then(function(report) { ko.applyBindings(new ActivityReport(report)); });
});
