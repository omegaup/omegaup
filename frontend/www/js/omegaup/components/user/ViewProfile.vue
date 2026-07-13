<template>
  <div class="container-fluid p-0 mt-0">
    <div
      v-if="
        isReadmeEnabled && (currentReadme !== null || profile.is_own_profile)
      "
      class="row mb-3"
    >
      <div class="col-md-12">
        <div class="card">
          <div
            class="card-header d-flex justify-content-between align-items-center"
          >
            <span>{{ T.profileAboutSection }}</span>
            <div>
              <button
                v-if="profile.is_own_profile && !isEditingReadme"
                class="btn btn-sm btn-outline-secondary"
                @click="startEditReadme"
              >
                {{ T.wordsEdit }}
              </button>
              <button
                v-if="
                  !profile.is_own_profile &&
                  currentReadme !== null &&
                  !readmeReportSubmitted
                "
                class="btn btn-sm btn-outline-warning"
                @click="reportReadme"
              >
                {{ T.profileAboutSectionReport }}
              </button>
            </div>
          </div>
          <div class="card-body">
            <template v-if="!isEditingReadme">
              <omegaup-markdown
                v-if="currentReadme"
                :markdown="currentReadme"
                :full-width="true"
              ></omegaup-markdown>
              <p v-else-if="profile.is_own_profile" class="text-muted mb-0">
                {{ T.profileAboutSectionAddPrompt }}
              </p>
            </template>
            <template v-else>
              <div
                ref="aboutMarkdownButtonBar"
                class="wmd-button-bar mb-2"
              ></div>
              <textarea
                ref="aboutMarkdownInput"
                v-model="readmeEditContent"
                class="form-control wmd-input mb-2"
                :maxlength="MAX_ABOUT_LENGTH"
                @input="enforceLimit"
              ></textarea>
              <div class="d-flex justify-content-end mt-1">
                <small
                  :class="{
                    'text-danger': isOverLimit,
                    'text-warning': isNearLimit,
                    'text-muted': !isNearLimit,
                  }"
                >
                  {{ charCount }} / {{ MAX_ABOUT_LENGTH }}
                </small>
              </div>
              <div
                v-if="readmeEditContent"
                class="border p-3 mb-2 readme-preview"
              >
                <omegaup-markdown
                  :markdown="readmeEditContent"
                  :full-width="true"
                />
              </div>
              <button class="btn btn-primary btn-sm mr-2" @click="saveReadme">
                {{ T.wordsSaveChanges }}
              </button>
              <button
                class="btn btn-secondary btn-sm"
                @click="cancelEditReadme"
              >
                {{ T.wordsCancel }}
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header" data-profile-tabs>
            <nav class="nav nav-tabs" role="tablist" data-profile-navtabs>
              <a
                v-if="profile.is_own_profile || !profile.is_private"
                :href="`#${ViewProfileTabs.Badges}`"
                class="nav-item nav-link"
                :class="{
                  active: currentSelectedTab === ViewProfileTabs.Badges,
                }"
                @click="currentSelectedTab = ViewProfileTabs.Badges"
              >
                {{ T.wordsBadgesObtained }}
                <span class="badge badge-secondary">
                  {{ profileBadges.size }}
                </span>
              </a>
              <a
                v-if="profile.is_own_profile || !profile.is_private"
                :href="`#${ViewProfileTabs.Problems}`"
                class="nav-item nav-link"
                :class="{
                  active: currentSelectedTab === ViewProfileTabs.Problems,
                }"
                @click="currentSelectedTab = ViewProfileTabs.Problems"
                >{{ T.wordsProblems }}</a
              >
              <a
                v-if="profile.is_own_profile || !profile.is_private"
                :href="`#${ViewProfileTabs.Contests}`"
                class="nav-item nav-link"
                :class="{
                  active: currentSelectedTab === ViewProfileTabs.Contests,
                }"
                @click="currentSelectedTab = ViewProfileTabs.Contests"
              >
                {{ T.profileContests }}
                <span class="badge badge-secondary">
                  {{ Object.keys(contests).length }}
                </span>
              </a>
              <a
                v-if="profile.is_own_profile || !profile.is_private"
                :href="`#${ViewProfileTabs.CreatedContent}`"
                class="nav-item nav-link"
                :class="{
                  active: currentSelectedTab === ViewProfileTabs.CreatedContent,
                }"
                data-created-content-tab
                @click="currentSelectedTab = ViewProfileTabs.CreatedContent"
                >{{ T.profileCreatedContent }}</a
              >
              <a
                :href="`#${ViewProfileTabs.Data}`"
                class="nav-item nav-link"
                :class="{ active: currentSelectedTab === ViewProfileTabs.Data }"
                @click="currentSelectedTab = ViewProfileTabs.Data"
                >{{ T.profilePersonalData }}</a
              >
              <a
                v-if="profile.is_own_profile || !profile.is_private"
                :href="`#${ViewProfileTabs.Charts}`"
                class="nav-item nav-link"
                :class="{
                  active: currentSelectedTab === ViewProfileTabs.Charts,
                }"
                @click="currentSelectedTab = ViewProfileTabs.Charts"
                >{{ T.wordsStatistics }}</a
              >
            </nav>
            <div class="tab-content">
              <div
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-badges-tab"
              >
                <omegaup-badge-list
                  v-if="currentSelectedTab == ViewProfileTabs.Badges"
                  :all-badges="profileBadges"
                  :show-all-badges-link="true"
                  :visitor-badges="visitorBadges"
                ></omegaup-badge-list>
              </div>
              <div
                v-if="currentSelectedTab == ViewProfileTabs.Problems"
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-problems-tab"
              >
                <omegaup-grid-paginator
                  :columns="3"
                  :items="solvedProblems"
                  :items-per-page="30"
                  :title="T.profileSolvedProblems"
                  :should-show-filter-input="true"
                  class="mb-3"
                ></omegaup-grid-paginator>
                <omegaup-grid-paginator
                  :columns="3"
                  :items="unsolvedProblems"
                  :items-per-page="30"
                  :title="T.profileUnsolvedProblems"
                  class="mb-3"
                ></omegaup-grid-paginator>
                <omegaup-grid-paginator
                  :columns="3"
                  :items="createdProblems"
                  :items-per-page="30"
                  :title="T.profileCreatedProblems"
                  class="mb-3"
                ></omegaup-grid-paginator>
                <omegaup-grid-paginator
                  :columns="3"
                  :items="bookmarkedProblems"
                  :items-per-page="30"
                  :title="T.profileBookmarkedProblems"
                  class="mb-3"
                ></omegaup-grid-paginator>
              </div>
              <div
                v-show="currentSelectedTab == ViewProfileTabs.Contests"
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-contests-tab"
              >
                <omegaup-table-paginator
                  :column-names="columnNames"
                  :items="contests"
                  :items-per-page="15"
                >
                </omegaup-table-paginator>
              </div>
              <div
                v-if="currentSelectedTab == ViewProfileTabs.CreatedContent"
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-created-content-tab"
              >
                <omegaup-grid-paginator
                  :columns="3"
                  :items="createdProblems"
                  :items-per-page="30"
                  :title="T.profileCreatedProblems"
                  class="mb-3"
                >
                  <template v-if="profile.is_own_profile" #header-link
                    ><a
                      href="/problem/mine/"
                      class="float-right align-self-center"
                      >{{ T.profileCreatedContentSeeAll }}</a
                    ></template
                  >
                </omegaup-grid-paginator>
                <omegaup-grid-paginator
                  :columns="3"
                  :items="createdContests"
                  :items-per-page="30"
                  :title="T.profileCreatedContests"
                  class="mb-3"
                >
                  <template v-if="profile.is_own_profile" #header-link
                    ><a
                      href="/contest/mine/"
                      class="float-right align-self-center"
                      >{{ T.profileCreatedContentSeeAll }}</a
                    ></template
                  >
                </omegaup-grid-paginator>
                <omegaup-grid-paginator
                  :columns="3"
                  :items="createdCourses"
                  :items-per-page="30"
                  :title="T.profileCreatedCourses"
                  class="mb-3"
                >
                  <template v-if="profile.is_own_profile" #header-link
                    ><a
                      href="/course/mine/"
                      class="float-right align-self-center"
                      >{{ T.profileCreatedContentSeeAll }}</a
                    ></template
                  >
                </omegaup-grid-paginator>
              </div>
              <div
                v-if="currentSelectedTab == ViewProfileTabs.Data"
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-user-info-tab"
              >
                <omegaup-user-basicinfo
                  :profile="profile"
                  :rank="rank"
                ></omegaup-user-basicinfo>
              </div>
              <div
                v-if="currentSelectedTab == ViewProfileTabs.Charts"
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-charts-tab"
              >
                <div
                  v-if="profileStatistics"
                  class="row mb-4 statistics-boxes-row"
                >
                  <div class="col-lg-6 mb-3">
                    <omegaup-problem-solving-progress
                      :difficulty="profileStatistics.difficulty"
                      :attempting="profileStatistics.attempting"
                    ></omegaup-problem-solving-progress>
                  </div>
                  <div class="col-lg-6 mb-3">
                    <omegaup-tags-solved-chart
                      :tags="profileStatistics.tags"
                    ></omegaup-tags-solved-chart>
                  </div>
                </div>
                <div class="chart-section">
                  <omegaup-user-charts
                    v-if="charts"
                    :data="charts"
                    :username="profile.username"
                  ></omegaup-user-charts>
                </div>
                <div class="chart-section-heatmap">
                  <omegaup-user-heatmap
                    :username="profile.username"
                    :available-years="availableYears"
                    :data="charts"
                    @year-changed="
                      (year) => $emit('heatmap-year-changed', year)
                    "
                  ></omegaup-user-heatmap>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import * as Highcharts from 'highcharts/highstock';
