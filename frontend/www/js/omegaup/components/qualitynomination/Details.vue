<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{T.reviewing}} {{this.problem.title}} ({{this.problem.alias}})</h2>
    </div>
    <div class="panel-body">
      <div id="SettingsPage_Content">
        <ul class="uiList fbSettingsList _4kg _6-h _4ks">
          <li class="fbSettingsListItem clearfix uiListItem"></li>
          <li class="fbSettingsListItem clearfix uiListItem">
            <a class="pvm phs fbSettingsListLink clearfix"><span class=
            "pls fbSettingsListItemLabel"><strong>{{T.qualityNominationType}}</strong></span>
            <span class="fbSettingsListItemContent fcg">{{this.nomination}}</span></a>
          </li>
          <li class="fbSettingsListItem clearfix uiListItem"></li>
          <li class="fbSettingsListItem clearfix uiListItem">
            <a class="pvm phs fbSettingsListLink clearfix"><span class=
            "pls fbSettingsListItemLabel"><strong>{{T.qualityUserThatNominated}}</strong></span>
            <span class="fbSettingsListItemContent fcg">{{this.nominator.name}}
            ({{this.nominator.username}})</span></a>
          </li>
          <li class="fbSettingsListItem clearfix uiListItem"></li>
          <li class="fbSettingsListItem clearfix uiListItem">
            <a class="pvm phs fbSettingsListLink clearfix"><span class=
            "pls fbSettingsListItemLabel"><strong>{{T.wordsProblem}}</strong></span> <span class=
            "fbSettingsListItemContent fcg">{{this.problem.title}}
            ({{this.problem.alias}})</span></a> <a v-bind:href=
            "userUrl(this.problem.alias)">{{T.problemEditGoToProblem}}</a>
          </li>
          <li class="fbSettingsListItem clearfix uiListItem"></li>
          <li class="fbSettingsListItem clearfix uiListItem">
            <a class="pvm phs fbSettingsListLink clearfix"><span class=
            "pls fbSettingsListItemLabel"><strong>{{T.wordsDetails}}</strong></span> <span class=
            "fbSettingsListItemContent fcg">{{this.contents}}</span></a>
          </li>
          <li class="fbSettingsListItem clearfix uiListItem"></li>
          <li class="fbSettingsListItem clearfix uiListItem">
            <a class="pvm phs fbSettingsListLink clearfix"><span class=
            "pls fbSettingsListItemLabel"><strong>{{T.wordsVerdict}}</strong></span> <span class=
            "fbSettingsListItemContent fcg">
            <div class="">
              <button class="btn btn-danger">{{T.wordsBanProblem}}</button> <button class=
              "btn btn-success">{{T.wordsKeepProblem}}</button>
            </div></span></a>
          </li>
        </ul>
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
