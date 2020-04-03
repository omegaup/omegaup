<template>
  <div class="slide">
    <div class="container-lg">
      <div class="row align-items-center">
        <div class="slide-text col-md-6">
          <h3>{{ title }}</h3>
          <p>{{ description }}</p>
          <div slot="buttons"></div>
        </div>
        <div class="col-md-6">
          <img class="img-fluid slide-img" v-bind:src="imageSrc"/>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.slide {
  padding: 50px 20px;
  text-align: center;
  display: flex;
  align-items: center;

  @media only screen and (min-width: 767px) {
    height: 600px;
    padding: 50px;
  }

  &-img {
    max-height: 400px;
    @media only screen and (max-width: 767px) {
      height: 300px;
      margin-top: 40px;
    }
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class Slide extends Vue {
  @Prop() title!: string;
  @Prop() description!: string;
  @Prop() imageSrc!: string;
  T = T;
}
</script>