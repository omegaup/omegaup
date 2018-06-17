import {OmegaUp, UI, API, T} from '../omegaup.js';
import Vue from 'vue';
import ContestEdit from '../components/contest/ContestEdit.vue';

OmegaUp.on('ready', function() {
    var contestAlias =
      /\/contest\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

    API.Contest.adminDetails({contest_alias: contestAlias}).then(function(contest) {
    API.Problem.list().then(function(problems){
        let contest_edit = new Vue({
            el: '#contest-edit',
            render: function(createElement) {
                return createElement('contest-edit', {
                    props: {
                        contest: contest,
                        problems: problems.results
                    },
                    on: {
                        updateContest: function(ev) {
                            API.Contest.update({
                                contest_alias: contestAlias,
                                title: ev.title,
                                description: ev.description,
                                start_time: (ev.start_time.val().getTime()) / 1000,
                                finish_time: (ev.finish_time.val().getTime()) / 1000,
                                window_length: ev.windowLength,
                                alias: ev.alias,
                                points_decay_factor: ev.pointsDecayFactor,
                                submissions_gap: ev.submissionsGap,
                                feedback: ev.feedback,
                                penalty: ev.penalty, public: ev.public,
                                scoreboard: ev.scoreboard,
                                penalty_type: ev.penaltyType,
                                show_scoreboard_after: ev.showScoreboardAfter,
                                contestant_must_register: ev.contestantMustRegister,
                                basic_information: ev.needsBasicInformation,
                                requests_user_information: ev.requestsUserInformation
                            })
                        }
                    }
                });
            },
            components: {
                'contest-edit': ContestEdit
            },
        });
    }).fail(omegaup.UI.apiError);
    }).fail(omegaup.UI.apiError);
});
