<template>
  <div class="container-fluid p-5 max-width mx-auto">
    <h1 class="title-font p-0 mb-2 text-center">{{ title }}</h1>
    <div class="mb-4">
      <a href="/problem/collection/" data-nav-problems-collection>{{
        T.problemCollectionBackCollections
      }}</a>
    </div>
    <div class="d-flex flex-row collection-layout">
      <div
        v-if="isMobileViewport && filtersVisible"
        class="filters-backdrop"
        @click="filtersVisible = false"
      ></div>
      <div
        class="filters-sidebar"
        :class="{ 'filters-hidden': !filtersVisible }"
      >
        <omegaup-problem-filter-tags
          :selected-tags="selectedTags"
          :tags="availableTags"
          :public-quality-tags="publicQualityTags"
          @new-selected-tag="
            (selectedTags) =>
              $emit(
                'apply-filter',
                columnName,
                sortOrder,
                difficulty,
                quality,
                selectedTags,
              )
          "
        ></omegaup-problem-filter-tags>

        <div
          class="problem-tags-toggle-row mb-3"
          :title="isProblemTagsLocked ? T.problemTagsLockedByPreferences : ''"
        >
          <omegaup-toggle-switch
            data-problem-tags-toggle
            :checked-value="effectiveShowProblemTags"
            :text-description="T.userEditShowProblemTags"
            :size="ToggleSwitchSize.Small"
            :disabled="isProblemTagsLocked"
            @update:value="
              (value) => {
                if (!isProblemTagsLocked) showProblemTags = value;
              }
            "
          ></omegaup-toggle-switch>
          <span v-if="isProblemTagsLocked" class="problem-tags-lock">
            <font-awesome-icon icon="lock" />
          </span>
        </div>
        <div class="filter-cards">
          <omegaup-problem-filter-difficulty
            :selected-difficulty="difficulty"
            @change-difficulty="
              (difficulty) =>
                $emit(
                  'apply-filter',
                  columnName,
                  sortOrder,
                  difficulty,
                  quality,
                  selectedTags,
                )
            "
          ></omegaup-problem-filter-difficulty>

          <omegaup-problem-filter-quality
            :quality="quality"
            @change-quality="
              (quality) =>
                $emit(
                  'apply-filter',
                  columnName,
                  sortOrder,
                  difficulty,
                  quality,
                  selectedTags,
                )
            "
          ></omegaup-problem-filter-quality>
        </div>
      </div>

      <button
        class="btn btn-outline-secondary btn-sm filter-toggle"
        :title="
          filtersVisible ? T.collectionHideFilters : T.collectionShowFilters
        "
        :aria-label="
          filtersVisible ? T.collectionHideFilters : T.collectionShowFilters
        "
        @click="filtersVisible = !filtersVisible"
      >
        <font-awesome-icon
          :icon="filtersVisible ? 'chevron-left' : 'chevron-right'"
        />
      </button>

      <div class="flex-grow-1 main-content-wrapper">
        <div v-if="!problems || problems.length == 0" class="card-body">
          <div class="empty-table-message">
            {{ T.courseAssignmentProblemsEmpty }}
          </div>
        </div>

        <omegaup-problem-base-list
          v-else
          :problems="problemsToShow"
          :logged-in="loggedIn"
          :selected-tags="selectedTags"
          :pager-items="pagerItems"
          :wizard-tags="wizardTags"
          :language="language"
          :languages="languages"
          :keyword="keyword"
          :modes="modes"
          :columns="columns"
          :mode="modes"
          :column="column"
          :tags="tagsList"
          :sort-order="sortOrder"
          :column-name="columnName"
          :path="`/problem/collection/${level}/`"
          :show-problem-tags="effectiveShowProblemTags"
          @apply-filter="
            (columnName, sortOrder) =>
              $emit(
                'apply-filter',
                columnName,
                sortOrder,
                difficulty,
                quality,
                selectedTags,
              )
          "
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import problem_FilterTags from './FilterTags.vue';
import problem_BaseList from './BaseList.vue';
import problem_FilterDifficulty from './FilterDifficulty.vue';
import problem_FilterQuality from './FilterQuality.vue';
import omegaup_ToggleSwitch, { ToggleSwitchSize } from '../ToggleSwitch.vue';
import T from '../../lang';
import { types } from '../../api_types';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faChevronLeft,
  faChevronRight,
} from '@fortawesome/free-solid-svg-icons';

library.add(faChevronLeft, faChevronRight);

const MOBILE_BREAKPOINT = 576;

@Component({
  components: {
    'omegaup-problem-filter-tags': problem_FilterTags,
    'omegaup-problem-base-list': problem_BaseList,
    'omegaup-problem-filter-difficulty': problem_FilterDifficulty,
    'omegaup-problem-filter-quality': problem_FilterQuality,
    'font-awesome-icon': FontAwesomeIcon,
    'omegaup-toggle-switch': omegaup_ToggleSwitch,
  },
})
export default class CollectionList extends Vue {
  @Prop() data!: types.CollectionDetailsByLevelPayload;
  @Prop() problems!: omegaup.Problem[];
  @Prop() loggedIn!: boolean;
  @Prop({ default: () => [] }) selectedTags!: string[];
  @Prop() pagerItems!: types.PageItem[];
  @Prop() wizardTags!: omegaup.Tag[];
  @Prop() language!: string;
  @Prop() languages!: string[];
  @Prop() keyword!: string;
  @Prop() modes!: string[];
  @Prop() columns!: string[];
  @Prop() mode!: string;
  @Prop() column!: string;
  @Prop({ default: () => [] }) tagsList!: string[];
  @Prop() sortOrder!: string;
  @Prop() columnName!: string;
  @Prop() difficulty!: string;
  @Prop() quality!: string;
  @Prop({ default: false }) hideProblemTagsPreference!: boolean;

