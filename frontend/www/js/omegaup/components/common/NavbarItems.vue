<template>
  <div class="mr-auto mt-2 mt-lg-0">
    <ul
      v-if="!omegaUpLockDown && (!inContest || isAdmin)"
      class="navbar-nav align-items-start px-3"
    >
      <li
        v-if="isLoggedIn"
        class="nav-item dropdown nav-contests nav-item-align"
        :class="{ active: navbarSection === 'contests' }"
      >
        <a
          class="nav-link px-2 dropdown-toggle"
          href="#"
          role="button"
          data-nav-contests
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="false"
        >
          {{ T.wordsContests }}
        </a>
        <div class="dropdown-menu fullwidth-mobile-fit-lg navbar-item-dropdown">
          <slot name="contests-items">
            <omegaup-navbar-item
              :title="T.navViewContests"
              :description="T.navViewContestsDesc"
              :icon="['fas', 'trophy']"
              href="/arena/"
              data-nav-contests-arena
            />
            <template v-if="isMainUserIdentity">
              <omegaup-navbar-item
                :title="T.contestsJoinScoreboards"
                :description="T.contestsJoinScoreboardsDesc"
                :icon="['fas', 'list-ol']"
                href="/scoreboardmerge/"
              />
              <omegaup-navbar-item
                v-if="!isUnder13User"
                :title="T.contestsCreate"
                :description="T.contestsCreateDesc"
                :icon="['fas', 'calendar-plus']"
                href="/contest/new/"
                data-nav-contests-create
              />
            </template>
          </slot>
        </div>
      </li>
      <li v-else :class="{ active: navbarSection === 'contests' }">
        <a class="nav-link px-2" href="/arena/" data-nav-contests-arena>{{
          T.wordsContests
        }}</a>
      </li>

      <li
        v-if="isLoggedIn"
        class="nav-item dropdown nav-courses nav-item-align"
        :class="{ active: navbarSection === 'courses' }"
      >
        <a
          class="nav-link px-2 dropdown-toggle"
          href="#"
          role="button"
          data-nav-courses
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="false"
        >
          {{ T.navCourses }}
        </a>
        <div class="dropdown-menu fullwidth-mobile-fit-lg navbar-item-dropdown">
          <slot name="courses-items">
            <omegaup-navbar-item
              :title="T.navViewCourses"
              :description="T.navViewCoursesDesc"
              :icon="['fas', 'graduation-cap']"
              href="/course/"
              data-nav-courses-all
            />
            <template v-if="isMainUserIdentity && !isUnder13User">
              <omegaup-navbar-item
                :title="T.courseCreate"
                :description="T.courseCreateDesc"
                :icon="['fas', 'chalkboard-teacher']"
                href="/course/new/"
                data-nav-courses-create
              />
            </template>
          </slot>
        </div>
      </li>
      <li
        v-else
        :class="{ active: navbarSection === 'course' }"
        data-nav-course
      >
        <a class="nav-link px-2" href="/course/home/">{{ T.navCourses }}</a>
      </li>

      <li
        class="nav-item dropdown nav-problems nav-item-align"
        :class="{ active: navbarSection === 'problems' }"
      >
        <a
          class="nav-link px-2 dropdown-toggle"
          href="#"
          role="button"
          data-toggle="dropdown"
          data-nav-problems
          aria-haspopup="true"
          aria-expanded="false"
        >
          {{ T.wordsProblems }}
        </a>
        <div class="dropdown-menu fullwidth-mobile-fit-lg navbar-item-dropdown">
          <slot name="problems-items">
            <omegaup-navbar-item
              :title="T.navViewProblems"
              :description="T.navViewProblemsDesc"
              :icon="['fas', 'layer-group']"
              href="/problem/collection/"
              data-nav-problems-collection
            />
            <omegaup-navbar-item
              :title="T.navViewProblemsAll"
              :description="T.navViewProblemsAllDesc"
              :icon="['fas', 'list']"
              href="/problem/"
              data-nav-problems-list
            />
            <omegaup-navbar-item
              :title="T.bookmarkedProblems"
              :description="T.bookmarkedProblemsDesc"
              :icon="['fas', 'bookmark']"
              href="/profile/#problems"
            />
            <hr class="menu-divider" />
            <omegaup-navbar-item
              :title="T.navViewLatestSubmissions"
              :description="T.navViewLatestSubmissionsDesc"
              :icon="['fas', 'history']"
              href="/submissions/"
            />

            <template v-if="!isLoggedIn">
              <omegaup-navbar-item
                :title="T.createZipFileForProblem"
                :description="T.createZipFileForProblemDesc"
                :icon="['fas', 'plus-circle']"
                href="/problem/creator/"
              />
            </template>

            <template v-else>
              <div class="collapse-submenu">
                <button
                  type="button"
                  class="dropdown-item dropdown-toggle"
                  data-nav-problems-create-options
                  :aria-expanded="isCreateProblemSubmenuOpen ? 'true' : 'false'"
                  @click.stop.prevent="onCreateProblemClick"
                >
                  {{ T.myproblemsListCreateProblem }}
                </button>

                <div v-show="isCreateProblemSubmenuOpen" class="pl-3">
                  <a
                    class="dropdown-item"
                    href="/problem/creator/"
                    @click.stop
                    >{{ T.myproblemsListCreateZipFileProblem }}</a
                  >
                  <a
                    class="dropdown-item"
                    href="/problem/new/"
                    data-nav-problems-create
                    @click.stop
                    >{{ T.myproblemsListCreateProblemWithExistingZipFile }}</a
                  >
                </div>
              </div>
            </template>
            <omegaup-navbar-item
              v-if="isReviewer"
              :title="T.navQualityNominationQueue"
              :description="T.navQualityNominationQueueDesc"
              :icon="['fas', 'clipboard-check']"
              href="/nomination/"
            />
          </slot>
        </div>
      </li>

      <li
        class="nav-item dropdown nav-rank nav-item-align"
        :class="{ active: navbarSection === 'rank' }"
      >
        <a
          class="nav-link px-2 dropdown-toggle"
          href="#"
          role="button"
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="false"
        >
          {{ T.navRanking }}
        </a>
        <div class="dropdown-menu fullwidth-mobile-fit-lg navbar-item-dropdown">
          <omegaup-navbar-item
            :title="T.navUserRanking"
            :description="T.navUserRankingDesc"
            :icon="['fas', 'chart-line']"
            href="/rank/"
          />
          <omegaup-navbar-item
            :title="T.navCompareUsers"
            :description="T.navCompareUsersDesc"
            :icon="['fas', 'balance-scale']"
            href="/rank/compare/"
          />
          <omegaup-navbar-item
            :title="T.navAuthorRanking"
            :description="T.navAuthorRankingDesc"
            :icon="['fas', 'pen-nib']"
            href="/rank/authors/"
          />
          <omegaup-navbar-item
            :title="T.navSchoolRanking"
            :description="T.navSchoolRankingDesc"
            :icon="['fas', 'school']"
            href="/rank/schools/"
          />
          <omegaup-navbar-item
            :title="T.navCoderOfTheMonth"
            :description="T.navCoderOfTheMonthDesc"
            :icon="['fas', 'medal']"
            href="/coderofthemonth/"
          />
          <omegaup-navbar-item
            :title="T.navCoderOfTheMonthFemale"
            :description="T.navCoderOfTheMonthFemaleDesc"
            :icon="['fas', 'medal']"
            href="/coderofthemonth/female/"
          />
          <omegaup-navbar-item
            :title="T.navSchoolOfTheMonth"
            :description="T.navSchoolOfTheMonthDesc"
            :icon="['fas', 'award']"
            href="/schoolofthemonth/"
          />
        </div>
      </li>

      <li class="nav-item dropdown nav-item-align">
        <a
          class="nav-link px-2 dropdown-toggle"
          href="#"
          role="button"
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="false"
        >
          {{ T.navHelp }}
        </a>
        <div class="dropdown-menu fullwidth-mobile-fit-lg navbar-item-dropdown">
          <template v-for="(entry, index) in helpMenuEntries">
            <hr
              v-if="entry.divider"
              :key="`help-divider-${index}`"
              class="menu-divider"
            />
            <omegaup-navbar-item
              v-else
              :key="`help-item-${index}`"
              :title="entry.title"
              :description="entry.description"
              :icon="entry.icon"
              :href="entry.href"
              target="_blank"
              :rel="entry.rel"
            />
          </template>
        </div>
      </li>
    </ul>
    <ul v-else class="navbar-nav mr-auto"></ul>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { getExternalUrl } from '../../urlHelper';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faAward,
  faBalanceScale,
  faBook,
  faBookmark,
  faCalendarPlus,
  faChalkboardTeacher,
  faChartLine,
  faClipboardCheck,
  faCode,
  faComments,
  faDatabase,
  faGraduationCap,
  faHistory,
  faLayerGroup,
  faList,
  faListOl,
  faMedal,
  faNewspaper,
  faPen,
  faPenNib,
  faPlusCircle,
  faRobot,
  faSchool,
  faTrophy,
  faVideo,
} from '@fortawesome/free-solid-svg-icons';
import NavbarItem from './NavbarItem.vue';

