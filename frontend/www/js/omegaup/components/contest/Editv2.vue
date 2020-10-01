<template>
  <div>
    <div class="page-header">
      <h1>
        {{
          ui.formatString(T.contestEditWithTitle, {
            title: ui.contestTitle(details),
          })
        }}
        <small>
          &ndash;
          <a v-bind:href="`/arena/${details.alias}/`">
            {{ T.contestDetailsGoToContest }}</a
          >
        </small>
      </h1>
    </div>

    <ul class="nav nav-pills my-3">
      <li class="nav-item dropdown">
        <a
          href="#"
          data-toggle="dropdown"
          role="button"
          class="nav-link active dropdown-toggle"
          aria-haspopup="true"
          aria-expanded="false"
          >{{ activeTab }}</a
        >
        <div class="dropdown-menu">
          <a
            href="#"
            data-toggle="tab"
            class="dropdown-item"
            v-bind:class="{ active: showTab === 'new_form' }"
            v-if="!virtual"
            v-on:click="showTab = 'new_form'"
            >{{ T.contestEdit }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            class="dropdown-item"
            v-bind:class="{ active: showTab === 'problems' }"
            v-if="!virtual"
            v-on:click="showTab = 'problems'"
            >{{ T.wordsAddProblem }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            class="dropdown-item"
            v-bind:class="{ active: showTab === 'publish' }"
            v-if="!virtual"
            v-on:click="showTab = 'publish'"
            >{{ T.contestNewFormAdmissionMode }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            class="dropdown-item"
            v-bind:class="{ active: virtual && showTab === 'contestants' }"
            v-on:click="showTab = 'contestants'"
            >{{ T.contestAdduserAddContestant }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            class="dropdown-item"
            v-bind:class="{ active: showTab === 'admins' }"
            v-on:click="showTab = 'admins'"
            >{{ T.omegaupTitleContestAddAdmin }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            class="dropdown-item"
            v-bind:class="{ active: showTab === 'links' }"
            v-on:click="showTab = 'links'"
            >{{ T.showLinks }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            class="dropdown-item"
            v-bind:class="{ active: showTab === 'clone' }"
            v-on:click="showTab = 'clone'"
            >{{ T.courseEditClone }}</a
          >
        </div>
      </li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane active" v-if="showTab === 'new_form'">
        <omegaup-contest-new-form
          v-bind:initial-alias="details.alias"
          v-bind:initial-title="details.title"
          v-bind:initial-description="details.description"
          v-bind:initial-start-time="details.start_time"
          v-bind:initial-finish-time="details.finish_time"
          v-bind:initial-window-length="details.window_length"
          v-bind:initial-points-decay-factor="details.points_decay_factor"
          v-bind:initial-submissions-gap="details.submissions_gap"
          v-bind:initial-languages="details.languages"
          v-bind:initial-feedback="details.feedback"
          v-bind:initial-penalty="details.penalty"
          v-bind:initial-scoreboard="details.scoreboard"
          v-bind:initial-penalty-type="details.penalty_type"
          v-bind:initial-show-scoreboard-after="details.show_scoreboard_after"
          v-bind:initial-partial-score="details.partial_score"
          v-bind:initial-needs-basic-information="
            details.needs_basic_information
          "
          v-bind:initial-requests-user-information="
            details.requests_user_information
          "
          v-bind:all-languages="details.available_languages"
          v-bind:update="true"
          v-on:emit-update-contest="
            (newFormComponent) => $emit('update-contest', newFormComponent)
          "
        ></omegaup-contest-new-form>
      </div>
      <div class="tab-pane active" v-if="showTab === 'problems'">
        <omegaup-contest-add-problem
          v-bind:contest-alias="details.alias"
          v-bind:initial-points="details.partial_score ? 100 : 1"
          v-bind:initial-problems="problems"
          v-on:add-problem="(problem) => $emit('add-problem', problem)"
          v-on:get-versions="
            (problemAlias, addProblemComponent) =>
              $emit('get-versions', problemAlias, addProblemComponent)
          "
          v-on:remove-problem="
            (problemAlias) => $emit('remove-problem', problemAlias)
          "
          v-on:runs-diff="
            (problemAlias, versions, selectedCommit) =>
              $emit('runs-diff', problemAlias, versions, selectedCommit)
          "
        >
        </omegaup-contest-add-problem>
      </div>
      <div class="tab-pane active" v-if="showTab === 'publish'">
        <omegaup-common-publish
          v-bind:initial-admission-mode="details.admission_mode"
          v-bind:should-show-public-option="true"
          v-bind:admission-mode-description="T.contestAdmissionModeDescription"
          v-on:emit-update-admission-mode="
            (admissionMode) => $emit('update-admission-mode', admissionMode)
          "
        ></omegaup-common-publish>
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
import contest_AddProblem from './AddProblemv2.vue';
import contest_Admins from '../common/Admins.vue';
import contest_Clone from './Clone.vue';
import contest_Contestant from './Contestant.vue';
import common_Requests from '../common/Requests.vue';
import contest_Groups from './Groups.vue';
import contest_GroupAdmins from '../common/GroupAdmins.vue';
import contest_Links from './Links.vue';
import contest_NewForm from './NewForm.vue';
import common_Publish from '../common/Publishv2.vue';

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
  @Prop() admins!: types.ContestAdmin[];
  @Prop() details!: types.ContestAdminDetails;
  @Prop() groups!: types.ContestGroup[];
  @Prop() groupAdmins!: types.ContestGroupAdmin[];
  @Prop() problems!: types.ContestProblem[];
  @Prop() requests!: types.ContestRequest[];
  @Prop() users!: types.ContestUser[];

  T = T;
  ui = ui;
  showTab = ui.isVirtual(this.details) ? 'contestants' : 'new_form';
  virtual = ui.isVirtual(this.details);

  get activeTab(): string {
    switch (this.showTab) {
      case 'new_form':
        return T.contestEdit;
      case 'problems':
        return T.wordsAddProblem;
      case 'publish':
        return T.contestNewFormAdmissionMode;
      case 'contestants':
        return T.contestAdduserAddContestant;
      case 'admins':
        return T.omegaupTitleContestAddAdmin;
      case 'links':
        return T.showLinks;
      case 'clone':
        return T.courseEditClone;
      default:
        return T.contestEdit;
    }
  }
}
</script>
