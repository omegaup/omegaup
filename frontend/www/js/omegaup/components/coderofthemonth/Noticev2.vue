<template>
  <div class="notice py-3">
    <div class="container-lg">
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{
          ui.formatString(T.coderOfTheMonthNotice, { username: coderUsername })
        }}
        <button
          type="button"
          class="close"
          data-dismiss="alert"
          aria-label="Close"
        >
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';

@Component
export default class CoderOfTheMonthNotice extends Vue {
  @Prop() coderUsername!: string;
  T = T;
  ui = ui;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.notice {
  background: $omegaup-primary--darker;
}
</style>
