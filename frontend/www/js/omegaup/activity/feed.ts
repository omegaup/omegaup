import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import activity_Feed from '../components/activity/Feed.vue';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.ActivityFeedPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-activity-feed': activity_Feed,
    },
    render: function (createElement) {
      return createElement('omegaup-activity-feed', {
        props: {
          page: payload.page,
          length: payload.length,
          type: payload.type,
          alias: payload.alias,
          report: payload.events,
          pagerItems: payload.pagerItems,
        },
      });
    },
  });
});
