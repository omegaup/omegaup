<template>
  <div class="alert alert-warning" name="notice">
    {{ ui.formatString(T.coderOfTheMonthNotice, { username: coderUsername }) }}
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
