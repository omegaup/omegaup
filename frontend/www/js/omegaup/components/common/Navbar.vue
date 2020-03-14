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
          ><img alt="omegaUp" src="/media/omegaup_curves.png"/>
          <img
            alt="lockdown"
            title="lockdown"
            v-bind:src="header.lockDownImage"
            v-show="header.omegaUpLockDown"
        /></a>
      </div>
      <div aria-expanded="false" class="navbar-collapse collapse">
        <ul
          class="nav navbar-nav"
          v-if="!header.omegaUpLockDown && !header.inContest"
        >
          <li v-bind:class="{ active: header.navbarSection === 'arena' }">
            <a href="/arena/">{{ T.navArena }}</a>
          </li>
          <li
            class="dropdown nav-contests"
            v-bind:class="{ active: header.navbarSection === 'contests' }"
            v-if="header.isLoggedIn && header.isMainUserIdentity"
          >
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"
              ><span>{{ T.wordsContests }}</span> <span class="caret"></span
            ></a>
            <ul class="dropdown-menu">
              <li>
                <a href="/contest/new/">{{ T.contestsCreateNew }}</a>
              </li>
              <li>
                <a href="/contest/mine/">{{ T.navMyContests }}</a>
              </li>
              <li>
                <a href="/group/">{{ T.navMyGroups }}</a>
              </li>
              <li>
                <a href="/scoreboardmerge/">{{ T.contestsJoinScoreboards }}</a>
              </li>
            </ul>
          </li>
          <li
            class="dropdown nav-problems"
            v-bind:class="{ active: header.navbarSection === 'problems' }"
            v-if="header.isLoggedIn && header.isMainUserIdentity"
          >
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"
              ><span>{{ T.wordsProblems }}</span> <span class="caret"></span
            ></a>
            <ul class="dropdown-menu">
              <li>
                <a href="/problem/new/">{{ T.myproblemsListCreateProblem }}</a>
              </li>
              <li>
                <a href="/problem/mine/">{{ T.navMyProblems }}</a>
              </li>
              <li>
                <a href="/problem/">{{ T.wordsProblems }}</a>
              </li>
              <li>
                <a href="/submissions/">{{ T.wordsLatestSubmissions }}</a>
              </li>
              <li>
                <a href="/nomination/mine/">{{ T.navMyQualityNomination }}</a>
              </li>
              <li v-show="header.isReviewer">
                <a href="/nomination/">{{ T.navQualityNominationQueue }}</a>
              </li>
            </ul>
          </li>
          <li
            class="dropdown nav-problems"
            v-bind:class="{ active: header.navbarSection === 'problems' }"
            v-else=""
          >
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <span>{{ T.wordsProblems }}</span>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li>
                <a href="/problem/">{{ T.wordsProblems }}</a>
              </li>
              <li>
                <a href="/submissions/">{{ T.wordsLatestSubmissions }}</a>
              </li>
            </ul>
          </li>
          <li
            class="dropdown nav-rank"
            v-bind:class="{ active: header.navbarSection === 'rank' }"
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
                <a href="/rank/schools/">{{ T.navSchoolRanking }}</a>
              </li>
            </ul>
          </li>
          <li
            class="nav-schools"
            v-bind:class="{ active: header.navbarSection === 'schools' }"
          >
            <a href="/schools/">{{ T.navSchools }}</a>
          </li>
          <li>
            <a href="http://blog.omegaup.com/">{{ T.navBlog }}</a>
          </li>
          <li>
            <a href="https://omegaup.com/preguntas/">{{ T.navQuestions }}</a>
          </li>
        </ul>
        <ul class="nav navbar-nav" v-else=""></ul>
        <!-- in lockdown or contest mode there is no left navbar -->
        <ul class="nav navbar-nav navbar-right" v-if="!header.isLoggedIn">
          <li>
            <a v-bind:href="formattedLoginURL">{{ T.navLogIn }}</a>
          </li>
        </ul>
        <ul class="nav navbar-nav navbar-right" v-else="">
          <omegaup-notification-list
            v-bind:notifications="notifications"
          ></omegaup-notification-list>
          <li
            class="dropdown nav-user"
            v-bind:class="{ active: header.navbarSection === 'users' }"
          >
            <a
              class="dropdown-toggle user-dropdown"
              data-toggle="dropdown"
              href="#"
              ><img v-bind:src="header.gravatarURL51"/>
              <span class="username" v-bind:title="header.currentUsername">{{
                header.currentUsername
              }}</span>
              <omegaup-common-grader-badge
                v-show="header.isAdmin"
                v-bind:queueLength="graderQueueLength"
                v-bind:error="errorMessage !== null"
              ></omegaup-common-grader-badge>
              <span class="caret"></span
            ></a>
            <ul class="dropdown-menu" v-if="showNavbar">
              <li v-show="!header.omegaUpLockDown &amp;&amp; !header.inContest">
                <a href="/profile/"
                  ><span class="glyphicon glyphicon-user"></span>
                  {{ T.navViewProfile }}</a
                >
              </li>
              <li>
                <a href="/logout/"
                  ><span class="glyphicon glyphicon-log-out"></span>
                  {{ T.navLogOut }}</a
                >
              </li>
              <omegaup-common-grader-status
                v-bind:status="errorMessage !== null ? 'down' : 'ok'"
                v-bind:error="errorMessage"
                v-bind:graderInfo="graderInfo"
              ></omegaup-common-grader-status>
            </ul>
            <ul class="dropdown-menu" v-else="">
              <li v-show="!header.omegaUpLockDown &amp;&amp; !header.inContest">
                <a href="/profile/"
                  ><span class="glyphicon glyphicon-user"></span>
                  {{ T.navViewProfile }}</a
                >
              </li>
              <li>
                <a href="/logout/"
                  ><span class="glyphicon glyphicon-log-out"></span>
                  {{ T.navLogOut }}</a
                >
              </li>
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
      background-color: $white;
      background-image: linear-gradient(to bottom, $white 0, #ddd 100%);
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
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import notifications_List from '../notification/List.vue';
import common_GraderStatus from '../common/GraderStatus.vue';
import common_GraderBadge from '../common/GraderBadge.vue';

@Component({
  components: {
    'omegaup-notification-list': notifications_List,
    'omegaup-common-grader-status': common_GraderStatus,
    'omegaup-common-grader-badge': common_GraderBadge,
  },
})
export default class Navbar extends Vue {
  @Prop() header!: omegaup.NavbarPayload;
  @Prop() graderInfo!: omegaup.Grader;
  @Prop() graderQueueLength!: number;
  @Prop() errorMessage!: string;

  notifications: omegaup.Notification[] = [];
  T = T;

  get formattedLoginURL(): string {
    return `/login/?redirect=${encodeURIComponent(window.location.pathname)}`;
  }

  get showNavbar(): boolean {
    return (
      this.header.isAdmin &&
      !this.header.omegaUpLockDown &&
      !this.header.inContest
    );
  }
}
</script>
