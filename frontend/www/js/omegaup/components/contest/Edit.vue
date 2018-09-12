<template>
  <div>
    <div class="page-header">
      <h1>{{UI.formatString(T.contestEditWithTitle, {title: UI.contestTitle(contest)})}}
      <small><a v-bind:href=
      "`/arena/${contest.alias}/`">{{T.contestDetailsGoToContest}}</a></small></h1>
    </div>
    <ul class="nav nav-tabs nav-justified">
      <li v-bind:class="{active : !virtual}"
          v-if="!virtual"
          v-on:click="showTab = 'new_form'">
        <a data-toggle="tab">{{T.contestEdit}}</a>
      </li>
      <li class="problems"
          v-if="!virtual"
          v-on:click="showTab = 'problems'">
        <a data-toggle="tab">{{T.wordsAddProblem}}</a>
      </li>
      <li v-if="!virtual"
          v-on:click="showTab = 'publish'">
        <a data-toggle="tab">{{T.contestNewFormAdmissionMode}}</a>
      </li>
      <li class="contestants"
          v-bind:class="{active: virtual}"
          v-on:click="showTab = 'contestants'">
        <a data-toggle="tab">{{T.contestAdduserAddContestant}}</a>
      </li>
      <li v-on:click="showTab = 'admins'">
        <a data-toggle="tab">{{T.omegaupTitleContestAddAdmin}}</a>
      </li>
      <li v-on:click="showTab = 'group_admins'">
        <a data-toggle="tab">{{T.omegaupTitleContestAddGroupAdmin}}</a>
      </li>
      <li v-if="!virtual"
          v-on:click="showTab = 'links'">
        <a data-toggle="tab">{{T.showLinks}}</a>
      </li>
      <li v-if="!virtual"
          v-on:click="showTab = 'clone'">
        <a data-toggle="tab">{{T.courseEditClone}}</a>
      </li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane active"
           v-if="showTab === 'new_form'">
        <omegaup-contest-new-form v-bind:data="contest"
             v-bind:update="true"></omegaup-contest-new-form>
      </div>
      <div class="tab-pane active problems"
           v-if="showTab === 'problems'">
        <omegaup-contest-add-problem v-bind:data="problems"></omegaup-contest-add-problem>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'publish'">
        <omegaup-contest-publish v-bind:data="contest"></omegaup-contest-publish>
      </div>
      <div class="tab-pane active contestants"
           v-if="showTab === 'contestants'">
        <omegaup-contest-contestant v-bind:data="users"></omegaup-contest-contestant>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'admins'">
        <omegaup-contest-admins v-bind:data="admins"></omegaup-contest-admins>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'group_admins'">
        <omegaup-contest-group-admins v-bind:data="groupAdmins"></omegaup-contest-group-admins>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'links'">
        <omegaup-contest-links v-bind:data="contest"></omegaup-contest-links>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'clone'">
        <omegaup-contest-clone></omegaup-contest-clone>
      </div>
    </div>
  </div>
</template>

<script>
import {T, UI} from '../../omegaup.js';
import contest_AddProblem from './AddProblem.vue';
import contest_Admins from './Admins.vue';
import contest_Clone from './Clone.vue';
import contest_Contestant from './Contestant.vue';
import contest_GroupAdmins from './GroupAdmins.vue';
import contest_Links from './Links.vue';
import contest_NewForm from './NewForm.vue';
import contest_Publish from './Publish.vue';

export default {
  props: {
    data: Object,
  },
  data: function() {
    return {
      showTab: UI.isVirtual(this.data.contest) ? 'contestants' : 'new_form',
      T: T, virtual: UI.isVirtual(this.data.contest),
      UI: UI,
      contest: this.data.contest,
      problems: this.data.problems,
      users: this.data.users,
      admins: this.data.admins,
      groupAdmins: this.data.groupAdmins,
    };
  },
  components: {
    'omegaup-contest-add-problem': contest_AddProblem,
    'omegaup-contest-admins': contest_Admins,
    'omegaup-contest-clone': contest_Clone,
    'omegaup-contest-contestant': contest_Contestant,
    'omegaup-contest-group-admins': contest_GroupAdmins,
    'omegaup-contest-links': contest_Links,
    'omegaup-contest-new-form': contest_NewForm,
    'omegaup-contest-publish': contest_Publish,
  },
};
</script>
