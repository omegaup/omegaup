<template>
  <header>
    <nav class="navbar navbar-expand-lg navbar-color fixed-top p-0 text-right">
      <div class="container-xl pl-0 pl-xl-3">
        <a class="navbar-brand p-3 mr-0 mr-sm-3" href="/">
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

        <div class="d-inline-flex d-flex-row order-lg-1">
          <div
            v-if="isLoggedIn"
            class="navbar-nav navbar-right align-items-end d-lg-none"
          >
            <omegaup-notifications-clarifications
              v-if="inContest"
              :clarifications="clarifications"
            ></omegaup-notifications-clarifications>
            <omegaup-notification-list
              v-else
              :notifications="notifications"
              @read="readNotifications"
            ></omegaup-notification-list>
          </div>
          <ul v-if="!isLoggedIn" class="navbar-nav navbar-right d-lg-flex">
            <li class="nav-item">
              <a
                class="nav-link nav-login-text"
                :href="formattedLoginURL"
                data-login-button
                >{{ T.navLogIn }}</a
              >
            </li>
          </ul>
          <button
            class="navbar-toggler mr-2"
            type="button"
            data-toggle="collapse"
            data-target=".omegaup-navbar"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
        </div>

        <div class="collapse navbar-collapse omegaup-navbar">
          <omegaup-navbar-items
            :omega-up-lock-down="omegaUpLockDown"
            :in-contest="inContest"
            :is-logged-in="isLoggedIn"
            :is-reviewer="isReviewer"
            :is-admin="isAdmin"
            :is-main-user-identity="isMainUserIdentity"
            :is-under13-user="isUnder13User"
            :navbar-section="navbarSection"
          >
            <template v-if="hasTeachingObjective" #contests-items>
              <a
                v-if="isMainUserIdentity"
                class="dropdown-item"
                href="/contest/new/"
                data-nav-contests-create
              >
                {{ T.contestsCreate }}
              </a>
              <a class="dropdown-item" href="/arena/" data-nav-contests-arena>
                {{ T.navViewContests }}
              </a>
              <a
                v-if="isMainUserIdentity"
                class="dropdown-item"
                href="/scoreboardmerge/"
              >
                {{ T.contestsJoinScoreboards }}
              </a>
            </template>
            <template v-if="hasTeachingObjective" #courses-items>
              <template v-if="isMainUserIdentity">
                <a
                  class="dropdown-item"
                  href="/course/new/"
                  data-nav-courses-create
                >
                  {{ T.courseCreate }}
                </a>
              </template>
              <a class="dropdown-item" href="/course/" data-nav-courses-all>
                {{ T.navViewCourses }}
              </a>
            </template>
            <template v-if="hasTeachingObjective" #problems-items>
              <a
                v-if="isLoggedIn && isMainUserIdentity"
                class="dropdown-item"
                href="/problem/new/"
                data-nav-problems-create
                >{{ T.myproblemsListCreateProblem }}</a
              >
              <a
                class="dropdown-item"
                href="/problem/collection/"
                data-nav-problems-collection
                >{{ T.navViewProblems }}</a
              >
              <a class="dropdown-item" href="/submissions/">{{
                T.navViewLatestSubmissions
              }}</a>
              <a v-if="isReviewer" class="dropdown-item" href="/nomination/">{{
                T.navQualityNominationQueue
              }}</a>
            </template>
          </omegaup-navbar-items>
          <!-- in lockdown or contest mode there is no left navbar -->

          <div class="d-flex px-3 justify-content-between">
            <ul
              v-if="isLoggedIn"
              class="navbar-nav navbar-right align-items-right"
            >
              <li class="d-none d-lg-flex">
                <omegaup-notifications-clarifications
                  v-if="inContest"
                  :clarifications="clarifications"
                ></omegaup-notifications-clarifications>
                <omegaup-notification-list
                  v-else
                  :notifications="notifications"
                  @read="readNotifications"
                ></omegaup-notification-list>
              </li>
              <li
                class="nav-item dropdown nav-user nav-item-align"
                data-nav-right
              >
                <a
                  class="nav-link px-2 dropdown-toggle nav-user-link"
                  href="#"
                  role="button"
                  data-nav-user
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  <img
                    :src="gravatarURL51"
                    height="45"
                    class="pr-1 pt-1"
                  /><span class="username mr-2" :title="currentUsername">{{
                    currentUsername
                  }}</span>
                  <omegaup-common-grader-badge
                    v-show="isAdmin"
                    :queue-length="graderQueueLength"
                    :error="errorMessage !== null"
                    class="mr-1"
                  ></omegaup-common-grader-badge>
                </a>
                <div
                  class="dropdown-menu dropdown-menu-right allow-overflow h-auto overflow-auto"
                  data-dropdown-menu
                >
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
                    <template v-if="hasTeachingObjective">
                      <a class="dropdown-item" href="/problem/mine">{{
                        T.navMyProblems
                      }}</a>
                      <a
                        class="dropdown-item"
                        href="/course/mine"
                        data-nav-courses-mine
                        >{{ T.navMyCourses }}</a
                      >
                      <a
                        class="dropdown-item"
                        href="/contest/mine"
                        data-nav-user-contests
                        >{{ T.navMyContests }}</a
                      >
                      <a
                        class="dropdown-item"
                        href="/profile/#created-content"
                        >{{ T.navMyContent }}</a
                      >
                    </template>
                    <template v-else>
                      <a class="dropdown-item" href="/profile/#problems">{{
                        T.navProfileProblems
                      }}</a>
                      <a
                        class="dropdown-item"
                        href="/course/#enrolled"
                        data-nav-courses-mine
                        >{{ T.navCoursesEnrolled }}</a
                      >
                      <a
                        class="dropdown-item"
                        href="/arena/?page=1&tab_name=current&sort_order=none&filter=signedup"
                        data-nav-user-contests-enrolled
                        >{{ T.navContestsEnrolled }}</a
                      >
                      <a
                        v-if="!isUnder13User"
                        class="dropdown-item"
                        href="/dependents"
                        >{{ T.navDependents }}</a
                      >
                      <form v-if="!isUnder13User" class="collapse-submenu">
                        <div class="btn-group">
                          <a
                            class="dropdown-item"
                            href="/profile/#created-content"
                            >{{ T.navMyContent }}</a
                          >
                          <button
                            type="button"
                            class="btn dropdown-item dropdown-toggle dropdown-toggle-split"
                            data-toggle="collapse"
                            data-target=".collapse-links"
                            aria-expanded="false"
                            aria-controls="collapse-links"
                          ></button>
                        </div>
                        <div class="collapse collapse-links pl-3">
                          <a class="dropdown-item" href="/problem/mine">{{
                            T.navMyProblems
                          }}</a>
                          <a
                            class="dropdown-item"
                            href="/course/mine"
                            data-nav-courses-mine
                            >{{ T.navMyCourses }}</a
                          >
                          <a
                            class="dropdown-item"
                            href="/contest/mine"
                            data-nav-user-contests
                            >{{ T.navMyContests }}</a
                          >
                        </div>
                      </form>
                    </template>
                    <a
                      class="dropdown-item"
                      href="/group/"
                      data-nav-user-groups
                      >{{ T.navMyGroups }}</a
                    >
                    <a
                      class="dropdown-item"
                      href="/teamsgroup/"
                      data-nav-user-teams-groups
                      >{{ T.navMyTeamsGroups }}</a
                    >
                    <a class="dropdown-item" href="/nomination/mine/">{{
                      T.navMyQualityNomination
                    }}</a>
                  </template>
                  <div class="dropdown-divider"></div>
                  <!-- Logout button for desktop - navbar menu -->
                  <a
                    class="dropdown-item"
                    href="#"
                    data-logout-button
                    @click.prevent="logoutModalVisible = true"
                  >
                    <font-awesome-icon :icon="['fas', 'power-off']" />
                    {{ T.omegaupTitleLogout }}
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

            <!-- Logout button for mobile -->
            <a
              v-if="isLoggedIn"
              class="navbar justify-content-end d-lg-none align-items-start pt-4 d-flex align-items-center"
              href="#"
              @click.prevent="logoutModalVisible = true"
            >
              <font-awesome-icon :icon="['fas', 'power-off']" />
              <span class="ml-2">
                {{ T.omegaupTitleLogout }}
              </span>
            </a>
          </div>
        </div>

        <!-- Logout button for desktop - navbar -->
        <a
          v-if="isLoggedIn"
          class="navbar justify-content-end d-none d-lg-block order-1"
          href="#"
          @click.prevent="logoutModalVisible = true"
        >
          <font-awesome-icon :icon="['fas', 'power-off']" />
          {{ T.omegaupTitleLogout }}
        </a>
      </div>
    </nav>
    <div v-if="userVerificationDeadline" class="py-2 mt-2" :class="bannerColor">
      <div class="container-xl">
        {{
          daysUntilVerificationDeadline > 1
            ? ui.formatString(T.bannerVerifyAccount, {
                days: daysUntilVerificationDeadline,
              })
            : T.bannerLastDayToVerifyAccount
        }}
      </div>
    </div>
    <template v-if="fromLogin">
      <omegaup-user-objectives-questions
        v-if="isLoggedIn && isMainUserIdentity && userTypes.length === 0"
        @submit="(objectives) => $emit('update-user-objectives', objectives)"
      ></omegaup-user-objectives-questions>
      <omegaup-user-next-registered-contest
        v-if="isLoggedIn && nextRegisteredContest !== null"
        :next-registered-contest="nextRegisteredContest"
        @redirect="(alias) => $emit('redirect-next-registered-contest', alias)"
      ></omegaup-user-next-registered-contest>
      <div
        v-if="mentorCanChooseCoder"
        class="alert alert-info alert-dismissible fade show"
        role="alert"
      >
        <button type="button" class="close" data-dismiss="alert">
          &times;
        </button>
        <omegaup-markdown
          :markdown="T.coderOfTheMonthCanBeChosenManually"
        ></omegaup-markdown>
      </div>
    </template>
    <omegaup-logout-confirmation v-model="logoutModalVisible">
    </omegaup-logout-confirmation>
  </header>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import notifications_Clarifications from '../notification/Clarifications.vue';
