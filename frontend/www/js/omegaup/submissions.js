import {API, UI, OmegaUp, T} from './omegaup.js';
import Vue from 'vue';
import submissions from './components/Submissions.vue';

OmegaUp.on('ready', function() {
    API.Run.list({username: OmegaUp.username})
        .then(function(response) {
            var runs = response.runs;
            new Vue({
                el: '#submissions',
                render: function(createElement) {
                    return createElement('omegaup-submissions', {
                        props: {
                            runs: runs
                        }
                    });
                },
                components: {'omegaup-submissions': submissions}
            });
        })
        .fail(UI.apiError);
});
