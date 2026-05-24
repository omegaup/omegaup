<template>
  <div class="libinteractive-download panel panel-default">
    <div class="panel-heading">{{ T.outputOnlyFiles }}</div>
    <div class="panel-body">
      <a :href="zipUrl" class="btn btn-primary">{{ T.downloadZip }}</a>
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component } from 'vue-facing-decorator';
import T from '../../../lang';

@Component
export default class LibinteractiveDownload extends Vue {
  get zipUrl(): string {
    return '/problem/libinteractive/files.zip';
  }

  T = T;
}
</script>