library.add(
  faAward,
  faBalanceScale,
  faBook,
  faBookmark,
  faCalendarPlus,
  faChalkboardTeacher,
  faChartLine,
  faClipboardCheck,
  faCode,
  faComments,
  faDatabase,
  faGraduationCap,
  faHistory,
  faLayerGroup,
  faList,
  faListOl,
  faMedal,
  faNewspaper,
  faPen,
  faPenNib,
  faPlusCircle,
  faRobot,
  faSchool,
  faTrophy,
  faVideo,
);

interface HelpMenuEntry {
  divider?: boolean;
  title?: string;
  description?: string;
  icon?: [string, string];
  href?: string;
  rel?: string | null;
}

@Component({
  components: {
    'omegaup-navbar-item': NavbarItem,
  },
})
export default class NavbarItems extends Vue {
  @Prop() omegaUpLockDown!: boolean;
  @Prop() inContest!: boolean;
  @Prop() isLoggedIn!: boolean;
  @Prop() isReviewer!: boolean;
  @Prop() isAdmin!: boolean;
  @Prop() isMainUserIdentity!: boolean;
  @Prop() navbarSection!: string;
  @Prop() isUnder13User!: boolean;

  T = T;

  isCreateProblemSubmenuOpen = false;

  get OmegaUpBlogURL(): string {
    return getExternalUrl('OmegaUpBlogURL');
  }

