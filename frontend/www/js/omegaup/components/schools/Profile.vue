<template>
  <div class="container-lg p-5">
    <h2 class="text-center mb-4">
      <span v-if="rank !== 0" class="rank-number">#{{ rank }} </span>
      {{ name }}
    </h2>
    <div class="row mb-4">
      <div class="col-md-4">
        <ul v-if="country" class="list-group mb-3">
          <li class="list-group-item">
            <strong>{{ T.wordsCountry }}:</strong>
            {{ country.name }}
            <omegaup-country-flag
              v-bind:country="country.id"
            ></omegaup-country-flag>
          </li>
          <li v-if="stateName" class="list-group-item">
            <strong>{{ T.profileState }}:</strong> {{ stateName }}
          </li>
        </ul>
        <omegaup-grid-paginator
          v-bind:columns="1"
          v-bind:items="codersOfTheMonth"
          v-bind:items-per-page="5"
          v-bind:title="T.codersOfTheMonth"
        >
          <template slot="table-header">
            <thead>
              <tr>
                <th>{{ T.codersOfTheMonthUser }}</th>
                <th class="numericColumn">{{ T.codersOfTheMonthDate }}</th>
              </tr>
            </thead>
          </template>
        </omegaup-grid-paginator>
      </div>
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <highcharts v-bind:options="chartOptions"></highcharts>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <omegaup-grid-paginator
          v-bind:columns="1"
          v-bind:show-page-offset="true"
          v-bind:items="schoolUsers"
          v-bind:items-per-page="30"
          v-bind:title="T.schoolUsers"
          v-bind:sort-options="sortOptions"
          v-on:sort-option-change="updateUsers"
        >
          <template slot="table-header">
            <thead>
              <tr>
                <th scope="col" class="text-center">
                  {{ T.profileContestsTablePlace }}
                </th>
                <th scope="col">{{ T.username }}</th>
                <th scope="col" class="text-right">{{ sortByTableTitle }}</th>
              </tr>
            </thead>
          </template>
          <template slot="item-data" slot-scope="slotProps">
            <omegaup-username
              v-bind:username="slotProps.item.toString()"
              v-bind:classname="slotProps.item.classname"
              v-bind:linkify="true"
            ></omegaup-username>
          </template>
        </omegaup-grid-paginator>
      </div>
    </div>
  </div>
</template>

<style>
.list-group-item strong {
  display: inline-block;
  width: 60px;
}

.rank-number {
  color: gray;
}
</style>

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
