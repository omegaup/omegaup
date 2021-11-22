<template>
  <div class="container-fluid p-0 mt-0">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <nav class="nav nav-tabs" role="tablist" data-profile-navtabs>
              <a
                v-if="profile.is_own_profile || !profile.is_private"
                class="nav-item nav-link active"
                data-toggle="tab"
                @click="selectedTab = 'badges'"
              >
                {{ T.wordsBadgesObtained }}
                <span class="badge badge-secondary">
                  {{ profileBadges.size }}
                </span>
              </a>
              <a
                v-if="profile.is_own_profile || !profile.is_private"
                class="nav-item nav-link"
                data-toggle="tab"
                @click="selectedTab = 'problems'"
                >{{ T.wordsProblems }}</a
              >
              <a
                v-if="profile.is_own_profile || !profile.is_private"
                class="nav-item nav-link"
                data-toggle="tab"
                @click="selectedTab = 'contests'"
              >
                {{ T.profileContests }}
                <span class="badge badge-secondary">
                  {{ Object.keys(contests).length }}
                </span>
              </a>
              <a
                v-if="profile.is_own_profile || !profile.is_private"
                class="nav-item nav-link"
                data-toggle="tab"
                @click="selectedTab = 'created-content'"
                >{{ T.profileCreatedContent }}</a
              >
              <a
                class="nav-item nav-link"
                data-toggle="tab"
                @click="selectedTab = 'data'"
                >{{ T.profilePersonalData }}</a
              >
              <a
                v-if="profile.is_own_profile || !profile.is_private"
                class="nav-item nav-link"
                data-toggle="tab"
                @click="selectedTab = 'charts'"
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
                  v-if="selectedTab == 'badges'"
                  :all-badges="profileBadges"
                  :show-all-badges-link="true"
                  :visitor-badges="visitorBadges"
                ></omegaup-badge-list>
              </div>
              <div
                v-if="selectedTab == 'problems'"
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-problems-tab"
              >
                <omegaup-grid-paginator
                  :columns="3"
                  :items="solvedProblems"
                  :items-per-page="30"
                  :title="T.profileSolvedProblems"
                  class="mb-3"
                ></omegaup-grid-paginator>
                <omegaup-grid-paginator
                  :columns="3"
                  :items="unsolvedProblems"
                  :items-per-page="30"
                  :title="T.profileUnsolvedProblems"
                  class="mb-3"
                ></omegaup-grid-paginator>
                <omegaup-grid-paginator
                  :columns="3"
                  :items="createdProblems"
                  :items-per-page="30"
                  :title="T.profileCreatedProblems"
                  class="mb-3"
                ></omegaup-grid-paginator>
              </div>
              <div
                v-show="selectedTab == 'contests'"
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-contests-tab"
              >
                <omegaup-grid-paginator
                  :columns="1"
                  :items="contests"
                  :items-per-page="15"
                >
                  <template #table-header>
                    <thead>
                      <tr>
                        <th>{{ T.profileContestsTableContest }}</th>
                        <th class="text-right">
                          {{ T.profileContestsTablePlace }}
                        </th>
                      </tr>
                    </thead>
                  </template>
                </omegaup-grid-paginator>
              </div>
              <div
                v-if="selectedTab == 'created-content'"
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-created-content-tab"
              >
                <omegaup-grid-paginator
                  :columns="3"
                  :items="createdProblems"
                  :items-per-page="30"
                  :title="T.profileCreatedProblems"
                  class="mb-3"
                >
                  <template v-if="profile.is_own_profile" #header-link
                    ><a href="/problem/mine/" class="float-right">{{
                      T.profileCreatedContentSeeAll
                    }}</a></template
                  >
                </omegaup-grid-paginator>
              </div>
              <div
                v-if="selectedTab == 'data'"
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-user-info-tab"
              >
                <omegaup-user-basicinfo
                  :profile="profile"
                  :rank="rank"
                ></omegaup-user-basicinfo>
              </div>
              <div
                v-if="selectedTab == 'charts'"
                class="tab-pane fade show active"
                role="tab"
                aria-labelledby="nav-charts-tab"
              >
                <omegaup-user-charts
                  v-if="charts"
                  :data="charts"
                  :username="profile.username"
                ></omegaup-user-charts>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import country_Flag from '../CountryFlag.vue';
import user_BasicInfo from './BasicInfov2.vue';
import user_Username from './Username.vue';
import user_Charts from './Chartsv2.vue';
import user_MainInfo from './MainInfo.vue';
import badge_List from '../badge/List.vue';
import common_GridPaginator from '../common/GridPaginator.vue';
import { types } from '../../api_types';
import * as Highcharts from 'highcharts/highstock';
import * as ui from '../../ui';
import { Problem, ContestResult } from '../../linkable_resource';

@Component({
  components: {
    'omegaup-user-basicinfo': user_BasicInfo,
    'omegaup-user-username': user_Username,
    'omegaup-user-charts': user_Charts,
    'omegaup-user-maininfo': user_MainInfo,
    'omegaup-badge-list': badge_List,
    'omegaup-grid-paginator': common_GridPaginator,
    'omegaup-countryflag': country_Flag,
  },
})
export default class ViewProfile extends Vue {
  @Prop() data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop() profileBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;
  contests = Object.values(
    this.data?.contests ?? ({} as types.UserProfileContests),
  )
    .map((contest) => {
      const now = new Date();
      if (contest.place === null || now <= contest.data.finish_time) {
        return null;
      }
      return new ContestResult(contest);
    })
    .filter((contest) => Boolean(contest));
  charts: types.UserProfileStats[] = this.data?.stats ?? [];
  T = T;
  ui = ui;
  columns = 3;
  selectedTab =
    !this.profile.is_own_profile && this.profile.is_private ? 'data' : 'badges';
  normalizedRunCounts: Highcharts.PointOptionsObject[] = [];

  get createdProblems(): Problem[] {
    if (!this.data?.createdProblems) return [];
    return this.data.createdProblems.map((problem) => new Problem(problem));
  }
  get unsolvedProblems(): Problem[] {
    if (!this.data?.unsolvedProblems) return [];
    return this.data.unsolvedProblems.map((problem) => new Problem(problem));
  }
  get solvedProblems(): Problem[] {
    if (!this.data?.solvedProblems) return [];
    return this.data.solvedProblems.map((problem) => new Problem(problem));
  }
  get rank(): string {
    switch (this.profile.classname) {
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
}
</script>

<style lang="scss" scoped>
a:hover {
  cursor: pointer;
}
</style>
