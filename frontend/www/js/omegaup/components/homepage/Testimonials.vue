<template>
  <!-- id-lint off -->
  <div
    id="testimonials-carousel-display"
    class="carousel slide"
    data-ride="carousel"
    data-interval="8000"
  >
    <!-- id-lint off -->
    <ol class="carousel-indicators">
      <li
        v-for="(_, index) in testimonials"
        :key="index"
        data-target="#testimonials-carousel-display"
        :data-slide-to="index"
        :class="{ active: !index }"
      ></li>
    </ol>
    <div
      class="carousel-inner text-center py-5 py-md-0 d-flex align-items-center"
    >
      <div
        v-for="(testimonial, index) in testimonials"
        :key="index"
        class="carousel-item"
        :class="{ active: !index }"
      >
        <div class="container-lg py-4 px-5">
          <blockquote class="blockquote text-center">
            <p class="mb-0">{{ testimonial.text[T.locale] }}</p>
            <footer class="blockquote-footer mt-2">
              {{ testimonial.author.name }},
              <cite :title="testimonial.author.title[T.locale]">{{
                testimonial.author.title[T.locale]
              }}</cite>
            </footer>
          </blockquote>
        </div>
      </div>
    </div>
    <a
      class="carousel-control-prev"
      href="#testimonials-carousel-display"
      role="button"
      data-slide="prev"
    >
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">{{ T.wordsPrevious }}</span>
    </a>
    <a
      class="carousel-control-next"
      href="#testimonials-carousel-display"
      role="button"
      data-slide="next"
    >
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">{{ T.wordsNext }}</span>
    </a>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import testimonialsConfig from '../../testimonials.config';

@Component
export default class Testimonials extends Vue {
  T = T;
  testimonials = testimonialsConfig;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.carousel {
  background: var(--homepage-carousel-background-color) !important;
  color: var(--homepage-carousel-font-color);
}

.carousel-inner {
  min-height: 31rem;

  .carousel-item {
    blockquote.blockquote {
      font-size: 1.15rem;
      line-height: 1.15;

      footer {
        color: wheat;
      }
    }
  }
}
@media only screen and (min-width: 767px) {
  .carousel-inner {
    min-height: 12rem;
  }
}
</style>
