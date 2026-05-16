<template>
  <div class="output-only-download panel panel-default">
    <div class="panel-heading">{{ T.outputOnlyFiles }}</div>
    <div class="panel-body">
      <a :href="zipUrl" class="btn btn-primary"> {{ T.wordsDownload }} </a>
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component } from 'vue-facing-decorator';
import T from '../../../lang';

@Component
export default class OutputOnlyDownload extends Vue {
  get zipUrl(): string {
    // This should eventually be dynamic
    return '/problem/output-only/files.zip';
  }

  T = T;
}
</script>
