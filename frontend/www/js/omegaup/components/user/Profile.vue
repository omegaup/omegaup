<template>
  <div class="row">
    <div class="col-md-2 no-right-padding">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="thumbnail bottom-margin"><img v-bind:src="profile.gravatar_92"></div>
          <div v-if="profile.email">
            <a class="btn btn-default"
                 href="/profile/edit/">{{ T.profileEdit }}</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-10 no-right-padding">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">{{ profile.rankinfo.rank &gt; 0 ? `#${profile.rankinfo.rank} - `:
          '' }} <omegaup-user-username v-bind:classname="profile.classname"
                                 v-bind:username="profile.username"></omegaup-user-username>
                                 <img height="11"
               v-bind:src="`/media/flags/${profile.country_id.toLowerCase()}.png`"
               v-bind:title="profile.country_id"
               v-if="profile.country_id"
               width="16"></h2>
        </div><omegaup-user-basicinfo v-bind:profile="profile"
             v-bind:rank="rank"></omegaup-user-basicinfo>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.profileContests }} <span class="badge">{{ contests.length
          }}</span></h2>
        </div>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ T.profileContestsTableContest }}</th>
              <th>{{ T.profileContestsTablePlace }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="contest in contests">
              <td>
                <a v-bind:href="`/arena/${contest.data.alias}`">{{ contest.data.title }}</a>
              </td>
              <td><strong>{{ contest.place }}</strong></td>
            </tr>
          </tbody>
        </table>
        <div v-show="!contests"><img src="/media/wait.gif"></div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.profileSolvedProblems }} <span class="badge">{{
          solvedProblems.length }}</span></h2>
        </div>
        <table class="table table-striped">
          <thead>
            <tr>
              <th colspan="3">{{ T.profileSolvedProblemsTableTitle }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="group in groupedSolvedProblems">
              <td v-for="problem in group">
                <a v-bind:href="`/arena/problem/${problem.alias}`">{{ problem.title }}</a>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-show="!groupedSolvedProblems"><img src="/media/wait.gif"></div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.profileUnsolvedProblems }} <span class="badge">{{
          unsolvedProblems.length }}</span></h2>
        </div>
        <table class="table table-striped">
          <thead>
            <tr>
              <th colspan="3">{{ T.profileUnsolvedProblemsTableTitle }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="group in groupedUnsolvedProblems">
              <td v-for="problem in group">
                <a v-bind:href="`/arena/problem/${problem.alias}`">{{ problem.title }}</a>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-show="!groupedUnsolvedProblems"><img src="/media/wait.gif"></div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.wordsBadgesObtained }} <span class="badge">{{ badges.length
          }}</span></h2>
        </div>
        <div class="panel-body">
          <div class="badges-container">
            <omegaup-badge v-bind:alias="badge.badge_alias"
                 v-bind:key="badge.badge_alias"
                 v-bind:unlocked="badge.unlocked"
                 v-for="badge in badges"></omegaup-badge>
          </div>
        </div>
        <div v-show="!badges"><img src="/media/wait.gif"></div>
      </div>
      <div class="panel panel-default no-bottom-margin">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.profileStatistics }}</h2>
        </div><omegaup-user-charts v-bind:data="charts"
             v-bind:username="profile.username"
             v-if="charts"></omegaup-user-charts>
      </div>
    </div>
  </div>
</template>

<style>
.badges-container {
  display: grid;
  justify-content: space-between;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  grid-auto-rows: 180px;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import user_BasicInfo from './BasicInfo.vue';
import user_Username from './Username.vue';
import user_Charts from './Charts.vue';
import Badge from '../badge/Badge.vue';

@Component({
  components: {
    'omegaup-user-basicinfo': user_BasicInfo,
    'omegaup-user-username': user_Username,
    'omegaup-user-charts': user_Charts,
    'omegaup-badge': Badge,
  },
})
export default class UserProfile extends Vue {
  @Prop() profile!: omegaup.Profile;
  @Prop() contests!: omegaup.ContestResult[];
  @Prop() solvedProblems!: omegaup.Problem[];
  @Prop() unsolvedProblems!: omegaup.Problem[];
  @Prop() rank!: string;
  @Prop() charts!: any;
  @Prop() profileBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;

  T = T;
  columns = 3;

  get groupedSolvedProblems(): omegaup.Problem[][] {
    return this.groupElements(this.solvedProblems, this.columns);
  }

  get groupedUnsolvedProblems(): omegaup.Problem[][] {
    return this.groupElements(this.unsolvedProblems, this.columns);
  }

  get badges(): omegaup.Badge[] {
    const badges: omegaup.Badge[] = [];
    const unlockedBadges: omegaup.Badge[] = Array.from(this.profileBadges)
      .filter(x => this.visitorBadges.has(x))
      .map(x => {
        return { badge_alias: x, unlocked: true };
      });
    const lockedBadges: omegaup.Badge[] = Array.from(this.profileBadges)
      .filter(x => !this.visitorBadges.has(x))
      .map(x => {
        return { badge_alias: x, unlocked: false };
      });
    return unlockedBadges
      .concat(lockedBadges)
      .sort((a: omegaup.Badge, b: omegaup.Badge) => {
        if (a.badge_alias > b.badge_alias) {
          return 1;
        }
        if (a.badge_alias < b.badge_alias) {
          return -1;
        }
        return 0;
      });
  }

  groupElements(
    elements: omegaup.Problem[],
    columns: number,
  ): omegaup.Problem[][] {
    const groups = [];
    for (let i = 0; i < elements.length; i += columns) {
      groups.push(elements.slice(i, i + columns));
    }
    return groups;
  }
}

</script>
