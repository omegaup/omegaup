<template>
  <div class="card contest-list">
    <div class="card-header">
      <h1>{{ T.arenaPageTitle }}</h1>
      <p>{{ T.arenaPageIntroduction }}</p>
      <p>
        {{ T.frontPageIntroduction }}
        <a
          href="http://blog.omegaup.com/category/omegaup/omegaup-101/"
          target="_blank"
        >
          <small
            ><u>{{ T.frontPageIntroductionButton }}</u></small
          ></a
        >
      </p>
    </div>

    <div class="card-body">
      <div class="row">
        <div class="col-6">
          <ul class="nav nav-pills arena-tabs">
            <li class="nav-item dropdown">
              <a
                class="nav-link active dropdown-toggle"
                data-toggle="dropdown"
                data-contests
                href="#"
                role="button"
                aria-haspopup="true"
                aria-expanded="false"
                >{{ activeTab }}</a
              >
              <div class="dropdown-menu">
                <a
                  v-if="isLogged"
                  class="dropdown-item tab-participating"
                  :class="{ active: showTab === ContestsTab.Participating }"
                  data-toggle="tab"
                  data-list-participating
                  href="#"
                  @click="showTab = ContestsTab.Participating"
                >
                  {{ T.arenaMyActiveContests }}</a
                >
                <a
                  class="dropdown-item tab-recommended-current"
                  :class="{
                    active: showTab === ContestsTab.RecommendedCurrent,
                  }"
                  data-toggle="tab"
                  data-list-recommended-current
                  href="#"
                  @click="showTab = ContestsTab.RecommendedCurrent"
                >
                  {{ T.arenaRecommendedCurrentContests }}</a
                >
                <a
                  class="dropdown-item tab-current"
                  :class="{ active: showTab === ContestsTab.Current }"
                  data-toggle="tab"
                  data-list-current
                  href="#"
                  @click="showTab = ContestsTab.Current"
                >
                  {{ T.arenaCurrentContests }}</a
                >
                <a
                  class="dropdown-item tab-public"
                  :class="{ active: showTab === ContestsTab.Public }"
                  data-toggle="tab"
                  data-list-public
                  href="#"
                  @click="showTab = ContestsTab.Public"
                >
                  {{ T.arenaCurrentPublicContests }}</a
                >
                <a
                  class="dropdown-item tab-future"
                  :class="{ active: showTab === ContestsTab.Future }"
                  data-toggle="tab"
                  data-list-future
                  href="#"
                  @click="showTab = ContestsTab.Future"
                >
                  {{ T.arenaFutureContests }}</a
                >
                <a
                  class="dropdown-item tab-recommended-past"
                  :class="{ active: showTab === ContestsTab.RecommendedPast }"
                  data-toggle="tab"
                  data-list-recommended-past
                  href="#"
                  @click="showTab = ContestsTab.RecommendedPast"
                >
                  {{ T.arenaRecommendedOldContests }}</a
                >
                <a
                  class="dropdown-item tab-past"
                  :class="{ active: showTab === ContestsTab.Past }"
                  data-toggle="tab"
                  data-list-past
                  href="#"
                  @click="showTab = ContestsTab.Past"
                >
                  {{ T.arenaOldContests }}</a
                >
              </div>
            </li>
          </ul>
        </div>
        <div class="col-md-6">
          <form action="/arena/" method="GET">
            <div class="input-group">
              <input
                v-model="query"
                class="form-control"
                type="text"
                name="query"
                autocomplete="off"
                :placeholder="T.wordsKeyword"
              />
              <div class="input-group-append">
                <input
                  class="btn btn-primary btn-md active"
                  type="submit"
                  :value="T.wordsSearch"
                />
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="tab-content">
        <div
          v-if="showTab === ContestsTab.Participating"
          class="tab-pane active list-participating"
        >
          <omegaup-contest-filtered-list
            :contests="contests.participating"
            :show-times="true"
            :show-practice="false"
            :show-virtual="false"
            :show-public-updated="false"
            :recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
        <div
          v-if="showTab === ContestsTab.RecommendedCurrent"
          class="tab-pane active list-recommended-current"
        >
          <omegaup-contest-filtered-list
            :contests="contests.recommended_current"
            :show-times="true"
            :show-practice="false"
            :show-virtual="false"
            :show-public-updated="false"
            :recommended="true"
          ></omegaup-contest-filtered-list>
        </div>
        <div
          v-if="showTab === ContestsTab.Current"
          class="tab-pane active list-current"
        >
          <omegaup-contest-filtered-list
            :contests="contests.current"
            :show-times="true"
            :show-practice="false"
            :show-virtual="false"
            :show-public-updated="false"
            :recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
        <div
          v-if="showTab === ContestsTab.Public"
          class="tab-pane active list-public"
        >
          <omegaup-contest-filtered-list
            :contests="contests.public"
            :show-times="true"
            :show-practice="false"
            :show-virtual="false"
            :show-public-updated="true"
            :recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
        <div
          v-if="showTab === ContestsTab.Future"
          class="tab-pane active list-future"
        >
          <omegaup-contest-filtered-list
            :contests="contests.future"
            :show-times="true"
            :show-practice="false"
            :show-virtual="false"
            :show-public-updated="false"
            :recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
        <div
          v-if="showTab === ContestsTab.RecommendedPast"
          class="tab-pane active list-recommended-past"
        >
          <omegaup-contest-filtered-list
            :contests="contests.recommended_past"
            :show-times="false"
            :show-practice="true"
            :show-virtual="true"
            :show-public-updated="false"
            :recommended="true"
          ></omegaup-contest-filtered-list>
        </div>
        <div
          v-if="showTab === ContestsTab.Past"
          class="tab-pane active list-past"
        >
          <omegaup-contest-filtered-list
            :contests="contests.past"
            :show-times="false"
            :show-practice="true"
            :show-virtual="true"
            :show-public-updated="false"
            :recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import contest_FilteredList from '../contest/FilteredList.vue';
import { types } from '../../api_types';
import * as UI from '../../ui';

export enum ContestsTab {
  Participating = 'participating',
  RecommendedCurrent = 'recommended_current',
  Current = 'current',
  Public = 'public',
  Future = 'future',
  RecommendedPast = 'recommended_past',
  Past = 'past',
}

@Component({
  components: {
    'omegaup-contest-filtered-list': contest_FilteredList,
  },
})
export default class ArenaContestList extends Vue {
  @Prop({ default: null }) initialQuery!: null | string;
  @Prop() contests!: types.TimeTypeContests;
  @Prop() isLogged!: boolean;
  @Prop({ default: null }) selectedTab!: ContestsTab | null;

  T = T;
  ContestsTab = ContestsTab;
  showTab = this.selectedTab ?? ContestsTab.Participating;
  query = this.initialQuery;

  get activeTab(): string {
    switch (this.showTab) {
      case ContestsTab.Participating:
        return T.arenaMyActiveContests;
      case ContestsTab.RecommendedCurrent:
        return T.arenaRecommendedCurrentContests;
      case ContestsTab.Current:
        return T.arenaCurrentContests;
      case ContestsTab.Public:
        return T.arenaCurrentPublicContests;
      case ContestsTab.Future:
        return T.arenaFutureContests;
      case ContestsTab.RecommendedPast:
        return T.arenaRecommendedOldContests;
      case ContestsTab.Past:
        return T.arenaOldContests;
      default:
        return T.arenaMyActiveContests;
    }
  }

  @Watch('showTab')
  onTabChanged(newTab: string) {
    UI.reportPageView(location.pathname + '#' + newTab);
  }
}
</script>
