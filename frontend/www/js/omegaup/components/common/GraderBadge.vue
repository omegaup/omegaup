<template>
  <span class="grader-count badge" v-bind:class="graderBadgeClass">{{
    graderCounter
  }}</span>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';

@Component({})
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
    if (this.queueLength === -1) {
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
