var omegaup = typeof global === 'undefined' ?
                  (window.omegaup = window.omegaup || {}) :
                  (global.omegaup = global.omegaup || {});

omegaup.arena = omegaup.arena || {};

omegaup.arena.Notifications = function() {
  var self = this;

  self.notifications = ko.observableArray();
  self.notificationMapping = {};
  self.unread = ko.observable(false);
  self.flashInterval = null;

  self.unread.subscribe(function(newValue) {
    if (newValue) {
      if (self.flashInterval) return;
      self.flashInterval = setInterval(self.flashTitle, 1000);
    } else {
      if (!self.flashInterval) return;
      clearInterval(self.flashInterval);
      self.flashInterval = null;
      if (document.title.indexOf('!') === 0) {
        document.title = document.title.substring(2);
      }
    }
  });
};

omegaup.arena.Notifications.prototype.flashTitle = function(reset) {
  if (document.title.indexOf('!') === 0) {
    document.title = document.title.substring(2);
  } else if (!reset) {
    document.title = '! ' + document.title;
  }
};

omegaup.arena.Notifications.prototype.attach = function(element) {
  var self = this;

  self.button = $('.notification-button', element);
  self.button.click(function() { self.unread(false); });

  self.onMarkAllAsRead = function() {
    self.notifications.removeAll();
    for (var key in self.notificationMapping) {
      if (!self.notificationMapping.hasOwnProperty(key)) continue;
      localStorage.setItem(key, new Date().getTime());
    }
    self.notificationMapping = {};
  };

  if (element[0] && !ko.dataFor(element[0])) ko.applyBindings(self, element[0]);
};

omegaup.arena.Notifications.prototype.notify = function(data) {
  var self = this;

  if (self.notificationMapping.hasOwnProperty(data.id)) {
    // Update the pre-existing notification.
    var notification = self.notificationMapping[data.id];
    for (var key in data) {
      if (!data.hasOwnProperty(key) || notification[key]() == data[key])
        continue;
      notification[key](data[key]);
    }
    self.unread(true);
    var audio = document.getElementById('notification-audio');
    if (audio != null) audio.play();
    return;
  }

  var lastModified = parseInt((typeof(localStorage) !== 'undefined' &&
                               localStorage.getItem(data.id)) ||
                                  '0',
                              10) ||
                     0;
  if (lastModified >= data.modificationTime) return;

  for (var key in data) {
    if (!data.hasOwnProperty(key)) continue;
    data[key] = ko.observable(data[key]);
  }
  data.onCloseClicked = function() {
    localStorage.setItem(data.id(), new Date().getTime());
    self.notifications.remove(data);
  };
  self.notificationMapping[data.id()] = data;

  self.notifications.push(data);

  self.unread(true);
  var audio = document.getElementById('notification-audio');
  if (audio != null) audio.play();
};
