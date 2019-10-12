import common_Footer from '../components/common/Footer.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  // const headerPayload =
  //     JSON.parse(document.getElementById('header-payload').innerText);
  let commonFooter = new Vue({
    el: '#common-footer',
    render: function(createElement) {
      return createElement('omegaup-common-footer', {
        // props: {
        //   data: this.data,
        // },
      });
    },
    // data: {
    //   data: headerPayload,
    // },
    components: {
      'omegaup-common-footer': common_Footer,
    },
  });

  // if (headerPayload.isAdmin) {
  //   API.Notification.myList({})
  //       .then(function(data) {
  //         commonNavbar.notifications = data.notifications;
  //       })
  //       .fail(UI.apiError);
  // }
});