<template>
  <div class="alert alert-warning"
       name="notice"
       v-if="show">
    {{ UI.formatString(T.coderOfTheMonthNotice, {username: coderUsername}) }}
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class CoderOfTheMonthNotice extends Vue {
  @Prop() coderUsername!: string;
  @Prop() currentUsername!: string;

  T = T;
  UI = UI;

  get show(): boolean {
    return (
      this.currentUsername !== null &&
      this.coderUsername !== null &&
      this.coderUsername === this.currentUsername
    );
  }
}

</script>
