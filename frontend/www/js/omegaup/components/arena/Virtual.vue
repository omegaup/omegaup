<template>
    <div class="contest panel">
        <div class="panel-body">
            <div class="text-center">
                <h2>{{detail.title + ' - ' + T.wordsVirtual}}</h2>
                    <span>{{detail.contestDurationString}}</span>
                    <form>
                        <div class="row">
                            <div class="form-group col-md-4"></div>
                            <div class="form-group col-md-4">
                                <label>{{T.contestNewFormStartDate}}</label>
                                <omegaup-datetimepicker v-model="start_time"></omegaup-datetimepicker>
                            </div>
                            <div class="form-group col-md-4"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">{{T.contestNewFormScheduleVirtualContest}}</button>
                    </form>
                </div>
            </div> <!-- div contest-details -->
            <hr>
            <div class="">
                <h1>{{T.registerForContestChallenges}}</h1>
                <p>{{detail.description}}</p>
            </div>
            <div class="">
                <h1>{{T.RegisterForContestRules}}</h1>
                <ul>
                    <li>{{detail.scoreboardTimeString}}</li>
                    <li>{{detail.submissionGapString}}</li>
                </ul>
            </div>
        </div><!-- panel-->
    </div>
</template>

<script>
import {API, T, UI, OmegaUp} from '../../omegaup.js';
import {Arena} from '../../arena/arena.js';
import DateTimePicker from '../DateTimePicker.vue';

export default {
    props: {},
    data: function() {
        return {
            T: T,
            UI: UI,
            detail: {},
            start_time: new Date(),
        }
    },
    created: function() {
        var self = this;

        let contestAlias = /\/arena\/([^\/]+)\/virtual/.exec(window.location.pathname)[1];

        API.Contest.details({contest_alias: contestAlias}).then(function(response){
            var detail = response

            //convert time to H:i:s
            let delta = detail.finish_time - detail.start_time
            let deltaHour = Math.floor(delta / 3600);
            let deltaMinute = Math.floor(delta % 3600 / 60);
            let deltaSecond = delta % 3600 % 60;

            // String Format
            detail.contestDurationString = deltaHour + ' ' + T.wordsHours + ' '+ deltaMinute + ' ' + T.wordsMinutes + ' ' + deltaSecond + ' ' + T.wordsSecond;
            detail.scoreboardTimeString = UI.formatString(T.contestIntroScoreboardTimePercent, {window_length: detail.scoreboard});
            detail.submissionGapString = UI.formatString(T.contestIntroSubmissionsSeparationDesc, {window_length: Math.floor(detail.submissions_gap / 60)});

            self.detail = detail;
            self.start_time = new Date();
        });
    },
    components: {
        'omegaup-datetimepicker': DateTimePicker
    }
}
</script>
