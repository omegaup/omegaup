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
              v-on:click="markResolution('resolved')"
            >
              {{ T.wordsBanProblem }}
            </button>
            <button
              class="btn btn-success"
              v-bind:disabled="!rationale"
              v-on:click="markResolution('banned')"
            >
              {{ T.wordsKeepProblem }}
            </button>
            <button
              class="btn btn-warning"
              v-bind:disabled="!rationale"
              v-on:click="markResolution('warning')"
            >
              {{ T.wordsWarningProblem }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';

interface QualityNominationContents {
  original: string;
  rationale: string;
  reason: string;
}

@Component
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

  userUrl(alias: string): string {
    return `/profile/${alias}/`;
  }

  problemUrl(alias: string): string {
    return `/arena/problem/${alias}/`;
  }

  markResolution(newStatus: string): void {
    this.$emit('mark-resolution', this, newStatus);
  }
}
</script>

<style>
textarea {
  margin: 0 0 10px;
}
</style>
