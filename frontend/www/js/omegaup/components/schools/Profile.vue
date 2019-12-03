<template>
  <div class="container">
    <div class="page-header">
      <h1 class="text-center">{{ name }}</h1>
    </div>
    <div class="row">
      <div class="col-md-4">
        <div class="panel panel-default">
          <ul class="list-group">
            <li class="list-group-item">
              <strong>{{ T.wordsCountry }}:</strong>
              {{ country.name }}
              <omegaup-country-flag
                v-bind:country="country.id"
              ></omegaup-country-flag>
            </li>
            <li class="list-group-item">
              <strong>{{ T.profileState }}:</strong> {{ stateName }}
            </li>
          </ul>
        </div>
        <omegaup-grid-paginator
          v-bind:columns="1"
          v-bind:items="codersOfTheMonth"
          v-bind:items-per-page="5"
          v-bind:title="T.codersOfTheMonth"
        >
          <thead>
            <tr>
              <th>{{ T.codersOfTheMonthUser }}</th>
              <th>{{ T.codersOfTheMonthDate }}</th>
            </tr>
          </thead></omegaup-grid-paginator
        >
      </div>
      <div class="col-md-8">
        <div class="panel panel-default">
          <div class="panel-body">
            <omegaup-school-chart
              v-bind:data="monthlySolvedProblemsCount"
              v-bind:school="name"
            ></omegaup-school-chart>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.list-group-item strong {
  display: inline-block;
  width: 60px;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import CountryFlag from '../CountryFlag.vue';
import SchoolChart from './Chart.vue';
import GridPaginator from '../GridPaginator.vue';
import { SchoolCoderOfTheMonth } from '../../types.ts';

interface ProblemsSolvedCount {
  year: number;
  month: number;
  count: number;
}

@Component({
  components: {
    'omegaup-country-flag': CountryFlag,
    'omegaup-school-chart': SchoolChart,
    'omegaup-grid-paginator': GridPaginator,
  },
})
export default class SchoolProfile extends Vue {
  @Prop() name!: string;
  @Prop() country!: omegaup.Country;
  @Prop() stateName!: string;
  @Prop() monthlySolvedProblemsCount!: ProblemsSolvedCount[];
  @Prop() users!: omegaup.SchoolUser[];
  @Prop() codersOfTheMonth!: SchoolCoderOfTheMonth;

  T = T;
  UI = UI;
}
</script>
