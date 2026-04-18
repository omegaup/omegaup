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
        <div class="dropdown-menu fullwidth-mobile-fit-lg">
          <slot name="contests-items">
            <a class="dropdown-item" href="/arena/" data-nav-contests-arena>
              {{ T.navViewContests }}
            </a>
            <template v-if="isMainUserIdentity">
              <a class="dropdown-item" href="/scoreboardmerge/">
                {{ T.contestsJoinScoreboards }}
              </a>
              <a
                v-if="!isUnder13User"
                class="dropdown-item"
                href="/contest/new/"
                data-nav-contests-create
              >
                {{ T.contestsCreate }}
              </a>
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
        <div class="dropdown-menu fullwidth-mobile-fit-lg">
          <slot name="courses-items">
            <a class="dropdown-item" href="/course/" data-nav-courses-all>
              {{ T.navViewCourses }}
            </a>
            <template v-if="isMainUserIdentity && !isUnder13User">
              <a
                class="dropdown-item"
                href="/course/new/"
                data-nav-courses-create
              >
                {{ T.courseCreate }}
              </a>
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
        <div class="dropdown-menu fullwidth-mobile-fit-lg">
          <slot name="problems-items">
            <a
              class="dropdown-item"
              href="/problem/collection/"
              data-nav-problems-collection
              >{{ T.navViewProblems }}</a
            >
            <a class="dropdown-item" href="/problem/" data-nav-problems-list>{{
              T.navViewProblemsAll
            }}</a>
            <a class="dropdown-item" href="/profile/#problems">{{
              T.bookmarkedProblems
            }}</a>
            <hr class="menu-divider" />
            <a class="dropdown-item" href="/submissions/">{{
              T.navViewLatestSubmissions
            }}</a>

            <template v-if="!isLoggedIn">
              <a class="dropdown-item" href="/problem/creator/">{{
                T.createZipFileForProblem
              }}</a>
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
            <a v-if="isReviewer" class="dropdown-item" href="/nomination/">{{
              T.navQualityNominationQueue
            }}</a>
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
        <div class="dropdown-menu fullwidth-mobile-fit-lg">
          <a class="dropdown-item" href="/rank/">{{ T.navUserRanking }}</a>
          <a class="dropdown-item" href="/rank/compare/">{{
            T.navCompareUsers
          }}</a>
          <a class="dropdown-item" href="/rank/authors/">{{
            T.navAuthorRanking
          }}</a>
          <a class="dropdown-item" href="/rank/schools/">{{
            T.navSchoolRanking
          }}</a>
          <a class="dropdown-item" href="/coderofthemonth/">{{
            T.navCoderOfTheMonth
          }}</a>
          <a href="/coderofthemonth/female/" class="dropdown-item">{{
            T.navCoderOfTheMonthFemale
          }}</a>
          <a class="dropdown-item" href="/schoolofthemonth/">{{
            T.navSchoolOfTheMonth
          }}</a>
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
        <div class="dropdown-menu fullwidth-mobile-fit-lg help-dropdown">
          <a
            class="dropdown-item d-flex align-items-center help-dropdown-item"
            :href="YouTubeTutorialsURL"
            target="_blank"
          >
            <font-awesome-icon
              :icon="['fas', 'video']"
              class="help-item-icon flex-shrink-0"
              fixed-width
            />
            <span>
              <span class="d-block">{{ T.navTutorials }}</span>
              <small class="text-muted">{{ T.navTutorialsDesc }}</small>
            </span>
          </a>
          <a
            class="dropdown-item d-flex align-items-center help-dropdown-item"
            :href="DiscordInviteURL"
            target="_blank"
          >
            <font-awesome-icon
              :icon="['fas', 'comments']"
              class="help-item-icon flex-shrink-0"
              fixed-width
            />
            <span>
              <span class="d-block">{{ T.navDiscord }}</span>
              <small class="text-muted">{{ T.navDiscordDesc }}</small>
            </span>
          </a>
          <a
            class="dropdown-item d-flex align-items-center help-dropdown-item"
            :href="OmegaUpBlogURL"
            target="_blank"
          >
            <font-awesome-icon
              :icon="['fas', 'newspaper']"
              class="help-item-icon flex-shrink-0"
              fixed-width
            />
            <span>
              <span class="d-block">{{ T.navBlog }}</span>
              <small class="text-muted">{{ T.navBlogDesc }}</small>
            </span>
          </a>
          <hr class="menu-divider" />
          <a
            class="dropdown-item d-flex align-items-center help-dropdown-item"
            href="/problem/statement/"
            target="_blank"
            rel="noopener noreferrer"
          >
            <font-awesome-icon
              :icon="['fas', 'pen']"
              class="help-item-icon flex-shrink-0"
              fixed-width
            />
            <span>
              <span class="d-block">{{ T.navProblemStatementEditor }}</span>
              <small class="text-muted">{{
                T.navProblemStatementEditorDesc
              }}</small>
            </span>
          </a>
          <a
            class="dropdown-item d-flex align-items-center help-dropdown-item"
            href="/grader/ephemeral/"
            target="_blank"
            rel="noopener noreferrer"
          >
            <font-awesome-icon
              :icon="['fas', 'code']"
              class="help-item-icon flex-shrink-0"
              fixed-width
            />
            <span>
              <span class="d-block">{{ T.navOmegaUpIDE }}</span>
              <small class="text-muted">{{ T.navOmegaUpIDEDesc }}</small>
            </span>
          </a>
          <a
            class="dropdown-item d-flex align-items-center help-dropdown-item"
            href="/karel.js/"
            target="_blank"
            rel="noopener noreferrer"
          >
            <font-awesome-icon
              :icon="['fas', 'robot']"
              class="help-item-icon flex-shrink-0"
              fixed-width
            />
            <span>
              <span class="d-block">{{ T.navKarel }}</span>
              <small class="text-muted">{{ T.navKarelDesc }}</small>
            </span>
          </a>
          <hr class="menu-divider" />
          <a
            class="dropdown-item d-flex align-items-center help-dropdown-item"
            :href="AlgorithmsBookURL"
            target="_blank"
          >
            <font-awesome-icon
              :icon="['fas', 'book']"
              class="help-item-icon flex-shrink-0"
              fixed-width
            />
            <span>
              <span class="d-block">{{ T.navAlgorithmsBook }}</span>
              <small class="text-muted">{{ T.navAlgorithmsBookDesc }}</small>
            </span>
          </a>
          <a
            class="dropdown-item d-flex align-items-center help-dropdown-item"
            :href="CompetitiveProgrammingBookURL"
            target="_blank"
            rel="noopener noreferrer"
          >
            <font-awesome-icon
              :icon="['fas', 'database']"
              class="help-item-icon flex-shrink-0"
              fixed-width
            />
            <span>
              <span class="d-block">{{
                T.navCompetitiveProgrammingDataStructuresBook
              }}</span>
              <small class="text-muted">{{
                T.navCompetitiveProgrammingDataStructuresBookDesc
              }}</small>
            </span>
          </a>
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
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faBook,
  faCode,
  faComments,
  faDatabase,
  faNewspaper,
  faPen,
  faRobot,
  faVideo,
} from '@fortawesome/free-solid-svg-icons';

library.add(
  faBook,
  faCode,
  faComments,
  faDatabase,
  faNewspaper,
  faPen,
  faRobot,
  faVideo,
);

@Component({
  components: {
    FontAwesomeIcon,
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

  // Used by Cypress selector + click toggle
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

  onCreateProblemClick(): void {
    this.isCreateProblemSubmenuOpen = !this.isCreateProblemSubmenuOpen;
  }
}
</script>

<style lang="scss" scoped>
@media only screen and (max-width: 992px) {
  .help-dropdown {
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

.help-dropdown-item {
  white-space: normal;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.help-item-icon {
  box-sizing: content-box;
  width: 1.5rem;
  height: 1.5rem;
  padding: 0.55rem;
  margin-right: 0.85rem;
  background-color: #f1f3f5;
  border-radius: 4px;
  color: #6c757d;
  font-size: 1.25rem;
}
</style>
