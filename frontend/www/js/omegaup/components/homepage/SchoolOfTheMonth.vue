<template>
  <div class="card">
    <h5 class="card-header">
      {{ T.schoolOfTheMonth }}
    </h5>
    <div class="card-body text-center">
      <h5 class="card-title">
        <omegaup-countryflag
          v-bind:country="schoolOfTheMonth.country_id"
          v-if="schoolOfTheMonth.country_id !== null"
        ></omegaup-countryflag>
        <a v-bind:href="`/schools/profile/${schoolOfTheMonth.school_id}/`">{{
          schoolOfTheMonth.name
        }}</a>
      </h5>
      <!-- TODO: agregar más información (nombre del estado, país, total de usuarios...) -->
    </div>
    <div class="card-footer">
      <a href="/schoolofthemonth/">{{ T.coderOfTheMonthFullList }}</a>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import CountryFlag from '../CountryFlag.vue';
import T from '../../lang';

@Component({
  components: {
    'omegaup-countryflag': CountryFlag,
  },
})
export default class SchoolOfTheMonth extends Vue {
  @Prop() schoolOfTheMonth!: omegaup.SchoolOfTheMonth;

  T = T;
}
</script>
