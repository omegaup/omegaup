<template>
  <div>
    <div class="container-lg py-4">
      <h1 class="text-center mb-4">{{ T.omegaupTitleCommonIndex }}</h1>
    </div>
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
            :category="'female'"
            :coder-of-the-month="coderOfTheMonthFemale"
          ></omegaup-coder-of-the-month>
        </div>
        <div
          v-if="coderOfTheMonth"
          class="col-xs-10 col-sm-6 col-lg-4 mb-3 mb-lg-0"
        >
          <omegaup-coder-of-the-month
            :category="'all'"
            :coder-of-the-month="coderOfTheMonth"
          ></omegaup-coder-of-the-month>
        </div>
        <div
          v-if="schoolOfTheMonth"
          class="col-xs-10 col-sm-6 col-lg-4 mb-3 mb-lg-0"
        >
          <omegaup-school-of-the-month :school-of-the-month="schoolOfTheMonth">
          </omegaup-school-of-the-month>
        </div>
      </div>
      <div class="row align-items-stretch justify-content-around mt-4">
        <div
          v-if="rankTable.resultTotal"
          class="col-xs-10 col-md-6 mb-3 mb-md-0"
        >
          <omegaup-user-rank
            :page="rankTable.page"
            :length="rankTable.length"
            :is-index="rankTable.isIndex"
            :is-logged="rankTable.isLogged"
            :available-filters="rankTable.availableFilters"
            :filter="rankTable.filter"
            :ranking="rankTable.ranking"
            :result-total="rankTable.resultTotal"
          ></omegaup-user-rank>
        </div>
        <div
          v-if="schoolsRank.totalRows"
          class="col-xs-10 col-md-6 mb-3 mb-md-0"
        >
          <omegaup-school-rank
            :page="schoolsRank.page"
            :length="schoolsRank.length"
            :show-header="schoolsRank.showHeader"
            :total-rows="schoolsRank.totalRows"
            :rank="schoolsRank.rank"
          ></omegaup-school-rank>
        </div>
      </div>
    </div>
    <omegaup-testimonials></omegaup-testimonials>
    <div class="container-lg py-5">
      <omegaup-section
        :title="T.homepageCompeteSectionTitle"
        :description="T.homepageCompeteSectionDescription"
        :buttons="[
          {
            text: T.buttonGoToContests,
            href: '/arena/',
          },
        ]"
        :image-src="'/media/homepage/contests_section.svg'"
      ></omegaup-section>
      <omegaup-section
        :title="T.homepageTrainSectionTitle"
        :description="T.homepageTrainSectionDescription"
        :buttons="[
          {
            text: T.buttonGoToProblems,
            href: '/problem/',
          },
        ]"
        :image-src="'/media/homepage/problems_section.svg'"
        :image-to-right="true"
      ></omegaup-section>
      <omegaup-section
        :title="T.homepageCreateSectionTitle"
        :description="T.homepageCreateSectionDescription"
        :buttons="
          !isUnder13User
            ? [
                {
                  text: T.buttonCreateProblem,
                  href: '/problem/new/',
                },
                {
                  text: T.buttonCreateContest,
                  href: '/contest/new/',
                },
              ]
            : []
        "
        :image-src="'/media/homepage/create_section.svg'"
      ></omegaup-section>
      <omegaup-section
        :title="T.homepageTeachSectionTitle"
        :description="T.homepageTeachSectionDescription"
        :buttons="[
          {
            text: T.buttonGoToCourses,
            href: '/course/',
          },
        ]"
        :image-src="'/media/homepage/courses_section.svg'"
        :image-to-right="true"
      ></omegaup-section>
      <omegaup-sponsors
        :title="T.homepageSponsorsSectionTitle"
        :logos="[
          {
            class: 'img-fluid mx-auto d-block my-4',
            src: '/media/homepage/airbnb_logo.svg',
            alt: 'airbnbLogo',
            href: 'https://news.airbnb.com/2025-community-fund/',
          },
        ]"
      ></omegaup-sponsors>
    </div>
    <omegaup-cookie-accept-decline
      @cookie-clicked-accept="cookieClickedAccept"
      @cookie-clicked-decline="cookieClickedDecline"
      @cookie-clicked-postpone="cookieClickedPostpone"
    ></omegaup-cookie-accept-decline>
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
import homepage_Sponsors from './Sponsors.vue';
import homepage_Cookie from './CookieConsent.vue';
import VueCookies from 'vue-cookies';
Vue.use(VueCookies, { expire: -1 });

@Component({
  components: {
    'omegaup-carousel': homepage_Carousel,
    'omegaup-coder-of-the-month': homepage_CoderOfTheMonth,
    'omegaup-school-of-the-month': homepage_SchoolOfTheMonth,
    'omegaup-school-rank': school_Rank,
    'omegaup-user-rank': user_Rank,
    'omegaup-testimonials': homepage_Testimonials,
    'omegaup-section': homepage_Section,
    'omegaup-sponsors': homepage_Sponsors,
    'omegaup-cookie-accept-decline': homepage_Cookie,
  },
})
export default class Homepage extends Vue {
  @Prop() coderOfTheMonth!: types.UserProfile;
  @Prop() coderOfTheMonthFemale!: types.UserProfile;
  @Prop() schoolOfTheMonth!: omegaup.SchoolOfTheMonth;
  @Prop() currentUserInfo!: omegaup.User;
  @Prop() rankTable!: omegaup.UserRankTable;
  @Prop() schoolsRank!: omegaup.SchoolRankTable;
  @Prop() isUnder13User!: boolean;

  T = T;
  cookieClickedAccept() {
    // TODO: make an API to send the response to the server
    this.$cookies.set('accept-cookies', true, -1);
  }

  cookieClickedDecline() {
    // TODO: make an API to send the response to the server
    this.$cookies.set('accept-cookies', false, -1);
  }

  cookieClickedPostpone() {
    // TODO: make an API to send the response to the server
  }
}
</script>
