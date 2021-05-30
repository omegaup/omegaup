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
                  :class="{ active: showTab === 'participating' }"
                  data-toggle="tab"
                  data-list-participating
                  href="#"
                  @click="showTab = 'participating'"
                >
                  {{ T.arenaMyActiveContests }}</a
                >
                <a
                  class="dropdown-item tab-recommended-current"
                  :class="{ active: showTab === 'recommended_current' }"
                  data-toggle="tab"
                  data-list-recommended-current
                  href="#"
                  @click="showTab = 'recommended_current'"
                >
                  {{ T.arenaRecommendedCurrentContests }}</a
                >
                <a
                  class="dropdown-item tab-current"
                  :class="{ active: showTab === 'current' }"
                  data-toggle="tab"
                  data-list-current
                  href="#"
                  @click="showTab = 'current'"
                >
                  {{ T.arenaCurrentContests }}</a
                >
                <a
                  class="dropdown-item tab-public"
                  :class="{ active: showTab === 'public' }"
                  data-toggle="tab"
                  data-list-public
                  href="#"
                  @click="showTab = 'public'"
                >
                  {{ T.arenaCurrentPublicContests }}</a
                >
                <a
                  class="dropdown-item tab-future"
                  :class="{ active: showTab === 'future' }"
                  data-toggle="tab"
                  data-list-future
                  href="#"
                  @click="showTab = 'future'"
                >
                  {{ T.arenaFutureContests }}</a
                >
                <a
                  class="dropdown-item tab-recommended-past"
                  :class="{ active: showTab === 'recommended_past' }"
                  data-toggle="tab"
                  data-list-recommended-past
                  href="#"
                  @click="showTab = 'recommended_past'"
                >
                  {{ T.arenaRecommendedOldContests }}</a
                >
                <a
                  class="dropdown-item tab-past"
                  :class="{ active: showTab === 'past' }"
                  data-toggle="tab"
                  data-list-past
                  href="#"
                  @click="showTab = 'past'"
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
          v-if="showTab === 'participating'"
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
          v-if="showTab === 'recommended_current'"
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
        <div v-if="showTab === 'current'" class="tab-pane active list-current">
          <omegaup-contest-filtered-list
            :contests="contests.current"
            :show-times="true"
            :show-practice="false"
            :show-virtual="false"
            :show-public-updated="false"
            :recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
        <div v-if="showTab === 'public'" class="tab-pane active list-public">
          <omegaup-contest-filtered-list
            :contests="contests.public"
            :show-times="true"
            :show-practice="false"
            :show-virtual="false"
            :show-public-updated="true"
            :recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
        <div v-if="showTab === 'future'" class="tab-pane active list-future">
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
          v-if="showTab === 'recommended_past'"
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
        <div v-if="showTab === 'past'" class="tab-pane active list-past">
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
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import contest_FilteredList from '../contest/FilteredList.vue';

@Component({
  components: {
    'omegaup-contest-filtered-list': contest_FilteredList,
  },
})
export default class ArenaContestList extends Vue {
  @Prop() initialQuery!: string;
  @Prop() contests!: omegaup.ArenaContests;
  @Prop() isLogged!: boolean;

  T = T;
  showTab = '';
  query = this.initialQuery;

  get activeTab(): string {
    switch (this.showTab) {
      case 'participating':
        return T.arenaMyActiveContests;
      case 'recommended_current':
        return T.arenaRecommendedCurrentContests;
      case 'current':
        return T.arenaCurrentContests;
      case 'public':
        return T.arenaCurrentPublicContests;
      case 'future':
        return T.arenaFutureContests;
      case 'recommended_past':
        return T.arenaRecommendedOldContests;
      case 'past':
        return T.arenaOldContests;
      default:
        return T.arenaMyActiveContests;
    }
  }

  mounted() {
    for (const [timeType, contests] of Object.entries(this.contests)) {
      if (contests.length > 0) {
        this.showTab = timeType;
        break;
      }
    }
  }
}
</script>
