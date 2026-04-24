<template>
  <div class="container-lg p-4">
    <div
      class="slide d-flex align-items-center justify-content-center flex-column flex-lg-row"
    >
      <div class="titles-width text-center text-lg-left mb-4 mb-lg-0">
        <h2>{{ title }}</h2>
        <p>{{ description }}</p>
        <a
          v-if="button"
          class="btn btn-primary mb-3 slide-button"
          :href="button.href"
          :target="button.target"
          role="button"
        >
          {{ button.text[T.locale] }}
        </a>
      </div>
      <div class="image-container d-flex justify-content-center">
        <img
          class="d-block img-fluid image-width"
          :src="imageSrc"
          :alt="title"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class Slide extends Vue {
  @Prop() title!: string;
  @Prop() description!: string;
  @Prop() imageSrc!: string;
  @Prop() button!: {
    text: {
      en: string;
      es: string;
      pt: string;
    };
    href: string;
    target: string;
  };

  T = T;
}
</script>

<style lang="scss" scoped>
.slide {
  min-height: 43.5rem;

  @media only screen and (min-width: 992px) {
    min-height: 31rem;
  }

  h2 {
    font-size: 2.2rem;
    margin-bottom: 0.46em;
  }

  p {
    font-size: 1.16rem;
    margin-bottom: 1.1em;
  }
}
a.slide-button {
  padding: 0.44em 0.88em;
  margin-top: 0.5em;
  letter-spacing: 0.03rem;
  font-size: 1.08rem;
}
.image-container {
  width: 100%;
  max-width: 480px;
}
.image-width {
  width: 100%;
  height: auto;
  object-fit: contain;
}
.titles-width {
  width: 100%;
  max-width: 640px;
}
</style>
