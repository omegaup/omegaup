<template>
  <div class="container-lg p-4">
    <div
      class="slide d-flex align-items-center justify-content-around flex-wrap flex-lg-nowrap"
    >
      <div class="titles-width">
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
      <div>
        <img class="d-block image-width" height="320" :src="imageSrc" />
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
  @media only screen and (min-width: 767px) {
    height: 31rem;
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

.image-width {
  max-width: 480px;
}

.titles-width {
  max-width: 640px;
}
</style>
