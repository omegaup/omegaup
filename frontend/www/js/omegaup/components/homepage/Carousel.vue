<template>
  <!-- id-lint off -->
  <div id="carousel-display" class="carousel slide" data-ride="carousel">
    <!-- id-lint on -->
    <ol class="carousel-indicators">
      <li
        v-for="(_, index) in slides"
        v-bind:key="index"
        data-target="#carousel-display"
        v-bind:data-slide-to="index"
        v-bind:class="{ active: !index }"
      ></li>
    </ol>
    <div class="carousel-inner text-center py-5 py-md-0">
      <div
        v-for="(slide, index) in slides"
        v-bind:key="index"
        class="carousel-item"
        v-bind:class="{ active: !index }"
      >
        <omegaup-homepage-slide
          v-bind:title="slide.title[T.locale]"
          v-bind:description="slide.description[T.locale]"
          v-bind:image-src="slide.image"
          v-bind:button="slide.button"
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
      <span class="sr-only">{{ T.wordsPrevious }}</span>
    </a>
    <a
      class="carousel-control-next"
      href="#carousel-display"
      role="button"
      data-slide="next"
    >
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">{{ T.wordsNext }}</span>
    </a>
  </div>
</template>

<style lang="scss">
@import '../../../../sass/main.scss';

.carousel {
  background: $omegaup-primary--darker;
  color: $white;
}
</style>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import homepageSlide from './Slide.vue';
import carouselConfig from '../../carousel.config';

@Component({
  components: {
    'omegaup-homepage-slide': homepageSlide,
  },
})
export default class Carousel extends Vue {
  T = T;
  slides = carouselConfig;
}
</script>
