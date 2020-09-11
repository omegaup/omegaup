<template>
  <div class="mt-4">
    <ul class="nav justify-content-center nav-tabs">
      <li
        class="nav-item"
        role="tablist"
        v-for="tab in availableTabs"
        v-bind:key="tab.name"
      >
        <a
          v-bind:href="`#${tab.name}`"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          v-bind:aria-controls="tab.name"
          v-bind:class="{ active: selectedTab === tab.name }"
          v-bind:aria-selected="selectedTab === tab.name"
          v-on:click="onTabSelected(tab.name)"
        >
          {{ tab.text }}
          <span
            class="clarifications-count"
            v-bind:class="{ 'font-weight-bold': !clarificationsTabVisited }"
            v-if="tab.name === 'clarifications'"
            >{{ clarificationsCount }}</span
          >
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
          v-bind:show-visibility-indicators="true"
          v-bind:show-edit-link="this.user.admin"
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
          v-on:submit="
            (tag, qualitySeal) => $emit('submit-reviewer', tag, qualitySeal)
          "
        ></omegaup-quality-nomination-review>
        <omegaup-quality-nomination-demotion
          v-on:submit="
            (qualityDemotionComponent) =>
              $emit('submit-demotion', qualityDemotionComponent)
          "
        ></omegaup-quality-nomination-demotion>
        <omegaup-quality-nomination-promotion
          v-bind:can-nominate-problem="nominationStatus.canNoominateProblem"
          v-bind:dismissed="nominationStatus.dismissed"
          v-bind:dismissed-before-a-c="nominationStatus.dismissedBeforeAC"
          v-bind:nominated="nominationStatus.nominated"
          v-bind:nomination-before-a-c="nominationStatus.nominationBeforeAC"
          v-bind:solved="nominationStatus.solved"
          v-bind:tried="nominationStatus.tried"
          v-bind:problem-alias="problem.alias"
          v-on:submit="
            (qualityPromotionComponent) =>
              $emit('submit-promotion', qualityPromotionComponent)
          "
          v-on:dismiss="
            (qualityPromotionComponent) =>
              $emit('dismiss-promotion', qualityPromotionComponent)
          "
        ></omegaup-quality-nomination-promotion>
        <omegaup-arena-runs
          v-bind:problem-alias="problem.alias"
          v-bind:runs="runs"
          v-bind:show-details="true"
          v-bind:problemset-problems="[]"
        ></omegaup-arena-runs>
        <omegaup-problem-feedback
          v-bind:quality-histogram="histogram.qualityHistogram"
          v-bind:difficulty-histogram="histogram.difficultyHistogram"
          v-bind:quality-score="histogram.quality"
          v-bind:difficulty-score="histogram.difficulty"
        ></omegaup-problem-feedback>
        <omegaup-arena-solvers v-bind:solvers="solvers"></omegaup-arena-solvers>
      </div>
      <div
        class="tab-pane fade p-4"
        v-bind:class="{ 'show active': selectedTab === 'solution' }"
      >
        <omegaup-problem-solution
          v-bind:status="solutionStatus"
          v-bind:solution="solution"
          v-bind:available-tokens="availableTokens"
          v-bind:all-tokens="allTokens"
          v-on:get-solution="$emit('get-solution')"
          v-on:get-tokens="$emit('get-tokens')"
          v-on:unlock-solution="$emit('unlock-solution')"
        ></omegaup-problem-solution>
      </div>
      <div
        class="tab-pane fade p-4"
        v-bind:class="{ 'show active': selectedTab === 'runs' }"
      >
        <omegaup-arena-runs
          v-bind:runs="allRuns"
          v-bind:show-details="true"
          v-bind:show-user="true"
          v-bind:show-rejudge="true"
          v-bind:show-pager="true"
          v-bind:show-disqualify="true"
          v-bind:problemset-problems="[]"
        ></omegaup-arena-runs>
      </div>
      <div
        class="tab-pane fade p-4"
        v-bind:class="{ 'show active': selectedTab === 'clarifications' }"
      >
        <omegaup-arena-clarification-list
          v-bind:clarifications="clarifications"
          v-bind:in-contest="false"
          v-on:clarification-response="
            (id, responseText, isPublic) =>
              $emit('clarification-response', id, responseText, isPublic)
          "
        ></omegaup-arena-clarification-list>
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
import { Vue, Component, Prop, Emit, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';
import * as ui from '../../ui';
import arena_ClarificationList from '../arena/ClarificationList.vue';
import arena_Runs from '../arena/Runs.vue';
import arena_Solvers from '../arena/Solvers.vue';
import problem_Feedback from './Feedback.vue';
import problem_SettingsSummary from './SettingsSummaryV2.vue';
import problem_Solution from './Solution.vue';
import qualitynomination_Demotion from '../qualitynomination/DemotionPopup.vue';
import qualitynomination_Promotion from '../qualitynomination/Popup.vue';
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
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena-runs': arena_Runs,
    'omegaup-arena-solvers': arena_Solvers,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-username': user_Username,
    'omegaup-problem-feedback': problem_Feedback,
    'omegaup-problem-settings-summary': problem_SettingsSummary,
    'omegaup-problem-solution': problem_Solution,
    'omegaup-quality-nomination-review': qualitynomination_QualityReview,
    'omegaup-quality-nomination-demotion': qualitynomination_Demotion,
    'omegaup-quality-nomination-promotion': qualitynomination_Promotion,
  },
})
export default class ProblemDetails extends Vue {
  @Prop({
    default: () => {
      return [];
    },
  })
  allRuns!: types.Run[];
  @Prop() initialClarifications!: types.Clarification[];
  @Prop() problem!: types.ProblemInfo;
  @Prop() solvers!: types.BestSolvers[];
  @Prop() user!: types.UserInfoForProblem;
  @Prop() nominationStatus!: types.NominationStatus;
  @Prop() runs!: types.Run[];
  @Prop() solutionStatus!: string;
  @Prop({ default: null }) solution!: types.ProblemStatement | null;
  @Prop({ default: 0 }) availableTokens!: number;
  @Prop({ default: 0 }) allTokens!: number;
  @Prop() histogram!: types.Histogram;
  @Prop() initialTab!: string;

  T = T;
  ui = ui;
  time = time;
  selectedTab = this.initialTab;
  clarifications = this.initialClarifications || [];
  clarificationsTabVisited = false;

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

  get clarificationsCount(): string {
    if (this.clarifications.length === 0) return '';
    return `(${this.clarifications.length})`;
  }

  onTabSelected(tabName: string): void {
    if (this.selectedTab === 'clarifications') {
      this.clarificationsTabVisited = true;
    }
    this.selectedTab = tabName;
  }

  @Watch('selectedTab')
  onSelectedTabChanged(newValue: string, oldValue: string): void {
    this.$emit('tab-selected', newValue);
  }

  @Watch('initialClarifications')
  onClarificationsChanged(newValue: types.Clarification[]): void {
    this.clarifications = newValue;
  }
}
</script>
