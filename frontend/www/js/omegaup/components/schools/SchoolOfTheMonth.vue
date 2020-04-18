<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <ul class="nav nav-tabs">
        <li
          v-bind:class="{
            active: selectedTab === 'schoolsOfPreviousMonths',
          }"
        >
          <a
            data-toggle="tab"
            v-on:click="selectedTab = 'schoolsOfPreviousMonths'"
            >{{ T.schoolsOfTheMonth }}</a
          >
        </li>
        <li
          v-bind:class="{
            active: selectedTab === 'schoolsOfCurrentMonth',
          }"
        >
          <a
            data-toggle="tab"
            v-on:click="selectedTab = 'schoolsOfCurrentMonth'"
            >{{ T.schoolsOfTheMonthRank }}</a
          >
        </li>
        <li
          v-bind:class="{
            active: selectedTab === 'candidatesToSchoolOfTheMonth',
          }"
        >
          <a
            data-toggle="tab"
            v-on:click="selectedTab = 'candidatesToSchoolOfTheMonth'"
            >{{ T.schoolsOfTheMonthCandidates }}</a
          >
        </li>
      </ul>
    </div>
    <table
      class="table table-striped school-of-the-month-table"
      v-show="selectedTab === 'schoolsOfPreviousMonths'"
    >
      <thead>
        <tr>
          <th class="text-center">{{ T.wordsCountryRegion }}</th>
          <th>{{ T.wordsSchool }}</th>
          <th class="text-center">{{ T.wordsDate }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="school in schoolsOfPreviousMonths">
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
          <td class="text-center">{{ school.time }}</td>
        </tr>
      </tbody>
    </table>
    <table
      class="table table-striped school-of-the-month-table"
      v-show="selectedTab === 'schoolsOfCurrentMonth'"
    >
      <thead>
        <tr>
          <th class="text-center">{{ T.wordsCountryRegion }}</th>
          <th>{{ T.wordsSchool }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="school in schoolsOfCurrentMonth">
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
        </tr>
      </tbody>
    </table>
    <table
      class="table table-striped school-of-the-month-table"
      v-show="selectedTab === 'candidatesToSchoolOfTheMonth'"
    >
      <thead>
        <tr>
          <th class="text-center">{{ T.wordsCountryRegion }}</th>
          <th>{{ T.wordsSchool }}</th>
          <th class="numericColumn">
            {{ T.rankScore }}
          </th>
          <th class="text-center actions-column" v-if="isMentor">
            {{ T.wordsActions }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="school in candidatesToSchoolOfTheMonth">
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
          <td class="numericColumn">
            {{ school.score }}
          </td>
          <td class="text-center" v-if="isMentor">
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
import { omegaup } from '../../omegaup';
import T from '../../lang';
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
  selectedTab = 'schoolsOfPreviousMonths';
}
</script>
