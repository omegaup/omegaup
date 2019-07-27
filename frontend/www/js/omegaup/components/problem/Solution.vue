<template>
  <div class="panel">
  </div>
</template>

<style>
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class UserBasicEdit extends Vue {
  T = T;
}
</script>