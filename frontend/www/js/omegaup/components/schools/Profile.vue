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
          <div class="panel-heading">
            <h2 class="panel-title text-center">
              Envíos mensuales de los usuarios
            </h2>
          </div>
          <div class="panel-body">
            <trend-chart
              v-bind:datasets="[
                {
                  data: [10, 50, 20, 100, 40, 60, 80],
                  smooth: true,
                  showPoints: true,
                  fill: true,
                },
              ]"
              v-bind:grid="{
                verticalLines: true,
                horizontalLines: true,
              }"
              v-bind:labels="{
                xLabels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                yLabels: 5,
              }"
              v-bind:min="0"
            ></trend-chart>
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
import TrendChart from 'vue-trend-chart';

interface ProblemsSolvedCount {
  year: number;
  month: number;
  count: number;
}

@Component({
  components: {
    'omegaup-country-flag': countryFlag,
    'trend-chart': TrendChart,
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

  get solvedProblemsCountData(): number[] {
    return this.monthlySolvedProblemsCount.map(
      solvedProblemsCount => solvedProblemsCount.count,
    );
  }

  get solvedProblemsCountLabels(): string[] {
    return this.monthlySolvedProblemsCount.map(
      solvedProblemsCount =>
        `${solvedProblemsCount.year}-${solvedProblemsCount.month}`,
    );
  }
}
</script>
