<template>
  <div class="avatar"><img></div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Avatar extends Vue {
  @Prop({ default: '' }) private imageSrc!: string;
}

</script>

<style lang="scss" scoped>
.avatar {
  width: 80px;
  height: 80px;
  margin: 0 auto;

  img {
    width: 100%;
    border-radius: 50%;
  }
}
</style>
