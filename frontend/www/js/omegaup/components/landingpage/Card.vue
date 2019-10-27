<template>
  <div class="card">
    <slot></slot>
  </div>
</template>

<script lang="ts">
import { Vue } from 'vue-property-decorator';

export default class Card extends Vue {
}
</script>

<style lang="scss" scoped>
$omegaup-white: white;
$omegaup-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);

.card {
  border-radius: 5px;
  box-shadow: $omegaup-shadow;
  background-color: $omegaup-white;
  padding: 24px;
  width: 80%;
  margin: 0 auto;
}
</style>