<template>
  <div class="mt-4">
    <ul class="nav justify-content-center nav-tabs">
      <li
        v-for="tab in availableTabs"
        :key="tab.name"
        class="nav-item"
        role="tablist"
      >
        <a
          :href="`#${tab.name}`"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          :aria-controls="tab.name"
          :class="{ active: selectedTab === tab.name }"
          :aria-selected="selectedTab === tab.name"
          @click="onTabSelected(tab.name)"
        >
          {{ tab.text }}
          <span
            v-if="tab.name === 'clarifications'"
            class="clarifications-count"
            :class="{ 'font-weight-bold': hasUnreadClarifications }"
            >{{ clarificationsCount }}</span
          >
        </a>
      </li>
    </ul>
    <div class="tab-content">
      <div
        class="tab-pane fade p-4"
        :class="{ 'show active': selectedTab === 'problems' }"
      >
        <omegaup-problem-settings-summary
          :problem="problem"
          :show-visibility-indicators="true"
          :show-edit-link="user.admin"
        ></omegaup-problem-settings-summary>

        <div v-if="problem.karel_problem" class="karel-js-link my-3">
          <a
            class="p-3"
            :href="`/karel.js/${
              problem.sample_input ? `#mundo:${problem.sample_input}` : ''
            }`"
            target="_blank"
          >
            {{ T.openInKarelJs }}
            <font-awesome-icon :icon="['fas', 'external-link-alt']" />
          </a>
        </div>

        <div class="mt-4 markdown">
          <omegaup-markdown
            :markdown="problem.statement.markdown"
            :image-mapping="problem.statement.images"
            :problem-settings="problem.settings"
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
              :classname="problem.problemsetter.classname"
              :username="problem.problemsetter.username"
              :name="problem.problemsetter.name"
              :linkify="true"
            ></omegaup-username>
          </div>
          <div>
            {{
              ui.formatString(T.wordsUploadedOn, {
                date: time.formatDate(problem.problemsetter.creation_date),
              })
            }}
          </div>
          <div>
            <button class="btn btn-link" @click="onNewPromotion">
              {{ T.qualityNominationRateProblem }}
            </button>
          </div>
        </template>
        <omegaup-quality-nomination-review
          v-if="user.reviewer && !nominationStatus.alreadyReviewed"
          :allow-user-add-tags="allowUserAddTags"
          :level-tags="levelTags"
          :problem-level="problemLevel"
          :public-tags="publicTags"
          :selected-public-tags="selectedPublicTags"
          :selected-private-tags="selectedPrivateTags"
          :problem-alias="problem.alias"
          :problem-title="problem.title"
          @submit="
            (tag, qualitySeal) => $emit('submit-reviewer', tag, qualitySeal)
          "
        ></omegaup-quality-nomination-review>
        <omegaup-quality-nomination-demotion
          @submit="
            (qualityDemotionComponent) =>
              $emit('submit-demotion', qualityDemotionComponent)
          "
        ></omegaup-quality-nomination-demotion>
        <omegaup-overlay
          v-if="user.loggedIn"
          :show-overlay="showOverlay"
          @overlay-hidden="onPopupDismissed"
        >
          <template #popup>
            <omegaup-arena-runsubmit-popup
              :preferred-language="problem.preferred_language"
              :languages="problem.languages"
              :initial-show-form="showFormRunSubmit"
              @dismiss="onPopupDismissed"
              @submit-run="
                (code, selectedLanguage) =>
                  onRunSubmitted(code, selectedLanguage)
              "
            ></omegaup-arena-runsubmit-popup>
            <omegaup-quality-nomination-promotion
              :can-nominate-problem="nominationStatus.canNominateProblem"
              :dismissed="nominationStatus.dismissed"
              :dismissed-before-a-c="nominationStatus.dismissedBeforeAC"
              :nominated="nominationStatus.nominated"
              :nomination-before-a-c="nominationStatus.nominationBeforeAC"
              :solved="nominationStatus.solved"
              :tried="nominationStatus.tried"
              :problem-alias="problem.alias"
              @submit="
                (qualityPromotionComponent) =>
                  $emit('submit-promotion', qualityPromotionComponent)
              "
              @dismiss="
                (qualityPromotionComponent, isDismissed) =>
                  onPopupPromotionDismissed(
                    qualityPromotionComponent,
                    isDismissed,
                  )
              "
            ></omegaup-quality-nomination-promotion>
          </template>
        </omegaup-overlay>
        <omegaup-arena-runs
          :problem-alias="problem.alias"
          :runs="runs"
          :show-details="true"
          :problemset-problems="[]"
          @new-submission="onNewSubmission"
        ></omegaup-arena-runs>
        <omegaup-problem-feedback
          :quality-histogram="histogram.qualityHistogram"
          :difficulty-histogram="histogram.difficultyHistogram"
          :quality-score="histogram.quality"
          :difficulty-score="histogram.difficulty"
        ></omegaup-problem-feedback>
        <omegaup-arena-solvers :solvers="solvers"></omegaup-arena-solvers>
      </div>
      <div
        class="tab-pane fade p-4"
        :class="{ 'show active': selectedTab === 'solution' }"
      >
        <omegaup-problem-solution
          :status="solutionStatus"
          :solution="solution"
          :available-tokens="availableTokens"
          :all-tokens="allTokens"
          @get-solution="$emit('get-solution')"
          @get-tokens="$emit('get-tokens')"
          @unlock-solution="$emit('unlock-solution')"
        ></omegaup-problem-solution>
      </div>
      <div
        class="tab-pane fade p-4"
        :class="{ 'show active': selectedTab === 'runs' }"
      >
        <omegaup-arena-runs
          :runs="allRuns"
          :show-details="true"
          :show-user="true"
          :show-rejudge="true"
          :show-pager="true"
          :show-disqualify="true"
          :problemset-problems="[]"
        ></omegaup-arena-runs>
      </div>
      <div
        class="tab-pane fade p-4"
        :class="{ 'show active': selectedTab === 'clarifications' }"
      >
        <omegaup-arena-clarification-list
          :clarifications="clarifications"
          :in-contest="false"
          @clarification-response="
            (id, responseText, isPublic) =>
              $emit('clarification-response', id, responseText, isPublic)
          "
        ></omegaup-arena-clarification-list>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';
import * as ui from '../../ui';
import arena_ClarificationList from '../arena/ClarificationList.vue';
import arena_Runs from '../arena/Runs.vue';
import arena_RunSubmitPopup from '../arena/RunSubmitPopup.vue';
import arena_Solvers from '../arena/Solvers.vue';
import problem_Feedback from './Feedback.vue';
import problem_SettingsSummary from './SettingsSummaryV2.vue';
import problem_Solution from './Solution.vue';
import qualitynomination_Demotion from '../qualitynomination/DemotionPopup.vue';
import qualitynomination_Promotion from '../qualitynomination/PromotionPopup.vue';
import qualitynomination_QualityReview from '../qualitynomination/ReviewerPopupv2.vue';
import user_Username from '../user/Username.vue';
import omegaup_Markdown from '../Markdown.vue';
import omegaup_Overlay from '../Overlay.vue';

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
    'omegaup-arena-runsubmit-popup': arena_RunSubmitPopup,
    'omegaup-arena-solvers': arena_Solvers,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-overlay': omegaup_Overlay,
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
  @Prop() showNewRunWindow!: boolean;
  @Prop() showPromotionWindow!: boolean;
  @Prop() activeTab!: string;
  @Prop() allowUserAddTags!: boolean;
  @Prop() levelTags!: string[];
  @Prop() problemLevel!: string;
  @Prop() publicTags!: string[];
  @Prop() selectedPublicTags!: string[];
  @Prop() selectedPrivateTags!: string[];

  T = T;
  ui = ui;
  time = time;
  selectedTab = this.activeTab;
  clarifications = this.initialClarifications || [];
  showFormRunSubmit = this.showNewRunWindow;
  showFormPromotion = this.showPromotionWindow;
  showOverlay = this.showNewRunWindow;
  hasUnreadClarifications =
    this.initialClarifications?.length > 0 &&
    this.activeTab !== 'clarifications';

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

  onNewSubmission(): void {
    if (!this.user.loggedIn) {
      this.$emit('redirect-login-page');
    }
    this.showOverlay = true;
    this.showFormRunSubmit = true;
  }

  onNewPromotion(): void {
    if (!this.user.loggedIn) {
      this.$emit('redirect-login-page');
    }
    this.showOverlay = true;
    this.showFormPromotion = true;
  }

  onPopupDismissed(): void {
    this.showOverlay = false;
    this.showFormRunSubmit = false;
    this.showFormPromotion = false;
    this.$emit('update:activeTab', this.selectedTab);
  }

  onPopupPromotionDismissed(
    qualityPromotionComponent: qualitynomination_Promotion,
    isDismissed: boolean,
  ): void {
    this.onPopupDismissed();
    this.$emit('dismiss-promotion', qualityPromotionComponent, isDismissed);
  }

  onRunSubmitted(code: string, selectedLanguage: string): void {
    this.$emit('submit-run', code, selectedLanguage);
    this.onPopupDismissed();
  }

  @Emit('update:activeTab')
  onTabSelected(tabName: string): string {
    if (this.selectedTab === 'clarifications') {
      this.hasUnreadClarifications = false;
    }
    this.selectedTab = tabName;
    return this.selectedTab;
  }

  @Watch('initialClarifications')
  onInitialClarificationsChanged(newValue: types.Clarification[]): void {
    this.clarifications = newValue;
  }

  @Watch('showNewRunWindow')
  onShowNewRunWindowChanged(newValue: boolean): void {
    if (!newValue) return;
    this.onNewSubmission();
  }

  @Watch('clarifications')
  onClarificationsChanged(newValue: types.Clarification[]): void {
    if (this.selectedTab === 'clarifications' || newValue.length === 0) return;
    this.hasUnreadClarifications = true;
  }
}
</script>

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
