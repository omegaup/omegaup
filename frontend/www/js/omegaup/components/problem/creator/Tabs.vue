<template>
  <b-tabs small>
    <b-tab>
      <template #title>
        <BIconPencil class="mr-1" />
        <span name="writing" data-tab> {{ T.problemCreatorStatement }}</span>
      </template>
      <omegaup-problem-creator-statement-tab
        :current-markdown-prop="currentMarkdownProp"
        @show-update-success-message="
          () => $emit('show-update-success-message')
        "
      />
    </b-tab>
    <b-tab>
      <template #title>
        <BIconFileCode class="mr-1" />
        <span name="code" data-tab> {{ T.problemCreatorCode }}</span>
      </template>
      <omegaup-problem-creator-code-tab
        :code-prop="codeProp"
        :extension-prop="extensionProp"
        @show-update-success-message="
          () => $emit('show-update-success-message')
        "
      />
    </b-tab>
    <b-tab>
      <template #title>
        <BIconCheckCircle class="mr-1" />
        <span name="testcases" data-tab> {{ T.problemCreatorTestCases }}</span>
      </template>
      <omegaup-problem-creator-cases-tab
        @download-input-file="
          (fileObject) => $emit('download-input-file', fileObject)
        "
      />
    </b-tab>
    <b-tab>
      <template #title>
        <BIconFileEarmarkCheck class="mr-1" />
        <span name="solution" data-tab> {{ T.problemCreatorSolution }}</span>
      </template>
      <omegaup-problem-creator-solution-tab
        :current-solution-markdown-prop="currentSolutionMarkdownProp"
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
@Component({
  components: {
    'omegaup-problem-creator-statement-tab': problemCreator_StatementTab,
    'omegaup-problem-creator-code-tab': problemCreator_CodeTab,
    'omegaup-problem-creator-solution-tab': problemCreator_SolutionTab,
    'omegaup-problem-creator-cases-tab': problemCreator_CasesTab,
  },
})
export default class Tabs extends Vue {
  T = T;
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
