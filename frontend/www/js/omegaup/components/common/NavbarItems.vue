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
            <hr
              style="margin-top: 0em; margin-bottom: 0em; border-width: 2px"
            />
            <a class="dropdown-item" href="/submissions/">{{
              T.navViewLatestSubmissions
            }}</a>
            <template v-if="!isLoggedIn">
              <a class="dropdown-item" href="/problem/creator/">{{
                T.createZipFileForProblem
              }}</a>
            </template>
            <template v-else>
              <form class="collapse-submenu">
                <button
                  type="button"
                  class="dropdown-item dropdown-toggle"
                  data-toggle="collapse"
                  data-target=".collapse-links"
                  data-nav-problems-create-options
                  aria-expanded="false"
                  aria-controls="collapse-links"
                >
                  {{ T.myproblemsListCreateProblem }}
                </button>
                <div class="collapse collapse-links pl-3">
                  <a class="dropdown-item" href="/problem/creator/">{{
                    T.myproblemsListCreateZipFileProblem
                  }}</a>
                  <a
                    class="dropdown-item"
                    href="/problem/new/"
                    data-nav-problems-create
                    >{{ T.myproblemsListCreateProblemWithExistingZipFile }}</a
                  >
                </div>
              </form>
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
            class="dropdown-item"
            href="https://www.youtube.com/playlist?list=PLdSCJwXErQ8FhVwmlySvab3XtEVdE8QH4"
            target="_blank"
            >{{ T.navTutorials }}</a
          >
          <a
            class="dropdown-item"
            href="https://discord.com/invite/K3JFd9d3wk"
            target="_blank"
            >{{ T.navDiscord }}</a
          >
          <a
            class="dropdown-item"
            href="http://blog.omegaup.com/"
            target="_blank"
            >{{ T.navBlog }}</a
          >
          <a
            class="dropdown-item text-wrap"
            href="https://drive.google.com/file/d/1PLOO3wLCnOVC_cODwiofahsRGeyoJeCU/view"
            target="_blank"
            >{{ T.navAlgorithmsBook }}</a
          >
          <a
            class="dropdown-item text-wrap"
            href="https://hdl.handle.net/11059/16567"
            target="_blank"
            rel="noopener noreferrer"
          >
            {{ T.navCompetitiveProgrammingDataStructuresBook }}
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

@Component
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
</style>
