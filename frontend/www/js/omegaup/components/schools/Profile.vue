<template>
  <div class="py-0 px-0 mx-auto my-0">
    <h2 class="text-center mb-4">
      <span v-if="rank !== 0" class="rank-number">#{{ rank }} </span>
      {{ name }}
    </h2>
    <div class="row mb-3">
      <div class="col-md-4">
        <ul v-if="country" class="list-group mb-3">
          <li class="list-group-item">
            <strong>{{ T.wordsCountry }}:</strong>
            {{ country.name }}
            <omegaup-country-flag :country="country.id"></omegaup-country-flag>
          </li>
          <li v-if="stateName" class="list-group-item">
            <strong>{{ T.profileState }}:</strong> {{ stateName }}
          </li>
        </ul>
        <omegaup-table-paginator
          :column-names="columnNames"
          :items="codersOfTheMonth"
          :items-per-page="3"
          :title="T.codersOfTheMonth"
        >
        </omegaup-table-paginator>
      </div>
      <div class="col-md-8 pl-0">
        <div class="card h-100">
          <div class="card-body">
            <highcharts :options="chartOptions"></highcharts>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <omegaup-table-paginator
          :column-names="userColumnNames"
          :show-page-offset="true"
          :items="schoolUsers"
          :items-per-page="30"
          :title="T.schoolUsers"
          :sort-options="sortOptions"
          @sort-option-change="updateUsers"
        >
          <template #item-data="slotProps">
            <omegaup-username
              :username="slotProps.item.toString()"
              :classname="slotProps.item.classname"
              :linkify="true"
            ></omegaup-username>
          </template>
        </omegaup-table-paginator>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';
import CountryFlag from '../CountryFlag.vue';
import GridPaginator from '../common/GridPaginator.vue';
import UserName from '../user/Username.vue';
import { types } from '../../api_types';
import { SchoolCoderOfTheMonth, SchoolUser } from '../../linkable_resource';
import { Chart } from 'highcharts-vue';

@Component({
  components: {
    'omegaup-country-flag': CountryFlag,
    'omegaup-grid-paginator': GridPaginator,
    'omegaup-username': UserName,
    highcharts: Chart,
  },
})
export default class SchoolProfile extends Vue {
  @Prop() name!: string;
  @Prop() rank!: number;
  @Prop() country!: omegaup.Country;
  @Prop() stateName!: string;
  @Prop() monthlySolvedProblemsCount!: types.SchoolProblemsSolved[];
  @Prop() users!: SchoolUser[];
  @Prop() codersOfTheMonth!: SchoolCoderOfTheMonth;
  @Prop() chartOptions!: Chart;

  T = T;
  ui = ui;
  sortBy = 'solved_problems';
  sortOptions = [
    {
      title: T.profileSolvedProblems,
      value: 'solved_problems',
    },
    {
      title: T.profileCreatedProblems,
      value: 'created_problems',
    },
    {
      title: T.profileOrganizedContests,
      value: 'organized_contests',
    },
  ];

  get columnNames(): Array<{ name: string; style: string }> {
    return [
      { name: T.codersOfTheMonthUser, style: '' },
      { name: T.codersOfTheMonthDate, style: 'text-right' },
    ];
  }

  get userColumnNames(): Array<{ name: string; style: string }> {
    return [
      { name: T.profileContestsTablePlace, style: 'col-1 text-left' },
      { name: T.username, style: 'text-center' },
      { name: this.sortByTableTitle, style: 'text-right' },
    ];
  }

  get schoolUsers(): SchoolUser[] {
    return this.users.sort((userA, userB) => {
      if (userA.getDisplayValue() < userB.getDisplayValue()) return 1;
      if (userA.getDisplayValue() > userB.getDisplayValue()) return -1;
      return 0;
    });
  }

  get sortByTableTitle(): string {
    switch (this.sortBy) {
      case 'solved_problems':
        return T.profileSolvedProblems;
      case 'created_problems':
        return T.profileCreatedProblems;
      case 'organized_contests':
        return T.profileOrganizedContests;
      default:
        return '';
    }
  }

  updateUsers(newSortBy: string): void {
    this.users.forEach((user) => (user.displayField = newSortBy));
    this.sortBy = newSortBy;
  }
}
</script>

<style scoped>
.list-group-item strong {
  display: inline-block;
  width: 60px;
}

.rank-number {
  color: gray;
}

h2 {
  font-size: 1.8rem;
  letter-spacing: 0.01rem;
}
</style>
