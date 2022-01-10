<template>
  <b-tabs small>
    <b-tab>
      <template #title>
        <BIconPencil class="mr-1" />
        <span name="writing"> {{ T.problemCreatorStatement }}</span>
      </template>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconFileCode class="mr-1" />
        <span name="code"> {{ T.problemCreatorCode }}</span>
      </template>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconCheckCircle class="mr-1" />
        <span name="testcases"> {{ T.problemCreatorTestCases }}</span>
      </template>
      <cases-tab />
    </b-tab>
    <b-tab>
      <template #title>
        <BIconArrowUpRight class="mr-1" />
        <span name="solution"> {{ T.problemCreatorSolution }}</span>
      </template>
    </b-tab>
  </b-tabs>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import cases_Tab from './cases/CasesTab.vue';
import T from '../../../lang';
@Component({
  components: { 'cases-tab': cases_Tab },
})
export default class Tabs extends Vue {
  T = T;
}
</script>
