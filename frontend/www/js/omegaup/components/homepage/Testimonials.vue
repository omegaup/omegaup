<template>
  <!-- id-lint off -->
  <div
    class="carousel slide"
    data-ride="carousel"
    data-interval="8000"
    id="testimonials-carousel-display"
  >
    <!-- id-lint off -->
    <ol class="carousel-indicators">
      <li
        data-target="#testimonials-carousel-display"
        v-bind:key="index"
        v-bind:data-slide-to="index"
        v-bind:class="{ active: !index }"
        v-for="(_, index) in testimonials"
      ></li>
    </ol>
    <div
      class="carousel-inner text-center py-5 py-md-0 d-flex align-items-center"
    >
      <div
        class="carousel-item"
        v-bind:class="{ active: !index }"
        v-bind:key="index"
        v-for="(testimonial, index) in testimonials"
      >
        <div class="container-lg py-4 px-5">
          <blockquote class="blockquote text-center">
            <p class="mb-0">{{ testimonial.text[T.locale] }}</p>
            <footer class="blockquote-footer mt-2">
              {{ testimonial.author.name }},
              <cite v-bind:title="testimonial.author.title[T.locale]">{{
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

<style lang="scss" scoped>
.carousel-inner {
  min-height: 12rem;

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
</style>

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
