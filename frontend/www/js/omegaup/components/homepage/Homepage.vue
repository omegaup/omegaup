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
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-8">
          <omegaup-common-welcome></omegaup-common-welcome>
          <div class="row">
            <div class="col-md-6">
              <omegaup-rank-table
                v-bind:page="rankTable.page"
                v-bind:length="rankTable.length"
                v-bind:isIndex="rankTable.isIndex"
                v-bind:isLogged="rankTable.isLogged"
                v-bind:availableFilters="rankTable.availableFilters"
                v-bind:filter="rankTable.filter"
                v-bind:ranking="rankTable.ranking"
                v-bind:resultTotal="rankTable.resultTotal"
              ></omegaup-rank-table>
            </div>
            <div class="col-md-6" v-if="schoolsRank !== null">
              <omegaup-schools-rank
                v-bind:page="schoolsRank.page"
                v-bind:length="schoolsRank.length"
                v-bind:showHeader="schoolsRank.showHeader"
                v-bind:totalRows="schoolsRank.totalRows"
                v-bind:rank="schoolsRank.rank"
              ></omegaup-schools-rank>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <omegaup-common-social-media
            v-if="enableSocialMediaResources"
          ></omegaup-common-social-media>
          <omegaup-coder-of-the-month
            v-if="coderOfTheMonth"
            v-bind:username="coderOfTheMonth.username"
            v-bind:classname="coderOfTheMonth.classname"
            v-bind:name="coderOfTheMonth.name"
            v-bind:country="coderOfTheMonth.country"
            v-bind:country_id="coderOfTheMonth.country_id"
            v-bind:state="coderOfTheMonth.state"
            v-bind:gravatar_92="coderOfTheMonth.gravatar_92"
            v-bind:school="coderOfTheMonth.school"
          ></omegaup-coder-of-the-month>
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
import common_Welcome from '../common/Welcome.vue';
import common_SocialMedia from '../common/SocialMedia.vue';
import common_RecomendedMaterial from '../common/RecomendedMaterial.vue';
import contest_Upcoming from '../contest/Upcoming.vue';
import coderofthemonth_Notice from '../coderofthemonth/Notice.vue';
import coderofthemonth from '../coderofthemonth/CoderOfTheMonth.vue';
import rankTable from '../RankTable.vue';
import schools_Rank from '../schools/Rank.vue';

@Component({
  components: {
    'omegaup-common-welcome': common_Welcome,
    'omegaup-common-social-media': common_SocialMedia,
    'omegaup-common-recomended-material': common_RecomendedMaterial,
    'omegaup-contest-upcoming': contest_Upcoming,
    'omegaup-coder-of-the-month-notice': coderofthemonth_Notice,
    'omegaup-coder-of-the-month': coderofthemonth,
    'omegaup-rank-table': rankTable,
    'omegaup-schools-rank': schools_Rank,
    highcharts: Chart,
  },
})
export default class Home extends Vue {
  @Prop() coderOfTheMonth!: omegaup.CoderOfTheMonth;
  @Prop() coderOfTheMonthFemale!: omegaup.CoderOfTheMonth;
  @Prop() currentUserInfo!: omegaup.User;
  @Prop() rankTable!: omegaup.UserRankTable;
  @Prop() schoolsRank!: omegaup.SchoolRankTable;
  @Prop() enableSocialMediaResources!: boolean;
  @Prop() upcomingContests!: omegaup.Contest[];
  @Prop() chartOptions!: Chart;

  T = T;
}
</script>
