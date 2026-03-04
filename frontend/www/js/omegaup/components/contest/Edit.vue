<template>
  <div class="course-edit">
    <div class="page-header">
      <h1>
        {{
          ui.formatString(T.contestEditWithTitle, {
            title: ui.contestTitle(details),
          })
        }}
        <small>
          &ndash;
          <a :href="ui.contestURL(details)" data-contest-link-button>
            {{ T.contestDetailsGoToContest }}</a
          >
        </small>
      </h1>
    </div>

    <ul class="nav nav-pills mt-4">
      <li class="nav-item" role="presentation">
        <a
          v-if="!virtual"
          href="#new_form"
          data-contest-new-form
          class="nav-link"
          :class="{ active: showTab === 'new_form' }"
          @click="showTab = 'new_form'"
          >{{ T.contestEdit }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          v-if="!virtual"
          href="#problems"
          class="nav-link problems"
          :class="{ active: showTab === 'problems' }"
          @click="showTab = 'problems'"
          >{{ T.contestEditAddProblems }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          v-if="!virtual && !details.contest_for_teams"
          href="#publish"
          class="nav-link admission-mode"
          :class="{ active: showTab === 'publish' }"
          @click="showTab = 'publish'"
          >{{ T.contestNewFormAdmissionMode }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          v-if="
            originalContestAdmissionMode != 'private' &&
            !details.contest_for_teams
          "
          href="#contestants"
          data-nav-contestant
          class="nav-link contestants"
          :class="{ active: showTab === 'contestants' }"
          @click="showTab = 'contestants'"
          >{{ T.contestAdduserAddContestant }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          v-if="details.contest_for_teams"
          href="#groups"
          data-nav-group
          class="nav-link groups"
          :class="{ active: showTab === 'groups' }"
          @click="showTab = 'groups'"
          >{{ T.contestAddgroupAddGroup }}</a
        >
      </li>

      <li class="nav-item" role="presentation">
        <a
          v-if="!virtual"
          href="#admins"
          class="nav-link"
          :class="{ active: showTab === 'admins' }"
          @click="showTab = 'admins'"
          >{{ T.omegaupTitleContestAddAdmin }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#links"
          class="nav-link"
          :class="{ active: showTab === 'links' }"
          @click="showTab = 'links'"
          >{{ T.showLinks }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#clone"
          class="nav-link"
          :class="{ active: showTab === 'clone' }"
          @click="showTab = 'clone'"
          >{{ T.courseEditClone }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          href="#archive"
          class="nav-link"
          :class="{ active: showTab === 'archive' }"
          @click="showTab = 'archive'"
          >{{ T.contestEditArchive }}</a
        >
      </li>
      <li class="nav-item" role="presentation">
        <a
          v-if="certificatesDetails.isCertificateGenerator"
          href="#certificates"
          class="nav-link"
          :class="{ active: showTab === 'certificates' }"
          @click="showTab = 'certificates'"
          >{{ T.contestEditCertificates }}</a
        >
      </li>
    </ul>

    <div class="tab-content mt-2">
      <div v-if="showTab === 'new_form'" class="tab-pane active">
        <omegaup-contest-new-form
          :admission-mode="details.admission_mode"
          :default-show-all-contestants-in-scoreboard="
            details.default_show_all_contestants_in_scoreboard
          "
          :initial-alias="details.alias"
          :initial-title="details.title"
          :initial-description="details.description"
          :initial-start-time="details.start_time"
          :initial-finish-time="details.finish_time"
          :initial-window-length="details.window_length"
          :initial-points-decay-factor="details.points_decay_factor"
          :initial-submissions-gap="details.submissions_gap"
          :initial-languages="details.languages"
          :initial-feedback="details.feedback"
          :initial-penalty="details.penalty"
          :initial-scoreboard="details.scoreboard"
          :initial-penalty-type="details.penalty_type"
          :initial-show-scoreboard-after="details.show_scoreboard_after"
          :score-mode="details.score_mode"
          :initial-needs-basic-information="details.needs_basic_information"
          :initial-requests-user-information="details.requests_user_information"
          :all-languages="details.available_languages"
          :teams-group-alias="teamsGroupAlias"
          :contest-for-teams="details.contest_for_teams"
          :has-submissions="details.has_submissions"
          :update="true"
          :search-result-teams-groups="searchResultTeamsGroups"
          :problems="problems"
          :can-set-recommended="details.canSetRecommended"
          :initial-recommended="details.recommended"
          :invalid-parameter-name="invalidParameterName"
          @update-search-result-teams-groups="
            (query) => $emit('update-search-result-teams-groups', query)
          "
          @update-contest="(contest) => $emit('update-contest', contest)"
          @language-remove-blocked="
            (language) => $emit('language-remove-blocked', language)
          "
        ></omegaup-contest-new-form>
      </div>
      <div v-if="showTab === 'problems'" class="tab-pane active">
        <omegaup-contest-add-problem
          :contest-alias="details.alias"
          :initial-points="details.score_mode !== 'all_or_nothing' ? 100 : 1"
          :initial-problems="problems"
          :search-result-problems="searchResultProblems"
          @add-problem="(request) => $emit('add-problem', request)"
          @update-search-result-problems="
            (request) => $emit('update-search-result-problems', request)
          "
          @get-versions="(request) => $emit('get-versions', request)"
          @remove-problem="
            (problemAlias) => $emit('remove-problem', problemAlias)
          "
          @runs-diff="
            (problemAlias, versions, selectedCommit) =>
              $emit('runs-diff', problemAlias, versions, selectedCommit)
          "
        >
        </omegaup-contest-add-problem>
      </div>
      <div v-if="showTab === 'publish'" class="tab-pane active">
        <omegaup-common-publish
          :default-show-all-contestants-in-scoreboard="
            details.default_show_all_contestants_in_scoreboard
          "
          :admission-mode="details.admission_mode"
          :should-show-public-option="true"
          :admission-mode-description="T.contestAdmissionModeDescription"
          :alias="details.alias"
          @show-copy-message="() => $emit('show-copy-message')"
          @update-admission-mode="
            (request) => $emit('update-admission-mode', request)
          "
        ></omegaup-common-publish>
      </div>
      <div v-if="showTab === 'contestants'" class="tab-pane active contestants">
        <omegaup-contest-add-contestant
          :contest="details"
          :users="users"
          :search-result-users="searchResultUsers"
          @add-user="(contestants) => $emit('add-user', contestants)"
          @update-search-result-users="
            (query) => $emit('update-search-result-users', query)
          "
          @remove-user="(contestant) => $emit('remove-user', contestant)"
          @save-end-time="(user) => $emit('save-end-time', user)"
        ></omegaup-contest-add-contestant>
        <omegaup-common-requests
          :data="requests"
          :text-add-participant="T.contestAdduserAddContestant"
          @accept-request="(request) => $emit('accept-request', request)"
          @deny-request="(request) => $emit('deny-request', request)"
        ></omegaup-common-requests>
        <omegaup-contest-groups
          :groups="groups"
          :search-result-groups="searchResultGroups"
          @update-search-result-groups="
            (query) => $emit('update-search-result-groups', query)
          "
          @emit-add-group="(groupAlias) => $emit('add-group', groupAlias)"
          @emit-remove-group="(groupAlias) => $emit('remove-group', groupAlias)"
        ></omegaup-contest-groups>
      </div>
      <div v-if="showTab === 'groups'" class="tab-pane active groups">
        <omegaup-contest-teams-groups
          :teams-group="teamsGroup"
          :search-result-teams-groups="searchResultTeamsGroups"
          :has-submissions="details.has_submissions"
          @update-search-result-teams-groups="
            (query) => $emit('update-search-result-teams-groups', query)
          "
          @replace-teams-group="
            (request) => $emit('replace-teams-group', request)
          "
        ></omegaup-contest-teams-groups>
      </div>
      <div v-if="showTab === 'admins'" class="tab-pane active">
        <omegaup-common-admins
          :admins="admins"
          :search-result-users="searchResultUsers"
          @add-admin="(username) => $emit('add-admin', username)"
          @remove-admin="(username) => $emit('remove-admin', username)"
          @update-search-result-users="
            (query) => $emit('update-search-result-users', query)
          "
        ></omegaup-common-admins>
        <div class="mt-2"></div>
        <omegaup-common-group-admins
          :group-admins="groupAdmins"
          :search-result-groups="searchResultGroups"
          @add-group-admin="
            (groupAlias) => $emit('add-group-admin', groupAlias)
          "
          @remove-group-admin="
            (groupAlias) => $emit('remove-group-admin', groupAlias)
          "
          @update-search-result-groups="
            (query) => $emit('update-search-result-groups', query)
          "
        ></omegaup-common-group-admins>
      </div>
      <div v-if="showTab === 'links'" class="tab-pane active">
        <omegaup-contest-links
          :data="details"
          @download-csv-scoreboard="
            (contestAlias) => $emit('download-csv-scoreboard', contestAlias)
          "
        ></omegaup-contest-links>
      </div>
      <div v-if="showTab === 'clone'" class="tab-pane active">
        <omegaup-contest-clone
          @clone="
            ({ title, alias, description, startTime }) =>
              $emit('clone-contest', title, alias, description, startTime)
          "
        ></omegaup-contest-clone>
      </div>
      <div v-if="showTab === 'archive'" class="tab-pane active">
        <omegaup-common-archive
          :already-archived="alreadyArchived"
          :archive-button-description="archiveButtonDescription"
          :archive-confirm-text="T.contestEditArchiveConfirmText"
          :archive-header-title="T.contestEditArchiveContest"
          :archive-help-text="archiveUnarchiveDescription"
          @archive="onArchiveContest"
        ></omegaup-common-archive>
      </div>
      <div v-if="showTab === 'certificates'" class="tab-pane active">
        <omegaup-contest-certificates
          :certificates-details="certificatesDetails"
          @generate="
            (certificateCutoff) =>
              $emit('generate-certificates', certificateCutoff)
          "
        ></omegaup-contest-certificates>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';

import contest_AddProblem from './AddProblem.vue';
import contest_AddContestant from './AddContestant.vue';
import contest_Clone from './Clone.vue';
import common_Admins from '../common/Admins.vue';
import common_Archive from '../common/Archive.vue';
import common_Requests from '../common/Requests.vue';
import common_GroupAdmins from '../common/GroupAdmins.vue';
import contest_Groups from './Groups.vue';
import contest_TeamsGroups from './TeamsGroup.vue';
import contest_Links from './Links.vue';
import contest_NewForm from './Form.vue';
import common_Publish from '../common/Publish.vue';
import contest_Certificates from './Certificates.vue';

@Component({
  components: {
    'omegaup-contest-add-problem': contest_AddProblem,
    'omegaup-common-admins': common_Admins,
    'omegaup-contest-clone': contest_Clone,
    'omegaup-contest-add-contestant': contest_AddContestant,
    'omegaup-common-archive': common_Archive,
    'omegaup-common-requests': common_Requests,
    'omegaup-contest-groups': contest_Groups,
    'omegaup-contest-teams-groups': contest_TeamsGroups,
    'omegaup-common-group-admins': common_GroupAdmins,
    'omegaup-contest-links': contest_Links,
    'omegaup-contest-new-form': contest_NewForm,
    'omegaup-common-publish': common_Publish,
    'omegaup-contest-certificates': contest_Certificates,
  },
})
export default class Edit extends Vue {
  @Prop() admins!: types.ContestAdmin[];
  @Prop() details!: types.ContestAdminDetails;
  @Prop() initialTab!: string;
  @Prop() groups!: types.ContestGroup[];
  @Prop() groupAdmins!: types.ContestGroupAdmin[];
  @Prop() problems!: types.ProblemsetProblemWithVersions[];
  @Prop() requests!: types.ContestRequest[];
  @Prop() users!: types.ContestUser[];
  @Prop() searchResultProblems!: types.ListItem[];
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop() teamsGroup!: types.ContestGroup | null;
  @Prop() searchResultTeamsGroups!: types.ListItem[];
  @Prop() searchResultGroups!: types.ListItem[];
  @Prop({ default: null }) originalContestAdmissionMode!: null | string;
  @Prop() certificatesDetails!: types.ContestCertificatesAdminDetails;
  @Prop({ default: null }) invalidParameterName!: null | string;

  T = T;
  ui = ui;
  virtual = ui.isVirtual(this.details);
  showTab = this.selectedTab();
  alreadyArchived = this.details.archived;

  selectedTab(): string {
    if (this.initialTab != '') {
      return this.initialTab;
    }
    if (!ui.isVirtual(this.details)) {
      return 'new_form';
    }
    if (this.originalContestAdmissionMode != 'private') {
      return 'contestants';
    }
    return 'links';
  }

  get activeTab(): string {
    switch (this.showTab) {
      case 'new_form':
        return T.contestEdit;
      case 'problems':
        return T.contestEditAddProblems;
      case 'publish':
        return T.contestNewFormAdmissionMode;
      case 'contestants':
        return T.contestAdduserAddContestant;
      case 'groups':
        return T.contestAddgroupAddGroup;
      case 'admins':
        return T.omegaupTitleContestAddAdmin;
      case 'links':
        return T.showLinks;
      case 'clone':
        return T.courseEditClone;
      case 'archive':
        return T.contestEditArchive;
      case 'certificates':
        return T.contestEditCertificates;
      default:
        return T.contestEdit;
    }
  }

  get archiveButtonDescription(): string {
    if (this.alreadyArchived) {
      return T.contestEditUnarchiveContest;
    }
    return T.contestEditArchiveContest;
  }

  get archiveUnarchiveDescription(): string {
    if (this.alreadyArchived) {
      return T.contestEditUnarchiveHelpText;
    }
    return T.contestEditArchiveHelpText;
  }

  get teamsGroupAlias(): null | types.ListItem {
    if (!this.teamsGroup) {
      return null;
    }
    return { key: this.teamsGroup?.alias, value: this.teamsGroup.name };
  }

  onArchiveContest(archive: boolean): void {
    this.$emit('archive-contest', this.details.alias, archive);
    this.alreadyArchived = archive;
  }
}
</script>
