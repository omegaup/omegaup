<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        {{ T.certificateDetailsTitle }}
      </h4>
    </div>
    <div class="panel-body">
      {{ ui.formatString(T.certificateDetailsBody, { uuid: this.uuid }) }}
    </div>
  </div>
</template>

<style></style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';

@Component
export default class CertificateDetails extends Vue {
  @Prop() uuid!: string;

  T = T;
}
</script>
