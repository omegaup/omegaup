<template>
  <div>
    <omegaup-coder-of-the-month-notice
      v-if="
        currentUserInfo &&
          ((coderOfTheMonth &&
            coderOfTheMonth.username == currentUserInfo.username) ||
            (coderOfTheMonthFemale &&
              coderOfTheMonthFemale.username == currentUserInfo.username))
      "
      v-bind:coderUsername="currentUserInfo.username"
    ></omegaup-coder-of-the-month-notice>
    <!-- TODO: esto debe ser acomodado al final de toda la migración -->
    <omegaup-carousel></omegaup-carousel>
    <div class="container-lg py-5">
      <div class="row align-items-center justify-content-around">
        <div
          class="col-xs-10 col-sm-6 col-lg-4 mb-3 mb-lg-0"
          v-if="coderOfTheMonthFemale"
        >
          <omegaup-coder-of-the-month
            v-bind:category="'female'"
            v-bind:coder-of-the-month="coderOfTheMonthFemale"
          ></omegaup-coder-of-the-month>
        </div>
        <div
          class="col-xs-10 col-sm-6 col-lg-4 mb-3 mb-lg-0"
          v-if="coderOfTheMonth"
        >
          <omegaup-coder-of-the-month
            v-bind:category="'all'"
            v-bind:coder-of-the-month="coderOfTheMonth"
          ></omegaup-coder-of-the-month>
        </div>
        <div
          class="col-xs-10 col-sm-6 col-lg-4 mb-3 mb-lg-0"
          v-if="schoolOfTheMonth"
        >
          <omegaup-school-of-the-month
            v-bind:school-of-the-month="schoolOfTheMonth"
          >
          </omegaup-school-of-the-month>
        </div>
      </div>
      <div class="row align-items-center justify-content-around mt-3">
        <div class="col-xs-10 col-md-6 mb-3 mb-md-0" v-if="coderOfTheMonth">
          <omegaup-user-rank
            v-bind:page="rankTable.page"
            v-bind:length="rankTable.length"
            v-bind:isIndex="rankTable.isIndex"
            v-bind:isLogged="rankTable.isLogged"
            v-bind:availableFilters="rankTable.availableFilters"
            v-bind:filter="rankTable.filter"
            v-bind:ranking="rankTable.ranking"
            v-bind:resultTotal="rankTable.resultTotal"
          ></omegaup-user-rank>
        </div>
        <div
          class="col-xs-10 col-md-6 mb-3 mb-md-0"
          v-if="schoolsRank !== null"
        >
          <omegaup-school-rank
            v-bind:page="schoolsRank.page"
            v-bind:length="schoolsRank.length"
            v-bind:showHeader="schoolsRank.showHeader"
            v-bind:totalRows="schoolsRank.totalRows"
            v-bind:rank="schoolsRank.rank"
          ></omegaup-school-rank>
        </div>
      </div>
    </div>
    <omegaup-testimonials></omegaup-testimonials>
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-4">
          <omegaup-common-social-media
            v-if="enableSocialMediaResources"
          ></omegaup-common-social-media>
          <omegaup-common-recomended-material></omegaup-common-recomended-material>
          <omegaup-contest-upcoming
            v-bind:contests="upcomingContests"
          ></omegaup-contest-upcoming>
          <div class="panel panel-default">
            <highcharts v-bind:options="chartOptions"></highcharts>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { Chart } from 'highcharts-vue';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import homepage_Carousel from './Carousel.vue';
import homepage_CoderOfTheMonth from './CoderOfTheMonth.vue';
import homepage_SchoolOfTheMonth from './SchoolOfTheMonth.vue';
import homepage_Testimonials from './Testimonials.vue';
import school_Rank from '../schools/Rankv2.vue';
import user_Rank from '../user/Rank.vue';

import common_SocialMedia from '../common/SocialMedia.vue';
import common_RecomendedMaterial from '../common/RecomendedMaterial.vue';
import contest_Upcoming from '../contest/Upcoming.vue';
import coderofthemonth_Notice from '../coderofthemonth/Notice.vue';

@Component({
  components: {
    'omegaup-carousel': homepage_Carousel,
    'omegaup-coder-of-the-month': homepage_CoderOfTheMonth,
    'omegaup-school-of-the-month': homepage_SchoolOfTheMonth,
    'omegaup-school-rank': school_Rank,
    'omegaup-user-rank': user_Rank,
    'omegaup-testimonials': homepage_Testimonials,
    'omegaup-common-social-media': common_SocialMedia,
    'omegaup-common-recomended-material': common_RecomendedMaterial,
    'omegaup-contest-upcoming': contest_Upcoming,
    'omegaup-coder-of-the-month-notice': coderofthemonth_Notice,
    highcharts: Chart,
  },
})
export default class Homepage extends Vue {
  @Prop() coderOfTheMonth!: omegaup.CoderOfTheMonth;
  @Prop() coderOfTheMonthFemale!: omegaup.CoderOfTheMonth;
  @Prop() schoolOfTheMonth!: omegaup.SchoolOfTheMonth;
  @Prop() currentUserInfo!: omegaup.User;
  @Prop() rankTable!: omegaup.UserRankTable;
  @Prop() schoolsRank!: omegaup.SchoolRankTable;
  @Prop() enableSocialMediaResources!: boolean;
  @Prop() upcomingContests!: omegaup.Contest[];
  @Prop() chartOptions!: Chart;

  T = T;
}
</script>
