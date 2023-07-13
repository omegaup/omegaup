<template>
  <div class="row p-4 text-center justify-content-around align-items-center">
    <h3 class="col-12 mb-3 display-4">{{ title }}</h3>
    <div
      class="p-3 col-md-6 mt-2 mt-md-0"
      :class="{ 'order-md-2': imageToRight }"
    >
      <img class="img-fluid" :src="imageSrc" />
    </div>
    <div class="col-md-6 mt-2 mt-md-0" :class="{ 'order-md-1': imageToRight }">
      <p class="section-description">{{ description }}</p>
      <div class="d-flex justify-content-center">
        <a
          v-for="(button, index) in buttons"
          :key="index"
          class="btn btn-primary section-link mx-2 mb-3"
          role="button"
          :href="button.href"
        >
          {{ button.text }}
        </a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Homepage extends Vue {
  @Prop() title!: string;
  @Prop() description!: string;
  @Prop() buttons!: {
    text: string;
    href: string;
  }[];
  @Prop() imageSrc!: string;
  @Prop({ default: false }) imageToRight!: boolean;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

h3.display-4 {
  color: $omegaup-primary--darker;
  font-weight: normal;
}

p.section-description,
a.section-link {
  font-size: 1.3rem;
}

img.img-fluid {
  max-height: 320px;
}

a.section-link {
  color: $omegaup-white;
}
</style>
