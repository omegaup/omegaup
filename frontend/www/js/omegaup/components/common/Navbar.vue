<template>
  <div class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container navbar-inner">
      <div class="navbar-header">
        <button
          aria-expanded="false"
          class="navbar-toggle collapsed"
          data-target=".navbar-collapse"
          data-toggle="collapse"
          type="button"
        >
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span> <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/"
          ><img alt="omegaUp" src="/media/omegaup_curves.png" />
          <img
            v-show="omegaUpLockDown"
            alt="lockdown"
            title="lockdown"
            :src="lockDownImage"
        /></a>
      </div>
      <div aria-expanded="false" class="navbar-collapse collapse">
        <ul
          v-if="!omegaUpLockDown && (!inContest || isAdmin)"
          class="nav navbar-nav"
        >
          <li
            v-if="isLoggedIn"
            class="dropdown nav-contests"
            :class="{ active: navbarSection === 'contests' }"
          >
            <a
              class="dropdown-toggle"
              data-toggle="dropdown"
              data-nav-contests
              href="#"
              ><span>{{ T.wordsContests }}</span> <span class="caret"></span
            ></a>
            <ul class="dropdown-menu">
              <li>
                <a href="/arena/" data-nav-contests-arena>{{
                  T.navAllContests
                }}</a>
              </li>

              <template v-if="isMainUserIdentity">
                <li>
                  <a href="/contest/new/" data-nav-contests-create>{{
                    T.contestsCreateNew
                  }}</a>
                </li>
                <li>
                  <a href="/scoreboardmerge/">{{
                    T.contestsJoinScoreboards
                  }}</a>
                </li>
              </template>
            </ul>
          </li>
          <li v-else :class="{ active: navbarSection === 'contests' }">
            <a href="/arena/" data-nav-contests-arena>{{ T.wordsContests }}</a>
          </li>
          <li
            v-if="isLoggedIn"
            class="dropdown nav-courses"
            :class="{ active: navbarSection === 'courses' }"
          >
            <a class="dropdown-toogle" data-toggle="dropdown" data-nav-courses
              ><span>{{ T.navCourses }}</span
              ><span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li>
                <a href="/course/" data-nav-courses-all>
                  {{ T.navAllCourses }}
                </a>
              </li>
              <template v-if="isMainUserIdentity">
                <li>
                  <a href="/course/new/" data-nav-courses-create>
                    {{ T.buttonCreateCourse }}
                  </a>
                </li>
              </template>
            </ul>
          </li>
          <li v-else :class="{ active: navbarSection === 'courses' }">
            <a href="/course/">{{ T.navCourses }}</a>
          </li>
          <li
            class="dropdown nav-problems"
            :class="{ active: navbarSection === 'problems' }"
          >
            <a
              class="dropdown-toggle"
              data-toggle="dropdown"
              data-nav-problems
              href="#"
            >
              <span>{{ T.wordsProblems }}</span> <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li>
                <a href="/problem/collection/" data-nav-problems-collection>{{
                  T.problemcollectionViewProblems
                }}</a>
              </li>
              <li v-if="isLoggedIn && isMainUserIdentity">
                <a href="/problem/new/" data-nav-problems-create>{{
                  T.myproblemsListCreateProblem
                }}</a>
              </li>
              <li>
                <a href="/submissions/">{{ T.wordsLatestSubmissions }}</a>
              </li>
              <li v-if="isReviewer">
                <a href="/nomination/">{{ T.navQualityNominationQueue }}</a>
              </li>
            </ul>
          </li>
          <li
            class="dropdown nav-rank"
            :class="{ active: navbarSection === 'rank' }"
          >
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <span>{{ T.navRanking }}</span>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li>
                <a href="/rank/">{{ T.navUserRanking }}</a>
              </li>
              <li>
                <a href="/rank/authors/">{{ T.navAuthorRanking }}</a>
              </li>
              <li>
                <a href="/rank/schools/">{{ T.navSchoolRanking }}</a>
              </li>
              <li>
                <a href="/coderofthemonth/">{{ T.navCoderOfTheMonth }}</a>
              </li>
              <li>
                <a href="/coderofthemonth/female/">{{
                  T.navCoderOfTheMonthFemale
                }}</a>
              </li>
              <li>
                <a href="/schoolofthemonth/">{{ T.navSchoolOfTheMonth }}</a>
              </li>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <span>{{ T.navHelp }}</span>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li>
                <a
                  href="https://www.youtube.com/playlist?list=PLdSCJwXErQ8FhVwmlySvab3XtEVdE8QH4"
                  >{{ T.navTutorials }}</a
                >
              </li>
              <li>
                <a href="http://blog.omegaup.com/">{{ T.navBlog }}</a>
              </li>
            </ul>
          </li>
        </ul>
        <ul v-else class="nav navbar-nav"></ul>
        <!-- in lockdown or contest mode there is no left navbar -->
        <ul v-if="!isLoggedIn" class="nav navbar-nav navbar-right">
          <li>
            <a :href="formattedLoginURL">{{ T.navLogIn }}</a>
          </li>
        </ul>
        <ul v-else class="nav navbar-nav navbar-right">
          <omegaup-notifications-clarifications
            v-if="inContest"
            :clarifications="notificationsClarifications"
            :is-admin="isAdmin"
          ></omegaup-notifications-clarifications>
          <li
            class="dropdown nav-user"
            :class="{ active: navbarSection === 'users' }"
            data-nav-right
          >
            <a
              class="dropdown-toggle user-dropdown"
              data-toggle="dropdown"
              data-nav-user
              href="#"
              ><img :src="gravatarURL51" />
              <span class="username" :title="currentUsername">{{
                currentUsername
              }}</span>
              <omegaup-common-grader-badge
                v-show="isAdmin"
                :queue-length="graderQueueLength"
                :error="errorMessage !== null"
              ></omegaup-common-grader-badge>
              <span class="caret"></span
            ></a>
            <ul class="dropdown-menu">
              <template v-if="!omegaUpLockDown && (!inContest || isAdmin)">
                <div class="text-center">
                  <img
                    :src="gravatarURL128"
                    height="70"
                    class="img-circle"
                    :title="currentUsername"
                  />
                  <h4 v-if="currentName !== ''">
                    <strong>{{ currentName }}</strong>
                  </h4>
                  <h4 v-else>
                    <strong>{{ currentUsername }}</strong>
                  </h4>
                  <h5>
                    <strong>{{ currentEmail }}</strong>
                  </h5>
                </div>
                <li>
                  <a href="/profile/" data-nav-profile class="text-center"
                    ><span class="glyphicon glyphicon-user"></span>
                    {{ T.navViewProfile }}</a
                  >
                </li>
                <li role="separator" class="divider"></li>
                <template v-if="identitiesNotLoggedIn.length > 0">
                  <li
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
                        class="img-circle"
                        :title="identity.username"
                      />{{ identity.username }}
                    </button>
                  </li>
                  <li role="separator" class="divider"></li>
                </template>
                <li>
                  <a href="/badge/list/">{{ T.navViewBadges }}</a>
                </li>
                <li>
                  <a href="/course/mine/" data-nav-courses-mine
                    >{{ T.navMyCourses }}
                  </a>
                </li>
                <li>
                  <a href="/problem/mine/">{{ T.navMyProblems }}</a>
                </li>
                <li>
                  <a href="/contest/mine/" data-nav-user-contests>{{
                    T.navMyContests
                  }}</a>
                </li>
                <li>
                  <a href="/group/" data-nav-user-groups>{{ T.navMyGroups }}</a>
                </li>
                <li>
                  <a href="/teamsgroup/" data-nav-user-teams-groups>{{
                    T.navMyTeamsGroups
                  }}</a>
                </li>
                <li>
                  <a href="/nomination/mine/">{{ T.navMyQualityNomination }}</a>
                </li>
              </template>
              <li role="separator" class="divider"></li>
              <li>
                <a href="/logout/"
                  ><span class="glyphicon glyphicon-log-out"></span>
                  {{ T.navLogOut }}</a
                >
              </li>
              <omegaup-common-grader-status
                v-show="isAdmin"
                :status="errorMessage !== null ? 'down' : 'ok'"
                :error="errorMessage"
                :grader-info="graderInfo"
              ></omegaup-common-grader-status>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import notifications_Clarifications from '../notification/Clarifications.vue';
