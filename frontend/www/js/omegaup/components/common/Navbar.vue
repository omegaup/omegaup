<template>
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top p-0 text-right">
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
        <div class="d-inline-flex d-flex-row">
          <a
            v-if="isLoggedIn"
            class="navbar justify-content-end mr-2 d-lg-none"
            href="/logout/"
          >
            <font-awesome-icon :icon="['fas', 'power-off']" />
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
        </div>
        <div class="collapse navbar-collapse omegaup-navbar">
          <omegaup-navbar-items
            :omega-up-lock-down="omegaUpLockDown"
            :in-contest="inContest"
            :is-logged-in="isLoggedIn"
            :is-reviewer="isReviewer"
            :is-admin="isAdmin"
            :is-main-user-identity="isMainUserIdentity"
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
          <ul v-if="!isLoggedIn" class="navbar-nav navbar-right">
            <li class="nav-item">
              <a
                class="nav-link px-2"
                :href="formattedLoginURL"
                data-login-button
                >{{ T.navLogIn }}</a
              >
            </li>
          </ul>
          <ul v-else class="navbar-nav navbar-right align-items-end">
            <omegaup-notifications-clarifications
              v-if="inContest"
              :clarifications="clarifications"
            ></omegaup-notifications-clarifications>
            <omegaup-notification-list
              v-else
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
              <div class="dropdown-menu dropdown-menu-right allow-overflow">
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
                    <a class="dropdown-item" href="/profile/#created-content">{{
                      T.navMyContent
                    }}</a>
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
                      href="/arena/#participating"
                      data-nav-user-contests
                      >{{ T.navContestsEnrolled }}</a
                    >
                    <form class="collapse-submenu">
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

        <a
          v-if="isLoggedIn"
          class="navbar justify-content-end d-none d-lg-block"
          href="/logout/"
        >
          <font-awesome-icon :icon="['fas', 'power-off']" />
        </a>
      </div>
    </nav>
    <omegaup-user-objectives-questions
      v-if="
        fromLogin && isLoggedIn && isMainUserIdentity && userTypes.length === 0
      "
      @submit="(objectives) => $emit('update-user-objectives', objectives)"
    ></omegaup-user-objectives-questions>
  </header>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import notifications_Clarifications from '../notification/Clarifications.vue';
import notifications_List from '../notification/List.vue';
import common_GraderStatus from '../common/GraderStatus.vue';
import common_GraderBadge from '../common/GraderBadge.vue';
import user_objectives_questions from '../user/ObjectivesQuestions.vue';
import navbar_items from './NavbarItems.vue';

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
    'omegaup-user-objectives-questions': user_objectives_questions,
    'omegaup-navbar-items': navbar_items,
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

  T = T;
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

  .collapse-submenu .btn:focus {
    box-shadow: 0 0 0 0;
  }
}

.allow-overflow {
  overflow-y: scroll;
  height: 65vh;
  max-width: 40vw;
}
@media only screen and (max-width: 992px) {
  .allow-overflow {
    height: 45vh;
    max-width: 80vw;
  }
}
</style>
