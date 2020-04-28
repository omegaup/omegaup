<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.wordsReviewingProblem }}</h2>
    </div>
    <div class="panel-body">
      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-3">
            <strong>{{ T.qualityNominationType }}</strong>
          </div>
          <div class="col-sm-4">
            {{ this.nomination }}
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <strong>{{ T.wordsNominator }}</strong>
          </div>
          <div class="col-sm-4">
            {{ this.nominator.name }} (<a
              v-bind:href="userUrl(this.nominator.username)"
              >{{ this.nominator.username }}</a
            >)
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <strong>{{ T.wordsProblem }}</strong>
          </div>
          <div class="col-sm-4">
            {{ this.problem.title }} (<a
              v-bind:href="problemUrl(this.problem.alias)"
              >{{ this.problem.alias }}</a
            >)
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <strong>{{ T.wordsAuthor }}</strong>
          </div>
          <div class="col-sm-4">
            {{ this.author.name }} (<a
              v-bind:href="userUrl(this.author.username)"
              >{{ this.author.username }}</a
            >)
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <strong>{{ T.wordsDetails }}</strong>
          </div>
          <div class="col-sm-8">
            <pre>{{ this.contents }}</pre>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <strong>{{ T.banProblemFormQuestion }}</strong>
            <span
              aria-hidden="true"
              class="glyphicon glyphicon-info-sign"
              data-placement="top"
              data-toggle="tooltip"
              v-bind:title="T.banProblemFormComments"
            ></span>
          </div>
          <div
            class="col-sm-8"
            v-bind:class="{ 'has-error': !rationale, 'has-success': rationale }"
          >
            <textarea
              class="form-control"
              name="rationale"
              type="text"
              v-model="rationale"
            ></textarea>
          </div>
        </div>
        <div
          class="row"
          v-if="this.nomination == 'demotion' &amp;&amp; this.reviewer == true"
        >
          <div class="col-sm-3">
            <strong>{{ T.wordsVerdict }}</strong>
          </div>
          <div class="col-sm-8">
            <button
              class="btn btn-danger"
              v-bind:disabled="!rationale"
              v-on:click="mark('banned')"
            >
              {{ T.wordsBanProblem }}
            </button>
            <button
              class="btn btn-success"
              v-bind:disabled="!rationale"
              v-on:click="mark('resolved')"
            >
              {{ T.wordsKeepProblem }}
            </button>
            <button
              class="btn btn-warning"
              v-bind:disabled="!rationale"
              v-on:click="mark('warning')"
            >
              {{ T.wordsWarningProblem }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <!--<div class="confirmation" v-if="confirmationShow">
      <button class="close" type="button" v-on:click="onHide">×</button>
      <div class="form-group">
        <div class="question-text">
          {{ T.demotionProblemMultipleQuestion}}
        </div>
        <button
          class="btn btn-success"
          v-bind:disabled="!rationale"
          v-on:click="markResolution(true)"
          >
          {{ T.demotionProblemMultipleAnswerYes }}
        </button>
        <button
          class="btn btn-danger"
          v-bind:disabled="!rationale"
          v-on:click="markResolution(false)"
          >
          {{ T.demotionProblemMultipleAnswerNo }}
        </button>
      </div>
    </div>-->
    <omegaup-common-confirmation
      v-if="confirmationShow"
      v-bind:question="T.demotionProblemMultipleQuestion"
      v-bind:answer-yes="T.demotionProblemMultipleAnswerYes"
      v-bind:answer-no="T.demotionProblemMultipleAnswerNo"
      v-on:close="confirmationShow = false"
      v-on:yes="markResolution(true)"
      v-on:no="markResolution(false)"
    ></omegaup-common-confirmation>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import confirmation from '../common/Confirmation.vue';

interface QualityNominationContents {
  original: string;
  rationale: string;
  reason: string;
}

@Component({
  components: {
    'omegaup-common-confirmation': confirmation,
  },
})
export default class QualityNominationDetails extends Vue {
  @Prop() author!: omegaup.User;
  @Prop() contents!: QualityNominationContents;
  @Prop() initialRationale!: string;
  @Prop() nomination!: string;
  @Prop() nominator!: omegaup.User;
  @Prop() problem!: omegaup.Problem;
  @Prop() qualitynomination_id!: number;
  @Prop() reviewer!: boolean;
  @Prop() votes!: omegaup.NominationVote[];

  T = T;
  rationale = this.initialRationale;
  confirmationShow = false;
  status = 'banned';

  userUrl(alias: string): string {
    return `/profile/${alias}/`;
  }

  problemUrl(alias: string): string {
    return `/arena/problem/${alias}/`;
  }

  markResolution(all: boolean): void {
    this.$emit('mark-resolution', this, this.status, all);
  }

  mark(status: string): void {
    this.status = status;
    this.confirmationShow = true;
  }

  onHide(): void {
    this.confirmationShow = false;
  }
}
</script>

<style>
textarea {
  margin: 0 0 10px;
}

.confirmation {
  position: fixed;
  top: 25%;
  left: 30%;
  z-index: 9999999 !important;
  width: 350px;
  height: 150px;
  margin: 2em auto 0 auto;
  border: 2px solid #ccc;
  padding: 1em;
  overflow: auto;
  background: gray;
}

.confirmation .question-text {
  font-weight: bold;
  padding-bottom: 4px;
  text-align: center;
}
</style>
