<template>
  <div class="panel panel-primary">
    <div class="panel-heading" v-if="!update">
      <h3 class="panel-title">{{ T.contestNew }}</h3>
    </div>
    <div class="panel-body">
      <div class="btn-group bottom-margin">
        <button class="btn btn-default" v-on:click="fillOmi()">
          {{ T.contestNewFormOmiStyle }}
        </button>
        <button class="btn btn-default" v-on:click="fillPreIoi()">
          {{ T.contestNewForm }}
        </button>
        <button class="btn btn-default" v-on:click="fillConacup()">
          {{ T.contestNewFormConacupStyle }}
        </button>
      </div>
      <form class="new_contest_form" v-on:submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.wordsTitle }}</label>
            <input class="form-control" size="30" type="text" v-model="title" />
          </div>
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormShortTitle_alias_ }}</label>
            <input
              class="form-control"
              v-bind:disabled="update"
              type="text"
              v-model="alias"
            />
            <p class="help-block">
              {{ T.contestNewFormShortTitle_alias_Desc }}
            </p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormStartDate }}</label>
            <omegaup-datetimepicker
              v-model="startTime"
            ></omegaup-datetimepicker>
            <p class="help-block">{{ T.contestNewFormStartDateDesc }}</p>
          </div>
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormEndDate }}</label>
            <omegaup-datetimepicker
              v-model="finishTime"
            ></omegaup-datetimepicker>
            <p class="help-block">{{ T.contestNewFormEndDateDesc }}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormDescription }}</label>
            <textarea
              class="form-control"
              cols="30"
              rows="10"
              v-model="description"
            ></textarea>
          </div>
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormDifferentStarts }}</label>
            <div class="checkbox">
              <label
                ><input type="checkbox" v-model="windowLengthEnabled" />
                {{ T.wordsEnable }}</label
              >
            </div>
            <input
              class="form-control"
              size="3"
              type="text"
              v-bind:disabled="!windowLengthEnabled"
              v-model="windowLength"
            />
            <p class="help-block">{{ T.contestNewFormDifferentStartsDesc }}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormScoreboardTimePercent }}</label>
            <input
              class="form-control scoreboard-time-percent"
              size="3"
              type="text"
              v-model="scoreboard"
            />
            <p class="help-block">
              {{ T.contestNewFormScoreboardTimePercentDesc }}
            </p>
          </div>
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormSubmissionsSeparation }}</label>
            <input
              class="form-control"
              size="2"
              type="text"
              v-model="submissionsGap"
            />
            <p class="help-block">
              {{ T.contestNewFormSubmissionsSeparationDesc }}
            </p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormPenaltyType }}</label>
            <select class="form-control" v-model="penaltyType">
              <option value="none">
                {{ T.contestNewFormNoPenalty }}
              </option>
              <option value="problem_open">
                {{ T.contestNewFormByProblem }}
              </option>
              <option value="contest_start">
                {{ T.contestNewFormByContests }}
              </option>
              <option value="runtime">
                {{ T.contestNewFormByRuntime }}
              </option>
            </select>
            <p class="help-block">{{ T.contestNewFormPenaltyTypeDesc }}</p>
          </div>
          <div class="form-group col-md-6">
            <label>{{ T.wordsPenalty }}</label>
            <input
              class="form-control"
              size="2"
              type="text"
              v-model="penalty"
            />
            <p class="help-block">{{ T.contestNewFormPenaltyDesc }}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.wordsFeedback }}</label>
            <select class="form-control" v-model="feedback">
              <option value="yes">
                {{ T.wordsYes }}
              </option>
              <option value="no">
                {{ T.wordsNo }}
              </option>
              <option value="partial">
                {{ T.wordsPartial }}
              </option>
            </select>
            <p class="help-block">
              {{ T.contestNewFormImmediateFeedbackDesc }}
            </p>
          </div>
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormPointDecrementFactor }}</label>
            <input
              class="form-control"
              size="4"
              type="text"
              v-model="pointsDecayFactor"
            />
            <p class="help-block">
              {{ T.contestNewFormPointDecrementFactorDesc }}
            </p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormScoreboardAtEnd }}</label>
            <select class="form-control" v-model="showScoreboardAfter">
              <option v-bind:value="true">
                {{ T.wordsYes }}
              </option>
              <option v-bind:value="false">
                {{ T.wordsNo }}
              </option>
            </select>
            <p class="help-block">{{ T.contestNewFormScoreboardAtEndDesc }}</p>
          </div>
          <div class="form-group col-md-6">
            <label>{{ T.wordsLanguages }}</label
            ><br />
            <select
              class="form-control selectpicker"
              multiple="multiple"
              v-model="languages"
            >
              <option
                v-bind:value="lang"
                v-for="(language, lang) in availableLanguages"
              >
                {{ language }}
              </option>
            </select>
            <p class="help-block">{{ T.contestNewFormLanguages }}</p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormBasicInformationRequired }}</label>
            <div class="checkbox">
              <label
                ><input type="checkbox" v-model="needsBasicInformation" />{{
                  T.wordsEnable
                }}</label
              >
            </div>
            <p class="help-block">
              {{ T.contestNewFormBasicInformationRequiredDesc }}
            </p>
          </div>
          <div class="form-group col-md-6">
            <label>{{ T.contestNewFormUserInformationRequired }}</label>
            <select class="form-control" v-model="requestsUserInformation">
              <option value="no">
                {{ T.wordsNo }}
              </option>
              <option value="optional">
                {{ T.wordsOptional }}
              </option>
              <option value="required">
                {{ T.wordsRequired }}
              </option>
            </select>
            <p class="help-block">
              {{ T.contestNewFormUserInformationRequiredDesc }}
            </p>
          </div>
        </div>
        <div class="form-group">
          <button class="btn btn-primary" type="submit" v-if="update">
            {{ T.contestNewFormUpdateContest }}
          </button>
          <button class="btn btn-primary" type="submit" v-else="">
            {{ T.contestNewFormScheduleContest }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import DateTimePicker from '../DateTimePicker.vue';

@Component({
  components: {
    'omegaup-datetimepicker': DateTimePicker,
  },
})
export default class NewForm extends Vue {
  @Prop() data!: omegaup.Contest;
  @Prop() update!: boolean;
  @Prop() allLanguages!: string[];
  @Prop() initialStartTime!: Date;
  @Prop() initialFinishTime!: Date;

  T = T;
  alias = this.data ? this.data.alias : null;
  availableLanguages = this.data
    ? this.data.available_languages
    : this.allLanguages;
  contest = this.data || null;
  contestantMustRegister = this.data
    ? this.data.contestant_must_register
    : null;
  description = this.data ? this.data.description : null;
  feedback = this.data ? this.data.feedback : 'yes';
  finishTime = this.data ? this.data.finish_time : this.initialFinishTime;
  scoreboard = this.data ? this.data.scoreboard : 100;
  languages = this.data ? this.data.languages : [];
  needsBasicInformation = this.data ? this.data.needs_basic_information : null;
  penalty = this.data ? this.data.penalty : 0;
  penaltyType = this.data ? this.data.penalty_type : 'none';
  penaltyCalcPolicy = this.data ? this.data.penalty_calc_policy : null;
  pointsDecayFactor = this.data ? this.data.points_decay_factor : 0.0;
  requestsUserInformation = this.data
    ? this.data.requests_user_information
    : 'no';
  startTime = this.data ? this.data.start_time : this.initialStartTime;
  showPenalty = this.data ? this.data.show_penalty : null;
  showScoreboardAfter = this.data ? this.data.show_scoreboard_after : true;
  submissionsGap =
    this.data && this.data.submissions_gap ? this.data.submissions_gap / 60 : 1;
  title = this.data ? this.data.title : null;
  titlePlaceHolder = '';
  windowLength = this.data ? this.data.window_length || 0 : null;
  windowLengthEnabled = this.data
    ? this.data.window_length != 0 && this.data.window_length != null
    : false;

  @Watch('windowLengthEnabled')
  onPropertyChange(newValue: boolean): void {
    if (!newValue) {
      this.windowLength = null;
    }
  }

  fillOmi(): void {
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
  }

  fillPreIoi(): void {
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
  }

  fillConacup(): void {
    this.titlePlaceHolder = T.contestNewFormTitlePlaceholderConacupStyle;
    this.windowLengthEnabled = false;
    this.windowLength = 0;
    this.scoreboard = 75;
    this.pointsDecayFactor = 0;
    this.submissionsGap = 1;
    this.feedback = 'yes';
    this.penalty = 20;
    this.penaltyType = 'none';
    this.showScoreboardAfter = true;
  }

  onSubmit() {
    if (this.update) {
      this.$emit('emit-update-contest', this);
      return;
    }
    this.$emit('create-contest', {
      alias: this.alias,
      title: this.title,
      description: this.description,
      start_time: this.startTime
        ? this.startTime.getTime() / 1000
        : this.initialStartTime.getTime() / 1000,
      finish_time: this.finishTime
        ? this.finishTime.getTime() / 1000
        : this.initialFinishTime.getTime() / 1000,
      window_length:
        this.windowLength === null || !this.windowLengthEnabled
          ? 0
          : this.windowLength,
      points_decay_factor: this.pointsDecayFactor,
      submissions_gap: this.submissionsGap ? this.submissionsGap * 60 : 60,
      languages: this.languages,
      feedback: this.feedback,
      penalty: this.penalty,
      scoreboard: this.scoreboard,
      penalty_type: this.penaltyType,
      show_scoreboard_after: this.showScoreboardAfter,
      basic_information: this.needsBasicInformation ? 1 : 0,
      requests_user_information: this.requestsUserInformation,
    });
  }
}
</script>
