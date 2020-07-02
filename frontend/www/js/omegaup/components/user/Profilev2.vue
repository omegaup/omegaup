<template>
  <div class="container-fluid p-0 mt-0">
    <div class="row">
      <div class="col-md-2">
        <div class="card">
          <img
            class="m-1"
            height="20"
            v-bind:src="`/media/flags/${profile.country_id.toLowerCase()}.png`"
            v-bind:title="profile.country_id"
            v-if="profile.country_id"
            width="25"
          />
          <div class="card-body">
            <div class="img-thumbnail rounded-circle bottom-margin">
              <img class="rounded-circle" v-bind:src="profile.gravatar_92" />
            </div>
          </div>
          <div class="card-title text-center">
            <div class="mb-3">
              <omegaup-user-username
                v-bind:classname="profile.classname"
                v-bind:username="profile.username"
              ></omegaup-user-username>
            </div>
            <div class="mb-3">
              <h4 class="m-0">
                {{ profile.rankinfo.rank &gt; 0 ? `#${profile.rankinfo.rank}`:
                    '' }}
              </h4>
              <p>
                <small>
                  {{ T.wordsRanking }}
                </small>
              </p>
            </div>
            <div class="mb-3">
              <h4 class="m-0">
                {{ Object.keys(this.solvedProblems).length }}
              </h4>
              <p>
                <small>{{ T.profileSolvedProblems }}</small>
              </p>
            </div>
            <div class="mb-3">
              <h5 class="m-0">
                {{
                  this.programmingLanguages[
                    this.profile.preferred_language
                  ].split(' ')[0]
                }}
              </h5>
              <p><small>Lenguaje de programación</small></p>
            </div>

            <!--<p class="mb-0">{{ profile.email.split('@')[0] }}</p>
            <p>@{{ profile.email.split('@')[1] }}</p>
            <p>{{ profile.school }}</p>-->
          </div>
          <div class="mb-3 text-center" v-if="profile.email">
            <a class="btn btn-primary btn-sm" href="/profile/edit/">{{
              T.profileEdit
            }}</a>
          </div>
        </div>
      </div>
      <div class="col-md-10">
        <div class="card">
          <div class="card-header">
            <!--<nav>-->
            <ul class="nav nav-tabs" role="tablist">
              <li v-on:click="selectedTab = 'badges'">
                <a class="nav-item nav-link active" data-toggle="tab">
                  {{ T.wordsBadgesObtained }}
                  <span class="badge badge-secondary">
                    {{ this.profileBadges.size }}
                  </span>
                </a>
              </li>
              <li v-on:click="selectedTab = 'problems'">
                <a class="nav-item nav-link" data-toggle="tab">{{
                  T.wordsProblems
                }}</a>
              </li>
              <li v-on:click="selectedTab = 'contests'">
                <a class="nav-item nav-link" data-toggle="tab">
                  {{ T.profileContests }}
                  <span class="badge badge-secondary">
                    {{ Object.keys(contests).length }}
                  </span>
                </a>
              </li>
              <li v-on:click="selectedTab = 'data'">
                <a class="nav-item nav-link" data-toggle="tab">{{
                  T.wordsPersonalData
                }}</a>
              </li>
              <li v-on:click="selectedTab = 'charts'">
                <a class="nav-item nav-link" data-toggle="tab">{{
                  T.wordsStatistics
                }}</a>
              </li>
            </ul>
            <!--</nav>-->
            <div class="tab-content">
              <div
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-badges-tab"
              >
                <omegaup-badge-list
                  v-bind:all-badges="profileBadges"
                  v-bind:show-all-badges-link="true"
                  v-bind:visitor-badges="visitorBadges"
                  v-if="selectedTab == 'badges'"
                ></omegaup-badge-list>
              </div>
              <div
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-problems-tab"
                v-if="selectedTab == 'problems'"
              >
                <omegaup-grid-paginator
                  v-bind:columns="3"
                  v-bind:items="solvedProblems"
                  v-bind:items-per-page="30"
                  v-bind:title="T.profileSolvedProblems"
                  class="mb-3"
                ></omegaup-grid-paginator>
                <omegaup-grid-paginator
                  v-bind:columns="3"
                  v-bind:items="unsolvedProblems"
                  v-bind:items-per-page="30"
                  v-bind:title="T.profileUnsolvedProblems"
                  class="mb-3"
                ></omegaup-grid-paginator>
                <omegaup-grid-paginator
                  v-bind:columns="3"
                  v-bind:items="createdProblems"
                  v-bind:items-per-page="30"
                  v-bind:title="T.profileCreatedProblems"
                  class="mb-3"
                ></omegaup-grid-paginator>
              </div>
              <div
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-contests-tab"
                v-if="selectedTab == 'contests'"
              >
                <omegaup-grid-paginator
                  v-bind:columns="1"
                  v-bind:items="contests"
                  v-bind:items-per-page="15"
                >
                  <template slot="table-header">
                    <thead>
                      <tr>
                        <th>{{ T.profileContestsTableContest }}</th>
                        <th class="numericColumn">
                          {{ T.profileContestsTablePlace }}
                        </th>
                      </tr>
                    </thead>
                  </template>
                </omegaup-grid-paginator>
              </div>
              <div
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-user-info-tab"
                v-if="selectedTab == 'data'"
              >
                <omegaup-user-basicinfo
                  v-bind:profile="profile"
                  v-bind:rank="rank"
                ></omegaup-user-basicinfo>
              </div>
              <div
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-charts-tab"
                v-if="selectedTab == 'charts'"
              >
                <omegaup-user-charts
                  v-bind:data="charts"
                  v-bind:username="profile.username"
                  v-bind:periodStatisticOptions="this.periodStatisticOptions"
                  v-on:emit-update-period-statistics="
                    (profileComponent, categories, data) =>
                      $emit(
                        'update-period-statistics',
                        profileComponent,
                        categories,
                        data,
                      )
                  "
                  v-bind:aggregateStatisticOptions="
                    this.aggregateStatisticOptions
                  "
                  v-on:emit-update-aggregate-statistics="
                    profileComponent =>
                      $emit('update-aggregate-statistics', profileComponent)
                  "
                  v-if="charts"
                ></omegaup-user-charts>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
a:hover {
  cursor: pointer;
}
.card-header {
  font-size: 1rem !important;
}
th.numericColumn {
  text-align: right;
}
* {
  font-size: 1rem;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { Chart } from 'highcharts-vue';
import user_BasicInfo from './BasicInfov2.vue';
import user_Username from './Username.vue';
import user_Charts from './Chartsv2.vue';
import badge_List from '../badge/List.vue';
import gridPaginator from '../common/GridPaginator.vue';
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
  @Prop() profileBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;
  @Prop() periodStatisticOptions!: Chart;
  @Prop() aggregateStatisticOptions!: Chart;
  @Prop() programmingLanguages!: any;
  T = T;
  columns = 3;
  selectedTab = 'badges';
}
</script>
