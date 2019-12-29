<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <ul class="nav nav-tabs">
        <li
          class="active"
          v-on:click="selectedTab = 'candidatesToSchoolOfTheMonth'"
        >
          <a data-toggle="tab">{{ T.schoolsOfTheMonthCandidates }}</a>
        </li>
      </ul>
    </div>
    <table class="table table-striped school-of-the-month-table">
      <thead>
        <tr>
          <th class="text-center">{{ T.wordsMonthCountry }}</th>
          <th>{{ T.wordsSchool }}</th>
          <th
            class="numericColumn"
            v-if="selectedTab === 'candidatesToSchoolOfTheMonth'"
          >
            {{ T.rankScore }}
          </th>
          <th
            class="text-center actions-column"
            v-if="selectedTab === 'candidatesToSchoolOfTheMonth' && isMentor"
          >
            {{ T.wordsActions }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="school in visibleSchools">
          <td class="text-center">
            <omegaup-country-flag
              v-bind:country="school.country_id"
            ></omegaup-country-flag>
          </td>
          <td>
            <a v-bind:href="`/schools/profile/${school.school_id}/`">{{
              school.name
            }}</a>
          </td>
          <td
            class="numericColumn"
            v-if="selectedTab === 'candidatesToSchoolOfTheMonth'"
          >
            {{ school.score }}
          </td>
          <td
            class="text-center"
            v-if="selectedTab == 'candidatesToSchoolOfTheMonth' && isMentor"
          >
            <button
              class="btn btn-primary"
              v-if="canChooseSchool &amp;&amp; !schoolIsSelected"
              v-on:click="$emit('select-school', school.school_id)"
            >
              {{ T.schoolOfTheMonthChooseAsSchool }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style>
table.school-of-the-month-table > tbody > tr > td {
  vertical-align: middle;
}

.actions-column {
  width: 250px;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import country_Flag from '../CountryFlag.vue';

@Component({
  components: {
    'omegaup-country-flag': country_Flag,
  },
})
export default class SchoolOfTheMonth extends Vue {
  @Prop() schoolsOfCurrentMonth!: omegaup.SchoolOfTheMonth[];
  @Prop() schoolsOfPreviousMonths!: omegaup.SchoolOfTheMonth[];
  @Prop() candidatesToSchoolOfTheMonth!: omegaup.SchoolOfTheMonth[];
  @Prop() isMentor!: boolean;
  @Prop() canChooseSchool!: boolean;
  @Prop() schoolIsSelected!: boolean;

  T = T;
  selectedTab = 'candidatesToSchoolOfTheMonth';

  get visibleSchools(): omegaup.SchoolOfTheMonth[] {
    switch (this.selectedTab) {
      case 'candidatesToSchoolOfTheMonth':
        return this.candidatesToSchoolOfTheMonth;
      default:
        return this.schoolsOfCurrentMonth;
    }
  }
}
</script>
