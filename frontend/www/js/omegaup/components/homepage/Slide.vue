<template>
  <div class="container-lg p-4">
    <div
      class="slide d-flex align-items-center justify-content-around flex-wrap flex-lg-nowrap"
    >
      <div>
        <h3>{{ title }}</h3>
        <p>{{ description }}</p>
        <div slot="buttons"></div>
      </div>
      <div>
        <img class="d-block" height="320" v-bind:src="imageSrc" />
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.slide {
  @media only screen and (min-width: 767px) {
    height: 500px;
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Slide extends Vue {
  @Prop() title!: string;
  @Prop() description!: string;
  @Prop() imageSrc!: string;
}
</script>