  T = T;
  ToggleSwitchSize = ToggleSwitchSize;
  level = this.data.level;
  filtersVisible =
    typeof window === 'undefined'
      ? true
      : window.innerWidth >= MOBILE_BREAKPOINT;
  showProblemTags = true;
  isMobileViewport =
    typeof window === 'undefined'
      ? false
      : window.innerWidth < MOBILE_BREAKPOINT;

  mounted(): void {
    this.updateViewportMode();
    window.addEventListener('resize', this.updateViewportMode);
  }

  beforeDestroy(): void {
    window.removeEventListener('resize', this.updateViewportMode);
  }

  updateViewportMode(): void {
    const wasMobileViewport = this.isMobileViewport;
    this.isMobileViewport = window.innerWidth < MOBILE_BREAKPOINT;
    if (this.isMobileViewport === wasMobileViewport) {
      return;
    }
    this.filtersVisible = !this.isMobileViewport;
  }

  get problemsToShow(): omegaup.Problem[] {
    if (this.effectiveShowProblemTags) return this.problems;
    // Keep filtering logic intact but hide rendered tags by stripping them.
    return this.problems.map((problem) => ({
      ...problem,
      tags: [],
    }));
  }

  get isProblemTagsLocked(): boolean {
    return this.hideProblemTagsPreference;
  }

  get effectiveShowProblemTags(): boolean {
    if (this.isProblemTagsLocked) return false;
    return this.showProblemTags;
  }

  get publicQualityTags(): types.TagWithProblemCount[] {
    const tagNames: Set<string> = new Set(
      this.data.frequentTags.map((x) => x.name),
    );
    return this.data.publicTags.filter((tag) => !tagNames.has(tag.name));
  }

  get availableTags(): types.TagWithProblemCount[] {
    const tags: types.TagWithProblemCount[] = this.data.frequentTags;
    const selectedTagNames = new Set<string>(this.selectedTags);
    return tags.concat(
      this.publicQualityTags.filter((tag) => selectedTagNames.has(tag.name)),
    );
  }

  get title(): string {
    switch (this.level) {
      case 'problemLevelBasicKarel':
        return T.problemLevelBasicKarel;
      case 'problemLevelBasicIntroductionToProgramming':
        return T.problemLevelBasicIntroductionToProgramming;
      case 'problemLevelIntermediateMathsInProgramming':
        return T.problemLevelIntermediateMathsInProgramming;
      case 'problemLevelIntermediateDataStructuresAndAlgorithms':
        return T.problemLevelIntermediateDataStructuresAndAlgorithms;
      case 'problemLevelIntermediateAnalysisAndDesignOfAlgorithms':
        return T.problemLevelIntermediateAnalysisAndDesignOfAlgorithms;
      case 'problemLevelAdvancedCompetitiveProgramming':
        return T.problemLevelAdvancedCompetitiveProgramming;
      case 'problemLevelAdvancedSpecializedTopics':
        return T.problemLevelAdvancedSpecializedTopics;
      default:
        return '';
    }
  }
}
</script>

<style scoped>
.title-font {
  font-size: 2rem;
  letter-spacing: 0.01rem;
}

.max-width {
  max-width: 75rem;
}

.problem-tags-toggle-row {
  display: flex;
  align-items: center;
}

.problem-tags-lock {
  font-size: 0.9rem;
  margin-left: 2px;
  cursor: help;
}

.filters-sidebar {
  width: 250px;
  min-width: 250px;
  transition: width 0.3s ease, min-width 0.3s ease, opacity 0.2s ease;
  overflow: hidden;
  padding-right: 1rem;
}

.filters-sidebar.filters-hidden {
  width: 0;
  min-width: 0;
  opacity: 0;
  padding-right: 0;
}

.filter-toggle {
  align-self: flex-start;
  flex-shrink: 0;
  margin-right: 0.5rem;
  line-height: 1;
  padding: 0.25rem 0.35rem;
}

.main-content-wrapper {
  min-width: 0;
  flex: 1;
}

.collection-layout {
  position: relative;
}

.filters-backdrop {
  display: none;
}

@media (max-width: 575.98px) {
  .filters-sidebar {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 20;
    width: min(18rem, 85vw);
    min-width: min(18rem, 85vw);
    height: 100%;
    padding-right: 0.5rem;
    padding-left: 0.5rem;
    background: var(--problem-collection-list-mobile-sidebar-background-color);
    border-right: 1px solid
      var(--problem-collection-list-mobile-sidebar-border-color);
    box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, opacity 0.2s ease;
  }

  .filters-sidebar.filters-hidden {
    width: min(18rem, 85vw);
    min-width: min(18rem, 85vw);
    transform: translateX(-100%);
    pointer-events: none;
  }

  .filter-toggle {
    position: sticky;
    top: 0.5rem;
    z-index: 30;
  }

  .filters-backdrop {
    display: block;
    position: fixed;
    inset: 0;
    z-index: 10;
    background: rgba(0, 0, 0, 0.2);
  }

  .main-content-wrapper {
    width: 100%;
  }
}
</style>
