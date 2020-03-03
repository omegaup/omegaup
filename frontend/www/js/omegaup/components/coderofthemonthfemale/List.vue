<template>
  <div class="wait_for_ajax panel panel-default">
    <div class="panel-heading">
      <ul class="nav nav-tabs">
        <li class="active" v-on:click="selectedTab = 'codersOfTheMonthFemale'">
          <a data-toggle="tab">{{ T.codersOfTheMonthFemale }}</a>
        </li>
        <li v-on:click="selectedTab = 'codersOfPreviousMonthFemale'">
          <a data-toggle="tab">{{ T.codersOfTheMonthFemaleRank }}</a>
        </li>
        <li v-on:click="selectedTab = 'candidatesToCoderOfTheMonthFemale'">
          <a data-toggle="tab">{{ T.codersOfTheMonthFemaleListCandidate }}</a>
        </li>
      </ul>
    </div>
    <div class="panel-body"></div>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th></th>
          <th>{{ T.codersOfTheMonthCountry }}</th>
          <th>{{ T.codersOfTheMonthUser }}</th>
          <th v-if="selectedTab == 'codersOfTheMonth'">
            {{ T.codersOfTheMonthDate }}
          </th>
          <th
            class="numericColumn"
            v-if="selectedTab == 'candidatesToCoderOfTheMonth'"
          >
            {{ T.profileStatisticsNumberOfSolvedProblems }}
          </th>
          <th
            class="numericColumn"
            v-if="selectedTab == 'candidatesToCoderOfTheMonth'"
          >
            {{ T.rankScore }}
          </th>
          <th
            class="numericColumn"
            v-if="selectedTab == 'candidatesToCoderOfTheMonth' && isMentor"
          >
            {{ T.wordsActions }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="coder in visibleCoders">
          <td><img v-bind:src="coder.gravatar_32" /></td>
          <td>
            <omegaup-countryflag
              v-bind:country="coder.country_id"
            ></omegaup-countryflag>
          </td>
          <td>
            <omegaup-user-username
              v-bind:classname="coder.classname"
              v-bind:linkify="true"
              v-bind:username="coder.username"
            ></omegaup-user-username>
          </td>
          <td v-if="selectedTab == 'codersOfTheMonthFemale'">
            {{ coder.date }}
          </td>
          <td
            class="numericColumn"
            v-if="selectedTab == 'candidatesToCoderOfTheMonthFemale'"
          >
            {{ coder.ProblemsSolved }}
          </td>
          <td
            class="numericColumn"
            v-if="selectedTab == 'candidatesToCoderOfTheMonthFemale'"
          >
            {{ coder.score }}
          </td>
          <td
            class="numericColumn"
            v-if="
              selectedTab == 'candidatesToCoderOfTheMonthFemale' && isMentor
            "
          >
            <button
              class="btn btn-primary"
              v-if="canChooseCoder &amp;&amp; !coderIsSelected"
              v-on:click="$emit('select-coder', coder.username)"
            >
              {{ T.coderOfTheMonthChooseAsCoder }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import user_Username from '../user/Username.vue';
import country_Flag from '../CountryFlag.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': country_Flag,
  },
})
export default class CoderOfTheMonthList extends Vue {
  @Prop() codersOfCurrentMonthFemale!: omegaup.CoderOfTheMonth[];
  @Prop() codersOfPreviousMonthFemale!: omegaup.CoderOfTheMonth[];
  @Prop() candidatesToCoderOfTheMonthFemale!: omegaup.CoderOfTheMonth[];
  @Prop() canChooseCoder!: boolean;
  @Prop() coderIsSelected!: boolean;
  @Prop() isMentor!: boolean;

  T = T;
  selectedTab = 'codersOfTheMonthFemale';

  get visibleCoders(): omegaup.CoderOfTheMonth[] {
    switch (this.selectedTab) {
      case 'codersOfTheMonthFemale':
      default:
        return this.codersOfCurrentMonthFemale;
      case 'codersOfPreviousMonthFemale':
        return this.codersOfPreviousMonthFemale;
      case 'candidatesToCoderOfTheMonthFemale':
        return this.candidatesToCoderOfTheMonthFemale;
    }
  }
}
</script>
