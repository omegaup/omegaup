import group_List from '../components/group/List.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.GroupListPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-group-list': group_List,
    },
    render: function (createElement) {
      return createElement('omegaup-group-list', {
        props: { groups: payload.groups },
      });
    },
  });
});
