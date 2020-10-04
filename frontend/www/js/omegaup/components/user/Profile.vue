<template>
  <div class="row">
    <div class="col-md-2 no-right-padding">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="thumbnail bottom-margin">
            <img v-bind:src="profile.gravatar_92" />
          </div>
          <div v-if="profile.email">
            <a class="btn btn-default" href="/profile/edit/">{{
              T.profileEdit
            }}</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-10 no-right-padding">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">
            {{ profile.rankinfo.rank &gt; 0 ? `#${profile.rankinfo.rank} - `:
          '' }}
            <omegaup-user-username
              v-bind:classname="profile.classname"
              v-bind:username="profile.username"
            ></omegaup-user-username>
            <img
              v-if="profile.country_id"
              height="11"
              v-bind:src="`/media/flags/${profile.country_id.toLowerCase()}.png`"
              v-bind:title="profile.country_id"
              width="16"
            />
          </h2>
        </div>
        <omegaup-user-basicinfo
          v-bind:profile="profile"
          v-bind:rank="rank"
        ></omegaup-user-basicinfo>
      </div>
      <omegaup-grid-paginator
        v-bind:columns="1"
        v-bind:items="contests"
        v-bind:items-per-page="15"
        v-bind:title="T.profileContests"
      >
        <template slot="table-header">
          <thead>
            <tr>
              <th>{{ T.profileContestsTableContest }}</th>
              <th class="numericColumn">{{ T.profileContestsTablePlace }}</th>
            </tr>
          </thead>
        </template>
      </omegaup-grid-paginator>
      <omegaup-grid-paginator
        v-bind:columns="3"
        v-bind:items="createdProblems"
        v-bind:items-per-page="30"
        v-bind:title="T.profileCreatedProblems"
      ></omegaup-grid-paginator>
      <omegaup-grid-paginator
        v-bind:columns="3"
        v-bind:items="solvedProblems"
        v-bind:items-per-page="30"
        v-bind:title="T.profileSolvedProblems"
      ></omegaup-grid-paginator>
      <omegaup-grid-paginator
        v-bind:columns="3"
        v-bind:items="unsolvedProblems"
        v-bind:items-per-page="30"
        v-bind:title="T.profileUnsolvedProblems"
      ></omegaup-grid-paginator>
      <omegaup-badge-list
        v-bind:all-badges="profileBadges"
        v-bind:show-all-badges-link="true"
        v-bind:visitor-badges="visitorBadges"
      ></omegaup-badge-list>
      <div class="panel panel-default no-bottom-margin">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.profileStatistics }}</h2>
        </div>
        <omegaup-user-charts
          v-if="charts"
          v-bind:data="charts"
          v-bind:username="profile.username"
          v-bind:periodStatisticOptions="periodStatisticOptions"
          v-bind:aggregateStatisticOptions="aggregateStatisticOptions"
          v-on:emit-update-period-statistics="
            (profileComponent, categories, data) =>
              $emit(
                'update-period-statistics',
                profileComponent,
                categories,
                data,
              )
          "
          v-on:emit-update-aggregate-statistics="
            (profileComponent) =>
              $emit('update-aggregate-statistics', profileComponent)
          "
        ></omegaup-user-charts>
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
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { Chart } from 'highcharts-vue';
import user_BasicInfo from './BasicInfo.vue';
import user_Username from './Username.vue';
import user_Charts from './Charts.vue';
import badge_List from '../badge/List.vue';
import gridPaginator from '../GridPaginator.vue';
import { Problem, ContestResult } from '../../linkable_resource';

@Component({
  components: {
    'omegaup-user-basicinfo': user_BasicInfo,
    'omegaup-user-username': user_Username,
    'omegaup-user-charts': user_Charts,
    'omegaup-badge-list': badge_List,
    'omegaup-grid-paginator': gridPaginator,
  },
})
export default class UserProfile extends Vue {
  @Prop() profile!: omegaup.Profile;
  @Prop() contests!: ContestResult[];
  @Prop() solvedProblems!: Problem[];
  @Prop() unsolvedProblems!: Problem[];
  @Prop() createdProblems!: Problem[];
  @Prop() rank!: string;
  @Prop() charts!: any;
  @Prop() periodStatisticOptions!: Chart;
  @Prop() aggregateStatisticOptions!: Chart;
  @Prop() profileBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;

  T = T;
  columns = 3;
}
</script>
