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
               v-bind:src="`/media/flags/${profile.country_id}.png`"
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

<script>
import {T} from '../../omegaup.js';
import user_BasicInfo from './BasicInfo.vue';
import user_Username from './Username.vue';
import user_Charts from './Charts.vue';
export default {
  props: {
    profile: Object,
    contests: Array,
    solvedProblems: Array,
    unsolvedProblems: Array,
    rank: String,
    charts: Object,
  },
  computed: {
    groupedSolvedProblems: function() {
      return this.groupElements(this.solvedProblems, this.columns);
    },
    groupedUnsolvedProblems: function() {
      return this.groupElements(this.unsolvedProblems, this.columns);
    },
  },
  methods: {
    groupElements(elements, columns) {
      let groups = [];
      for (let i = 0; i < elements.length; i += columns) {
        groups.push(elements.slice(i, i + columns));
      }
      return groups;
    },
  },
  data: function() {
    return { T: T, columns: 3, }
  },
  components: {
    'omegaup-user-basicinfo': user_BasicInfo,
    'omegaup-user-username': user_Username,
    'omegaup-user-charts': user_Charts,
  }
}
</script>
