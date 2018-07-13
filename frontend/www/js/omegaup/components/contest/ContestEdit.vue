<template>
  <div>
    <div class="page-header">
      <h1>{{T.contestEdit + ' ' + contest.title}} <small><a v-bind:href=
      "`/arena/${contest.alias}/`">{{T.contestDetailsGoToContest}}</a></small></h1>
    </div>
    <ul class="nav nav-tabs nav-justified">
      <li class="active"
          v-if="!virtual"
          v-on:click="showTab = 'new_form'">
        <a data-toggle="tab">{{T.contestEdit}}</a>
      </li>
      <li v-if="!virtual"
          v-on:click="showTab = 'problems'">
        <a data-toggle="tab">{{T.wordsAddProblem}}</a>
      </li>
      <li v-if="!virtual"
          v-on:click="showTab = 'publish'">
        <a data-toggle="tab">{{T.makePublic}}</a>
      </li>
      <li v-bind:class="{active: virtual}"
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
        <contest-new-form v-bind:contest="contest"
             v-bind:update="true"></contest-new-form>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'problems'">
        <contest-add-problem v-bind:problems="problems"></contest-add-problem>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'publish'">
        <contest-publish v-bind:contest="contest"></contest-publish>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'contestants'">
        <contest-contestant v-bind:users="users"></contest-contestant>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'admins'">
        <contest-admins v-bind:admins="admins"></contest-admins>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'group_admins'">
        <contest-group-admins v-bind:groupadmins="groupAdmins"></contest-group-admins>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'links'">
        <contest-links v-bind:contest="contest"></contest-links>
      </div>
      <div class="tab-pane active"
           v-if="showTab === 'clone'">
        <contest-clone></contest-clone>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import ContestNewForm from './ContestNewForm.vue';
import ContestAddProblem from './ContestAddProblem.vue';
import ContestPublish from './ContestPublish.vue';
import ContestContestant from './ContestContestant.vue';
import ContestAdmins from './ContestAdmins.vue';
import ContestGroupAdmins from './ContestGroupAdmins.vue';
import ContestLinks from './ContestLinks.vue';
import ContestClone from './ContestClone.vue';

export default {
  props: {
    contest: Object,
    problems: Array,
    users: Array,
    admins: Array,
    groupAdmins: Array
  },
  data: function() {
    return {
      showTab: this.isVirtual ? "contestants" : "new_form", T: T,
          virtual: this.isVirtual()
    }
  },
  methods: {isVirtual: function() { return this.contest.rerun_id != 0;}},
  components: {
    'contest-new-form': ContestNewForm,
    'contest-add-problem': ContestAddProblem,
    'contest-publish': ContestPublish,
    'contest-contestant': ContestContestant,
    'contest-admins': ContestAdmins,
    'contest-group-admins': ContestGroupAdmins,
    'contest-links': ContestLinks,
    'contest-clone': ContestClone
  }
}
</script>
