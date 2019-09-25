import common_Navbar from '../components/common/Navbar.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const headerPayload =
      JSON.parse(document.getElementById('header-payload').innerText);
  let commonNavbar = new Vue({
    el: '#common-navbar',
    render: function(createElement) {
      return createElement('omegaup-common-navbar', {
        props: {
          data: this.data,
        },
      });
    },
    data: {
      data: headerPayload,
    },
    components: {
      'omegaup-common-navbar': common_Navbar,
    },
  });

  if (headerPayload.isAdmin) {
    API.Notification.myList({})
        .then(function(data) {
          commonNavbar.notifications = data.notifications;
        })
        .fail(UI.apiError);
  }
});