  get YouTubeTutorialsURL(): string {
    return getExternalUrl('YouTubeTutorialsURL');
  }

  get DiscordInviteURL(): string {
    return getExternalUrl('DiscordInviteURL');
  }

  get AlgorithmsBookURL(): string {
    return getExternalUrl('AlgorithmsBookURL');
  }

  get CompetitiveProgrammingBookURL(): string {
    return getExternalUrl('CompetitiveProgrammingBookURL');
  }

  get helpMenuEntries(): HelpMenuEntry[] {
    return [
      {
        title: T.navTutorials,
        description: T.navTutorialsDesc,
        icon: ['fas', 'video'],
        href: this.YouTubeTutorialsURL,
      },
      {
        title: T.navDiscord,
        description: T.navDiscordDesc,
        icon: ['fas', 'comments'],
        href: this.DiscordInviteURL,
      },
      {
        title: T.navBlog,
        description: T.navBlogDesc,
        icon: ['fas', 'newspaper'],
        href: this.OmegaUpBlogURL,
      },
      { divider: true },
      {
        title: T.navProblemStatementEditor,
        description: T.navProblemStatementEditorDesc,
        icon: ['fas', 'pen'],
        href: '/problem/statement/',
        rel: 'noopener noreferrer',
      },
      {
        title: T.navOmegaUpIDE,
        description: T.navOmegaUpIDEDesc,
        icon: ['fas', 'code'],
        href: '/grader/ephemeral/',
        rel: 'noopener noreferrer',
      },
      {
        title: T.navKarel,
        description: T.navKarelDesc,
        icon: ['fas', 'robot'],
        href: '/karel.js/',
        rel: 'noopener noreferrer',
      },
      { divider: true },
      {
        title: T.navAlgorithmsBook,
        description: T.navAlgorithmsBookDesc,
        icon: ['fas', 'book'],
        href: this.AlgorithmsBookURL,
      },
      {
        title: T.navCompetitiveProgrammingDataStructuresBook,
        description: T.navCompetitiveProgrammingDataStructuresBookDesc,
        icon: ['fas', 'database'],
        href: this.CompetitiveProgrammingBookURL,
        rel: 'noopener noreferrer',
      },
    ];
  }

  onCreateProblemClick(): void {
    this.isCreateProblemSubmenuOpen = !this.isCreateProblemSubmenuOpen;
  }
}
</script>

<style lang="scss" scoped>
@media only screen and (max-width: 992px) {
  .navbar-item-dropdown {
    min-width: auto !important;
    width: auto !important;
    max-width: 85vw !important;
    left: auto !important;
    right: 0 !important;

    .dropdown-item {
      white-space: normal !important;
      word-wrap: break-word !important;
      overflow-wrap: break-word !important;
      word-break: break-word !important;
      line-height: 1.4 !important;
      padding: 0.5rem 1rem !important;
      max-width: 100% !important;
      display: block !important;
    }
  }
}

.menu-divider {
  margin-top: 0em;
  margin-bottom: 0em;
  border-width: 2px;
}
</style>
