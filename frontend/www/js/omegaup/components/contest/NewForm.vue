<template>
  <div class="panel panel-primary">
    <div class="panel-heading"
         v-if="!update">
      <h3 class="panel-title">{{T.contestNew}}</h3>
    </div>
    <div class="panel-body">
      <div class="btn-group bottom-margin"
           v-if="update">
        <button class="btn btn-default"
             v-on:click="fillOmi()">{{T.contestNewFormOmiStyle}}</button> <button class=
             "btn btn-default"
             v-on:click="fillPreIoi()">{{T.contestNewForm}}</button> <button class=
             "btn btn-default"
             v-on:click="fillConacup()">{{T.contestNewFormConacupStyle}}</button>
      </div>
      <form class="new_contest_form"
            v-on:submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{T.wordsTitle}}</label> <input class="form-control"
                 size="30"
                 type="text"
                 v-model="title">
          </div>
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormShortTitle_alias_}}</label> <input class="form-control"
                 disabled="update"
                 type="text"
                 v-model="alias">
            <p class="help-block">{{T.contestNewFormShortTitle_alias_Desc}}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormStartDate}}</label> <omegaup-datetimepicker v-model=
            "startTime"></omegaup-datetimepicker>
            <p class="help-block">{{T.contestNewFormStartDateDesc}}</p>
          </div>
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormEndDate}}</label> <omegaup-datetimepicker v-model=
            "finishTime"></omegaup-datetimepicker>
            <p class="help-block">{{T.contestNewFormEndDateDesc}}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormDescription}}</label>
            <textarea class="form-control"
                 cols="30"
                 rows="10"
                 v-model="description"></textarea>
          </div>
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormDifferentStarts}}</label>
            <div class="checkbox">
              <label><input type="checkbox"
                     v-model="windowLengthEnabled"> {{T.wordsEnable}}</label>
            </div><input class="form-control"
                 size="3"
                 type="text"
                 v-bind:disabled="!windowLengthEnabled"
                 v-model="windowLength">
            <p class="help-block">{{T.contestNewFormDifferentStartsDesc}}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormScoreboardTimePercent}}</label> <input class="form-control"
                 size="3"
                 type="text"
                 v-model="scoreboard">
            <p class="help-block">{{T.contestNewFormScoreboardTimePercentDesc}}</p>
          </div>
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormSubmissionsSeparation}}</label> <input class="form-control"
                 size="2"
                 type="text"
                 v-model="submissionsGap"
                 value="1">
            <p class="help-block">{{T.contestNewFormSubmissionsSeparationDesc}}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormPenaltyType}}</label> <select class="form-control"
                 v-model="penaltyType">
              <option value="none">
                {{T.contestNewFormNoPenalty}}
              </option>
              <option value="problem_open">
                {{T.contestNewFormByProblem}}
              </option>
              <option value="contest_start">
                {{T.contestNewFormByContests}}
              </option>
              <option value="runtime">
                {{T.contestNewFormByRuntime}}
              </option>
            </select>
            <p class="help-block">{{T.contestNewFormPenaltyTypeDesc}}</p>
          </div>
          <div class="form-group col-md-6">
            <label>{{T.wordsPenalty}}</label> <input class="form-control"
                 size="2"
                 type="text"
                 v-model="penalty">
            <p class="help-block">{{T.contestNewFormPenaltyDesc}}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{T.wordsFeedback}}</label> <select class="form-control"
                 v-model="feedback">
              <option value="yes">
                {{T.wordsYes}}
              </option>
              <option value="no">
                {{T.wordsNo}}
              </option>
              <option value="partial">
                {{T.wordsPartial}}
              </option>
            </select>
            <p class="help-block">{{T.contestNewFormImmediateFeedbackDesc}}</p>
          </div>
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormPointDecrementFactor}}</label> <input class="form-control"
                 size="4"
                 type="text"
                 v-model="pointsDecayFactor">
            <p class="help-block">{{T.contestNewFormPointDecrementFactorDesc}}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormScoreboardAtEnd}}</label> <select class="form-control"
                 v-model="showScoreboardAfter">
              <option value="1">
                {{T.wordsYes}}
              </option>
              <option value="0">
                {{T.wordsNo}}
              </option>
            </select>
            <p class="help-block">{{T.contestNewFormScoreboardAtEndDesc}}</p>
          </div>
          <div class="form-group col-md-6">
            <label>{{T.wordsLanguages}}</label><br>
            <select class="form-control"
                 multiple="multiple"
                 v-model="languages">
              <option v-bind:value="lang"
                      v-for="(language, lang) in availableLanguages">
                {{language}}
              </option>
            </select>
            <p class="help-block">{{T.contestNewFormLanguages}}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormBasicInformationRequired}}</label>
            <div class="checkbox">
              <label><input type="checkbox"
                     v-model="needsBasicInformation">{{T.wordsEnable}}</label>
            </div>
            <p class="help-block">{{T.contestNewFormBasicInformationRequiredDesc}}</p>
          </div>
          <div class="form-group col-md-6">
            <label>{{T.contestNewFormUserInformationRequired}}</label> <select class="form-control"
                 v-model="requestsUserInformation">
              <option value="no">
                {{T.wordsNo}}
              </option>
              <option value="optional">
                {{T.wordsOptional}}
              </option>
              <option value="required">
                {{T.wordsRequired}}
              </option>
            </select>
            <p class="help-block">{{T.contestNewFormUserInformationRequiredDesc}}</p>
          </div>
        </div>
        <div class="form-group">
          <button class="btn btn-primary"
               type="submit"
               v-if="update">{{T.contestNewFormUpdateContest}}</button> <button class=
               "btn btn-primary"
               type="submit"
               v-else="">{{T.contestNewFormScheduleContest}}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import DateTimePicker from '../DateTimePicker.vue';

