<template>
  <div class="container">
    <div class="page-header">
      <h1 class="text-center">{{ name }}</h1>
    </div>
    <div class="row">
      <div class="col-md-4">
        <div class="row">
          <div class="panel panel-default">
            <ul class="list-group">
              <li class="list-group-item">
                <strong>{{ T.wordsCountry }}:</strong>
                {{ country.country_name }}
                <omegaup-country-flag
                  v-bind:country="country.country_id"
                ></omegaup-country-flag>
              </li>
              <li class="list-group-item">
                <strong>{{ T.profileState }}:</strong> {{ stateName }}
              </li>
            </ul>
          </div>
        </div>
        <div class="row">
          <!-- Acá irán los Coders del Mes paginados -->
        </div>
      </div>
      <div class="col-md-8">
        <div class="panel panel-default">
          <div class="panel-body">
            <omegaup-school-barchart
              v-bind:data="[
                {
                  year: '2019',
                  month: '5',
                  count: '20',
                },
                {
                  year: '2019',
                  month: '6',
                  count: '25',
                },
                {
                  year: '2019',
                  month: '7',
                  count: '25',
                },
                {
                  year: '2019',
                  month: '8',
                  count: '60',
                },
                {
                  year: '2019',
                  month: '9',
                  count: '200',
                },
                {
                  year: '2019',
                  month: '10',
                  count: '100',
                },
              ]"
              v-bind:school="name"
            ></omegaup-school-barchart>
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
import countryFlag from '../CountryFlag.vue';
import schoolBarchart from './Barchart.vue';

interface ProblemsSolvedCount {
  year: number;
  month: number;
  count: number;
}

@Component({
  components: {
    'omegaup-country-flag': countryFlag,
    'omegaup-school-barchart': schoolBarchart,
  },
})
export default class SchoolProfile extends Vue {
  @Prop() name!: string;
  @Prop() country!: omegaup.Country;
  @Prop() stateName!: string;
  @Prop() monthlySolvedProblemsCount!: ProblemsSolvedCount[];
  @Prop() users!: omegaup.SchoolUser[];

  T = T;
  UI = UI;

  // get solvedProblemsCountData(): number[] {
  //   return this.monthlySolvedProblemsCount.map(
  //     solvedProblemsCount => solvedProblemsCount.count,
  //   );
  // }

  // get solvedProblemsCountLabels(): string[] {
  //   return this.monthlySolvedProblemsCount.map(
  //     solvedProblemsCount =>
  //       `${solvedProblemsCount.year}-${solvedProblemsCount.month}`,
  //   );
  // }
}
</script>
