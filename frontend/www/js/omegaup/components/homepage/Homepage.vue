<template>
  <div>
    <omegaup-carousel :carousel-items="carouselItems"></omegaup-carousel>
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
    <omegaup-getting-started
      :is-under13-user="isUnder13User"
    ></omegaup-getting-started>
    <div class="container-lg py-5">
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
import Vue, { defineComponent } from 'vue';
import type { PropType } from 'vue';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import homepage_Carousel from './Carousel.vue';
import homepage_CoderOfTheMonth from './CoderOfTheMonth.vue';
import homepage_SchoolOfTheMonth from './SchoolOfTheMonth.vue';
import homepage_Testimonials from './Testimonials.vue';
import homepage_GettingStarted from './GettingStarted.vue';
import school_Rank from '../schools/Rank.vue';
import user_Rank from '../user/Rank.vue';
import homepage_Sponsors from './Sponsors.vue';
import homepage_Cookie from './CookieConsent.vue';
import VueCookies from 'vue-cookies';
Vue.use(VueCookies, { expires: -1 });

export default defineComponent({
  name: 'Homepage',
  components: {
    'omegaup-carousel': homepage_Carousel,
    'omegaup-coder-of-the-month': homepage_CoderOfTheMonth,
    'omegaup-school-of-the-month': homepage_SchoolOfTheMonth,
    'omegaup-school-rank': school_Rank,
    'omegaup-user-rank': user_Rank,
    'omegaup-testimonials': homepage_Testimonials,
    'omegaup-getting-started': homepage_GettingStarted,
    'omegaup-sponsors': homepage_Sponsors,
    'omegaup-cookie-accept-decline': homepage_Cookie,
  },
  props: {
    coderOfTheMonth: {
      type: Object as PropType<types.UserProfile | null>,
      default: null,
    },
    coderOfTheMonthFemale: {
      type: Object as PropType<types.UserProfile | null>,
      default: null,
    },
    schoolOfTheMonth: {
      type: Object as PropType<omegaup.SchoolOfTheMonth | null>,
      default: null,
    },
    currentUserInfo: {
      type: Object as PropType<omegaup.User>,
      required: true,
    },
    rankTable: {
      type: Object as PropType<omegaup.UserRankTable>,
      required: true,
    },
    schoolsRank: {
      type: Object as PropType<omegaup.SchoolRankTable>,
      required: true,
    },
    isUnder13User: {
      type: Boolean,
      required: true,
    },
    carouselItems: {
      type: Array as PropType<types.CarouselItem[]>,
      default: () => [],
    },
  },
  data() {
    return {
      T,
    };
  },
  methods: {
    cookieClickedAccept() {
      // TODO: make an API to send the response to the server
      this.$cookies.set('accept-cookies', true, -1);
    },

    cookieClickedDecline() {
      // TODO: make an API to send the response to the server
      this.$cookies.set('accept-cookies', false, -1);
    },

    cookieClickedPostpone() {
      // TODO: make an API to send the response to the server
    },
  },
});
</script>
