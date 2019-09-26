<template>
  <div class="navbar navbar-default navbar-fixed-top"
       role="navigation">
    <div class="container navbar-inner">
      <div class="navbar-header">
        <button aria-expanded="false"
             class="navbar-toggle collapsed"
             data-target=".navbar-collapse"
             data-toggle="collapse"
             type="button"><span class="sr-only">Toggle navigation</span> <span class=
             "icon-bar"></span> <span class="icon-bar"></span> <span class=
             "icon-bar"></span></button> <a class="navbar-brand"
             href="/"><img alt="omegaUp"
             src="/media/omegaup_curves.png"> <img alt="lockdown"
             title="lockdown"
             v-bind:src="data.lockDownImage"
             v-show="data.omegaUpLockDown"></a>
      </div>
      <div aria-expanded="false"
           class="navbar-collapse collapse">
        <ul class="nav navbar-nav"
            v-if="!data.omegaUpLockDown &amp;&amp; !data.inContest">
          <li v-bind:class="activeMenu('arena')">
            <a href="/arena/">{{ T.navArena }}</a>
          </li>
          <li class="dropdown nav-contests"
              v-bind:class="activeMenu('contests')"
              v-show="data.isLoggedIn">
            <a class="dropdown-toggle"
                data-toggle="dropdown"
                href="#"><span>{{ T.wordsContests }}</span> <span class="caret"></span></a>
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
          <li class="dropdown nav-problems"
              v-bind:class="activeMenu('problems')"
              v-if="data.isLoggedIn">
            <a class="dropdown-toggle"
                data-toggle="dropdown"
                href="#"><span>{{ T.wordsProblems }}</span> <span class="caret"></span></a>
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
                <a href="/nomination/mine/">{{ T.navMyQualityNomination }}</a>
              </li>
              <li v-show="data.isReviewer">
                <a href="/nomination/">{{ T.navQualityNominationQueue }}</a>
              </li>
            </ul>
          </li>
          <li class="nav-problems"
              v-bind:class="activeMenu('problems')"
              v-else="">
            <a href="/problem/">{{ T.wordsProblems }}</a>
          </li>
          <li class="nav-rank"
              v-bind:class="activeMenu('rank')">
            <a href="/rank/">{{ T.navRanking }}</a>
          </li>
          <li class="nav-schools"
              v-bind:class="activeMenu('schools')">
            <a href="/schools/">{{ T.navSchools }}</a>
          </li>
          <li>
            <a href="http://blog.omegaup.com/">{{ T.navBlog }}</a>
          </li>
          <li>
            <a href="https://omegaup.com/preguntas/">{{ T.navQuestions }}</a>
          </li>
        </ul>
        <ul class="nav navbar-nav"
            v-else=""></ul><!-- in lockdown or contest mode there is no left navbar -->
        <ul class="nav navbar-nav navbar-right"
            v-if="!data.isLoggedIn">
          <li>
            <a v-bind:href="formattedLoginURL">{{ T.navLogIn }}</a>
          </li>
        </ul>
        <ul class="nav navbar-nav navbar-right"
            v-else="">
          <omegaup-notification-list v-bind:notifications=
          "notifications"></omegaup-notification-list>
          <li class="dropdown nav-user"
              v-bind:class="activeMenu('users')">
            <a class="dropdown-toggle user-dropdown"
                data-toggle="dropdown"
                href="#"><img v-bind:src="data.gravatarURL51"> <span class="username"
                  v-bind:title="data.currentUsername">{{ data.currentUsername }}</span> <span class=
                  "grader-count badge"
                  v-show="data.isAdmin">…</span> <span class="caret"></span></a>
            <ul class="dropdown-menu"
                v-if="navbarHidden">
              <li v-show="!data.omegaUpLockDown &amp;&amp; !data.inContest">
                <a href="/profile/"><span class="glyphicon glyphicon-user"></span> {{
                T.navViewProfile }}</a>
              </li>
              <li>
                <a href="/logout/"><span class="glyphicon glyphicon-log-out"></span> {{ T.navLogOut
                }}</a>
              </li>
              <hr class="dropdown-separator">
              <li class="grader-submissions">
                <a class="grader-submissions-link"
                    href="/arena/admin/">{{ T.wordsLatestSubmissions }}</a>
              </li>
              <li class="grader grader-status"></li>
              <li class="grader grader-broadcaster-sockets"></li>
              <li class="grader grader-embedded-runner"></li>
              <li class="grader grader-queues"></li>
            </ul>
            <ul class="dropdown-menu"
                v-else="">
              <li v-show="!data.omegaUpLockDown &amp;&amp; !data.inContest">
                <a href="/profile/"><span class="glyphicon glyphicon-user"></span> {{
                T.navViewProfile }}</a>
              </li>
              <li>
                <a href="/logout/"><span class="glyphicon glyphicon-log-out"></span> {{ T.navLogOut
                }}</a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import notifications_List from '../notification/List.vue';

interface NavbarComponent {
  omegaUpLockDown: boolean;
  inContest: boolean;
  isLoggedIn: boolean;
  isReviewer: boolean;
  gravatarURL51: string;
  currentUsername: string;
  isAdmin: boolean;
  lockDownImage: string;
  navbarSection: string;
}

@Component({
  components: {
    'omegaup-notification-list': notifications_List,
  },
})
export default class Navbar extends Vue {
  @Prop() data!: NavbarComponent;

  notifications: omegaup.Notification[] = [];
  T = T;

  get formattedLoginURL(): string {
    return `/login/?redirect=${encodeURIComponent(window.location.pathname)}`;
  }

  get navbarHidden(): boolean {
    return (
      this.data.isAdmin && !this.data.omegaUpLockDown && !this.data.inContest
    );
  }

  activeMenu(menu: string): string {
    return this.data.navbarSection === menu ? 'active' : '';
  }
}

</script>
