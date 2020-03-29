<template>
  <div class="alert alert-warning" name="notice">
    {{ UI.formatString(T.coderOfTheMonthNotice, { username: coderUsername }) }}
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as UI from '../../ui';

@Component
export default class CoderOfTheMonthNotice extends Vue {
  @Prop() coderUsername!: string;

  T = T;
  UI = UI;
}
</script>