import { Component, Prop, Vue, Watch, Ref } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import {
  Contest,
  ContestResult,
  Course,
  Problem,
} from '../../linkable_resource';
import { Experiments } from '../../omegaup';
import * as ui from '../../ui';
import * as Markdown from '@/third_party/js/pagedown/Markdown.Editor.js';
import * as markdown from '../../markdown';
import badge_List from '../badge/List.vue';
import common_GridPaginator from '../common/GridPaginator.vue';
import common_TablePaginator from '../common/TablePaginator.vue';
import country_Flag from '../CountryFlag.vue';
import common_Markdown from '../Markdown.vue';
import user_BasicInfo from './BasicInfov2.vue';
import user_Charts from './Charts.vue';
import user_MainInfo from './MainInfo.vue';
import problem_SolvingProgress from './ProblemSolvingProgress.vue';
import tags_SolvedChart from './TagsSolvedChart.vue';
import user_Heatmap from './UserHeatmap.vue';
import user_Username from './Username.vue';

export enum ViewProfileTabs {
  Badges = 'badges',
  Problems = 'problems',
  Contests = 'contests',
  CreatedContent = 'created-content',
  Data = 'data',
  Charts = 'charts',
}

function getInitialSelectedTab(
  profile: types.UserProfileInfo,
  selectedTab: string | null,
): string {
  if (!profile.is_own_profile && profile.is_private) {
    return ViewProfileTabs.Data;
  }
  return selectedTab ?? ViewProfileTabs.Badges;
}

