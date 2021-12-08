<template>
  <b-tabs small>
    <b-tab>
      <template #title>
        <BIconPencil class="mr-1" />
        <span name="tab"> {{ T.problemCreatorStatement }}</span>
      </template>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconFileCode class="mr-1" />
        <span name="tab"> {{ T.problemCreatorCode }}</span>
      </template>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconCheckCircle class="mr-1" />
        <span name="tab"> {{ T.problemCreatorTestCases }}</span>
      </template>
      <cases-main-window />
    </b-tab>
    <b-tab>
      <template #title>
        <BIconArrowUpRight class="mr-1" />
        <span name="tab"> {{ T.problemCreatorSolution }}</span>
      </template>
    </b-tab>
  </b-tabs>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import cases_MainWindow from './cases/MainWindow.vue';
import T from '../../../lang';
@Component({
  components: { 'cases-main-window': cases_MainWindow },
})
export default class Tabs extends Vue {
  T = T;
}
</script>
