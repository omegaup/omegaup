import contest_List from '../components/contest/ContestMyList.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let payload =
      JSON.parse(document.getElementById('my-next-contests-payload').innerText);
  let contestMyList = new Vue({
    el: '#my-next-contests',
    render: function(createElement) {
      return createElement('my-next-contests', {
        props: {list: payload},
      });
    },
    components: {
      'my-next-contests': contest_List,
    },
  });
});
