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
    </b-tab>
    <b-tab>
      <template #title>
        <BIconCheckCircle class="mr-1" />
        <span name="testcases" data-tab> {{ T.problemCreatorTestCases }}</span>
      </template>
      <omegaup-problem-creator-cases-tab />
    </b-tab>
    <b-tab>
      <template #title>
        <BIconArrowUpRight class="mr-1" />
        <span name="solution" data-tab> {{ T.problemCreatorSolution }}</span>
      </template>
      <omegaup-problem-creator-solution-tab />
    </b-tab>
  </b-tabs>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import problemCreator_CasesTab from './cases/CasesTab.vue';
import problemCreator_StatementTab from './statement/StatementTab.vue';
import problemCreator_SolutionTab from './solution/SolutionTab.vue';
import T from '../../../lang';
@Component({
  components: {
    'omegaup-problem-creator-statement-tab': problemCreator_StatementTab,
    'omegaup-problem-creator-solution-tab': problemCreator_SolutionTab,
    'omegaup-problem-creator-cases-tab': problemCreator_CasesTab,
  },
})
export default class Tabs extends Vue {
  T = T;
}
</script>
