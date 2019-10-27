<template>
  <carousel>
    <div class="slide" v-for="(slide, index) in this.carouselSlides" :key="index">
			<div class="slide-content">
				<template v-if="index % 2 === 0">
          <div class="slide-section">
            <div class="slide-text">
              <h4 class="slide-title">{{ slide.title.es }}</h4>
              <p class="slide-description">{{ slide.description.es }}</p>
              <UIButton label="Más información" />
            </div>
          </div>

          <div class="slide-section">
            <img class="carousel-image" :src="slide.image" />
          </div>
        </template>

        <template v-else>
          <div class="slide-section">
            <img class="carousel-image" :src="slide.image" />
          </div>

          <div class="slide-section">
            <div class="slide-text">
              <h4 class="slide-title">{{ slide.title.es }}</h4>
              <p class="slide-description">{{ slide.description.es }}</p>
              <UIButton label="Más información" />
            </div>
          </div>
        </template>
			</div>
    </div>
  </carousel>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import { VueAgile } from 'vue-agile';
import OmegaupButton from './Button.vue';
import carouselConfig from '../../landingpage/carousel.config.js';

@Component({
  components: {
    carousel: VueAgile,
    UIButton: OmegaupButton,
  },
})
export default class OmegaupCarousel extends Vue {
  config = carouselConfig;

  get carouselSlides() {
    const slides = this.config.splice(1)
    console.log({ slides })
    return slides
  }
}
</script>

<style lang="scss">
$omegaup-primary--darker: #466aaf;
$omegaup-primary--accent: #0275d8;
$omegaup-unit-size: 8px;
$omegaup-unit-1: 8px;
$omegaup-unit-2: 16px;
$omegaup-unit-3: 24px;
$omegaup-unit-4: 32px;
$omegaup-unit-5: 40px;
$omegaup-unit-6: 48px;
$omegaup-unit-7: 56px;
$omegaup-unit-8: 64px;
$omegaup-unit-9: 72px;
$omegaup-unit-10: 80px;
 
// VueAgile styles
.agile {
  .agile__actions {
    .agile__nav-button {
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
        background-color: rgba(#000, 0.5);
        opacity: 1;
      }

      &.agile__nav-button--prev {
        left: 0;
      }

      &.agile__nav-button--next {
        right: 0;
      }
    }

    .agile__dots {
      bottom: 8px;
      left: 50%;
      position: absolute;
      transform: translateX(-50%);

      .agile__dot {
        margin: 0 8px;

        button {
          background-color: transparent;
          border: 1px solid #fff;
          border-radius: 50%;
          cursor: pointer;
          display: block;
          height: 8px;
          font-size: 0;
          line-height: 0;
          margin: 0;
          padding: 0;
          transition-duration: 0.3s;
          width: 8px;
        }

        &.agile__dot--current,
        &:hover {
          button {
            background-color: #fff;
          }
        }
      }
    }
  }

  // Slides styles
  .slide {
    background-color: #466aaf;
    color: white;
    display: block;
    height: 500px;

    .slide-content {
      width: 80%;
      margin: 0 auto;
      display: flex;
    }

    .carousel-image {
      height: 300px;
      width: auto;
      margin-top: -40px;

      @media only screen and (max-width: 767px) {
        height: 200px;
        margin-top: 40px;
      }
    }

    .slide-text {
      position: fixed;
      top: 112px;
      width: 40%;
      text-align: center;

      .btn {
        margin: 0 auto;
      }
    }

    .slide-section {
      display: inline-block;
      width: 50%;
      margin-top: 100px;
    }
  }
}
</style>