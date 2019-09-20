<template>
  <div class="navbar navbar-default navbar-fixed-top"
       role="navigation">
    <div class="container navbar-inner">
      <div class="navbar-header">
        <a class="navbar-brand"
             href="/"><img alt="omegaUp"
             src="/media/omegaup_curves.png"> <img alt="lockdown"
             title="lockdown"
             v-bind:src="lockDownImage"
             v-show="omegaUpLockDown"></a>
      </div>
      <div aria-expanded="false"
           class="navbar-collapse collapse">
        <ul class="nav navbar-nav"
            v-if="!omegaUpLockDown &amp;&amp; !inContest">
          <li>
            <a href="/arena/">{{ T.navArena }}</a>
          </li>
          <li class="dropdown nav-contests"
              v-show="isLoggedIn">
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
                <a href="/scoreboardmerge.php">{{ T.contestsJoinScoreboards }}</a>
              </li>
            </ul>
          </li>
          <li class="dropdown nav-problems"
              v-show="isLoggedIn">
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
              <li v-show="isReviewer">
                <a href="/nomination/">{{ T.navQualityNominationQueue }}</a>
              </li>
            </ul>
          </li>
          <li class="nav-problems"
              v-show="!isLoggedIn">
            <a href="/problem/">{{ T.wordsProblems }}</a>
          </li>
          <li class="nav-rank">
            <a href="/rank/">{{ T.navRanking }}</a>
          </li>
          <li class="nav-schools">
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
            v-if="!isLoggedIn">
          <li>
            <a v-bind:href="formattedLoginURL">{{ T.navLogIn }}</a>
          </li>
        </ul>
        <ul class="nav navbar-nav navbar-right"
            v-else="">
          <omegaup-notification-list v-bind:notifications=
          "notifications"></omegaup-notification-list>
          <li class="dropdown nav-user">
            <a class="dropdown-toggle user-dropdown"
                data-toggle="dropdown"
                href="#"><img v-bind:src="gravatarURL51"> <span class="username"
                  v-bind:title="currentUsername">{{ currentUsername }}</span> <span class=
                  "grader-count badge"
                  v-show="isAdmin">…</span> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li v-show="!omegaUpLockDown &amp;&amp; !inContest">
                <a href="/profile/"><span class="glyphicon glyphicon-user"></span> {{
                T.navViewProfile }}</a>
              </li>
              <li>
                <a href="/logout/"><span class="glyphicon glyphicon-log-out"></span> {{ T.navLogOut
                }}</a>
              </li>
              <hr class="dropdown-separator"
                  v-show="isAdmin &amp;&amp; !omegaUpLockDown &amp;&amp; !inContest">
              <li class="grader-submissions"
                  v-show="isAdmin &amp;&amp; (!omegaUpLockDown &amp;&amp; !inContest)">
                <a class="grader-submissions-link"
                    href="/arena/admin/">{{ T.wordsLatestSubmissions }}</a>
              </li>
              <li class="grader grader-status"
                  v-show="isAdmin &amp;&amp; !omegaUpLockDown &amp;&amp; !inContest"></li>
              <li class="grader grader-broadcaster-sockets"
                  v-show="isAdmin &amp;&amp; !omegaUpLockDown &amp;&amp; !inContest"></li>
              <li class="grader grader-embedded-runner"
                  v-show="isAdmin &amp;&amp; !omegaUpLockDown &amp;&amp; !inContest"></li>
              <li class="grader grader-queues"
                  v-show="isAdmin &amp;&amp; !omegaUpLockDown &amp;&amp; !inContest"></li>
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
  requestURI: string;
  isAdmin: boolean;
  lockDownImage: string;
}

@Component({
  components: {
    'omegaup-notification-list': notifications_List,
  },
})
export default class Navbar extends Vue {
  @Prop() data!: NavbarComponent;

  omegaUpLockDown = this.data.omegaUpLockDown;
  inContest = this.data.inContest;
  isLoggedIn = this.data.isLoggedIn;
  isReviewer = this.data.isReviewer;
  gravatarURL51 = this.data.gravatarURL51;
  currentUsername = this.data.currentUsername;
  requestURI = this.data.requestURI;
  isAdmin = this.data.isAdmin;
  lockDownImage = this.data.lockDownImage;
  notifications: omegaup.Notification[] = [];
  T = T;

  get formattedLoginURL() {
    return `/login/?redirect=${this.requestURI}`;
  }
}

</script>
