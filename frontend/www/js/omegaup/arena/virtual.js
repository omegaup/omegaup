import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';
import virtual from '../components/arena/virtual.vue';

OmegaUp.on('ready', function() {
  let virtual_ = new Vue({
    el: '#virtual',
    render: function(createElement) { return createElement('virtual'); },
    components: {'virtual': virtual}
  });
});
