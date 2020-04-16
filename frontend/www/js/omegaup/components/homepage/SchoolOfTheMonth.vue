<template>
  <div class="card h-100">
    <h5 class="card-header">
      {{ T.schoolOfTheMonth }}
    </h5>
    <div
      class="card-body text-center d-flex flex-column justify-content-center"
    >
      <h5 class="card-title">
        <omegaup-countryflag
          v-bind:country="schoolOfTheMonth.country_id"
          v-if="schoolOfTheMonth.country_id !== null"
        ></omegaup-countryflag>
        <a v-bind:href="`/schools/profile/${schoolOfTheMonth.school_id}/`">{{
          schoolOfTheMonth.name
        }}</a>
      </h5>
      <!-- TODO: agregar más información (logo, total de usuarios...) -->
      <div
        class="card-text"
        v-if="schoolOfTheMonth.state && schoolOfTheMonth.country"
      >
        {{ schoolOfTheMonth.state }}, {{ schoolOfTheMonth.country }}
      </div>
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
