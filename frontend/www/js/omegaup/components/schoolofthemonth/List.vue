<template>
  <div>
    <ul class="nav nav-tabs">
      <li class="nav-item">
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          aria-controls="allSchoolsOfTheMonth"
          v-bind:class="{ active: selectedTab === 'allSchoolsOfTheMonth' }"
          v-bind:aria-selected="selectedTab === 'allSchoolsOfTheMonth'"
          v-on:click="selectedTab = 'allSchoolsOfTheMonth'"
        >
          {{ T.schoolsOfTheMonth }}
        </a>
      </li>
      <li class="nav-item">
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          aria-controls="schoolsOfPreviousMonth"
          v-bind:class="{ active: selectedTab === 'schoolsOfPreviousMonth' }"
          v-bind:aria-selected="selectedTab === 'schoolsOfPreviousMonth'"
          v-on:click="selectedTab = 'schoolsOfPreviousMonth'"
        >
          {{ T.schoolsOfTheMonthRank }}
        </a>
      </li>
      <li class="nav-item">
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          aria-controls="candidatesToSchoolOfTheMonth"
          v-bind:class="{
            active: selectedTab === 'candidatesToSchoolOfTheMonth',
          }"
          v-bind:aria-selected="selectedTab === 'candidatesToSchoolOfTheMonth'"
          v-on:click="selectedTab = 'candidatesToSchoolOfTheMonth'"
        >
          {{ T.schoolsOfTheMonthCandidates }}
        </a>
      </li>
    </ul>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th scope="col" class="text-center">{{ T.wordsCountryRegion }}</th>
          <th scope="col" class="text-center">{{ T.wordsSchool }}</th>
          <th
            v-if="selectedTab === 'allSchoolsOfTheMonth'"
            scope="col"
            class="text-center"
          >
            {{ T.wordsDate }}
          </th>
          <template v-else-if="selectedTab === 'candidatesToSchoolOfTheMonth'">
            <th scope="col" class="text-right">
              {{ T.rankScore }}
            </th>
            <th v-if="isMentor" scope="col" class="text-center">
              {{ T.wordsActions }}
            </th>
          </template>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(school, index) in visibleSchools" v-bind:key="index">
          <td class="text-center">
            <omegaup-country-flag
              v-bind:country="school.country_id"
            ></omegaup-country-flag>
          </td>
          <td class="text-center">
            <a v-bind:href="`/schools/profile/${school.school_id}/`">{{
              school.name
            }}</a>
          </td>
          <td v-if="selectedTab === 'allSchoolsOfTheMonth'" class="text-center">
            {{ school.time }}
          </td>
          <template v-else-if="selectedTab === 'candidatesToSchoolOfTheMonth'">
            <td class="text-right">
              {{ school.score }}
            </td>
            <td v-if="isMentor" class="text-center">
              <button
                v-if="canChooseSchool && !schoolIsSelected"
                class="btn btn-sm btn-primary"
                v-on:click="$emit('select-school', school.school_id)"
              >
                {{ T.schoolOfTheMonthChooseAsSchool }}
              </button>
            </td>
          </template>
        </tr>
      </tbody>
    </table>
  </div>
</template>

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
  @Prop() schoolsOfPreviousMonths!: omegaup.SchoolOfTheMonth[];
  @Prop() schoolsOfPreviousMonth!: omegaup.SchoolOfTheMonth[];
  @Prop() candidatesToSchoolOfTheMonth!: omegaup.SchoolOfTheMonth[];
  @Prop() isMentor!: boolean;
  @Prop() canChooseSchool!: boolean;
  @Prop() schoolIsSelected!: boolean;

  T = T;
  selectedTab = 'allSchoolsOfTheMonth';

  get visibleSchools(): omegaup.SchoolOfTheMonth[] {
    switch (this.selectedTab) {
      case 'allSchoolsOfTheMonth':
      default:
        return this.schoolsOfPreviousMonths;
      case 'schoolsOfPreviousMonth':
        return this.schoolsOfPreviousMonth;
      case 'candidatesToSchoolOfTheMonth':
        return this.candidatesToSchoolOfTheMonth;
    }
  }
}
</script>
