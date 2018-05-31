<template>
  <div class="contest panel">
    <div class="panel-body">
      <div class="text-center">
        <h2>{{UI.formatString(T.virtualTitle, {title:
        detail.title})}}</h2><span>{{contestDurationString}}</span>
        <form class="form"
              v-on:submit.prevent="onSubmit">
          <div class="row">
            <div class="form-group col-md-4"></div>
            <div class="form-group col-md-4">
              <label>{{T.contestNewFormStartDate}}</label> <omegaup-datetimepicker v-model=
              "startTime"></omegaup-datetimepicker>
            </div>
            <div class="form-group col-md-4"></div>
          </div><button class="btn btn-primary"
                type="submit">{{T.contestNewFormScheduleVirtualContest}}</button>
        </form>
      </div>
      <hr>
      <div class="">
        <h1>{{T.registerForContestChallenges}}</h1>
        <p>{{detail.description}}</p>
      </div>
      <div class="">
        <h1>{{T.RegisterForContestRules}}</h1>
        <ul>
          <li>{{scoreboardTimeString}}</li>
          <li>{{submissionGapString}}</li>
        </ul>
      </div>
    </div><!-- div contest-details -->
  </div>
</template>

<script>
import {API, T, UI, OmegaUp} from '../../omegaup.js';
import {Arena} from '../../arena/arena.js';
import DateTimePicker from '../DateTimePicker.vue';

export default {
  props: {detail: Object},
  data: function() {
    return { T: T, UI: UI, startTime: new Date(), }
  },
  computed: {
    contestDurationString: function() {
      let detail = this.detail;
      let deltaTime = UI.formatDelta(detail.finish_time - detail.start_time);
      // convert time to H:i:s
      let delta = deltaTime.split(":");

      return delta[0] + ' ' + T.wordsHours + ' ' + delta[1] + ' ' +
             T.wordsMinutes + ' ' + delta[2] + ' ' + T.wordsSecond;
    },
    scoreboardTimeString: function() {
      let detail = this.detail;
      let scoreboard = detail.scoreboard;
      return UI.formatString(T.contestIntroScoreboardTimePercent,
                             {window_length: scoreboard});
    },
    submissionGapString: function() {
      let detail = this.detail;
      let submissionsGap = detail.submission_gap;
      return UI.formatString(T.contestIntroSubmissionsSeparationDesc,
                             {window_length: Math.floor(submissionsGap / 60)});
    }
  },
  methods: {onSubmit: function() { this.$emit('submit', this);}},
  components: {'omegaup-datetimepicker': DateTimePicker}
}
</script>
