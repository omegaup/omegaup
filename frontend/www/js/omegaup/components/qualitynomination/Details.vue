<template>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2 class="panel-title">{{T.wordsReviewingProblem}}</h2>
        </div>
        <div class="panel-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-3"><strong>{{T.qualityNominationType}}</strong></div>
                    <div class="col-sm-4">{{this.nomination}}</div>
                </div>

            <div class="row">
                <div class="col-sm-3"><strong>{{T.qualityUserThatNominated}}</strong></div>
                <div class="col-sm-4">{{ this.nominator.name}} ({{this.nominator.username}})</div>
            </div>

            <div class="row">
                <div class="col-sm-3"><strong>{{T.wordsProblem}}</strong> </div>
                <div class="col-sm-4">{{this.problem.title}} ({{this.problem.alias}})
                    - <a v-bind:href="userUrl(this.problem.alias)">{{T.problemEditGoToProblem}}</a>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3"><strong>{{T.wordsDetails}}</strong> </div>
                <div class="col-sm-4">{{this.contents}}</div>
            </div>

            <div class="row">
                <div class="col-sm-3"><strong>{{T.wordsVerdict}}</strong></div>
                <div class="col-sm-4">
                  <button class="btn btn-danger">{{T.wordsBanProblem}}</button> 
                  <button class="btn btn-success">{{T.wordsKeepProblem}}</button>
                </div>
            </div>
        </div>
      </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {
    contents: Array,
    nomination: String,
    nominator: {username: String, name: String},
    problem: {alias: String, title: String},
    qualitynomination_id: Number,
    votes: Array
  },
  data: function() {
    return {
      T: T,
    };
  },
  methods: {
    userUrl: function(alias) { return '/arena/problem/' + alias + '/';},
  }
};
</script>
