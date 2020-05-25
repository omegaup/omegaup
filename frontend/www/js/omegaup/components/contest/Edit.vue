<template>
  <div>
    <div class="page-header">
      <h1>
        {{
          UI.formatString(T.contestEditWithTitle, {
            title: UI.contestTitle(contest),
          })
        }}
        <small
          ><a v-bind:href="`/arena/${contest.alias}/`">{{
            T.contestDetailsGoToContest
          }}</a></small
        >
      </h1>
    </div>
    <ul class="nav nav-tabs nav-justified">
      <li
        v-bind:class="{ active: !virtual }"
        v-if="!virtual"
        v-on:click="showTab = 'new_form'"
      >
        <a data-toggle="tab">{{ T.contestEdit }}</a>
      </li>
      <li class="problems" v-if="!virtual" v-on:click="showTab = 'problems'">
        <a data-toggle="tab">{{ T.wordsAddProblem }}</a>
      </li>
      <li
        class="admission-mode"
        v-if="!virtual"
        v-on:click="showTab = 'publish'"
      >
        <a data-toggle="tab">{{ T.contestNewFormAdmissionMode }}</a>
      </li>
      <li
        class="contestants"
        v-bind:class="{ active: virtual }"
        v-on:click="showTab = 'contestants'"
      >
        <a data-toggle="tab">{{ T.contestAdduserAddContestant }}</a>
      </li>
      <li v-on:click="showTab = 'admins'">
        <a data-toggle="tab">{{ T.omegaupTitleContestAddAdmin }}</a>
      </li>
      <li v-if="!virtual" v-on:click="showTab = 'links'">
        <a data-toggle="tab">{{ T.showLinks }}</a>
      </li>
      <li v-if="!virtual" v-on:click="showTab = 'clone'">
        <a data-toggle="tab">{{ T.courseEditClone }}</a>
      </li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane active" v-if="showTab === 'new_form'">
        <omegaup-contest-new-form
          v-bind:initial-alias="contest.alias"
          v-bind:initial-title="contest.title"
          v-bind:initial-description="contest.description"
          v-bind:initial-start-time="contest.start_time"
          v-bind:initial-finish-time="contest.finish_time"
          v-bind:initial-window-length="contest.window_length"
          v-bind:initial-points-decay-factor="contest.points_decay_factor"
          v-bind:initial-submissions-gap="contest.submissions_gap"
          v-bind:initial-languages="contest.languages"
          v-bind:initial-feedback="contest.feedback"
          v-bind:initial-penalty="contest.penalty"
          v-bind:initial-scoreboard="contest.scoreboard"
          v-bind:initial-penalty-type="contest.penalty_type"
          v-bind:initial-show-scoreboard-after="contest.show_scoreboard_after"
          v-bind:initial-partial-score="contest.partial_score"
          v-bind:initial-needs-basic-information="
            contest.needs_basic_information
          "
          v-bind:initial-requests-user-information="
            contest.requests_user_information
          "
          v-bind:all-languages="contest.available_languages"
          v-bind:update="true"
          v-on:emit-update-contest="
            newFormComponent => $emit('update-contest', newFormComponent)
          "
        ></omegaup-contest-new-form>
      </div>
      <div class="tab-pane active problems" v-if="showTab === 'problems'">
        <omegaup-contest-add-problem
          v-bind:contest-alias="contest.alias"
          v-bind:initialPoints="contest.partial_score ? 100 : 1"
          v-bind:data="problems"
          v-on:emit-add-problem="
            addProblemComponent => $emit('add-problem', addProblemComponent)
          "
          v-on:emit-change-alias="
            (addProblemComponent, newProblemAlias) =>
              $emit('get-versions', newProblemAlias, addProblemComponent)
          "
          v-on:emit-remove-problem="
            addProblemComponent => $emit('remove-problem', addProblemComponent)
          "
          v-on:emit-runs-diff="
            (addProblemComponent, versions, selectedCommit) =>
              $emit('runs-diff', addProblemComponent, versions, selectedCommit)
          "
        >
        </omegaup-contest-add-problem>
      </div>
      <div class="tab-pane active" v-if="showTab === 'publish'">
        <omegaup-common-publish
          v-bind:initialAdmissionMode="contest.admission_mode"
          v-bind:shouldShowPublicOption="true"
          v-bind:admissionModeDescription="
            T.contestNewFormAdmissionModeDescription
          "
          v-on:emit-update-admission-mode="
            publishComponent => $emit('update-admission-mode', publishComponent)
          "
        ></omegaup-common-publish>
      </div>
      <div class="tab-pane active contestants" v-if="showTab === 'contestants'">
        <omegaup-contest-contestant
          v-bind:contest="contest"
          v-bind:data="users"
          v-on:emit-add-user="
            contestantComponent => $emit('add-user', contestantComponent)
          "
          v-on:emit-remove-user="
            contestantComponent => $emit('remove-user', contestantComponent)
          "
          v-on:emit-save-end-time="selected => $emit('save-end-time', selected)"
        ></omegaup-contest-contestant>
        <omegaup-common-requests
          v-bind:data="requests"
          v-bind:text-add-participant="T.contestAdduserAddContestant"
          v-on:emit-accept-request="
            (requestsComponent, username) =>
              $emit('accept-request', requestsComponent, username)
          "
          v-on:emit-deny-request="
            (requestsComponent, username) =>
              $emit('deny-request', requestsComponent, username)
          "
        ></omegaup-common-requests>
        <omegaup-contest-groups
          v-bind:data="groups"
          v-if="isIdentitiesExperimentEnabled"
          v-on:emit-add-group="
            groupsComponent => $emit('add-group', groupsComponent)
          "
          v-on:emit-remove-group="
            groupsComponent => $emit('remove-group', groupsComponent)
          "
        ></omegaup-contest-groups>
      </div>
      <div class="tab-pane active" v-if="showTab === 'admins'">
        <omegaup-contest-admins
          v-bind:initial-admins="admins"
          v-bind:has-parent-component="true"
          v-on:emit-add-admin="
            addAdminComponent => $emit('add-admin', addAdminComponent)
          "
          v-on:emit-remove-admin="
            addAdminComponent => $emit('remove-admin', addAdminComponent)
          "
        ></omegaup-contest-admins>
        <omegaup-contest-group-admins
          v-bind:initial-groups="groupAdmins"
          v-bind:has-parent-component="true"
          v-on:emit-add-group-admin="
            groupAdminsComponent =>
              $emit('add-group-admin', groupAdminsComponent)
          "
          v-on:emit-remove-group-admin="
            groupAdminsComponent =>
              $emit('remove-group-admin', groupAdminsComponent)
          "
        ></omegaup-contest-group-admins>
      </div>
      <div class="tab-pane active" v-if="showTab === 'links'">
        <omegaup-contest-links v-bind:data="contest"></omegaup-contest-links>
      </div>
      <div class="tab-pane active" v-if="showTab === 'clone'">
        <omegaup-contest-clone
          v-on:emit-clone="
            cloneComponent => $emit('clone-contest', cloneComponent)
          "
        ></omegaup-contest-clone>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup, OmegaUp } from '../../omegaup';
