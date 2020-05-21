<template>
  <div class="card contest-list">
    <div class="card-header">
      <h1>{{ T.arenaPageTitle }}</h1>
      <p>{{ T.arenaPageIntroduction }}</p>
      <div class="row">
        <div class="col-md-6">
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
    </div>

    <div class="card-body">
      <ul class="nav nav-pills nav-fill arena-tabs">
        <li
          class="nav-item"
          v-if="isLogged"
          v-on:click="showTab = 'participating'"
        >
          <a
            class="nav-link tab-participating"
            v-bind:class="{ active: showTab === 'participating' }"
            data-toggle="tab"
          >
            {{ T.arenaMyActiveContests }}</a
          >
        </li>
        <li class="nav-item" v-on:click="showTab = 'recommended_current'">
          <a
            class="nav-link tab-recommended-current"
            v-bind:class="{ active: showTab === 'recommended_current' }"
            data-toggle="tab"
          >
            {{ T.arenaRecommendedCurrentContests }}</a
          >
        </li>
        <li class="nav-item" v-on:click="showTab = 'current'">
          <a
            class="nav-link tab-current"
            v-bind:class="{ active: showTab === 'current' }"
            data-toggle="tab"
          >
            {{ T.arenaCurrentContests }}</a
          >
        </li>
        <li class="nav-item" v-on:click="showTab = 'public'">
          <a
            class="nav-link tab-public"
            v-bind:class="{ active: showTab === 'public' }"
            data-toggle="tab"
          >
            {{ T.arenaCurrentPublicContests }}</a
          >
        </li>
        <li class="nav-item" v-on:click="showTab = 'future'">
          <a
            class="nav-link tab-future"
            v-bind:class="{ active: showTab === 'future' }"
            data-toggle="tab"
          >
            {{ T.arenaFutureContests }}</a
          >
        </li>
        <li class="nav-item" v-on:click="showTab = 'recommended_past'">
          <a
            class="nav-link tab-recommended-past"
            v-bind:class="{ active: showTab === 'recommended_past' }"
            data-toggle="tab"
          >
            {{ T.arenaRecommendedOldContests }}</a
          >
        </li>
        <li class="nav-item" v-on:click="showTab = 'past'">
          <a
            class="nav-link tab-past"
            v-bind:class="{ active: showTab === 'past' }"
            data-toggle="tab"
          >
            {{ T.arenaOldContests }}</a
          >
        </li>
      </ul>

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
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
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
