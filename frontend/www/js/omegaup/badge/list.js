import Vue from 'vue';
import badge_List from '../components/badge/List.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let badgeList = new Vue({
    el: '#badges-list',
    render: function(createElement) {
      return createElement('omegaup-badge-list', {
        props: {
          allBadges: this.allBadges,
          visitorBadges: this.visitorBadges,
          showAllBadgesLink: false,
        }
      });
    },
    data: {
      allBadges: new Set(),
      visitorBadges: new Set(),
    },
    components: {
      'omegaup-badge-list': badge_List,
    },
  });
  if (payload.logged_in) {
    API.Badge.myList({})
        .then(function(data) {
          badgeList.visitorBadges =
              new Set(data['badges'].map(badge => badge.badge_alias));
        })
        .fail(UI.apiError);
  }

  API.Badge.list({})
      .then(function(data) { badgeList.allBadges = new Set(data); })
      .fail(UI.apiError);
});