import notifications_List from '../notification/List.vue';
import omegaup_Markdown from '../Markdown.vue';
import common_GraderStatus from '../common/GraderStatus.vue';
import common_GraderBadge from '../common/GraderBadge.vue';
import user_objectives_questions from '../user/ObjectivesQuestions.vue';
import user_next_registered_contest from '../user/NextRegisteredContest.vue';
import navbar_items from './NavbarItems.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faSignOutAlt, faUser } from '@fortawesome/free-solid-svg-icons';
import LogoutConfirmation from './LogoutConfirmation.vue';
library.add(faSignOutAlt, faUser);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-notification-list': notifications_List,
    'omegaup-notifications-clarifications': notifications_Clarifications,
    'omegaup-common-grader-status': common_GraderStatus,
    'omegaup-common-grader-badge': common_GraderBadge,
    'omegaup-user-objectives-questions': user_objectives_questions,
    'omegaup-user-next-registered-contest': user_next_registered_contest,
    'omegaup-navbar-items': navbar_items,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-logout-confirmation': LogoutConfirmation,
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
  @Prop() clarifications!: types.Clarification[];
  @Prop() fromLogin!: boolean;
  @Prop() userTypes!: string[];
  @Prop() nextRegisteredContest!: types.ContestListItem | null;
  @Prop() isUnder13User!: boolean;
  @Prop() mentorCanChooseCoder!: boolean;
  @Prop() userVerificationDeadline!: Date | null;

  T = T;
  ui = ui;
  logoutModalVisible = false;
  teachingUserTypes = ['teacher', 'coach', 'independent-teacher'];
  hasTeachingObjective = this.teachingUserTypes.some((teachingType) =>
    this.userTypes.includes(teachingType),
  );

  get formattedLoginURL(): string {
    return `/login/?redirect=${encodeURIComponent(window.location.pathname)}`;
  }

  get identitiesNotLoggedIn(): types.AssociatedIdentity[] {
    return this.associatedIdentities.filter(
      (identity) => identity.username !== this.currentUsername,
    );
  }

  readNotifications(notifications: types.Notification[], url?: string): void {
    this.$emit('read-notifications', notifications, url);
  }

  get daysUntilVerificationDeadline(): number | null {
    if (!this.userVerificationDeadline) {
      return null;
    }
    const today = new Date();
    const deadline = new Date(this.userVerificationDeadline);
    const timeDifference = deadline.getTime() - today.getTime();
    const daysDifference = Math.ceil(timeDifference / (1000 * 3600 * 24));
    return daysDifference;
  }

  get bannerColor() {
    if (
      this.daysUntilVerificationDeadline !== null &&
      this.daysUntilVerificationDeadline <= 1
    ) {
      return 'bg-danger';
    }
    return 'bg-warning';
  }
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