const aboutMarkdownConverter = new markdown.Converter({
  preview: true,
});
const MAX_ABOUT_LENGTH = 1000;

@Component({
  components: {
    'omegaup-markdown': common_Markdown,
    'omegaup-user-basicinfo': user_BasicInfo,
    'omegaup-user-username': user_Username,
    'omegaup-user-charts': user_Charts,
    'omegaup-user-heatmap': user_Heatmap,
    'omegaup-user-maininfo': user_MainInfo,
    'omegaup-problem-solving-progress': problem_SolvingProgress,
    'omegaup-tags-solved-chart': tags_SolvedChart,
    'omegaup-badge-list': badge_List,
    'omegaup-grid-paginator': common_GridPaginator,
    'omegaup-table-paginator': common_TablePaginator,
    'omegaup-countryflag': country_Flag,
  },
})
export default class ViewProfile extends Vue {
  @Ref() readonly aboutMarkdownButtonBar!: HTMLDivElement;
  @Ref() readonly aboutMarkdownInput!: HTMLTextAreaElement;
  @Prop() data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop() profileBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;
  @Prop({ default: null }) selectedTab!: string | null;
  @Prop({ default: () => [] }) availableYears!: number[];
  @Prop({ default: null }) profileStatistics!: {
    solved: number;
    attempting: number;
    difficulty: {
      easy: number;
      medium: number;
      hard: number;
      unlabelled: number;
    };
    tags: Array<{ name: string; count: number }>;
  } | null;
  contests = Object.values(
    this.data?.contests ?? ({} as types.UserProfileContests),
  )
    .map((contest) => {
      const now = new Date();
      if (contest.place === null || now <= contest.data.finish_time) {
        return null;
      }
      return new ContestResult(contest);
    })
    .filter((contest) => Boolean(contest));
  charts: types.UserProfileStats[] = this.data?.stats ?? [];
  ViewProfileTabs = ViewProfileTabs;
  T = T;
  ui = ui;
  aboutMarkdownEditor: Markdown.Editor | null = null;
  columns = 3;
  currentSelectedTab = getInitialSelectedTab(this.profile, this.selectedTab);
  normalizedRunCounts: Highcharts.PointOptionsObject[] = [];
  currentReadme: string = this.profile.readme ?? '';
  isEditingReadme = false;
  readmeEditContent: string = '';
  readmeReportSubmitted = false;
  isReadmeEnabled = Experiments.loadGlobal().isEnabled('user_readme');
  MAX_ABOUT_LENGTH = MAX_ABOUT_LENGTH;