export default {
  props: {
    update: Boolean,
    data: Object,
  },
  data: function() {
    return {
      alias: this.data.alias,
      availableLanguages: this.data.available_languages,
      contest: this.data,
      contestantMustRegister: this.data.contestant_must_register,
      description: this.data.description,
      feedback: this.data.feedback,
      finishTime: this.data.finish_time,
      scoreboard: this.data.scoreboard,
      languages: this.data.languages,
      needsBasicInformation: this.data.needs_basic_information,
      penalty: this.data.penalty,
      penaltyType: this.data.penalty_type,
      penaltyCalcPolicy: this.data.penalty_calc_policy,
      pointsDecayFactor: this.data.points_decay_factor,
      requestsUserInformation: this.data.requests_user_information,
      startTime: this.data.start_time,
      showPenalty: this.data.show_penalty,
      showScoreboardAfter: this.data.show_scoreboard_after,
      submissionsGap: this.data.submissions_gap,
      title: this.data.title,
      titlePlaceHolder: '',
      windowLength:
          (this.data.window_length == 0 || this.data.window_length == null) ?
              '' :
              this.data.window_length,
      windowLengthEnabled: this.data.window_length != 0 &&
                               this.data.window_length != '' &&
                               this.data.window_length != null,
      T: T,
    };
  },
  methods: {
    fillOmi: function() {
      this.titlePlaceHolder = T.contestNewFormTitlePlaceholderOmiStyle;
      this.windowLengthEnabled = false;
      this.windowLength = 0;
      this.scoreboard = 0;
      this.pointsDecayFactor = 0;
      this.submissionsGap = 1;
      this.feedback = 'yes';
      this.penalty = 0;
      this.penaltyType = 'none';
      this.showScoreboardAfter = true;
    },
    fillPreIoi: function() {
      this.titlePlaceHolder = T.contestNewFormTitlePlaceholderIoiStyle;
      this.windowLengthEnabled = true;
      this.windowLength = 180;
      this.scoreboard = 0;
      this.pointsDecayFactor = 0;
      this.submissionsGap = 0;
      this.feedback = 'yes';
      this.penalty = 0;
      this.penaltyType = 'none';
      this.showScoreboardAfter = true;
    },
    fillConacup: function() {
      this.titlePlaceHolder = T.contestNewFormTitlePlaceholderConacupStyle;
      this.windowLengthEnabled = false;
      this.windowLength = '';
      this.scoreboard = 75;
      this.pointsDecayFactor = 0;
      this.submissionsGap = 1;
      this.feedback = 'yes';
      this.penalty = 20;
      this.penaltyType = 'none';
      this.showScoreboardAfter = true;
    },
    onSubmit: function() { this.$parent.$emit('update-contest', this);},
  },
  components: {
    'omegaup-datetimepicker': DateTimePicker,
  },
};
</script>
