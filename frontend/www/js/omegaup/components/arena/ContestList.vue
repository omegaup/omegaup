<template>
  <div class="panel contest-list">
    <div class="panel-heading panel-default">
      <div class="text-right">
        <form action="/arena/" method="GET">
          <div class="form-inline">
            <div class="form-group">
              <input
                class="form-control"
                type="text"
                name="query"
                autocomplete="off"
                v-model="query"
                v-bind:placeholder="T.wordsKeyword"
              />
            </div>
            <input
              class="btn btn-primary btn-lg active"
              type="submit"
              v-bind:value="T.wordsSearch"
            />
          </div>
        </form>
      </div>
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

    <div class="panel-body">
      <ul class="nav nav-pills arena-tabs">
        <li
          class="nav-item"
          v-bind:class="{ active: showTab === 'participating' }"
          v-if="isLogged"
          v-on:click="showTab = 'participating'"
        >
          <a class="nav-link tab-participating" data-toggle="tab">
            {{ T.arenaMyActiveContests }}</a
          >
        </li>
        <li
          class="nav-item"
          v-bind:class="{ active: showTab === 'recommended_current' }"
          v-on:click="showTab = 'recommended_current'"
        >
          <a class="nav-link tab-recommended-current" data-toggle="tab">
            {{ T.arenaRecommendedCurrentContests }}</a
          >
        </li>
        <li
          class="nav-item"
          v-bind:class="{ active: showTab === 'current' }"
          v-on:click="showTab = 'current'"
        >
          <a class="nav-link tab-current" data-toggle="tab">
            {{ T.arenaCurrentContests }}</a
          >
        </li>
        <li
          class="nav-item"
          v-bind:class="{ active: showTab === 'public' }"
          v-on:click="showTab = 'public'"
        >
          <a class="nav-link tab-public" data-toggle="tab">
            {{ T.arenaCurrentPublicContests }}</a
          >
        </li>
        <li
          class="nav-item"
          v-bind:class="{ active: showTab === 'future' }"
          v-on:click="showTab = 'future'"
        >
          <a class="nav-link tab-future" data-toggle="tab">
            {{ T.arenaFutureContests }}</a
          >
        </li>
        <li
          class="nav-item"
          v-bind:class="{ active: showTab === 'recommended_past' }"
          v-on:click="showTab = 'recommended_past'"
        >
          <a class="nav-link tab-recommended-past" data-toggle="tab">
            {{ T.arenaRecommendedOldContests }}</a
          >
        </li>
        <li
          class="nav-item"
          v-bind:class="{ active: showTab === 'past' }"
          v-on:click="showTab = 'past'"
        >
          <a class="nav-link tab-past" data-toggle="tab">
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
            v-bind:recommended="true"
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

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
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
