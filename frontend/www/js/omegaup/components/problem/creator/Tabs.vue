<template>
  <b-tabs small>
    <b-tab>
      <template #title>
        <BIconPencil class="mr-1" />
        <span name="writing" data-tab> {{ T.problemCreatorStatement }}</span>
      </template>
      <omegaup-problem-creator-statement-tab />
    </b-tab>
    <b-tab>
      <template #title>
        <BIconFileCode class="mr-1" />
        <span name="code" data-tab> {{ T.problemCreatorCode }}</span>
      </template>
      <omegaup-problem-creator-code-tab />
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
import problemCreator_StatementTab from './statement/StatementTab.vue';
import problemCreator_CodeTab from './code/CodeTab.vue';
import T from '../../../lang';
@Component({
  components: {
    'omegaup-problem-creator-statement-tab': problemCreator_StatementTab,
    'omegaup-problem-creator-code-tab': problemCreator_CodeTab,
    'cases-tab': cases_Tab,
  },
})
export default class Tabs extends Vue {
  T = T;
}
</script>
