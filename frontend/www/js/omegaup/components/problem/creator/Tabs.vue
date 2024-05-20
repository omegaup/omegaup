<template>
  <b-tabs small>
    <b-tab>
      <template #title>
        <BIconPencil class="mr-1" />
        <span name="writing" data-tab> {{ T.problemCreatorStatement }}</span>
      </template>
      <statement-tab />
    </b-tab>
    <b-tab>
      <template #title>
        <BIconFileCode class="mr-1" />
        <span name="code" data-tab> {{ T.problemCreatorCode }}</span>
      </template>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconCheckCircle class="mr-1" />
        <span name="testcases" data-tab> {{ T.problemCreatorTestCases }}</span>
      </template>
      <cases-tab />
    </b-tab>
    <b-tab>
      <template #title>
        <BIconArrowUpRight class="mr-1" />
        <span name="solution" data-tab> {{ T.problemCreatorSolution }}</span>
      </template>
    </b-tab>
  </b-tabs>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import cases_Tab from './cases/CasesTab.vue';
import statement_Tab from './statement/StatementTab.vue';
import T from '../../../lang';
@Component({
  components: { 'statement-tab': statement_Tab, 'cases-tab': cases_Tab },
})
export default class Tabs extends Vue {
  T = T;
}
</script>
