import Vue from 'vue';
import UserBasicEdit from '../components/user/BasicEdit.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  let basicEdit = new Vue({
    el: '#user-basic-edit',
    render: function(createElement) {
      return createElement('omegaup-user-basic-edit');
    },
    components: {
      'omegaup-user-basic-edit': UserBasicEdit,
    },
  });
});
