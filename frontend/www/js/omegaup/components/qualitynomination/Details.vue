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
            {{ this.nominator.name }} (<a v-bind:href="userUrl(this.nominator.username)">{{
            this.nominator.username }}</a>)
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <strong>{{ T.wordsProblem }}</strong>
          </div>
          <div class="col-sm-4">
            {{ this.problem.title }} (<a v-bind:href="problemUrl(this.problem.alias)">{{
            this.problem.alias }}</a>)
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <strong>{{ T.wordsAuthor }}</strong>
          </div>
          <div class="col-sm-4">
            {{ this.author.name }} (<a v-bind:href="userUrl(this.author.username)">{{
            this.author.username }}</a>)
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <strong>{{ T.wordsDetails }}</strong>
          </div>
          <div class="col-sm-8">
            <pre>{{ this.contents | pretty }}</pre>
          </div>
        </div>
        <div class="row"
             v-if="this.nomination == 'demotion' &amp;&amp; this.reviewer == true">
          <div class="col-sm-3">
            <strong>{{ T.wordsVerdict }}</strong>
          </div>
          <div class="col-sm-4">
            <button class="btn btn-danger"
                 v-on:click="markResolution(true, false)">{{ T.wordsBanProblem }}</button>
                 <button class="btn btn-success"
                 v-on:click="markResolution(false, false)">{{ T.wordsKeepProblem }}</button>
          </div>
        </div>
      </div>
    </div>
    <div class="qualitynomination-demotionpopup">
      <div class="panel panel-default popup"
           v-show="showReportDialog">
        <template v-if="currentView == 'question'">
          <button class="close"
                    type="button"
                    v-on:click="onHide">×</button>
          <div class="title-text">
            {{ T.wordsBanProblem }}
          </div>
          <div class="form-group">
            <div class="question-text">
              {{ T.banProblemFormQuestion }}
            </div>
          </div>
          <div class="form-group">
            <label class="control-label">{{ T.banProblemFormComments }}</label>
            <textarea class="input-text"
                 name="rationale"
                 type="text"
                 v-model="rationale"></textarea>
          </div>
          <div class="button-row">
            <button class="col-md-4 btn btn-primary"
                 v-on:click="markResolution(true, true)">{{ T.wordsSend }}</button>
          </div>
        </template>
        <template v-if="currentView == 'thanks'">
          <div class="centered">
            <h1>{{ T.reportProblemFormThanksForReview }}</h1>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import {T, API} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {
    contents: Object,
    nomination: String,
    nominator: {username: String, name: String},
    author: {username: String, name: String},
    problem: {alias: String, title: String},
    qualitynomination_id: Number,
    reviewer: Boolean,
    votes: Array,
    rationale: String
  },
  data: function() {
    return {
      T: T,
      currentView: 'question',
      showReportDialog: false,
      selectedReason: undefined,
    };
  },
  methods: {
    onHide: function() {
      this.showReportDialog = false;
      this.currentView = 'question';
    },
    userUrl: function(alias) { return '/profile/' + alias + '/';},
    problemUrl: function(alias) { return '/arena/problem/' + alias + '/';},
    markResolution: function(banProblem, confirmation) {
      if (banProblem && !confirmation) {
        this.showReportDialog = true;
        this.currentView = 'question';
      } else {
        let newStatus = banProblem ? 'approved' : 'denied';
        API.QualityNomination.resolve({
                               problem_alias: this.problem.alias,
                               status: newStatus,
                               qualitynomination_id: this.qualitynomination_id,
                               rationale: this.rationale
                             })
            .then(function(data) {
              omegaup.UI.success(T.qualityNominationResolutionSuccess);
            })
            .fail(UI.apiError);
        this.currentView = 'thanks';
        setTimeout(() => this.onHide(), 3000);
      }
    },
  }
};
</script>

<style>

.qualitynomination-demotionpopup .popup {
  position: fixed;
  bottom: 10px;
  right: 4%;
  z-index: 9999999 !important;
  width: 420px;
  height: 370px;
  margin: 2em auto 0 auto;
  border: 2px solid #ccc;
  padding: 1em;
  overflow: auto;
}

.qualitynomination-demotionpopup .question-text {
  font-weight: bold;
  padding-bottom: 4px;
}

.qualitynomination-demotionpopup .title-text {
  font-weight: bold;
  font-size: 1em;
  padding-bottom: 1em;
}

.qualitynomination-demotionpopup .control-label {
  width: 100%;
}

.qualitynomination-demotionpopup .input-text {
  height: 100px;
  width: 100%;
}

.qualitynomination-demotionpopup .input-line {
  width: 100%;
}

.qualitynomination-demotionpopup .button-row {
  width: 100%;
  margin-left: 66%;
}

.qualitynomination-demotionpopup .centered {
  margin-left: 20%;
  margin-top: 24%;
  position: absolute;
}
</style>
