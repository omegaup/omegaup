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
            <pre>{{ this.contents }}</pre>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <strong>{{ T.banProblemFormQuestion }}</strong> <span aria-hidden="true"
                 class="glyphicon glyphicon-info-sign"
                 data-placement="top"
                 data-toggle="tooltip"
                 v-bind:title="T.banProblemFormComments"></span>
          </div>
          <div class="col-sm-8">
            <textarea class="form-control"
                 name="rationale"
                 type="text"
                 v-model="rationale"></textarea>
          </div>
        </div>
        <div class="row"
             v-if="this.nomination == 'demotion' &amp;&amp; this.reviewer == true">
          <div class="col-sm-3">
            <strong>{{ T.wordsVerdict }}</strong>
          </div>
          <div class="col-sm-8">
            <button class="btn btn-danger"
                 v-on:click="markResolution(true)">{{ T.wordsBanProblem }}</button> <button class=
                 "btn btn-success"
                 v-on:click="markResolution(false)">{{ T.wordsKeepProblem }}</button>
          </div>
        </div>
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
    votes: Array
  },
  data: function() { return {T: T, rationale: ''};},
  methods: {
    userUrl: function(alias) { return '/profile/' + alias + '/';},
    problemUrl: function(alias) { return '/arena/problem/' + alias + '/';},
    markResolution: function(banProblem) {
      if (!this.rationale && banProblem) {
        omegaup.UI.error(T.editFieldRequired);
        return;
      }
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
    },
  }
};
</script>

<style>

textarea {
  margin: 0 0 10px;
}
</style>
