<template>
  <div class="row p-4 text-center justify-content-around align-items-center">
    <h3 class="col-12 mb-3 display-4 font-weight-medium">{{ title }}</h3>
    <div
      class="p-3 col-md-6 mt-2 mt-md-0"
      :class="{ 'order-md-2': imageToRight }"
    >
      <img class="img-fluid" :src="imageSrc" />
    </div>
    <div class="col-md-6 mt-2 mt-md-0" :class="{ 'order-md-1': imageToRight }">
      <p class="section-description">{{ description }}</p>
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
  font-size: 2.4rem;
  margin: 0.8em auto 1.4em auto;
  letter-spacing: 0.01rem;
}
p.section-description {
  font-size: 1.24rem;
}
img.img-fluid {
  max-height: 260px;
}
a.section-link {
  color: $omegaup-white;
  padding: 0.44em 0.88em;
  margin-top: 0.5em;
  letter-spacing: 0.03rem;
  font-size: 1.08rem;
}
</style>
