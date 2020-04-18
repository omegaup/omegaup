<template>
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top p-0">
      <div class="container-xl pl-0 pl-xl-3">
        <a class="navbar-brand p-3" href="/">
          <img
            alt="omegaUp"
            src="/media/omegaup_curves.png"
            height="20"
            class="d-inline-block"
          />
          <img
            alt="lockdown"
            title="lockdown"
            v-bind:src="lockDownImage"
            v-show="omegaUpLockDown"
            v-bind:class="{ 'd-inline-block': omegaUpLockDown }"
            height="20"
          />
        </a>
        <button
          class="navbar-toggler"
          type="button"
          data-toggle="collapse"
          data-target=".omegaup-navbar"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse omegaup-navbar">
          <ul class="navbar-nav mr-auto" v-if="!omegaUpLockDown && !inContest">
            <li
              class="nav-item dropdown nav-contests"
              v-bind:class="{ active: navbarSection === 'contests' }"
              v-if="isLoggedIn"
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
                <a class="dropdown-item" href="/arena/" data-nav-contests-arena>
                  {{ T.navAllContests }}
                </a>
                <template v-if="isMainUserIdentity">
                  <a
                    class="dropdown-item"
                    href="/contest/new/"
                    data-nav-contests-create
                  >
                    {{ T.contestsCreateNew }}
                  </a>
                  <a class="dropdown-item" href="/scoreboardmerge/">
                    {{ T.contestsJoinScoreboards }}
                  </a>
                </template>
              </div>
            </li>
            <li
              v-bind:class="{ active: navbarSection === 'contests' }"
              v-else=""
            >
              <a class="nav-link px-2" href="/arena/" data-nav-contests-arena>{{
                T.wordsContests
              }}</a>
            </li>
            <li
              class="nav-item nav-courses"
              v-bind:class="{ active: navbarSection === 'courses' }"
            >
              <a class="nav-link px-2" href="/schools/">{{ T.navCourses }}</a>
            </li>
            <li
              class="nav-item dropdown nav-problems"
              v-bind:class="{ active: navbarSection === 'problems' }"
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
                <a
                  class="dropdown-item"
                  href="/problem/"
                  data-nav-problems-all
                  >{{ T.navAllProblems }}</a
                >
                <a
                  v-if="isLoggedIn && isMainUserIdentity"
                  class="dropdown-item"
                  href="/problem/new/"
                  data-nav-problems-create
                  >{{ T.myproblemsListCreateProblem }}</a
                >
                <a class="dropdown-item" href="/submissions/">{{
                  T.wordsLatestSubmissions
                }}</a>
                <a
                  v-if="isReviewer"
                  class="dropdown-item"
                  href="/nomination/"
                  >{{ T.navQualityNominationQueue }}</a
                >
              </div>
            </li>
            <li
              class="nav-item dropdown nav-rank"
              v-bind:class="{ active: navbarSection === 'rank' }"
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
                <a class="dropdown-item" href="/rank/">{{
                  T.navUserRanking
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
                  >{{ T.navTutorials }}</a
                >
                <a class="dropdown-item" href="http://blog.omegaup.com/">{{
                  T.navBlog
                }}</a>
                <a
                  class="dropdown-item"
                  href="https://omegaup.com/preguntas/"
                  >{{ T.navQuestions }}</a
                >
              </div>
            </li>
          </ul>
          <ul class="navbar-nav mr-auto" v-else=""></ul>
          <!-- in lockdown or contest mode there is no left navbar -->
          <ul class="navbar-nav navbar-right" v-if="!isLoggedIn">
            <li class="nav-item">
              <a class="nav-link px-2" v-bind:href="formattedLoginURL">{{
                T.navLogIn
              }}</a>
            </li>
          </ul>
          <ul class="navbar-nav navbar-right" v-else="">
            <!--
              TODO: Hay que darle soporte a estos dos componentes
            <omegaup-notifications-clarifications
              v-bind:initialClarifications="initialClarifications"
              v-if="inContest"
            ></omegaup-notifications-clarifications>
            <omegaup-notification-list
              v-bind:notifications="notifications"
            ></omegaup-notification-list>
            -->
            <li class="nav-item dropdown nav-user">
              <a
                class="nav-link px-2 dropdown-toggle nav-user-link"
                href="#"
                role="button"
                data-nav-user
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              >
                <img v-bind:src="gravatarURL51" height="45" class="mr-2" /><span
                  class="username"
                  v-bind:title="currentUsername"
                  >{{ currentUsername }}</span
                >
                <omegaup-common-grader-badge
                  v-show="isAdmin"
                  v-bind:queueLength="graderQueueLength"
                  v-bind:error="errorMessage !== null"
                ></omegaup-common-grader-badge>
              </a>
              <div class="dropdown-menu dropdown-menu-right">
                <template v-show="!omegaUpLockDown && !inContest">
                  <a
                    class="dropdown-item"
                    href="/profile/"
                    v-show="!omegaUpLockDown && !inContest"
                  >
                    <font-awesome-icon v-bind:icon="['fas', 'user']" />
                    {{ T.navViewProfile }}
                  </a>
                  <a class="dropdown-item" href="/problem/mine/">{{
                    T.navMyProblems
                  }}</a>
                  <a
                    class="dropdown-item"
                    href="/contest/mine/"
                    data-nav-user-contests
                    >{{ T.navMyContests }}</a
                  >
                  <a
                    class="dropdown-item"
                    href="/group/"
                    data-nav-user-groups
                    >{{ T.navMyGroups }}</a
                  >
                  <a class="dropdown-item" href="/nomination/mine/">{{
                    T.navMyQualityNomination
                  }}</a>
                </template>
                <a class="dropdown-item" href="/logout/">
                  <font-awesome-icon v-bind:icon="['fas', 'sign-out-alt']" />
                  {{ T.navLogOut }}
                </a>
                <!-- TODO: Hacer que los estilos se ajusten a bootstrap4 -->
                <omegaup-common-grader-status
                  v-bind:status="errorMessage !== null ? 'down' : 'ok'"
                  v-bind:error="errorMessage"
                  v-bind:graderInfo="graderInfo"
                ></omegaup-common-grader-status>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>
</template>

<style lang="scss">
@import '../../../../sass/main.scss';
nav.navbar {
  background-color: $header-primary-color;

  .navbar-brand {
    background-color: $white;
    background-image: linear-gradient(to bottom, $white 0, #ddd 100%);
  }

  a.dropdown-item {
    color: black;
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import notifications_List from '../notification/List.vue';
import notifications_Clarifications from '../notification/Clarifications.vue';
import common_GraderStatus from '../common/GraderStatus.vue';
import common_GraderBadge from '../common/GraderBadge.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faSignOutAlt, faUser } from '@fortawesome/free-solid-svg-icons';
library.add(faSignOutAlt, faUser);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-notification-list': notifications_List,
    'omegaup-notifications-clarifications': notifications_Clarifications,
    'omegaup-common-grader-status': common_GraderStatus,
    'omegaup-common-grader-badge': common_GraderBadge,
  },
})
export default class Navbar extends Vue {
  @Prop() omegaUpLockDown!: boolean;
  @Prop() inContest!: boolean;
  @Prop() isLoggedIn!: boolean;
  @Prop() isReviewer!: boolean;
  @Prop() gravatarURL51!: string;
  @Prop() currentUsername!: string;
  @Prop() isAdmin!: boolean;
  @Prop() isMainUserIdentity!: boolean;
  @Prop() lockDownImage!: string;
  @Prop() navbarSection!: string;
  @Prop() graderInfo!: types.GraderStatus | null;
  @Prop() graderQueueLength!: number;
  @Prop() errorMessage!: string | null;
  @Prop() initialClarifications!: omegaup.Clarification[];

  notifications: types.Notification[] = [];
  clarifications: omegaup.Clarification[] = this.initialClarifications;
  T = T;

  get formattedLoginURL(): string {
    return `/login/?redirect=${encodeURIComponent(window.location.pathname)}`;
  }

  get showNavbar(): boolean {
    return this.isAdmin && !this.omegaUpLockDown && !this.inContest;
  }
}
</script>
