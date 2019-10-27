<template>
  <div class="omegaup-sections">
    <section class="section-content" v-for="(currentSection, index) in omegaupSections" :key="index">
      <h3 class="title">{{ currentSection.title }}</h3>

      <div class="section-container">
        <div class="section">
          <img v-if="index % 2 === 0 || isMobile" class="section-image" :src="currentSection.image" />
          <div v-else class="section-description">
            <h3>{{ currentSection.description }}</h3>
            <UIButton label="Más información" />
          </div>
        </div>
        <div class="section">
          <img v-if="index % 2 === 1 && !isMobile" class="section-image" :src="currentSection.image" />
          <div v-else class="section-description">
            <h3>{{ currentSection.description }}</h3>
            <UIButton label="Más información" />
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import OmegaupButton from './Button.vue';
import omegaupLandingSections from '../../landingpage/sections.config.js';

@Component({
  components: {
    UIButton: OmegaupButton,
  },
})
export default class Sections extends Vue {
  omegaupSections = omegaupLandingSections;

  get isMobile() {
    return window.innerWidth < 768;
  }
}
</script>

<style lang="scss" scoped>
$omegaup-white: white;
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

.omegaup-sections {
  display: block;

  .section-content {
    width: 80%;
    margin: 0 auto;
    text-align: center;

    &:not(:last-child) {
      margin-bottom: 100px;
    }

    .title {
      font-size: $omegaup-unit-6;
      color: $omegaup-primary--darker;
    }

    .section-container {
      display: flex;

      .section {
        display: inline-block;
        width: 50%;
        vertical-align: middle;

        @media only screen and (max-width: 767px) {
          width: 100%;
        }
        .section-description {
          margin-top: 100px;

          .btn {
            margin: 0 auto;
          }
        }
        .section-image {
          width: 500px;
        }
      }
    }
  }
}
</style>