import T from '../../lang';
import * as UI from '../../ui';
import contest_AddProblem from './AddProblem.vue';
import contest_Admins from '../common/Admins.vue';
import contest_Clone from './Clone.vue';
import contest_Contestant from './Contestant.vue';
import common_Requests from '../common/Requests.vue';
import contest_Groups from './Groups.vue';
import contest_GroupAdmins from '../common/GroupAdmins.vue';
import contest_Links from './Links.vue';
import contest_NewForm from './NewForm.vue';
import common_Publish from '../common/Publish.vue';

interface ContestEdit {
  admins: omegaup.UserRole[];
  contest: omegaup.Contest;
  groupAdmins: omegaup.ContestGroupAdmin[];
  problems: omegaup.Problem[];
  requests: omegaup.IdentityRequest[];
  users: omegaup.IdentityContest[];
  groups: omegaup.ContestGroup[];
}

@Component({
  components: {
    'omegaup-contest-add-problem': contest_AddProblem,
    'omegaup-contest-admins': contest_Admins,
    'omegaup-contest-clone': contest_Clone,
    'omegaup-contest-contestant': contest_Contestant,
    'omegaup-common-requests': common_Requests,
    'omegaup-contest-groups': contest_Groups,
    'omegaup-contest-group-admins': contest_GroupAdmins,
    'omegaup-contest-links': contest_Links,
    'omegaup-contest-new-form': contest_NewForm,
    'omegaup-common-publish': common_Publish,
  },
})
export default class Edit extends Vue {
  @Prop() data!: ContestEdit;

  T = T;
  UI = UI;
  showTab = UI.isVirtual(this.data.contest) ? 'contestants' : 'new_form';
  virtual = UI.isVirtual(this.data.contest);
  contest = this.data.contest;
  problems = this.data.problems;
  users = this.data.users;
  groups = this.data.groups;
  requests = this.data.requests;
  admins = this.data.admins;
  groupAdmins = this.data.groupAdmins;

  isIdentitiesExperimentEnabled =
    OmegaUp.experiments && OmegaUp.experiments.isEnabled('identities');
}
</script>