import common_GraderStatus from '../common/GraderStatus.vue';
import common_GraderBadge from '../common/GraderBadge.vue';

@Component({
  components: {
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
  @Prop({ default: () => [] })
  associatedIdentities!: types.AssociatedIdentity[];
  @Prop() currentEmail!: string;
  @Prop() currentName!: string;
  @Prop() currentUsername!: string;
  @Prop() isAdmin!: boolean;
  @Prop() isMainUserIdentity!: boolean;
  @Prop() lockDownImage!: string;
  @Prop() navbarSection!: string;
  @Prop() graderInfo!: types.GraderStatus | null;
  @Prop() graderQueueLength!: number;
  @Prop() errorMessage!: string | null;
  @Prop({ default: () => [] })
  notificationsClarifications!: types.Clarification[];

  notifications: types.Notification[] = [];
  T = T;

  get formattedLoginURL(): string {
    return `/login/?redirect=${encodeURIComponent(window.location.pathname)}`;
  }

  get identitiesNotLoggedIn(): types.AssociatedIdentity[] {
    return this.associatedIdentities.filter(
      (identity) => identity.username !== this.currentUsername,
    );
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

#root .navbar-default {
  border-color: transparent;
  margin: 0;
  border-bottom-width: 0;
  background-color: var(--header-primary-color);

  & .caret {
    border-top-color: var(--header-caret-border-color);
    border-bottom-color: var(--header-caret-border-color);
  }

  & .active {
    > a {
      background-color: var(--header-active-color);
    }
  }

  & .navbar-header {
    margin: 0;

    img {
      height: 20px;
    }

    .navbar-brand {
      background-color: var(--header-navbar-brand-background-color);
    }
  }

  & .user-dropdown {
    // Elimina el padding del elemento dropdown del nombre
    // del usuario para la redimesion de la imagen de perfil.
    padding: 0 12px 0 0;

    span {
      vertical-align: middle;

      &.username {
        display: inline-block;
        max-width: 80px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
    }

    img {
      width: 50px;
      height: 50px;
      margin-right: 10px;
    }
  }

  & .navbar-text {
    color: var(--header-font-primary-color);
  }

  & .navbar-nav {
    margin: 0;

    li {
      a {
        color: var(--header-font-primary-color);

        &:hover {
          background-color: var(--header-accent-color);
        }
      }
    }
  }

  & .nav {
    li {
      a {
        &:hover {
          background-color: var(--header-accent-color);
        }

        &:focus {
          background-color: var(--header-accent-color);
        }
      }
    }

    .dropdown-menu {
      li {
        a {
          color: var(--header-font-secondary-color);

          &:hover {
            background-color: var(--header-dropdown-active-item);
          }
        }
      }
    }

    & .dropdown:hover {
      & .dropdown-menu {
        display: block;
      }
    }
  }

  & .navbar-right {
    background-color: var(--header-primary-color);

    & .caret {
      border-top-color: var(--header-navbar-right-caret-border-color);
      border-bottom-color: var(--header-navbar-right-caret-border-color);
    }

    a {
      color: var(--header-navbar-right-a-font-color);

      & .grader-error {
        color: var(--status-error-color);
        background-image: linear-gradient(
          var(--badges-grader-error-gradient-from-background-color),
          var(--badges-grader-error-gradient-to-background-color)
        );
      }

      & .grader-ok {
        color: var(--status-success-color);
        background-image: linear-gradient(
          var(--badges-grader-ok-gradient-from-background-color),
          var(--badges-grader-ok-gradient-to-background-color)
        );
        background-color: var(--badges-grader-ok-background-color);
      }

      & .grader-warning {
        color: var(--status-warning-color);
        background-image: linear-gradient(
          to bottom,
          var(--badges-grader-warning-from-font-color) 0,
          var(--badges-grader-warning-to-font-color) 100%
        );
        border-color: var(--badges-grader-warning-border-color);
      }
    }
  }

  .container {
    @media (max-width: 991px) {
      max-width: 100% !important;
    }
  }

  .username {
    @media (max-width: 991px) {
      display: none !important;
    }
  }

  .grader-count {
    @media (max-width: 991px) {
      display: none !important;
    }
  }

  .navbar-inner {
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
    padding-left: 0;
    padding-right: 0;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;

    @media (max-width: 991px) {
      width: 100% !important;
    }
  }

  .navbar-collapse {
    max-height: none;

    &.in {
      overflow-y: visible;
    }
  }
}
</style>
