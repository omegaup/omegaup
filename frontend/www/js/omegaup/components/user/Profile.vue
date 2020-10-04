<template>
  <div class="row">
    <div class="col-md-2 no-right-padding">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="thumbnail bottom-margin">
            <img :src="profile.gravatar_92" />
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
              :classname="profile.classname"
              :username="profile.username"
            ></omegaup-user-username>
            <img
              v-if="profile.country_id"
              height="11"
              :src="`/media/flags/${profile.country_id.toLowerCase()}.png`"
              :title="profile.country_id"
              width="16"
            />
          </h2>
        </div>
        <omegaup-user-basicinfo
          :profile="profile"
          :rank="rank"
        ></omegaup-user-basicinfo>
      </div>
      <omegaup-grid-paginator
        :columns="1"
        :items="contests"
        :items-per-page="15"
        :title="T.profileContests"
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
        :columns="3"
        :items="createdProblems"
        :items-per-page="30"
        :title="T.profileCreatedProblems"
      ></omegaup-grid-paginator>
      <omegaup-grid-paginator
        :columns="3"
        :items="solvedProblems"
        :items-per-page="30"
        :title="T.profileSolvedProblems"
      ></omegaup-grid-paginator>
      <omegaup-grid-paginator
        :columns="3"
        :items="unsolvedProblems"
        :items-per-page="30"
        :title="T.profileUnsolvedProblems"
      ></omegaup-grid-paginator>
      <omegaup-badge-list
        :all-badges="profileBadges"
        :show-all-badges-link="true"
        :visitor-badges="visitorBadges"
      ></omegaup-badge-list>
      <div class="panel panel-default no-bottom-margin">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.profileStatistics }}</h2>
        </div>
        <omegaup-user-charts
          v-if="charts"
          :data="charts"
          :username="profile.username"
          :periodStatisticOptions="periodStatisticOptions"
          :aggregateStatisticOptions="aggregateStatisticOptions"
          @emit-update-period-statistics="
            (profileComponent, categories, data) =>
              $emit(
                'update-period-statistics',
                profileComponent,
                categories,
                data,
              )
          "
          @emit-update-aggregate-statistics="
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
