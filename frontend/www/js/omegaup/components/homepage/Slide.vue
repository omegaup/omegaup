<template>
  <div class="container-lg p-4">
    <div
      class="slide d-flex align-items-center justify-content-around flex-wrap flex-lg-nowrap"
    >
      <div>
        <h2>{{ title }}</h2>
        <p>{{ description }}</p>
        <a
          v-if="button"
          class="btn btn-primary mb-3"
          :href="button.href"
          :target="button.target"
          role="button"
        >
          {{ button.text[T.locale] }}
        </a>
      </div>
      <div>
        <img class="d-block" height="320" :src="imageSrc" />
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
  height: 43.5rem;

  @media only screen and (min-width: 767px) {
    height: 31rem;
  }

  h2 {
    font-size: 2.4rem;
  }

  p {
    font-size: 1.2rem;
  }
}
</style>
