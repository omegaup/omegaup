<template>
  <div class="container-fluid p-0 mt-0" data-user-profile-root>
    <h1 v-if="!profile.is_own_profile && profile.is_private">
      {{ ui.info(T.userProfileIsPrivate) }}
    </h1>
    <div class="row">
      <div class="col-md-2">
        <div class="card">
          <omegaup-countryflag
            v-if="profile.country_id"
            class="m-1"
            :country="profile.country_id"
          />
          <div class="card-body">
            <div class="img-thumbnail rounded-circle bottom-margin">
              <img class="rounded-circle" :src="profile.gravatar_92" />
            </div>
          </div>
          <div class="card-title text-center">
            <div class="mb-3">
              <omegaup-user-username
                :classname="profile.classname"
                :username="profile.username"
              ></omegaup-user-username>
            </div>
            <div class="mb-3">
              <h4 v-if="profile.rankinfo.rank > 0" class="m-0">
                {{ `#${profile.rankinfo.rank}` }}
              </h4>
              <small v-else>
                <strong> {{ rank }} </strong>
              </small>
              <p>
                <small>
                  {{ T.profileRank }}
                </small>
              </p>
            </div>
            <div
              v-if="profile.is_own_profile || !profile.is_private"
              class="mb-3"
            >
              <h4 class="m-0">
                {{ Object.keys(solvedProblems).length }}
              </h4>
              <p>
                <small>{{ T.profileSolvedProblems }}</small>
              </p>
            </div>
            <div
              v-if="
                profile.preferred_language &&
                (profile.is_own_profile || !profile.is_private)
              "
              class="mb-3"
            >
              <h5 class="m-0">
                {{
                  profile.programming_languages[
                    profile.preferred_language
                  ].split(' ')[0]
                }}
              </h5>
              <p>
                <small>{{ T.userEditPreferredProgrammingLanguage }}</small>
              </p>
            </div>
          </div>
          <div v-if="profile.is_own_profile" class="mb-3 text-center">
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
                        <th class="numericColumn">
                          {{ T.profileContestsTablePlace }}
                        </th>
                      </tr>
                    </thead>
                  </template>
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
    'omegaup-badge-list': badge_List,
    'omegaup-grid-paginator': common_GridPaginator,
    'omegaup-countryflag': country_Flag,
  },
})
export default class UserProfile extends Vue {
  @Prop() data!: types.ExtraProfileDetails | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop() profileBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;
  contests = this.data
    ? this.data.contests
      ? Object.values(this.data.contests)
          .map((contest) => {
            const now = new Date();
            if (contest.place === null || now <= contest.data.finish_time) {
              return null;
            }
            return new ContestResult(contest);
          })
          .filter((contest) => !!contest)
      : []
    : [];
  charts = this.data ? this.data.stats : [];
  T = T;
  ui = ui;
  columns = 3;
  selectedTab =
    !this.profile.is_own_profile && this.profile.is_private ? 'data' : 'badges';
  normalizedRunCounts: Highcharts.PointOptionsObject[] = [];

  get createdProblems(): Problem[] {
    if (!this.data) return [];
    if (!this.data.createdProblems) return [];
    return this.data.createdProblems.map((problem) => new Problem(problem));
  }
  get unsolvedProblems(): Problem[] {
    if (!this.data) return [];
    if (!this.data.unsolvedProblems) return [];
    return this.data.unsolvedProblems.map((problem) => new Problem(problem));
  }
  get solvedProblems(): Problem[] {
    if (!this.data) return [];
    if (!this.data.solvedProblems) return [];
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

th.numericColumn {
  text-align: right;
}

[data-user-profile-root] {
  font-size: 1rem;
}
</style>
