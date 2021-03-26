<template>
  <span class="grader-count badge" :class="graderBadgeClass">{{
    graderCounter
  }}</span>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class GraderCountBadge extends Vue {
  @Prop() queueLength!: number;
  @Prop() error!: boolean;

  get graderCounter(): string {
    if (this.error === true) {
      return '?';
    } else if (this.queueLength === -1) {
      return '…';
    }
    return `${this.queueLength}`;
  }

  get graderBadgeClass(): string {
    if (this.queueLength === -1 && this.error === false) {
      return '';
    } else if (this.error === true) {
      return 'grader-error';
    } else if (this.queueLength < 5) {
      return 'grader-ok';
    }
    return 'grader-warning';
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.grader-error {
  color: $chestnut;
  background-image: linear-gradient(
    rgb(242, 222, 222) 0px,
    rgb(231, 195, 195) 100%
  );
  background-color: rgb(242, 222, 222);
}

.grader-ok {
  color: $killarney;
  background-image: linear-gradient(
    rgb(223, 240, 216) 0px,
    rgb(200, 229, 188) 100%
  );
  background-color: rgb(223, 240, 216);
}

.grader-warning {
  color: $twine;
  background-image: linear-gradient(to bottom, $corn-silk 0, $corn-field 100%);
  border-color: $drover;
}
</style>
