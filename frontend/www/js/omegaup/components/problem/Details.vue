<template>
  <div class="mt-4">
    <ul v-if="shouldShowTabs" class="nav justify-content-center nav-tabs">
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
        v-if="problem"
        class="tab-pane fade p-4"
        :class="{ 'show active': selectedTab === 'problems' }"
      >
        <omegaup-problem-settings-summary
          :problem="problem"
          :show-visibility-indicators="showVisibilityIndicators"
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
            ref="statement-markdown"
            :markdown="problem.statement.markdown"
            :image-mapping="problem.statement.images"
            :problem-settings="problem.settings"
            @rendered="onProblemRendered"
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
          <slot name="quality-nomination-buttons">
            <div v-if="visibilityOfPromotionButton">
              <button class="btn btn-link" @click="onNewPromotion">
                {{ T.qualityNominationRateProblem }}
              </button>
            </div>
            <div v-if="user.loggedIn">
              <button
                class="btn btn-link"
                @click="onReportInappropriateProblem"
              >
                {{ T.wordsReportProblem }}
              </button>
            </div>
            <div v-if="user.reviewer && !nominationStatus.alreadyReviewed">
              <button class="btn btn-link" @click="onNewPromotionAsReviewer">
                {{ T.reviewerNomination }}
              </button>
            </div>
          </slot>
        </template>
        <omegaup-overlay
          v-if="user.loggedIn"
          :show-overlay="currentPopupDisplayed !== PopupDisplayed.None"
          @hide-overlay="onPopupDismissed"
        >
          <template #popup>
            <omegaup-arena-runsubmit-popup
              v-show="currentPopupDisplayed === PopupDisplayed.RunSubmit"
              :preferred-language="problem.preferred_language"
              :languages="problem.languages"
              @dismiss="onPopupDismissed"
              @submit-run="onRunSubmitted"
            ></omegaup-arena-runsubmit-popup>
            <omegaup-arena-rundetails-popup
              v-show="currentPopupDisplayed === PopupDisplayed.RunDetails"
              :data="currentRunDetailsData"
              @dismiss="onPopupDismissed"
            ></omegaup-arena-rundetails-popup>
            <omegaup-quality-nomination-promotion-popup
              v-show="currentPopupDisplayed === PopupDisplayed.Promotion"
              :solved="nominationStatus && nominationStatus.solved"
              :tried="nominationStatus && nominationStatus.tried"
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
            ></omegaup-quality-nomination-promotion-popup>
            <omegaup-quality-nomination-demotion-popup
              v-show="currentPopupDisplayed === PopupDisplayed.Demotion"
              @dismiss="currentPopupDisplayed = PopupDisplayed.None"
              @submit="
                (qualityDemotionComponent) =>
                  $emit('submit-demotion', qualityDemotionComponent)
              "
            ></omegaup-quality-nomination-demotion-popup>
            <omegaup-quality-nomination-reviewer-popup
              v-show="currentPopupDisplayed === PopupDisplayed.Reviewer"
              :allow-user-add-tags="allowUserAddTags"
              :level-tags="levelTags"
              :problem-level="problemLevel"
              :public-tags="publicTags"
              :selected-public-tags="selectedPublicTags"
              :selected-private-tags="selectedPrivateTags"
              :problem-alias="problem.alias"
              :problem-title="problem.title"
              @dismiss="currentPopupDisplayed = PopupDisplayed.None"
              @submit="
                (tag, qualitySeal) => $emit('submit-reviewer', tag, qualitySeal)
              "
            ></omegaup-quality-nomination-reviewer-popup>
          </template>
        </omegaup-overlay>
        <omegaup-arena-runs
          :problem-alias="problem.alias"
          :runs="runs"
          :show-details="true"
          :problemset-problems="[]"
          @details="(run) => onRunDetails(run.guid)"
          @new-submission="onNewSubmission"
        ></omegaup-arena-runs>
        <omegaup-problem-feedback
          :quality-histogram="parsedQualityHistogram"
          :difficulty-histogram="parsedDifficultyHistogram"
          :quality-score="histogramQuality"
          :difficulty-score="histogramDifficulty"
        ></omegaup-problem-feedback>
        <slot name="best-solvers-list">
          <omegaup-arena-solvers :solvers="solvers"></omegaup-arena-solvers>
        </slot>
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
          :show-all-runs="true"
          :runs="allRuns"
          :show-details="true"
          :show-user="true"
          :show-rejudge="true"
          :show-pager="true"
          :show-disqualify="true"
          :problemset-problems="[]"
          @details="(run) => onRunDetails(run.guid)"
          @rejudge="(run) => $emit('rejudge', run)"
          @disqualify="(run) => $emit('disqualify', run)"
          @filter-changed="
            (filter, value) => $emit('apply-filter', filter, value)
          "
        ></omegaup-arena-runs>
        <omegaup-overlay
          v-if="user.loggedIn"
          :show-overlay="currentPopupDisplayed !== PopupDisplayed.None"
          @hide-overlay="onPopupDismissed"
        >
          <template #popup>
            <omegaup-arena-rundetails-popup
              v-show="currentPopupDisplayed === PopupDisplayed.RunDetails"
              :data="currentRunDetailsData"
              @dismiss="onPopupDismissed"
            ></omegaup-arena-rundetails-popup>
          </template>
        </omegaup-overlay>
      </div>
      <div
        class="tab-pane fade p-4"
        :class="{ 'show active': selectedTab === 'clarifications' }"
      >
        <omegaup-arena-clarification-list
          :clarifications="clarifications"
          :in-contest="false"
          :is-admin="true"
          @clarification-response="
            (id, responseText, isPublic) =>
              $emit('clarification-response', id, responseText, isPublic)
          "
        >
          <template #new-clarification><div></div></template>
          <template #table-title>
            <th class="text-center" scope="col">{{ T.wordsContest }}</th>
          </template>
        </omegaup-arena-clarification-list>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref, Emit, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { messages, types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';
import * as ui from '../../ui';
import arena_ClarificationList from '../arena/ClarificationList.vue';
import arena_Runs from '../arena/Runs.vue';
import arena_RunSubmitPopup from '../arena/RunSubmitPopup.vue';
import arena_RunDetailsPopup from '../arena/RunDetailsPopup.vue';
import arena_Solvers from '../arena/Solvers.vue';
import problem_Feedback from './Feedback.vue';
import problem_SettingsSummary from './SettingsSummaryV2.vue';
import problem_Solution from './Solution.vue';
import qualitynomination_DemotionPopup from '../qualitynomination/DemotionPopup.vue';
import qualitynomination_PromotionPopup from '../qualitynomination/PromotionPopup.vue';
import qualitynomination_ReviewerPopup from '../qualitynomination/ReviewerPopup.vue';
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

export interface Tab {
  name: string;
  text: string;
}

export enum PopupDisplayed {
  None,
  RunSubmit,
  RunDetails,
  Promotion,
  Demotion,
  Reviewer,
}

const numericSort = <T extends { [key: string]: any }>(key: string) => {
  const isDigit = (ch: string) => '0' <= ch && ch <= '9';
  return (x: T, y: T) => {
    let i = 0,
      j = 0;
    for (; i < x[key].length && j < y[key].length; i++, j++) {
      if (isDigit(x[key][i]) && isDigit(x[key][j])) {
        let nx = 0,
          ny = 0;
        while (i < x[key].length && isDigit(x[key][i]))
          nx = nx * 10 + parseInt(x[key][i++]);
        while (j < y[key].length && isDigit(y[key][j]))
          ny = ny * 10 + parseInt(y[key][j++]);
        i--;
        j--;
        if (nx != ny) return nx - ny;
      } else if (x[key][i] < y[key][j]) {
        return -1;
      } else if (x[key][i] > y[key][j]) {
        return 1;
      }
    }
    return x[key].length - i - (y[key].length - j);
  };
};

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena-runs': arena_Runs,
    'omegaup-arena-runsubmit-popup': arena_RunSubmitPopup,
    'omegaup-arena-rundetails-popup': arena_RunDetailsPopup,
    'omegaup-arena-solvers': arena_Solvers,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-overlay': omegaup_Overlay,
    'omegaup-username': user_Username,
    'omegaup-problem-feedback': problem_Feedback,
    'omegaup-problem-settings-summary': problem_SettingsSummary,
    'omegaup-problem-solution': problem_Solution,
    'omegaup-quality-nomination-reviewer-popup': qualitynomination_ReviewerPopup,
    'omegaup-quality-nomination-demotion-popup': qualitynomination_DemotionPopup,
    'omegaup-quality-nomination-promotion-popup': qualitynomination_PromotionPopup,
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
  @Prop({ default: PopupDisplayed.None }) popupDisplayed!: PopupDisplayed;
  @Prop() activeTab!: string;
  @Prop() allowUserAddTags!: boolean;
  @Prop() levelTags!: string[];
  @Prop() problemLevel!: string;
  @Prop() publicTags!: string[];
  @Prop() selectedPublicTags!: string[];
  @Prop() selectedPrivateTags!: string[];
  @Prop() hasBeenNominated!: boolean;
  @Prop({ default: null }) runDetailsData!: types.RunDetails | null;
  @Prop() guid!: string;
  @Prop() isAdmin!: boolean;
  @Prop({ default: false }) showVisibilityIndicators!: boolean;
  @Prop({ default: false }) shouldShowTabs!: boolean;
  @Prop({ default: false }) shouldShowRunDetails!: boolean;

  @Ref('statement-markdown') readonly statementMarkdown!: omegaup_Markdown;

  PopupDisplayed = PopupDisplayed;
  T = T;
  ui = ui;
  time = time;
  selectedTab = this.activeTab;
  clarifications = this.initialClarifications || [];
  currentPopupDisplayed = this.popupDisplayed;
  hasUnreadClarifications =
    this.initialClarifications?.length > 0 &&
    this.activeTab !== 'clarifications';
  currentRunDetailsData = this.runDetailsData;

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
    if (this.clarifications.length > 9) return '(9+)';
    return `(${this.clarifications.length})`;
  }

  get visibilityOfPromotionButton(): boolean {
    return (
      (this.nominationStatus?.tried || this.nominationStatus?.solved) &&
      !this.hasBeenNominated
    );
  }

  get parsedQualityHistogram(): null | number[] {
    const qualityHistogram = this.histogram?.qualityHistogram;
    if (!qualityHistogram) {
      return null;
    }
    return JSON.parse(qualityHistogram);
  }

  get parsedDifficultyHistogram(): null | number[] {
    const difficultyHistogram = this.histogram?.difficultyHistogram;
    if (!difficultyHistogram) {
      return null;
    }
    return JSON.parse(difficultyHistogram);
  }

  get histogramQuality(): number {
    return this.histogram?.quality ?? 0;
  }

  get histogramDifficulty(): number {
    return this.histogram?.difficulty ?? 0;
  }

  onNewSubmission(): void {
    if (!this.user.loggedIn) {
      this.$emit('redirect-login-page');
      return;
    }
    this.currentPopupDisplayed = PopupDisplayed.RunSubmit;
  }

  onRunDetails(guid: string): void {
    this.$emit('show-run', this, guid);
    this.currentPopupDisplayed = PopupDisplayed.RunDetails;
  }

  onNewPromotion(): void {
    if (!this.user.loggedIn) {
      this.$emit('redirect-login-page');
      return;
    }
    this.currentPopupDisplayed = PopupDisplayed.Promotion;
  }

  onNewPromotionAsReviewer(): void {
    this.currentPopupDisplayed = PopupDisplayed.Reviewer;
  }

  onReportInappropriateProblem(): void {
    this.currentPopupDisplayed = PopupDisplayed.Demotion;
  }

  onPopupDismissed(): void {
    this.currentPopupDisplayed = PopupDisplayed.None;
    this.$emit('update:activeTab', this.selectedTab);
  }

  onPopupPromotionDismissed(
    qualityPromotionComponent: qualitynomination_PromotionPopup,
    isDismissed: boolean,
  ): void {
    this.onPopupDismissed();
    this.$emit('dismiss-promotion', qualityPromotionComponent, isDismissed);
  }

  onProblemRendered(): void {
    // TODO: We should probably refactor how the Markdown component is handled,
    // one for problems (with MathJax and the problem-specific logic) and another
    // for all other uses.
    //
    // It might be better for all of these things, plus the Markdown templating
    // system altogether to be replaced by the Markdown Vue component be able
    // to inject Vue components into the DOM after it's being rendered, so that
    // all the templating and interactivity can be handled by Vue instead of by
    // JavaScript.
    const libinteractiveInterfaceNameElement = this.statementMarkdown.$el.querySelector(
      'span.libinteractive-interface-name',
    ) as HTMLElement;
    if (
      libinteractiveInterfaceNameElement &&
      this.problem.settings?.interactive?.module_name
    ) {
      libinteractiveInterfaceNameElement.innerText = this.problem.settings.interactive.module_name.replace(
        /\.idl$/,
        '',
      );
    }

    const outputOnlyDownloadElement = this.statementMarkdown.$el.querySelector(
      '.output-only-download a',
    );
    if (outputOnlyDownloadElement) {
      outputOnlyDownloadElement.setAttribute(
        'href',
        `/probleminput/${this.problem.alias}/${this.problem.commit}/${this.problem.alias}-input.zip`,
      );
    }

    const libinteractiveDownloadFormElement = this.statementMarkdown.$el.querySelector(
      '.libinteractive-download form',
    ) as HTMLElement;
    if (libinteractiveDownloadFormElement) {
      libinteractiveDownloadFormElement.addEventListener(
        'submit',
        (e: Event) => {
          e.preventDefault();

          const form = e.target as HTMLElement;
          const alias = this.problem.alias;
          const commit = this.problem.commit;
          const os = (form.querySelector('.download-os') as HTMLInputElement)
            ?.value;
          const lang = (form.querySelector(
            '.download-lang',
          ) as HTMLInputElement)?.value;
          const extension = os == 'unix' ? '.tar.bz2' : '.zip';

          ui.navigateTo(
            `${window.location.protocol}//${window.location.host}/templates/${alias}/${commit}/${alias}_${os}_${lang}${extension}`,
          );
        },
      );
    }

    const libinteractiveDownloadLangElement = this.statementMarkdown.$el.querySelector(
      '.libinteractive-download .download-lang',
    ) as HTMLSelectElement;
    if (libinteractiveDownloadLangElement) {
      libinteractiveDownloadLangElement.addEventListener(
        'change',
        (e: Event) => {
          let form = e.target as HTMLElement;
          while (!form.classList.contains('libinteractive-download')) {
            if (!form.parentElement) {
              return;
            }
            form = form.parentElement;
          }
          (form.querySelector(
            '.libinteractive-extension',
          ) as HTMLElement).innerText = libinteractiveDownloadLangElement.value;
        },
      );
    }
  }

  onRunSubmitted(code: string, selectedLanguage: string): void {
    this.$emit('submit-run', { code, language: selectedLanguage });
    this.onPopupDismissed();
  }

  displayRunDetails(guid: string, data: messages.RunDetailsResponse): void {
    let sourceHTML,
      sourceLink = false;
    if (data.source?.indexOf('data:') === 0) {
      sourceLink = true;
      sourceHTML = data.source;
    } else if (data.source == 'lockdownDetailsDisabled') {
      sourceHTML =
        (typeof sessionStorage !== 'undefined' &&
          sessionStorage.getItem(`run:${guid}`)) ||
        T.lockdownDetailsDisabled;
    } else {
      sourceHTML = data.source;
    }

    const detailsGroups = data.details && data.details.groups;
    let groups = undefined;
    if (detailsGroups && detailsGroups.length) {
      detailsGroups.sort(numericSort('group'));
      for (const detailGroup of detailsGroups) {
        if (!detailGroup.cases) {
          continue;
        }
        detailGroup.cases.sort(numericSort('name'));
      }
      groups = detailsGroups;
    }

    Vue.set(
      this,
      'currentRunDetailsData',
      Object.assign({}, data, {
        logs: data.logs || '',
        judged_by: data.judged_by || '',
        source: sourceHTML,
        source_link: sourceLink,
        source_url: window.URL.createObjectURL(
          new Blob([data.source || ''], { type: 'text/plain' }),
        ),
        source_name: `Main.${data.language}`,
        groups: groups,
        show_diff: this.isAdmin ? data.show_diff : 'none',
        feedback: omegaup.SubmissionFeedback.None as omegaup.SubmissionFeedback,
      }),
    );

    this.$emit('change-show-run-location', { guid });
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

  @Watch('popupDisplayed')
  onPopupDisplayedChanged(newValue: PopupDisplayed): void {
    this.currentPopupDisplayed = newValue;
    if (newValue === PopupDisplayed.None) return;
    if (newValue === PopupDisplayed.RunSubmit) {
      this.onNewSubmission();
      return;
    }
    if (newValue === PopupDisplayed.Promotion) {
      ui.reportEvent('quality-nomination', 'shown');
      return;
    }
    if (newValue === PopupDisplayed.RunDetails && this.guid) {
      this.onRunDetails(this.guid);
    }
  }

  @Watch('clarifications')
  onClarificationsChanged(newValue: types.Clarification[]): void {
    if (this.selectedTab === 'clarifications' || newValue.length === 0) return;
    this.hasUnreadClarifications = true;
  }

  @Watch('shouldShowRunDetails')
  onShouldShowRunDetailsChanged(newValue: boolean): void {
    if (newValue && this.guid) {
      this.$emit('show-run', this, this.guid);
    }
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