  mounted(): void {
    if (this.isEditingReadme) {
      this.$nextTick(() => {
        this.initAboutEditor();
      });
    }
  }

  private initAboutEditor(): void {
    if (this.aboutMarkdownEditor) return;

    this.aboutMarkdownEditor = new Markdown.Editor(
      aboutMarkdownConverter.converter,
      '',
      {
        panels: {
          buttonBar: this.aboutMarkdownButtonBar,
          preview: null,
          input: this.aboutMarkdownInput,
        },
      },
    );

    this.aboutMarkdownEditor.run();
  }

  get charCount(): number {
    return this.readmeEditContent?.length || 0;
  }

  get isNearLimit(): boolean {
    return this.charCount >= MAX_ABOUT_LENGTH * 0.9;
  }

  get isOverLimit(): boolean {
    return this.charCount > MAX_ABOUT_LENGTH;
  }

  get createdContests(): Contest[] {
    if (!this.data?.createdContests) return [];
    let contests = this.data.createdContests;
    if (!this.profile.is_own_profile) {
      contests = contests.filter(
        (contest) => contest.admission_mode === 'public',
      );
    }
    return contests.map((contest) => new Contest(contest));
  }
  get createdCourses(): Course[] {
    if (!this.data?.createdCourses) return [];
    let courses = this.data.createdCourses;
    if (!this.profile.is_own_profile) {
      courses = courses.filter((course) => course.admission_mode === 'public');
    }
    return courses.map((course) => new Course(course));
  }
  get createdProblems(): Problem[] {
    if (!this.data?.createdProblems) return [];
    return this.data.createdProblems.map((problem) => new Problem(problem));
  }
  get unsolvedProblems(): Problem[] {
    if (!this.data?.unsolvedProblems) return [];
    return this.data.unsolvedProblems.map((problem) => new Problem(problem));
  }

  get columnNames(): Array<{ name: string; style: string }> {
    return [
      { name: T.profileContestsTableContest, style: 'text-left' },
      { name: T.profileContestsTablePlace, style: 'text-right' },
    ];
  }

