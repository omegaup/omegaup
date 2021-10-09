<template>
  <b-tabs small>
    <b-tab>
      <template #title>
        <BIconPencil class="mr-1" />
        <span> Redacción </span>
      </template>
      <span class="tab-window">1</span>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconFileCode class="mr-1" />
        <span> Solución </span>
      </template>
      <span class="tab-window">2</span>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconCheckCircle class="mr-1" />
        <span> Casos de prueba </span>
      </template>
      <CasesMainWindow class="tab-window" />
    </b-tab>
  </b-tabs>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import CasesMainWindow from './cases/MainWindow.vue';
@Component({
  components: {
    CasesMainWindow,
  },
})
export default class Tabs extends Vue {}
</script>

<style lang="scss">
.tab-window {
  min-height: 70vh;
}
</style>
