<template>
  <div class="contest panel">
    <div class="panel-body">
      <div class="text-center">
        <h2>{{detail.title + ' - ' +
        T.wordsVirtual}}</h2><span>{{detail.contestDurationString}}</span>
        <form class="form"
              v-on:submit.prevent="onSubmit">
          <div class="row">
            <div class="form-group col-md-4"></div>
            <div class="form-group col-md-4">
              <label>{{T.contestNewFormStartDate}}</label> <omegaup-datetimepicker v-model=
              "start_time"></omegaup-datetimepicker>
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
          <li>{{detail.scoreboardTimeString}}</li>
          <li>{{detail.submissionGapString}}</li>
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
  props: {},
  data: function() {
    return {
      T: T, UI: UI, detail: {}, start_time: new Date(),
          contest_alias: /\/arena\/([^\/]+)\/virtual/.exec(
              window.location.pathname)[1]
    }
  },
  created: function() {
    var self = this;

    API.Contest.publicDetails({contest_alias: this.contest_alias})
        .then(function(response) {
          var detail = response;

          // convert time to H:i:s
          let delta = detail.finish_time - detail.start_time;
          let deltaHour = Math.floor(delta / 3600000);
          let deltaMinute = Math.floor(delta % 3600000 / 60000);
          let deltaSecond = delta % 3600000 % 60000;

          // String Format
          detail.contestDurationString =
              deltaHour + ' ' + T.wordsHours + ' ' + deltaMinute + ' ' +
              T.wordsMinutes + ' ' + deltaSecond + ' ' + T.wordsSecond;
          detail.scoreboardTimeString =
              UI.formatString(T.contestIntroScoreboardTimePercent,
                              {window_length: detail.scoreboard});
          detail.submissionGapString = UI.formatString(
              T.contestIntroSubmissionsSeparationDesc,
              {window_length: Math.floor(detail.submissions_gap / 60)});

          self.detail = detail;
          self.start_time = new Date();
        })
        .fail(UI.apiError);
  },
  methods: {
    onSubmit: function() {
      API.Contest.createVirtual({
                   alias: this.contest_alias,
                   start_time: this.start_time.getTime() / 1000
                 })
          .then(function(response) {
            let virtual_contest_alias = response.alias;
            window.location = "/contest/" + virtual_contest_alias + "/edit/";
          })
          .fail(UI.apiError);
    }
  },
  components: {'omegaup-datetimepicker': DateTimePicker}
}
</script>
