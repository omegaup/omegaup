<template>
  <div>
    <ul class="nav justify-content-center nav-tabs" role="tablist">
      <li class="nav-item" v-for="tab in availableTabs" v-bind:key="tab.name">
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          v-bind:aria-controls="tab.name"
          v-bind:class="{ active: selectedTab === tab.name }"
          v-bind:aria-selected="selectedTab === tab.name"
          v-on:click="selectedTab = tab.name"
        >
          {{ tab.text }}
        </a>
      </li>
    </ul>
    <div class="tab-content">
      <div
        class="tab-pane fade p-4"
        v-bind:class="{ 'show active': selectedTab === 'problems' }"
      >
        <omegaup-problem-settings-summary
          v-bind:problem="problem"
          v-bind:showVisibilityIndicators="true"
          v-bind:showEditLink="this.user.admin"
        ></omegaup-problem-settings-summary>

        <div class="karel-js-link my-3" v-if="problem.karel_problem">
          <a
            class="p-3"
            v-bind:href="`/karel.js/${
              problem.sample_input ? `#mundo:${problem.sample_input}` : ''
            }`"
            target="_blank"
          >
            {{ T.openInKarelJs }}
            <font-awesome-icon v-bind:icon="['fas', 'external-link-alt']" />
          </a>
        </div>

        <div class="mt-4 markdown">
          <omegaup-markdown
            v-bind:markdown="problem.statement.markdown"
            v-bind:image-mapping="problem.statement.images"
            v-bind:problem-settings="problem.settings"
          ></omegaup-markdown>
        </div>
        <hr class="my-3" />
        <div class="font-italic">
          {{ `${T.wordsSource}: ${problem.source}` }}
        </div>
        <template v-if="problem.problemsetter">
          <div>
            {{ T.wordsProblemsetter }}:
            <omegaup-username
              v-bind:classname="problem.problemsetter.classname"
              v-bind:username="problem.problemsetter.username"
              v-bind:name="problem.problemsetter.name"
              v-bind:linkify="true"
            ></omegaup-username>
          </div>
          <div>
            {{
              ui.formatString(T.wordsUploadedOn, {
                date: time.formatDate(problem.problemsetter.creation_date),
              })
            }}
          </div>
        </template>
        <omegaup-quality-nomination-review
          v-if="user.reviewer && !nominationStatus.already_reviewed"
        ></omegaup-quality-nomination-review>
        <omegaup-quality-nomination-demotion></omegaup-quality-nomination-demotion>
        <omegaup-arena-runs
          v-bind:problem-alias="problem.alias"
          v-bind:runs="runs"
          v-bind:showDetails="true"
        ></omegaup-arena-runs>
        <omegaup-arena-solvers v-bind:solvers="solvers"></omegaup-arena-solvers>
      </div>
      <div
        class="tab-pane fade p-4"
        v-bind:class="{ 'show active': selectedTab === 'solution' }"
      >
        <!-- Solutions stuff -->
      </div>
      <div
        class="tab-pane fade p-4"
        v-bind:class="{ 'show active': selectedTab === 'runs' }"
      >
        <!-- Admin Runs stuff -->
      </div>
      <div
        class="tab-pane fade p-4"
        v-bind:class="{ 'show active': selectedTab === 'clarifications' }"
      >
        <!-- Clarifications stuff -->
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

table td {
  padding: 0.5rem;
}

.karel-js-link {
  border: 1px solid #eee;
  border-left: 0;
  border-radius: 3px;

  a {
    border-left: 5px solid #1b809e;
    display: block;
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';
import * as ui from '../../ui';
import arena_Runs from '../arena/Runs.vue';
import arena_Solvers from '../arena/Solvers.vue';
import problem_SettingsSummary from './SettingsSummaryV2.vue';
import qualitynomination_Demotion from '../qualitynomination/DemotionPopup.vue';
import qualitynomination_QualityReview from '../qualitynomination/ReviewerPopup.vue';
import user_Username from '../user/Username.vue';
import omegaup_Markdown from '../Markdown.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faEdit,
  faExclamationTriangle,
  faEyeSlash,
  faBan,
  faExternalLinkAlt,
} from '@fortawesome/free-solid-svg-icons';
library.add(
  faExclamationTriangle,
  faEdit,
  faEyeSlash,
  faBan,
  faExternalLinkAlt,
);

interface Tab {
  name: string;
  text: string;
}

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-arena-runs': arena_Runs,
    'omegaup-arena-solvers': arena_Solvers,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-username': user_Username,
    'omegaup-problem-settings-summary': problem_SettingsSummary,
    'omegaup-quality-nomination-review': qualitynomination_QualityReview,
    'omegaup-quality-nomination-demotion': qualitynomination_Demotion,
  },
})
export default class ProblemDetails extends Vue {
  @Prop() problem!: types.ProblemInfo;
  @Prop() solvers!: types.BestSolvers[];
  @Prop() user!: types.UserInfoForProblem;
  @Prop() nominationStatus!: types.NominationStatus;
  @Prop() runs!: types.Run[];

  T = T;
  ui = ui;
  time = time;
  selectedTab = 'problems';

  get availableTabs(): Tab[] {
    const tabs = [
      {
        name: 'problems',
        text: T.wordsProblem,
        visible: true,
      },
      {
        name: 'solution',
        text: T.wordsSolution,
        visible: this.user.loggedIn,
      },
      {
        name: 'runs',
        text: T.wordsRuns,
        visible: this.user.admin,
      },
      {
        name: 'clarifications',
        text: T.wordsClarifications,
        visible: this.user.admin,
      },
    ];
    return tabs.filter((tab) => tab.visible);
  }
}
</script>
