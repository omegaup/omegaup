import Vue from 'vue';
import badge_Details from '../components/badge/Details.vue';
import {OmegaUp, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let badgeDetails = new Vue({
    el: '#badge-details',
    render: function(createElement) {
      return createElement('omegaup-badge-details', {
        props: {
          badge: this.badge,
        }
      });
    },
    data: {
      badge: {
        badge_alias: payload.badge,
        assignation_time: -1,
        first_assignation: null,
        owners_percentage: null,
      }
    },
    components: {
      'omegaup-badge-details': badge_Details,
    },
  });

  API.Badge.badgeDetails({badge_alias: payload.badge})
      .then(function(data) {
        badgeDetails.badge['first_assignation'] = data['first_assignation'];
        badgeDetails.badge['owners_percentage'] = data['owners_percentage'];
      })
      .fail(UI.apiError);

  if (payload.logged_in) {
    API.Badge.myBadgeAssignationTime({badge_alias: payload.badge})
        .then(function(data) {
          badgeDetails.badge['assignation_time'] = data['assignation_time'];
        })
        .fail(UI.apiError);
  }
});
