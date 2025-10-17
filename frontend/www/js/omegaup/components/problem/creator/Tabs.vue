<template>
  <b-tabs v-model="activeTabIndex" small>
    <b-tab>
      <template #title>
        <BIconPencil class="mr-1" />
        <span name="writing" data-problem-creator-tab="statement">
          {{ T.problemCreatorStatement }}</span
        >
      </template>
      <omegaup-problem-creator-statement-tab
        :current-markdown-prop="currentMarkdownProp"
        :active-tab-index="activeTabIndex"
        @show-update-success-message="
          () => $emit('show-update-success-message')
        "
      />
    </b-tab>

    <b-tab>
      <template #title>
        <BIconFileCode class="mr-1" />
        <span name="code" data-problem-creator-tab="code">
          {{ T.problemCreatorCode }}</span
        >
      </template>
      <omegaup-problem-creator-code-tab
        :code-prop="codeProp"
        :extension-prop="extensionProp"
        :active-tab-index="activeTabIndex"
        @show-update-success-message="
          () => $emit('show-update-success-message')
        "
      />
    </b-tab>

    <b-tab>
      <template #title>
        <BIconCheckCircle class="mr-1" />
        <span name="testcases" data-problem-creator-tab="cases">
          {{ T.problemCreatorTestCases }}</span
        >
      </template>
      <omegaup-problem-creator-cases-tab
        :active-tab-index="activeTabIndex"
        @download-zip-file="
          (zipObject) => $emit('download-zip-file', zipObject)
        "
        @download-input-file="
          (fileObject) => $emit('download-input-file', fileObject)
        "
      />
    </b-tab>

    <b-tab>
      <template #title>
        <BIconFileEarmarkCheck class="mr-1" />
        <span name="solution" data-problem-creator-tab="solution">
          {{ T.problemCreatorSolution }}</span
        >
      </template>
      <omegaup-problem-creator-solution-tab
        :current-solution-markdown-prop="currentSolutionMarkdownProp"
        :active-tab-index="activeTabIndex"
        @show-update-success-message="
          () => $emit('show-update-success-message')
        "
      />
    </b-tab>
  </b-tabs>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import problemCreator_CasesTab from './cases/CasesTab.vue';
import problemCreator_StatementTab from './statement/StatementTab.vue';
import problemCreator_CodeTab from './code/CodeTab.vue';
import problemCreator_SolutionTab from './solution/SolutionTab.vue';
import T from '../../../lang';

export enum TabIndex {
  Statement = 0,
  Code = 1,
  TestCases = 2,
  Solution = 3,
}

@Component({
  components: {
    'omegaup-problem-creator-statement-tab': problemCreator_StatementTab,
    'omegaup-problem-creator-code-tab': problemCreator_CodeTab,
    'omegaup-problem-creator-cases-tab': problemCreator_CasesTab,
    'omegaup-problem-creator-solution-tab': problemCreator_SolutionTab,
  },
})
export default class Tabs extends Vue {
  T = T;
  activeTabIndex = TabIndex.Statement;

  @Prop({ default: T.problemCreatorEmpty })
  currentSolutionMarkdownProp!: string;
  @Prop({ default: T.problemCreatorEmpty })
  currentMarkdownProp!: string;
  @Prop({ default: T.problemCreatorEmpty })
  codeProp!: string;
  @Prop({ default: T.problemCreatorEmpty })
  extensionProp!: string;
}
</script>
