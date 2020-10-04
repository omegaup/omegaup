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
    <omegaup-carousel></omegaup-carousel>
    <div
      v-if="
        coderOfTheMonthFemale ||
        coderOfTheMonth ||
        schoolOfTheMonth ||
        rankTable.resultTotal ||
        schoolsRank.totalRows
      "
      class="container-lg py-5"
    >
      <div class="row align-items-stretch justify-content-around">
        <div
          v-if="coderOfTheMonthFemale"
          class="col-xs-10 col-sm-6 col-lg-4 mb-3 mb-lg-0"
        >
          <omegaup-coder-of-the-month
            v-bind:category="'female'"
            v-bind:coder-of-the-month="coderOfTheMonthFemale"
          ></omegaup-coder-of-the-month>
        </div>
        <div
          v-if="coderOfTheMonth"
          class="col-xs-10 col-sm-6 col-lg-4 mb-3 mb-lg-0"
        >
          <omegaup-coder-of-the-month
            v-bind:category="'all'"
            v-bind:coder-of-the-month="coderOfTheMonth"
          ></omegaup-coder-of-the-month>
        </div>
        <div
          v-if="schoolOfTheMonth"
          class="col-xs-10 col-sm-6 col-lg-4 mb-3 mb-lg-0"
        >
          <omegaup-school-of-the-month
            v-bind:school-of-the-month="schoolOfTheMonth"
          >
          </omegaup-school-of-the-month>
        </div>
      </div>
      <div class="row align-items-stretch justify-content-around mt-4">
        <div
          v-if="rankTable.resultTotal"
          class="col-xs-10 col-md-6 mb-3 mb-md-0"
        >
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
          v-if="schoolsRank.totalRows"
          class="col-xs-10 col-md-6 mb-3 mb-md-0"
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
    <div class="container-lg py-5">
      <omegaup-section
        v-bind:title="T.homepageCompeteSectionTitle"
        v-bind:description="T.homepageCompeteSectionDescription"
        v-bind:buttons="[
          {
            text: T.buttonGoToContests,
            href: '/arena/',
          },
        ]"
        v-bind:image-src="'/media/homepage/contests_section.svg'"
      ></omegaup-section>
      <omegaup-section
        v-bind:title="T.homepageTrainSectionTitle"
        v-bind:description="T.homepageTrainSectionDescription"
        v-bind:buttons="[
          {
            text: T.buttonGoToProblems,
            href: '/problem/',
          },
        ]"
        v-bind:image-src="'/media/homepage/problems_section.svg'"
        v-bind:image-to-right="true"
      ></omegaup-section>
      <omegaup-section
        v-bind:title="T.homepageCreateSectionTitle"
        v-bind:description="T.homepageCreateSectionDescription"
        v-bind:buttons="[
          {
            text: T.buttonCreateProblem,
            href: '/problem/new/',
          },
          {
            text: T.buttonCreateContest,
            href: '/contest/new/',
          },
        ]"
        v-bind:image-src="'/media/homepage/create_section.svg'"
      ></omegaup-section>
      <omegaup-section
        v-bind:title="T.homepageTeachSectionTitle"
        v-bind:description="T.homepageTeachSectionDescription"
        v-bind:buttons="[
          {
            text: T.buttonGoToCourses,
            href: '/course/',
          },
        ]"
        v-bind:image-src="'/media/homepage/courses_section.svg'"
        v-bind:image-to-right="true"
      ></omegaup-section>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import homepage_Carousel from './Carousel.vue';
import homepage_CoderOfTheMonth from './CoderOfTheMonth.vue';
import homepage_SchoolOfTheMonth from './SchoolOfTheMonth.vue';
import homepage_Testimonials from './Testimonials.vue';
import homepage_Section from './Section.vue';
import school_Rank from '../schools/Rank.vue';
import user_Rank from '../user/Rank.vue';
import coderofthemonth_Notice from '../coderofthemonth/Noticev2.vue';

@Component({
  components: {
    'omegaup-carousel': homepage_Carousel,
    'omegaup-coder-of-the-month': homepage_CoderOfTheMonth,
    'omegaup-school-of-the-month': homepage_SchoolOfTheMonth,
    'omegaup-school-rank': school_Rank,
    'omegaup-user-rank': user_Rank,
    'omegaup-testimonials': homepage_Testimonials,
    'omegaup-section': homepage_Section,
    'omegaup-coder-of-the-month-notice': coderofthemonth_Notice,
  },
})
export default class Homepage extends Vue {
  @Prop() coderOfTheMonth!: types.UserProfile;
  @Prop() coderOfTheMonthFemale!: types.UserProfile;
  @Prop() schoolOfTheMonth!: omegaup.SchoolOfTheMonth;
  @Prop() currentUserInfo!: omegaup.User;
  @Prop() rankTable!: omegaup.UserRankTable;
  @Prop() schoolsRank!: omegaup.SchoolRankTable;

  T = T;
}
</script>
