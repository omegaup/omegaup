<template>
  <div class="card ranking-width">
    <ul class="nav nav-tabs justify-content-arround">
      <li class="nav-item">
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          aria-controls="allSchoolsOfTheMonth"
          :class="{ active: selectedTab === 'allSchoolsOfTheMonth' }"
          :aria-selected="selectedTab === 'allSchoolsOfTheMonth'"
          @click="selectedTab = 'allSchoolsOfTheMonth'"
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
          :class="{ active: selectedTab === 'schoolsOfPreviousMonth' }"
          :aria-selected="selectedTab === 'schoolsOfPreviousMonth'"
          @click="selectedTab = 'schoolsOfPreviousMonth'"
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
          :class="{
            active: selectedTab === 'candidatesToSchoolOfTheMonth',
          }"
          :aria-selected="selectedTab === 'candidatesToSchoolOfTheMonth'"
          @click="selectedTab = 'candidatesToSchoolOfTheMonth'"
        >
          {{ T.schoolsOfTheMonthCandidates }}
        </a>
      </li>
    </ul>
    <div v-if="isDisabled" class="system-in-maintainance m-5 text-center">
      <omegaup-markdown
        :markdown="T.schoolOfTheMonthSystemInMaintainance"
      ></omegaup-markdown>
      <font-awesome-icon :icon="['fas', 'cogs']" />
    </div>
    <table v-else class="table table-striped table-hover">
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
            <th scope="col" class="text-center">
              {{ T.rankScore }}
            </th>
            <th v-if="isMentor" scope="col" class="text-center">
              {{ T.wordsActions }}
            </th>
          </template>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(school, index) in visibleSchools" :key="index">
          <td class="text-center">
            <omegaup-country-flag
              :country="school.country_id"
            ></omegaup-country-flag>
          </td>
          <td class="text-center">
            <a :href="`/schools/profile/${school.school_id}/`">{{
              school.name
            }}</a>
          </td>
          <td v-if="selectedTab === 'allSchoolsOfTheMonth'" class="text-center">
            {{ school.time }}
          </td>
          <template v-else-if="selectedTab === 'candidatesToSchoolOfTheMonth'">
            <td class="text-center">
              {{ school.score }}
            </td>
            <td v-if="isMentor" class="text-center">
              <button
                v-if="canChooseSchool && !schoolIsSelected"
                class="btn btn-sm btn-primary"
                @click="$emit('select-school', school.school_id)"
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
import omegaup_Markdown from '../Markdown.vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCogs } from '@fortawesome/free-solid-svg-icons';
library.add(faCogs);

@Component({
  components: {
    'omegaup-country-flag': country_Flag,
    'omegaup-markdown': omegaup_Markdown,
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class SchoolOfTheMonthList extends Vue {
  @Prop() schoolsOfPreviousMonths!: omegaup.SchoolOfTheMonth[];
  @Prop() schoolsOfPreviousMonth!: omegaup.SchoolOfTheMonth[];
  @Prop() candidatesToSchoolOfTheMonth!: omegaup.SchoolOfTheMonth[];
  @Prop() isMentor!: boolean;
  @Prop() canChooseSchool!: boolean;
  @Prop() schoolIsSelected!: boolean;
  @Prop({ default: true }) isDisabled!: boolean;

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

<style scoped>
.nav-link.active,
.nav-link:hover {
  border: none;
  border-left: 0.0625rem solid #dee2e6;
  border-right: 0.0625rem solid #dee2e6;
  border-top-left-radius: 0rem;
  border-top-right-radius: 0rem;
}
.nav .nav-tabs {
  border-bottom: 0rem;
}

.nav-link {
  font-weight: medium;
  letter-spacing: 0.022rem;
  padding: 0.65rem 1rem;
}
.ranking-width {
  max-width: 55rem;
  margin: 0 auto;
}
.system-in-maintainance {
  font-size: 180%;
  color: var(--general-in-maintainance-color);
}
</style>
