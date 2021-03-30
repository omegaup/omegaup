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
            v-show="omegaUpLockDown"
            alt="lockdown"
            title="lockdown"
            :src="lockDownImage"
            :class="{ 'd-inline-block': omegaUpLockDown }"
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
          <ul
            v-if="!omegaUpLockDown && (!inContest || isAdmin)"
            class="navbar-nav mr-auto"
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
                <a class="dropdown-item" href="/course/" data-nav-courses-all>
                  {{ T.navAllCourses }}
                </a>
                <template v-if="isMainUserIdentity">
                  <a
                    class="dropdown-item"
                    href="/course/new/"
                    data-nav-courses-create
                  >
                    {{ T.buttonCreateCourse }}
                  </a>
                </template>
              </div>
            </li>
            <li
              v-else
              :class="{ active: navbarSection === 'course' }"
              data-nav-course
            >
              <a class="nav-link px-2" href="/course/home/">{{
                T.navCourses
              }}</a>
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
                <a
                  class="dropdown-item"
                  href="/problem/collection/"
                  data-nav-problems-collection
                  >{{ T.problemcollectionViewProblems }}</a
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
                <a class="dropdown-item" href="/rank/">{{
                  T.navUserRanking
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
              </div>
            </li>
          </ul>
          <ul v-else class="navbar-nav mr-auto"></ul>
          <!-- in lockdown or contest mode there is no left navbar -->
          <ul v-if="!isLoggedIn" class="navbar-nav navbar-right">
            <li class="nav-item">
              <a class="nav-link px-2" :href="formattedLoginURL">{{
                T.navLogIn
              }}</a>
            </li>
          </ul>
          <ul v-else class="navbar-nav navbar-right">
            <!--
              TODO: Hay que darle soporte a estos dos componentes
            <omegaup-notifications-clarifications
              v-bind:initialClarifications="initialClarifications"
              v-if="inContest"
            ></omegaup-notifications-clarifications>
            -->
            <li class="nav-item dropdown nav-themes">
              <a
                class="nav-link px-2 dropdown-toggle"
                href="#"
                role="button"
                data-nav-themes
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              >
                {{ T.navbarChooseTheme }}
              </a>
              <div class="dropdown-menu">
                <a
                  v-for="theme in availableThemes"
                  :key="theme"
                  class="dropdown-item"
                  href="#"
                  @click="currentTheme = theme"
                >
                  {{ theme }}
                </a>
              </div>
            </li>
            <omegaup-notification-list
              :notifications="notifications"
              @read="readNotifications"
            ></omegaup-notification-list>
            <li class="nav-item dropdown nav-user" data-nav-right>
              <a
                class="nav-link px-2 dropdown-toggle nav-user-link"
                href="#"
                role="button"
                data-nav-user
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              >
                <img :src="gravatarURL51" height="45" class="mr-2" /><span
                  class="username"
                  :title="currentUsername"
                  >{{ currentUsername }}</span
                >
                <omegaup-common-grader-badge
                  v-show="isAdmin"
                  :queue-length="graderQueueLength"
                  :error="errorMessage !== null"
                ></omegaup-common-grader-badge>
              </a>
              <div class="dropdown-menu dropdown-menu-right">
                <template v-if="!omegaUpLockDown && (!inContest || isAdmin)">
                  <div class="text-center mb-1">
                    <img
                      :src="gravatarURL128"
                      height="70"
                      class="rounded-circle mb-1"
                      :title="currentUsername"
                    />
                    <h5 v-if="currentName !== ''" class="mx-2">
                      {{ currentName }}
                    </h5>
                    <h5 v-else class="mx-2">{{ currentUsername }}</h5>
                    <h6 class="mx-2">{{ currentEmail }}</h6>
                  </div>
                  <a
                    v-show="!omegaUpLockDown && (!inContest || isAdmin)"
                    class="dropdown-item text-center"
                    data-nav-profile
                    href="/profile/"
                  >
                    <font-awesome-icon :icon="['fas', 'user']" />
                    {{ T.navViewProfile }}
                    <div v-if="profileProgress !== 0" class="progress mt-2">
                      <div
                        class="progress-bar progress-bar-striped bg-info"
                        role="progressbar"
                        :style="{ width: `${profileProgress}%` }"
                        :aria-valuenow="profileProgress"
                        aria-valuemin="0"
                        aria-valuemax="100"
                      ></div>
                    </div>
                  </a>
                  <div class="dropdown-divider"></div>
                  <div v-if="identitiesNotLoggedIn.length > 0" class="mb-1">
                    <div
                      v-for="identity in identitiesNotLoggedIn"
                      :key="identity.username"
                    >
                      <button
                        class="btn btn-link dropdown-item"
                        @click="$emit('change-account', identity.username)"
                      >
                        <img
                          :src="gravatarURL51"
                          height="45"
                          class="rounded-circle mr-3"
                          :title="identity.username"
                        />{{ identity.username }}
                      </button>
                    </div>
                    <div class="dropdown-divider"></div>
                  </div>
                  <a class="dropdown-item" href="/badge/list/">{{
                    T.navViewBadges
                  }}</a>
                  <a class="dropdown-item" href="/problem/mine/">{{
                    T.navMyProblems
                  }}</a>
                  <a
                    class="dropdown-item"
                    href="/course/mine/"
                    data-nav-courses-mine
                    >{{ T.navMyCourses }}</a
                  >
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
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/logout/">
                  <font-awesome-icon :icon="['fas', 'sign-out-alt']" />
                  {{ T.navLogOut }}
                </a>
                <omegaup-common-grader-status
                  v-show="isAdmin"
                  :status="errorMessage !== null ? 'down' : 'ok'"
                  :error="errorMessage"
                  :grader-info="graderInfo"
                ></omegaup-common-grader-status>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import notifications_List from '../notification/List.vue';
import notifications_Clarifications from '../notification/Clarifications.vue';
import common_GraderStatus from '../common/GraderStatus.vue';
import common_GraderBadge from '../common/GraderBadge.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faSignOutAlt, faUser } from '@fortawesome/free-solid-svg-icons';
library.add(faSignOutAlt, faUser);

export enum Theme {
  LIGHT = 'light',
  COSMO = 'cosmo',
  CYBORG = 'cyborg',
  DARKLY = 'darkly',
  SALTE = 'slate',
  SUPERHERO = 'superhero',
}

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
  @Prop() gravatarURL128!: string;
  @Prop() associatedIdentities!: types.AssociatedIdentity[];
  @Prop() currentEmail!: string;
  @Prop() currentName!: string;
  @Prop() currentUsername!: string;
  @Prop() isAdmin!: boolean;
  @Prop() isMainUserIdentity!: boolean;
  @Prop() lockDownImage!: string;
  @Prop() navbarSection!: string;
  @Prop() notifications!: types.Notification[];
  @Prop() graderInfo!: types.GraderStatus | null;
  @Prop() graderQueueLength!: number;
  @Prop() errorMessage!: string | null;
  @Prop({ default: 0 }) profileProgress!: number;
  @Prop() initialClarifications!: types.Clarification[];
  @Prop({ default: Theme.LIGHT }) theme!: Theme;

  clarifications: types.Clarification[] = this.initialClarifications;
  T = T;
  currentTheme = this.theme;

  get formattedLoginURL(): string {
    return `/login/?redirect=${encodeURIComponent(window.location.pathname)}`;
  }

  get identitiesNotLoggedIn(): types.AssociatedIdentity[] {
    return this.associatedIdentities.filter(
      (identity) => identity.username !== this.currentUsername,
    );
  }

  get availableThemes(): string[] {
    return Object.values(Theme);
  }

  readNotifications(notifications: types.Notification[], url?: string): void {
    this.$emit('read-notifications', notifications, url);
  }

  @Watch('currentTheme')
  onCurrentThemeChanged(newValue: Theme) {
    localStorage.setItem(
      'theme-preferences',
      JSON.stringify({ theme: newValue }),
    );
    ui.updateTheme();
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

nav.navbar {
  background-color: var(--header-primary-color);

  .navbar-brand {
    background-color: var(--header-navbar-brand-background-color);
  }

  a.dropdown-item {
    color: var(--header-navbar-dropdown-item-font-color);
  }
}
</style>
