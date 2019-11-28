<template>
  <div class="container">
    <div class="page-header">
      <h1 class="text-center">{{ name }}</h1>
    </div>
    <div class="row">
      <div class="col-md-4">
        <div class="panel panel-default">
          <ul class="list-group">
            <div class="list-group-item">
              Pa�s: {{ country_name }}
            </div>
            <div class="list-group-item">
              Estado: {{ state_name }}
            </div>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import countryFlag from '../CountryFlag.vue';

@Component({
  components: {
    'omegaup-country-flag': countryFlag,
  }
})
export default class SchoolProfile extends Vue {
  @Prop() name!: string;
  @Prop() country_name!: string;
  @Prop() state_name!: string;

  T = T;
  UI = UI;
}
</script>