<template>
  <div>
    <div class="panel">
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
            v-if="isLogged"
            v-on:click="showTab = 'participating'"
          >
            <a class="nav-link" data-toggle="tab">
              {{ T.arenaMyActiveContests }}</a
            >
          </li>
          <li class="nav-item" v-on:click="showTab = 'recommended-current'">
            <a class="nav-link" data-toggle="tab">
              {{ T.arenaRecommendedCurrentContests }}</a
            >
          </li>
          <li class="nav-item" v-on:click="showTab = 'current'">
            <a class="nav-link" data-toggle="tab">
              {{ T.arenaCurrentContests }}</a
            >
          </li>
          <li class="nav-item" v-on:click="showTab = 'public'">
            <a class="nav-link" data-toggle="tab">
              {{ T.arenaCurrentPublicContests }}</a
            >
          </li>
          <li class="nav-item" v-on:click="showTab = 'future'">
            <a class="nav-link" data-toggle="tab">
              {{ T.arenaFutureContests }}</a
            >
          </li>
          <li class="nav-item" v-on:click="showTab = 'recommended-past'">
            <a class="nav-link" data-toggle="tab">
              {{ T.arenaRecommendedOldContests }}</a
            >
          </li>
          <li class="nav-item" v-on:click="showTab = 'past'">
            <a class="nav-link" data-toggle="tab"> {{ T.arenaOldContests }}</a>
          </li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane active" v-if="showTab === 'participating'">
            <omegaup-contest-filtered-list
              v-bind:contests="contests.participating"
              v-bind:showTimes="true"
              v-bind:showPractice="false"
              v-bind:showVirtual="false"
              v-bind:showPublicUpdated="false"
              v-bind:recommended="false"
            ></omegaup-contest-filtered-list>
          </div>
          <div class="tab-pane active" v-if="showTab === 'recommended-current'">
            <omegaup-contest-filtered-list
              v-bind:contests="contests.recommended_current"
              v-bind:showTimes="true"
              v-bind:showPractice="false"
              v-bind:showVirtual="false"
              v-bind:showPublicUpdated="false"
              v-bind:recommended="true"
            ></omegaup-contest-filtered-list>
          </div>
          <div class="tab-pane active" v-if="showTab === 'current'">
            <omegaup-contest-filtered-list
              v-bind:contests="contests.current"
              v-bind:showTimes="true"
              v-bind:showPractice="false"
              v-bind:showVirtual="false"
              v-bind:showPublicUpdated="false"
              v-bind:recommended="false"
            ></omegaup-contest-filtered-list>
          </div>
          <div class="tab-pane active" v-if="showTab === 'public'">
            <omegaup-contest-filtered-list
              v-bind:contests="contests.public"
              v-bind:showTimes="true"
              v-bind:showPractice="false"
              v-bind:showVirtual="false"
              v-bind:showPublicUpdated="true"
              v-bind:recommended="false"
            ></omegaup-contest-filtered-list>
          </div>
          <div class="tab-pane active" v-if="showTab === 'future'">
            <omegaup-contest-filtered-list
              v-bind:contests="contests.future"
              v-bind:showTimes="true"
              v-bind:showPractice="false"
              v-bind:showVirtual="false"
              v-bind:showPublicUpdated="false"
              v-bind:recommended="false"
            ></omegaup-contest-filtered-list>
          </div>
          <div class="tab-pane active" v-if="showTab === 'recommended-past'">
            <omegaup-contest-filtered-list
              v-bind:contests="contests.recommended_past"
              v-bind:showTimes="false"
              v-bind:showPractice="true"
              v-bind:showVirtual="true"
              v-bind:showPublicUpdated="false"
              v-bind:recommended="true"
            ></omegaup-contest-filtered-list>
          </div>
          <div class="tab-pane active" v-if="showTab === 'past'">
            <omegaup-contest-filtered-list
              v-bind:contests="contests.past"
              v-bind:showTimes="false"
              v-bind:showPractice="true"
              v-bind:showVirtual="true"
              v-bind:showPublicUpdated="false"
              v-bind:recommended="true"
            ></omegaup-contest-filtered-list>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
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
  @Prop() contests!: omegaup.Contests;
  @Prop() isLogged!: boolean;

  T = T;
  showTab = 'participating';
  query = this.initialQuery;
}
</script>
