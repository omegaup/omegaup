<template>
  <div class="panel">
    {{ status }}
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
  @Prop() status!: string;
  T = T;
}

</script>
