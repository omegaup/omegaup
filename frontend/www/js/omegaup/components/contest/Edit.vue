<template>
  <div>
    <div class="page-header">
      <h1>
        {{
          ui.formatString(T.contestEditWithTitle, {
            title: ui.contestTitle(contest),
          })
        }}
        <small
          ><a :href="`/arena/${contest.alias}/`">{{
            T.contestDetailsGoToContest
          }}</a></small
        >
      </h1>
    </div>
    <ul class="nav nav-tabs nav-justified">
      <li
        v-if="!virtual"
        :class="{ active: !virtual }"
        @click="showTab = 'new_form'"
      >
        <a data-toggle="tab">{{ T.contestEdit }}</a>
      </li>
      <li v-if="!virtual" class="problems" @click="showTab = 'problems'">
        <a data-toggle="tab">{{ T.wordsAddProblem }}</a>
      </li>
      <li v-if="!virtual" class="admission-mode" @click="showTab = 'publish'">
        <a data-toggle="tab">{{ T.contestNewFormAdmissionMode }}</a>
      </li>
      <li
        class="contestants"
        :class="{ active: virtual }"
        @click="showTab = 'contestants'"
      >
        <a data-toggle="tab">{{ T.contestAdduserAddContestant }}</a>
      </li>
      <li @click="showTab = 'admins'">
        <a data-toggle="tab">{{ T.omegaupTitleContestAddAdmin }}</a>
      </li>
      <li v-if="!virtual" @click="showTab = 'links'">
        <a data-toggle="tab">{{ T.showLinks }}</a>
      </li>
      <li v-if="!virtual" @click="showTab = 'clone'">
        <a data-toggle="tab">{{ T.courseEditClone }}</a>
      </li>
    </ul>
    <div class="tab-content">
      <div v-if="showTab === 'new_form'" class="tab-pane active">
        <omegaup-contest-new-form
          :initial-alias="contest.alias"
          :initial-title="contest.title"
          :initial-description="contest.description"
          :initial-start-time="contest.start_time"
          :initial-finish-time="contest.finish_time"
          :initial-window-length="contest.window_length"
          :initial-points-decay-factor="contest.points_decay_factor"
          :initial-submissions-gap="contest.submissions_gap"
          :initial-languages="contest.languages"
          :initial-feedback="contest.feedback"
          :initial-penalty="contest.penalty"
          :initial-scoreboard="contest.scoreboard"
          :initial-penalty-type="contest.penalty_type"
          :initial-show-scoreboard-after="contest.show_scoreboard_after"
          :initial-partial-score="contest.partial_score"
          :initial-needs-basic-information="contest.needs_basic_information"
          :initial-requests-user-information="contest.requests_user_information"
          :all-languages="contest.available_languages"
          :update="true"
          @emit-update-contest="
            (newFormComponent) => $emit('update-contest', newFormComponent)
          "
        ></omegaup-contest-new-form>
      </div>
      <div v-if="showTab === 'problems'" class="tab-pane active problems">
        <omegaup-contest-add-problem
          :contest-alias="contest.alias"
          :initialPoints="contest.partial_score ? 100 : 1"
          :data="problems"
          @emit-add-problem="
            (addProblemComponent) => $emit('add-problem', addProblemComponent)
          "
          @emit-change-alias="
            (addProblemComponent, newProblemAlias) =>
              $emit('get-versions', newProblemAlias, addProblemComponent)
          "
          @emit-remove-problem="
            (addProblemComponent) =>
              $emit('remove-problem', addProblemComponent)
          "
          @emit-runs-diff="
            (addProblemComponent, versions, selectedCommit) =>
              $emit('runs-diff', addProblemComponent, versions, selectedCommit)
          "
        >
        </omegaup-contest-add-problem>
      </div>
      <div v-if="showTab === 'publish'" class="tab-pane active">
        <omegaup-common-publish
          :initialAdmissionMode="contest.admission_mode"
          :shouldShowPublicOption="true"
          :admissionModeDescription="T.contestNewFormAdmissionModeDescription"
          @emit-update-admission-mode="
            (publishComponent) =>
              $emit('update-admission-mode', publishComponent)
          "
        ></omegaup-common-publish>
      </div>
      <div v-if="showTab === 'contestants'" class="tab-pane active contestants">
        <omegaup-contest-contestant
          :contest="contest"
          :data="users"
          @emit-add-user="
            (contestantComponent) => $emit('add-user', contestantComponent)
          "
          @emit-remove-user="
            (contestantComponent) => $emit('remove-user', contestantComponent)
          "
          @emit-save-end-time="(selected) => $emit('save-end-time', selected)"
        ></omegaup-contest-contestant>
        <omegaup-common-requests
          :data="requests"
          :text-add-participant="T.contestAdduserAddContestant"
          @emit-accept-request="
            (requestsComponent, username) =>
              $emit('accept-request', requestsComponent, username)
          "
          @emit-deny-request="
            (requestsComponent, username) =>
              $emit('deny-request', requestsComponent, username)
          "
        ></omegaup-common-requests>
        <omegaup-contest-groups
          v-if="isIdentitiesExperimentEnabled"
          :data="groups"
          @emit-add-group="
            (groupsComponent) => $emit('add-group', groupsComponent)
          "
          @emit-remove-group="
            (groupsComponent) => $emit('remove-group', groupsComponent)
          "
        ></omegaup-contest-groups>
      </div>
      <div v-if="showTab === 'admins'" class="tab-pane active">
        <omegaup-contest-admins
          :initial-admins="admins"
          :has-parent-component="true"
          @emit-add-admin="
            (addAdminComponent) => $emit('add-admin', addAdminComponent)
          "
          @emit-remove-admin="
            (addAdminComponent) => $emit('remove-admin', addAdminComponent)
          "
        ></omegaup-contest-admins>
        <omegaup-contest-group-admins
          :initial-groups="groupAdmins"
          :has-parent-component="true"
          @emit-add-group-admin="
            (groupAdminsComponent) =>
              $emit('add-group-admin', groupAdminsComponent)
          "
          @emit-remove-group-admin="
            (groupAdminsComponent) =>
              $emit('remove-group-admin', groupAdminsComponent)
          "
        ></omegaup-contest-group-admins>
      </div>
      <div v-if="showTab === 'links'" class="tab-pane active">
        <omegaup-contest-links :data="contest"></omegaup-contest-links>
      </div>
      <div v-if="showTab === 'clone'" class="tab-pane active">
        <omegaup-contest-clone
          @emit-clone="
            (cloneComponent) => $emit('clone-contest', cloneComponent)
          "
        ></omegaup-contest-clone>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup, OmegaUp } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
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
  requests: types.IdentityRequest[];
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
  ui = ui;
  showTab = ui.isVirtual(this.data.contest) ? 'contestants' : 'new_form';
  virtual = ui.isVirtual(this.data.contest);
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
