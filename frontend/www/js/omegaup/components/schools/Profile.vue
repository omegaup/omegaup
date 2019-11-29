<template>
  <div class="container">
    <div class="page-header">
      <h1 class="text-center">{{ name }}</h1>
    </div>
    <div class="row">
      <div class="col-md-4">
        <div class="row panel panel-default">
          <ul class="list-group">
            <li class="list-group-item">
              <strong>País:</strong> {{ country.country_name }}
              <omegaup-country-flag
              v-bind:country="country.country_id"></omegaup-country-flag>
            </li>
            <li class="list-group-item">
              <strong>Estado:</strong> {{ stateName }}
            </li>
          </ul>
        </div>
        <div class="row">
          <!-- Acá irán los Coders del Mes paginados -->
        </div>
      </div>
      <div class="col-md-8">
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
  year: number,
  month: number,
  count: number,
}

@Component({
  components: {
    'omegaup-country-flag': countryFlag,
    'trend-chart': TrendChart,
  }
})
export default class SchoolProfile extends Vue {
  @Prop() name!: string;
  @Prop() country!: omegaup.Country;
  @Prop() stateName!: string;
  @Prop() monthlySolvedProblemsCount!: ProblemsSolvedCount[];

  T = T;
  UI = UI;
}
</script>