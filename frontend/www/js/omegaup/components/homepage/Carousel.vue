<template>
  <vue-agile
    v-bind:autoplay="true"
    v-bind:autoplay-speed="5000"
    v-bind:timing="'ease-in-out'"
  >
    <omegaup-homepage-slide
      v-bind:title="T.carouselSlideWelcomeTitle"
      v-bind:description="T.carouselSlideWelcomeDescription"
      v-bind:image-src="'/media/homepage/carousel_slide_1.svg'"
    ></omegaup-homepage-slide>
    <omegaup-homepage-slide
      v-bind:title="T.carouselSlideMentorsTitle"
      v-bind:description="T.carouselSlideMentorsDescription"
      v-bind:image-src="'/media/homepage/carousel_slide_2.svg'"
    ></omegaup-homepage-slide>
    <omegaup-homepage-slide
      v-bind:title="T.carouselSlideSchoolsTitle"
      v-bind:description="T.carouselSlideSchoolsDescription"
      v-bind:image-src="'/media/homepage/carousel_slide_3.svg'"
    ></omegaup-homepage-slide>
  </vue-agile>
</template>

<style lang="scss">
@import '../../../../sass/main.scss';

$omegaup-primary--darker: #466aaf;
$omegaup-primary--accent: #0275d8;

.agile {
  background: $omegaup-primary--darker;
  color: $white;
  &__nav-button {
    background-color: green;
    background: transparent;
    border: none;
    color: #fff;
    cursor: pointer;
    font-size: 24px;
    height: 100%;
    position: absolute;
    top: 0;
    transition-duration: 0.3s;
    width: 80px;

    &:hover {
      background-color: rgba(0, 0, 0, 0.5);
      opacity: 1;
    }

    &--prev {
      left: 0;
    }

    &--next {
      right: 0;
    }
  }

  &__dots {
    bottom: 10px;
    left: 50%;
    position: absolute;
    transform: translateX(-50%);
  }

  &__dot {
    margin: 0 10px;

    button {
      background-color: transparent;
      border: 1px solid #fff;
      border-radius: 50%;
      cursor: pointer;
      display: block;
      height: 10px;
      font-size: 0;
      line-height: 0;
      margin: 0;
      padding: 0;
      transition-duration: 0.3s;
      width: 10px;
    }

    &--current button,
    &:hover button {
      background-color: #fff;
    }
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { VueAgile } from 'vue-agile';
import homepageSlide from './Slide.vue';

@Component({
  components: {
    VueAgile,
    'omegaup-homepage-slide': homepageSlide,
  },
})
export default class Carousel extends Vue {
  T = T;
}
</script>