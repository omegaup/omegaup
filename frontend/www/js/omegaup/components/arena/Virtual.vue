<template>
  <div class="contest panel">
    <div class="panel-body">
      <div class="text-center">
        <h2>{{UI.formatString(T.virtualTitle, {title:
        title})}}</h2><span>{{contestDurationString}}</span>
        <form class="form"
              v-on:submit.prevent="onSubmit">
          <div class="row">
            <div class="form-group col-md-4"></div>
            <div class="form-group col-md-4">
              <label>{{T.contestNewFormStartDate}}</label> <omegaup-datetimepicker v-model=
              "virtualContestStartTime"></omegaup-datetimepicker>
            </div>
            <div class="form-group col-md-4"></div>
          </div><button class="btn btn-primary"
                type="submit">{{T.contestNewFormScheduleVirtualContest}}</button>
        </form>
      </div>
      <hr>
      <div class="">
        <h1>{{T.registerForContestChallenges}}</h1>
        <p>{{description}}</p>
      </div>
      <div class="">
        <h1>{{T.registerForContestRules}}</h1>
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
  props: {
    title: String,
    description: String,
    startTime: Date,
    finishTime: Date,
    scoreboard: String,
    submissionGap: Number
  },
  data: function() {
    return { T: T, UI: UI, virtualContestStartTime: new Date(), }
  },
  computed: {
    contestDurationString: function() {
      return UI.formatDelta(this.finishTime - this.startTime);
    },
    scoreboardTimeString: function() {
      return UI.formatString(T.contestIntroScoreboardTimePercent,
                             {window_length: this.scoreboard});
    },
    submissionGapString: function() {
      return UI.formatString(
          T.contestIntroSubmissionsSeparationDesc,
          {window_length: Math.floor(this.submissionsGap / 60)});
    }
  },
  methods: {onSubmit: function() { this.$emit('submit', this);}},
  components: {'omegaup-datetimepicker': DateTimePicker}
}
</script>
