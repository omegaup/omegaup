<template>
  <div class="container-lg py-5">
    <div class="row align-items-center justify-content-around">
      <div
        class="col-md-6 col-lg mb-3 mb-lg-0 customized-card"
        v-if="coderOfTheMonthFemale"
      >
        <omegaup-coder-of-the-month
          v-bind:female="true"
          v-bind:coder-of-the-month="coderOfTheMonthFemale"
        ></omegaup-coder-of-the-month>
      </div>
      <div
        class="col-md-6 col-lg mb-3 mb-lg-0 customized-card"
        v-if="coderOfTheMonth"
      >
        <omegaup-coder-of-the-month
          v-bind:female="false"
          v-bind:coder-of-the-month="coderOfTheMonth"
        ></omegaup-coder-of-the-month>
      </div>
      <div
        class="col-md-6 col-lg mb-3 mb-lg-0 customized-card"
        v-if="schoolOfTheMonth"
      >
        <omegaup-school-of-the-month
          v-bind:school-of-the-month="schoolOfTheMonth"
        >
        </omegaup-school-of-the-month>
      </div>
    </div>
  </div>
</template>

<style>
.customized-card {
  max-width: 400px;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import homepage_CoderOfTheMonth from './CoderOfTheMonth.vue';
import homepage_SchoolOfTheMonth from './SchoolOfTheMonth.vue';
import T from '../../lang';

@Component({
  components: {
    'omegaup-coder-of-the-month': homepage_CoderOfTheMonth,
    'omegaup-school-of-the-month': homepage_SchoolOfTheMonth,
  },
})
export default class RanksSection extends Vue {
  @Prop() coderOfTheMonth!: omegaup.CoderOfTheMonth;
  @Prop() coderOfTheMonthFemale!: omegaup.CoderOfTheMonth;
  @Prop() schoolOfTheMonth!: omegaup.SchoolOfTheMonth;
  @Prop() usersRank!: omegaup.UserRankTable;
  @Prop() schoolsRank!: omegaup.SchoolRankTable;

  T = T;
}
</script>