  get solvedProblems(): Problem[] {
    if (!this.data?.solvedProblems) return [];
    return this.data.solvedProblems.map((problem) => new Problem(problem));
  }
  get bookmarkedProblems(): Problem[] {
    if (!this.data?.bookmarkedProblems) return [];
    return this.data.bookmarkedProblems.map(
      (problem: types.BookmarkProblem) => new Problem(problem as types.Problem),
    );
  }
  get rank(): string {
    switch (this.profile.classname) {
      case 'user-rank-beginner':
        return T.profileRankBeginner;
      case 'user-rank-specialist':
        return T.profileRankSpecialist;
      case 'user-rank-expert':
        return T.profileRankExpert;
      case 'user-rank-master':
        return T.profileRankMaster;
      case 'user-rank-international-master':
        return T.profileRankInternationalMaster;
      default:
        return T.profileRankUnrated;
    }
  }

  enforceLimit(event: Event): void {
    const target = event.target as HTMLTextAreaElement;
    if (target.value.length > MAX_ABOUT_LENGTH) {
      ui.error(T.aboutTooLongError);
      target.value = target.value.substring(0, MAX_ABOUT_LENGTH);
      this.readmeEditContent = target.value;
    }
  }

  startEditReadme(): void {
    this.readmeEditContent = this.currentReadme;
    this.isEditingReadme = true;
    this.$nextTick(() => {
      this.initAboutEditor();
      if (this.aboutMarkdownInput) {
        this.aboutMarkdownInput.focus();
        const length = this.aboutMarkdownInput.value.length;
        this.aboutMarkdownInput.setSelectionRange(length, length);
      }
    });
  }

  cancelEditReadme(): void {
    this.isEditingReadme = false;
    this.readmeEditContent = null;
    this.aboutMarkdownEditor = null;
  }

  saveReadme(): void {
    if (this.isOverLimit) {
      ui.error(T.aboutTooLongError);
      return;
    }

    const content = this.readmeEditContent ?? '';
    this.$emit('save-readme', {
      readme: content,
      onSuccess: () => {
        this.currentReadme = content;
        this.isEditingReadme = false;
        this.aboutMarkdownEditor = null;
      },
    });
  }

  reportReadme(): void {
    this.$emit('report-readme', {
      username: this.profile.username ?? '',
      onSuccess: () => {
        this.readmeReportSubmitted = true;
        ui.success(T.profileAboutSectionReportSuccess);
      },
    });
  }

  @Watch('selectedTab')
  onSelectedTabPropChanged(newValue: string | null) {
    if (newValue !== null) {
      this.currentSelectedTab = getInitialSelectedTab(this.profile, newValue);
    }
  }

  @Watch('currentSelectedTab')
  onCurrentSelectedTabChanged(newValue: string) {
    this.$emit('update:selectedTab', newValue);
  }

  @Watch('isEditingReadme')
  onEditingReadmeChanged(newValue: boolean): void {
    if (newValue) {
      this.$nextTick(() => {
        this.initAboutEditor();
      });
    }
  }
}
</script>

<style lang="scss">
@import '../../../../third_party/js/pagedown/demo/browser/demo.css';
a:hover {
  cursor: pointer;
}

.chart-section {
  background-color: var(--user-chart-section-background-color);
  border-radius: 4px;
  padding: 15px;
}

.chart-section-heatmap {
  background-color: var(--user-chart-section-heatmap-background-color);
  border-radius: 4px;
  padding: 0px;
  margin-top: 15px;
}

.chart-title {
  font-size: 1.1rem;
  font-weight: 500;
  color: var(--user-chart-title-color);
}

.statistics-boxes-row {
  margin-top: 16px;
}

.statistics-boxes-row > [class*='col-'] {
  display: flex;
}

.wmd-button-bar {
  background-color: var(--wmd-button-bar-background-color);
}

.wmd-input {
  font-family: monospace;
  resize: vertical;
  min-height: 200px;
}

.readme-preview {
  min-height: 150px;
  max-height: 500px;
  overflow-y: auto;
  background-color: var(
    --markdown-preview-background,
    var(--background-color-secondary)
  );
}
</style>
