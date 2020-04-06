<template>
  <!-- id-lint off -->
  <div class="carousel slide" data-ride="carousel" id="carousel-display">
    <!-- id-lint on -->
    <ol class="carousel-indicators">
      <li data-target=".carousel" data-slide-to="0" class="active"></li>
      <li data-target=".carousel" data-slide-to="1"></li>
      <li data-target=".carousel" data-slide-to="2"></li>
    </ol>
    <div class="carousel-inner text-center py-5 py-md-0">
      <div class="carousel-item active">
        <omegaup-homepage-slide
          v-bind:title="T.carouselSlideWelcomeTitle"
          v-bind:description="T.carouselSlideWelcomeDescription"
          v-bind:image-src="'/media/homepage/carousel_slide_1.svg'"
        ></omegaup-homepage-slide>
      </div>
      <div class="carousel-item">
        <omegaup-homepage-slide
          v-bind:title="T.carouselSlideMentorsTitle"
          v-bind:description="T.carouselSlideMentorsDescription"
          v-bind:image-src="'/media/homepage/carousel_slide_2.svg'"
        ></omegaup-homepage-slide>
      </div>
      <div class="carousel-item">
        <omegaup-homepage-slide
          v-bind:title="T.carouselSlideSchoolsTitle"
          v-bind:description="T.carouselSlideSchoolsDescription"
          v-bind:image-src="'/media/homepage/carousel_slide_3.svg'"
        ></omegaup-homepage-slide>
      </div>
    </div>
    <a
      class="carousel-control-prev"
      href="#carousel-display"
      role="button"
      data-slide="prev"
    >
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a
      class="carousel-control-next"
      href="#carousel-display"
      role="button"
      data-slide="next"
    >
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>
  </div>
</template>

<style lang="scss">
@import '../../../../sass/main.scss';

$omegaup-primary--darker: #466aaf;
$omegaup-primary--accent: #0275d8;
.carousel {
  background: $omegaup-primary--darker;
  color: $white;
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