.alert-info {
  margin: 1rem;
}

.navbar-color .navbar-toggler {
  color: var(--header-navbar-primary-link-color);
  border-color: var(--header-navbar-primary-link-color);
}

.navbar-color .navbar-toggler-icon {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='30' height='30' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.5%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

.nav-item-align {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

nav.navbar {
  background-color: var(--header-primary-color);

  .navbar-brand {
    background-color: var(--header-navbar-brand-background-color);
  }

  a.dropdown-item {
    color: var(--header-navbar-dropdown-item-font-color);
  }

  a {
    color: var(--header-navbar-primary-link-color);
  }

  .collapse-submenu .btn:focus {
    box-shadow: 0 0 0 0;
  }

  .dropdown-menu {
    overflow-y: auto;
    max-height: 75vh;
    scrollbar-width: none;
  }
}

.allow-overflow {
  overflow-y: scroll;
  height: 65vh;
  max-width: 40vw;
}

.nav-login-text {
  font-size: 14px;
  padding: auto;
}

.navbar-nav {
  .nav-item {
    width: 100% !important;
  }
}

.fullwidth-mobile-fit-lg {
  width: 100%;
}

.username {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 20vw;
  display: inline-block;
  vertical-align: middle;
}

@media (min-width: 992px) {
  .fullwidth-mobile-fit-lg {
    width: fit-content;
  }
}

@media only screen and (min-width: 385px) {
  .nav-login-text {
    font-size: inherit;
    padding: 0.5rem;
  }
}

@media only screen and (max-width: 992px) {
  .allow-overflow {
    height: 45vh;
    max-width: 80vw;
  }
}
</style>
