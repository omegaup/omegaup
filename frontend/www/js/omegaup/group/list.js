import group_GroupList from '../components/group/GroupList.vue';
import {OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let payload = JSON.parse(document.getElementById('payload').innerText);
  let groupList = new Vue({
    el: '#group_list',
    render: function(createElement) {
      return createElement('omegaup-group-grouplist', {
        props: {T: T, groups: this.groups},
      });
    },
    data: {
      groups: payload.groups,
    },
    components: {
      'omegaup-group-grouplist': group_GroupList,
    },
  });
});