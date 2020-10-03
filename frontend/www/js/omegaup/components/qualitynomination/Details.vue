<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.wordsReviewingProblem }}</h2>
    </div>
    <div class="card-body">
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
            <pre class="border rounded bg-light">{{ this.contents }}</pre>
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
            v-bind:class="{
              'has-error': !rationale,
              'has-success': rationale,
            }"
          >
            <textarea
              v-model="rationale"
              class="form-control"
              name="rationale"
              type="text"
            ></textarea>
          </div>
        </div>
        <div
          v-if="this.nomination == 'demotion' && this.reviewer == true"
          class="row"
        >
          <div class="col-sm-3">
            <strong>{{ T.wordsVerdict }}</strong>
          </div>
          <div class="col-sm-8 text-center">
            <button
              class="btn btn-danger"
              v-bind:disabled="!rationale"
              v-on:click="showConfirmationDialog('banned')"
            >
              {{ T.wordsBanProblem }}
            </button>
            <button
              class="btn btn-success"
              v-bind:disabled="!rationale"
              v-on:click="showConfirmationDialog('resolved')"
            >
              {{ T.wordsKeepProblem }}
            </button>
            <button
              class="btn btn-warning"
              v-bind:disabled="!rationale"
              v-on:click="showConfirmationDialog('warning')"
            >
              {{ T.wordsWarningProblem }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <omegaup-common-confirmation
      v-if="showConfirmation"
      v-bind:question="T.demotionProblemMultipleQuestion"
      v-bind:answer-yes="T.demotionProblemMultipleAnswerYes"
      v-bind:answer-no="T.demotionProblemMultipleAnswerNo"
      v-on:close="showConfirmation = false"
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
  showConfirmation = false;
  status = 'banned';

  userUrl(alias: string): string {
    return `/profile/${alias}/`;
  }

  problemUrl(alias: string): string {
    return `/arena/problem/${alias}/`;
  }

  markResolution(all: boolean): void {
    this.showConfirmation = false;
    this.$emit('mark-resolution', this, this.status, all);
  }

  showConfirmationDialog(status: string): void {
    this.status = status;
    this.showConfirmation = true;
  }
}
</script>

<style>
textarea {
  margin: 0 0 10px;
}
</style>
