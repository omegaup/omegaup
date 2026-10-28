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
              <textarea
                v-model="readmeEditContent"
                class="form-control mb-2"
                rows="10"
              ></textarea>
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
                >
                  <template #item-data="slotProps">
                    <a :href="slotProps.item.getUrl()">{{
                      slotProps.item.toString()
                    }}</a>
                    <span
                      v-if="slotProps.item.solved"
                      class="badge badge-success ml-1"
                      >{{ T.problemStatusSolved }}</span
                    >
                    <span
                      v-else-if="slotProps.item.attempted"
                      class="badge badge-warning ml-1"
                      >{{ T.problemStatusAttempted }}</span
                    >
                  </template>
                </omegaup-grid-paginator>
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
import { defineComponent, PropType } from 'vue';
import { types } from '../../api_types';
import T from '../../lang';
import {
  BookmarkedProblem,
  Contest,
  ContestResult,
  Course,
  Problem,
} from '../../linkable_resource';
import { Experiments } from '../../omegaup';
import * as ui from '../../ui';
import badge_List from '../badge/List.vue';
import common_GridPaginator from '../common/GridPaginator.vue';
import common_TablePaginator from '../common/TablePaginator.vue';
import common_Markdown from '../Markdown.vue';
import user_BasicInfo from './BasicInfov2.vue';
import user_Charts from './Charts.vue';
import problem_SolvingProgress from './ProblemSolvingProgress.vue';
import tags_SolvedChart from './TagsSolvedChart.vue';
import user_Heatmap from './UserHeatmap.vue';

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

export default defineComponent({
  name: 'ViewProfile',
  components: {
    'omegaup-markdown': common_Markdown,
    'omegaup-user-basicinfo': user_BasicInfo,
    'omegaup-user-charts': user_Charts,
    'omegaup-user-heatmap': user_Heatmap,
    'omegaup-problem-solving-progress': problem_SolvingProgress,
    'omegaup-tags-solved-chart': tags_SolvedChart,
    'omegaup-badge-list': badge_List,
    'omegaup-grid-paginator': common_GridPaginator,
    'omegaup-table-paginator': common_TablePaginator,
  },
  props: {
    data: {
      type: Object as PropType<types.ExtraProfileDetails | null>,
      default: null,
    },
    profile: {
      type: Object as PropType<types.UserProfileInfo>,
      required: true,
    },
    profileBadges: {
      type: (Set as unknown) as PropType<Set<string>>,
      default: () => new Set<string>(),
    },
    visitorBadges: {
      type: (Set as unknown) as PropType<Set<string>>,
      default: () => new Set<string>(),
    },
    selectedTab: {
      type: String as PropType<string | null>,
      default: null,
    },
    availableYears: {
      type: Array as PropType<number[]>,
      default: () => [],
    },
    profileStatistics: {
      type: Object as PropType<{
        solved: number;
        attempting: number;
        difficulty: {
          easy: number;
          medium: number;
          hard: number;
          unlabelled: number;
        };
        tags: Array<{ name: string; count: number }>;
      } | null>,
      default: null,
    },
  },
  emits: [
    'heatmap-year-changed',
    'save-readme',
    'report-readme',
    'update:selectedTab',
  ],
  data() {
    const contests = Object.values(
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

    return {
      contests,
      charts: (this.data?.stats ?? []) as types.UserProfileStats[],
      ViewProfileTabs,
      T,
      ui,
      columns: 3,
      currentSelectedTab: getInitialSelectedTab(this.profile, this.selectedTab),
      normalizedRunCounts: [] as Highcharts.PointOptionsObject[],
      currentReadme: this.profile.readme ?? null,
      isEditingReadme: false,
      readmeEditContent: null as string | null,
      readmeReportSubmitted: false,
      isReadmeEnabled: Experiments.loadGlobal().isEnabled('user_readme'),
    };
  },
  computed: {
    createdContests(): Contest[] {
      if (!this.data?.createdContests) return [];
      let contests = this.data.createdContests;
      if (!this.profile.is_own_profile) {
        contests = contests.filter(
          (contest) => contest.admission_mode === 'public',
        );
      }
      return contests.map((contest) => new Contest(contest));
    },
    createdCourses(): Course[] {
      if (!this.data?.createdCourses) return [];
      let courses = this.data.createdCourses;
      if (!this.profile.is_own_profile) {
        courses = courses.filter(
          (course) => course.admission_mode === 'public',
        );
      }
      return courses.map((course) => new Course(course));
    },
    createdProblems(): Problem[] {
      if (!this.data?.createdProblems) return [];
      return this.data.createdProblems.map((problem) => new Problem(problem));
    },
    unsolvedProblems(): Problem[] {
      if (!this.data?.unsolvedProblems) return [];
      return this.data.unsolvedProblems.map((problem) => new Problem(problem));
    },
    columnNames(): Array<{ name: string; style: string }> {
      return [
        { name: T.profileContestsTableContest, style: 'text-left' },
        { name: T.profileContestsTablePlace, style: 'text-right' },
      ];
    },
    solvedProblems(): Problem[] {
      if (!this.data?.solvedProblems) return [];
      return this.data.solvedProblems.map((problem) => new Problem(problem));
    },
    bookmarkedProblems(): BookmarkedProblem[] {
      if (!this.data?.bookmarkedProblems) return [];
      return this.data.bookmarkedProblems.map(
        (problem: types.BookmarkProblem) => new BookmarkedProblem(problem),
      );
    },
    rank(): string {
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
    },
  },
  watch: {
    selectedTab(newValue: string | null): void {
      if (newValue !== null) {
        this.currentSelectedTab = getInitialSelectedTab(this.profile, newValue);
      }
    },
    currentSelectedTab(newValue: string): void {
      this.$emit('update:selectedTab', newValue);
    },
    data(newData: types.ExtraProfileDetails | null): void {
      this.contests = Object.values(
        newData?.contests ?? ({} as types.UserProfileContests),
      )
        .map((contest) => {
          const now = new Date();
          if (contest.place === null || now <= contest.data.finish_time) {
            return null;
          }
          return new ContestResult(contest);
        })
        .filter((contest) => Boolean(contest));
      this.charts = newData?.stats ?? [];
    },
  },
  methods: {
    startEditReadme(): void {
      this.readmeEditContent = this.currentReadme;
      this.isEditingReadme = true;
    },
    cancelEditReadme(): void {
      this.isEditingReadme = false;
      this.readmeEditContent = null;
    },
    saveReadme(): void {
      const content = this.readmeEditContent ?? '';
      this.$emit('save-readme', {
        readme: content,
        onSuccess: () => {
          this.currentReadme = content;
          this.isEditingReadme = false;
        },
      });
    },
    reportReadme(): void {
      this.$emit('report-readme', {
        username: this.profile.username ?? '',
        onSuccess: () => {
          this.readmeReportSubmitted = true;
          ui.success(T.profileAboutSectionReportSuccess);
        },
      });
    },
  },
});
</script>

<style lang="scss">
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
</style>
