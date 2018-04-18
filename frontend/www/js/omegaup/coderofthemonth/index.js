import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';
import coder_of_the_month from '../components/coderofthemonth/CoderOfTheMonth.vue';

OmegaUp.on('ready', function() {
    let coderOfTheMonth = new Vue({
        el: '#coder-of-the-month',
        render: function(createElement) {
            return createElement('coder-of-the-month');
        },
        components: {
            'coder-of-the-month': coder_of_the_month
        }
    });
});