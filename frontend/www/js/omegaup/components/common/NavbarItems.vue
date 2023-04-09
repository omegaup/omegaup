<template>
  <div class="mr-auto">
    <ul
      v-if="!omegaUpLockDown && (!inContest || isAdmin)"
      class="navbar-nav align-items-end"
    >
      <li
        v-if="isLoggedIn"
        class="nav-item dropdown nav-contests"
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
        <div class="dropdown-menu">
          <slot name="contests-items">
            <a class="dropdown-item" href="/arena/" data-nav-contests-arena>
              {{ T.navViewContests }}
            </a>
            <template v-if="isMainUserIdentity">
              <a class="dropdown-item" href="/scoreboardmerge/">
                {{ T.contestsJoinScoreboards }}
              </a>
              <a
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
        class="nav-item dropdown nav-courses"
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
        <div class="dropdown-menu">
          <slot name="courses-items">
            <a class="dropdown-item" href="/course/" data-nav-courses-all>
              {{ T.navViewCourses }}
            </a>
            <template v-if="isMainUserIdentity">
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
        class="nav-item dropdown nav-problems"
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
        <div class="dropdown-menu">
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
            <a
              v-if="isLoggedIn && isMainUserIdentity"
              class="dropdown-item"
              href="/problem/new/"
              data-nav-problems-create
              >{{ T.myproblemsListCreateProblem }}</a
            >
            <a v-if="isReviewer" class="dropdown-item" href="/nomination/">{{
              T.navQualityNominationQueue
            }}</a>
          </slot>
        </div>
      </li>
      <li
        class="nav-item dropdown nav-rank"
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
        <div class="dropdown-menu">
          <a class="dropdown-item" href="/rank/">{{ T.navUserRanking }}</a>
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
      <li class="nav-item dropdown">
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
        <div class="dropdown-menu">
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
            href="https://omegaup.com/docs/assets/libroluisvargas.pdf"
            target="_blank"
            >{{ T.navAlgorithmsBook }}</a
          >
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

  T = T;
}
</script>
