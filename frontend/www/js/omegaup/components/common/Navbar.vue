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
            alt="lockdown"
            title="lockdown"
            v-bind:src="lockDownImage"
            v-show="omegaUpLockDown"
        /></a>
      </div>
      <div aria-expanded="false" class="navbar-collapse collapse">
        <ul class="nav navbar-nav" v-if="!omegaUpLockDown && !inContest">
          <li
            class="dropdown nav-contests"
            v-bind:class="{ active: navbarSection === 'contests' }"
            v-if="isLoggedIn"
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
          <li v-bind:class="{ active: navbarSection === 'contests' }" v-else>
            <a href="/arena/" data-nav-contests-arena>{{ T.wordsContests }}</a>
          </li>
          <li
            class="dropdown nav-courses"
            v-bind:class="{ active: navbarSection === 'courses' }"
            v-if="isLoggedIn"
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
          <li v-bind:class="{ active: navbarSection === 'courses' }" v-else>
            <a href="/course/">{{ T.navCourses }}</a>
          </li>
          <li
            class="dropdown nav-problems"
            v-bind:class="{ active: navbarSection === 'problems' }"
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
                <a href="/problem/" data-nav-problems-all>{{
                  T.navAllProblems
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
            v-bind:class="{ active: navbarSection === 'rank' }"
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
        <ul class="nav navbar-nav" v-else></ul>
        <!-- in lockdown or contest mode there is no left navbar -->
        <ul class="nav navbar-nav navbar-right" v-if="!isLoggedIn">
          <li>
            <a v-bind:href="formattedLoginURL">{{ T.navLogIn }}</a>
          </li>
        </ul>
        <ul class="nav navbar-nav navbar-right" v-else>
          <omegaup-notifications-clarifications
            v-bind:initialClarifications="initialClarifications"
            v-if="inContest"
          ></omegaup-notifications-clarifications>
          <li
            class="dropdown nav-user"
            v-bind:class="{ active: navbarSection === 'users' }"
            data-nav-right
          >
            <a
              class="dropdown-toggle user-dropdown"
              data-toggle="dropdown"
              data-nav-user
              href="#"
              ><img v-bind:src="gravatarURL51" />
              <span class="username" v-bind:title="currentUsername">{{
                currentUsername
              }}</span>
              <omegaup-common-grader-badge
                v-show="isAdmin"
                v-bind:queueLength="graderQueueLength"
                v-bind:error="errorMessage !== null"
              ></omegaup-common-grader-badge>
              <span class="caret"></span
            ></a>
            <ul class="dropdown-menu">
              <template v-show="!omegaUpLockDown && !inContest">
                <li>
                  <a href="/profile/" data-nav-profile
                    ><span class="glyphicon glyphicon-user"></span>
                    {{ T.navViewProfile }}</a
                  >
                </li>
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
                  <a href="/nomination/mine/">{{ T.navMyQualityNomination }}</a>
                </li>
              </template>
              <li>
                <a href="/logout/"
                  ><span class="glyphicon glyphicon-log-out"></span>
                  {{ T.navLogOut }}</a
                >
              </li>
              <omegaup-common-grader-status
                v-show="isAdmin"
                v-bind:status="errorMessage !== null ? 'down' : 'ok'"
                v-bind:error="errorMessage"
                v-bind:graderInfo="graderInfo"
              ></omegaup-common-grader-status>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
@import '../../../../sass/main.scss';

#root .navbar-default {
  border-color: transparent;
  margin: 0;
  border-bottom-width: 0;

  background-color: $header-primary-color;

  & .caret {
    border-top-color: $white;
    border-bottom-color: $white;
  }

  & .active {
    > a {
      background-color: $header-active-color;
    }
  }

  & .navbar-header {
    margin: 0;

    img {
      height: 20px;
    }

    .navbar-brand {
      background-color: #f2f2f2;
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
    color: $header-font-primary-color;
  }

  & .navbar-nav {
    margin: 0;
    li {
      a {
        color: $header-font-primary-color;

        &:hover {
          background-color: $header-accent-color;
        }
      }
    }
  }

  & .nav {
    li {
      a {
        &:hover {
          background-color: $header-accent-color;
        }

        &:focus {
          background-color: $header-accent-color;
        }
      }
    }

    .dropdown-menu {
      li {
        a {
          color: $header-font-secondary-color;

          &:hover {
            background-color: $header-dropdown-active-item;
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
    background-color: $header-primary-color;

    & .caret {
      border-top-color: $black;
      border-bottom-color: $black;
    }

    a {
      color: $black;

      & .grader-error {
        color: $status-error;
        background-image: linear-gradient(
          rgb(242, 222, 222) 0px,
          rgb(231, 195, 195) 100%
        );
      }

      & .grader-ok {
        color: $status-success;
        background-image: linear-gradient(
          rgb(223, 240, 216) 0px,
          rgb(200, 229, 188) 100%
        );
        background-color: rgb(223, 240, 216);
      }

      & .grader-warning {
        color: $status-warning;
        background-image: linear-gradient(to bottom, #fcf8e3 0, #f8efc0 100%);
        border-color: #f5e79e;
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
  @Prop() currentUsername!: string;
  @Prop() isAdmin!: boolean;
  @Prop() isMainUserIdentity!: boolean;
  @Prop() lockDownImage!: string;
  @Prop() navbarSection!: string;
  @Prop() graderInfo!: types.GraderStatus | null;
  @Prop() graderQueueLength!: number;
  @Prop() errorMessage!: string | null;
  @Prop() initialClarifications!: types.Clarification[];

  notifications: types.Notification[] = [];
  clarifications: types.Clarification[] = this.initialClarifications;
  T = T;

  get formattedLoginURL(): string {
    return `/login/?redirect=${encodeURIComponent(window.location.pathname)}`;
  }
}
</script>
