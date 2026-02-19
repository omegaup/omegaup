import common_Help from '../components/help/Help.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';

OmegaUp.on('ready', () => {
    new Vue({
        el: '#main-container',
        components: {
            'omegaup-help': common_Help,
        },
        render: function (createElement) {
            return createElement('omegaup-help');
        },
    });
});
