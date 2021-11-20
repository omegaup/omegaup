<template>
  <b-tabs small>
    <b-tab>
      <template #title>
        <BIconPencil class="mr-1" />
        <span> Redacción </span>
      </template>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconFileCode class="mr-1" />
        <span> Solución </span>
      </template>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconCheckCircle class="mr-1" />
        <span> Casos de prueba </span>
      </template>
    </b-tab>
  </b-tabs>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
@Component({})
export default class Tabs extends Vue {}
</script>
