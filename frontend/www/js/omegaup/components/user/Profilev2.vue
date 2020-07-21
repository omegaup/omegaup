<template>
  <div class="container-fluid p-0 mt-0" data-user-profile-root>
    <div class="row">
      <div class="col-md-2">
        <div class="card">
          <omegaup-countryflag
            class="m-1"
            v-bind:country="profile.country_id"
            v-if="profile.country_id"
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
                {{
                  profile.rankinfo.rank > 0 ? `#${profile.rankinfo.rank}` : ''
                }}
              </h4>
              <p>
                <small>
                  {{ T.wordsRanking }}
                </small>
              </p>
            </div>
            <div class="mb-3">
              <h4 class="m-0">
                {{ Object.keys(solvedProblems).length }}
              </h4>
              <p>
                <small>{{ T.profileSolvedProblems }}</small>
              </p>
            </div>
            <div class="mb-3">
              <h5 class="m-0">
                {{
                  profile.programming_languages[
                    profile.preferred_language
                  ].split(' ')[0]
                }}
              </h5>
              <p>
                <small>{{ T.wordsProgrammingLanguage }}</small>
              </p>
            </div>
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
            <nav class="nav nav-tabs" role="tablist">
              <a
                class="nav-item nav-link active"
                data-toggle="tab"
                v-on:click="selectedTab = 'badges'"
              >
                {{ T.wordsBadgesObtained }}
                <span class="badge badge-secondary">
                  {{ profileBadges.size }}
                </span>
              </a>
              <a
                class="nav-item nav-link"
                data-toggle="tab"
                v-on:click="selectedTab = 'problems'"
                >{{ T.wordsProblems }}</a
              >
              <a
                class="nav-item nav-link"
                data-toggle="tab"
                v-on:click="selectedTab = 'contests'"
              >
                {{ T.profileContests }}
                <span class="badge badge-secondary">
                  {{ Object.keys(contests).length }}
                </span>
              </a>
              <a
                class="nav-item nav-link"
                data-toggle="tab"
                v-on:click="selectedTab = 'data'"
                >{{ T.wordsPersonalData }}</a
              >
              <a
                class="nav-item nav-link"
                data-toggle="tab"
                v-on:click="selectedTab = 'charts'"
                >{{ T.wordsStatistics }}</a
              >
            </nav>
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
                  v-bind:periodStatisticOptions="periodStatisticOptions"
                  v-on:emit-update-period-statistics="
                    (profileComponent, categories, data) =>
                      onUpdatePeriodStatistics(
                        profileComponent,
                        categories,
                        data,
                      )
                  "
                  v-bind:aggregateStatisticOptions="aggregateStatisticOptions"
                  v-on:emit-update-aggregate-statistics="
                    (profileComponent) =>
                      onAggregateStatistics(profileComponent)
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

<style lang="scss" scoped>
a:hover {
  cursor: pointer;
}
th.numericColumn {
  text-align: right;
}
[data-user-profile-root] {
  font-size: 1rem;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { Chart } from 'highcharts-vue';
import country_Flag from '../CountryFlag.vue';
import user_BasicInfo from './BasicInfov2.vue';
import user_Username from './Username.vue';
import user_Charts from './Chartsv2.vue';
import badge_List from '../badge/List.vue';
import common_GridPaginator from '../common/GridPaginator.vue';
import { types } from '../../api_types';
import * as ui from '../../ui';
import { Problem, ContestResult } from '../../linkable_resource';

@Component({
  components: {
    'omegaup-user-basicinfo': user_BasicInfo,
    'omegaup-user-username': user_Username,
    'omegaup-user-charts': user_Charts,
    'omegaup-badge-list': badge_List,
    'omegaup-grid-paginator': common_GridPaginator,
    'omegaup-countryflag': country_Flag,
  },
})
export default class UserProfile extends Vue {
  @Prop() data!: types.UserProfileDetailsPayload;
  @Prop() profileBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;
  profile = this.data.profile;
  contests = this.data.contests
    ? Object.values(this.data.contests)
        .map((contest) => {
          const now = new Date();
          if (contest.place === null || now <= contest.data.finish_time) {
            return null;
          }
          return new ContestResult(contest);
        })
        .filter((contest) => !!contest)
    : [];
  charts = this.data.stats;
  T = T;
  columns = 3;
  selectedTab = 'badges';
  get createdProblems(): Problem[] {
    if (!this.data.createdProblems) return [];
    return this.data.createdProblems.map((problem) => new Problem(problem));
  }
  get unsolvedProblems(): Problem[] {
    if (!this.data.unsolvedProblems) return [];
    return this.data.unsolvedProblems.map((problem) => new Problem(problem));
  }
  get solvedProblems(): Problem[] {
    if (!this.data.solvedProblems) return [];
    return this.data.solvedProblems.map((problem) => new Problem(problem));
  }
  get rank(): string {
    switch (this.data.profile?.classname) {
      case 'user-rank-beginner':
        return T.profileRankBeginner;
      case 'user-rank-specialist':
        return T.profileRankSpecialist;
      case 'user-rank-expert':
        return T.profileRankExpert;
      case 'user-rank-master':
        return T.profileRankMaster;
      case 'user-rank-international-master':
        return T.profileRankInternationalMaster;
      default:
        return T.profileRankUnrated;
    }
  }

  get periodStatisticOptions(): any {
    return {
      title: {
        text: ui.formatString(T.profileStatisticsVerdictsOf, {
          user: this.data.profile?.username,
        }),
      },
      chart: { type: 'column' },
      xAxis: {
        categories: [],
        title: { text: T.profileStatisticsPeriod },
        labels: {
          rotation: -45,
        },
      },
      yAxis: {
        min: 0,
        title: { text: T.profileStatisticsNumberOfSolvedProblems },
        stackLabels: {
          enabled: false,
          style: {
            fontWeight: 'bold',
            color: 'gray',
          },
        },
      },
      legend: {
        align: 'right',
        x: -30,
        verticalAlign: 'top',
        y: 25,
        floating: true,
        backgroundColor: 'white',
        borderColor: '#CCC',
        borderWidth: 1,
        shadow: false,
      },
      tooltip: {
        headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}',
      },
      plotOptions: {
        column: {
          stacking: 'normal',
          dataLabels: {
            enabled: false,
            color: 'white',
          },
        },
      },
      series: [],
    };
  }
  get aggregateStatisticOptions(): any {
    return {
      title: {
        text: ui.formatString(T.profileStatisticsVerdictsOf, {
          user: this.data.profile?.username,
        }),
      },
      chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie',
      },
      xAxis: {
        title: { text: '' },
      },
      yAxis: {
        title: { text: '' },
      },
      tooltip: { pointFormat: '{series.name}: {point.y}' },
      plotOptions: {
        pie: {
          allowPointSelect: true,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            color: '#000000',
            connectorColor: '#000000',
            format: '<b>{point.name}</b>: {point.percentage:.1f} % ({point.y})',
          },
        },
      },
      series: [
        {
          name: T.profileStatisticsRuns,
          data: [],
        },
      ],
    };
  }
  onUpdatePeriodStatistics(
    e: user_Charts,
    categories: string[],
    data: omegaup.RunData[],
  ) {
    e.periodStatisticOptions.xAxis.categories = categories;
    e.periodStatisticOptions.series = data;
  }
  onAggregateStatistics(e: user_Charts) {
    e.aggregateStatisticOptions.series[0].data = e.normalizedRunCounts;
  }
}
</script>
