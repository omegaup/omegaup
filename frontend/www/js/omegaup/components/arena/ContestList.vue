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
                  class="dropdown-item tab-participating"
                  v-bind:class="{ active: showTab === 'participating' }"
                  v-if="isLogged"
                  v-on:click="showTab = 'participating'"
                  data-toggle="tab"
                  data-list-participating
                  href="#"
                >
                  {{ T.arenaMyActiveContests }}</a
                >
                <a
                  class="dropdown-item tab-recommended-current"
                  v-bind:class="{ active: showTab === 'recommended_current' }"
                  v-on:click="showTab = 'recommended_current'"
                  data-toggle="tab"
                  data-list-recommended-current
                  href="#"
                >
                  {{ T.arenaRecommendedCurrentContests }}</a
                >
                <a
                  class="dropdown-item tab-current"
                  v-bind:class="{ active: showTab === 'current' }"
                  v-on:click="showTab = 'current'"
                  data-toggle="tab"
                  data-list-current
                  href="#"
                >
                  {{ T.arenaCurrentContests }}</a
                >
                <a
                  class="dropdown-item tab-public"
                  v-bind:class="{ active: showTab === 'public' }"
                  v-on:click="showTab = 'public'"
                  data-toggle="tab"
                  data-list-public
                  href="#"
                >
                  {{ T.arenaCurrentPublicContests }}</a
                >
                <a
                  class="dropdown-item tab-future"
                  v-bind:class="{ active: showTab === 'future' }"
                  v-on:click="showTab = 'future'"
                  data-toggle="tab"
                  data-list-future
                  href="#"
                >
                  {{ T.arenaFutureContests }}</a
                >
                <a
                  class="dropdown-item tab-recommended-past"
                  v-bind:class="{ active: showTab === 'recommended_past' }"
                  v-on:click="showTab = 'recommended_past'"
                  data-toggle="tab"
                  data-list-recommended-past
                  href="#"
                >
                  {{ T.arenaRecommendedOldContests }}</a
                >
                <a
                  class="dropdown-item tab-past"
                  v-bind:class="{ active: showTab === 'past' }"
                  v-on:click="showTab = 'past'"
                  data-toggle="tab"
                  data-list-past
                  href="#"
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
                class="form-control"
                type="text"
                name="query"
                autocomplete="off"
                v-model="query"
                v-bind:placeholder="T.wordsKeyword"
              />
              <div class="input-group-append">
                <input
                  class="btn btn-primary btn-md active"
                  type="submit"
                  v-bind:value="T.wordsSearch"
                />
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="tab-content">
        <div
          class="tab-pane active list-participating"
          v-if="showTab === 'participating'"
        >
          <omegaup-contest-filtered-list
            v-bind:contests="contests.participating"
            v-bind:showTimes="true"
            v-bind:showPractice="false"
            v-bind:showVirtual="false"
            v-bind:showPublicUpdated="false"
            v-bind:recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
        <div
          class="tab-pane active list-recommended-current"
          v-if="showTab === 'recommended_current'"
        >
          <omegaup-contest-filtered-list
            v-bind:contests="contests.recommended_current"
            v-bind:showTimes="true"
            v-bind:showPractice="false"
            v-bind:showVirtual="false"
            v-bind:showPublicUpdated="false"
            v-bind:recommended="true"
          ></omegaup-contest-filtered-list>
        </div>
        <div class="tab-pane active list-current" v-if="showTab === 'current'">
          <omegaup-contest-filtered-list
            v-bind:contests="contests.current"
            v-bind:showTimes="true"
            v-bind:showPractice="false"
            v-bind:showVirtual="false"
            v-bind:showPublicUpdated="false"
            v-bind:recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
        <div class="tab-pane active list-public" v-if="showTab === 'public'">
          <omegaup-contest-filtered-list
            v-bind:contests="contests.public"
            v-bind:showTimes="true"
            v-bind:showPractice="false"
            v-bind:showVirtual="false"
            v-bind:showPublicUpdated="true"
            v-bind:recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
        <div class="tab-pane active list-future" v-if="showTab === 'future'">
          <omegaup-contest-filtered-list
            v-bind:contests="contests.future"
            v-bind:showTimes="true"
            v-bind:showPractice="false"
            v-bind:showVirtual="false"
            v-bind:showPublicUpdated="false"
            v-bind:recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
        <div
          class="tab-pane active list-recommended-past"
          v-if="showTab === 'recommended_past'"
        >
          <omegaup-contest-filtered-list
            v-bind:contests="contests.recommended_past"
            v-bind:showTimes="false"
            v-bind:showPractice="true"
            v-bind:showVirtual="true"
            v-bind:showPublicUpdated="false"
            v-bind:recommended="true"
          ></omegaup-contest-filtered-list>
        </div>
        <div class="tab-pane active list-past" v-if="showTab === 'past'">
          <omegaup-contest-filtered-list
            v-bind:contests="contests.past"
            v-bind:showTimes="false"
            v-bind:showPractice="true"
            v-bind:showVirtual="true"
            v-bind:showPublicUpdated="false"
            v-bind:recommended="false"
          ></omegaup-contest-filtered-list>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.empty-category {
  text-align: center;
  font-size: 200%;
  margin: 1em;
  color: #aaa;
}
</style>

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
