<template>
  <div>
    <ul class="nav nav-tabs">
      <li class="nav-item">
        <a
          class="nav-link"
          :class="{ active: activeTabIndex === TabIndex.Statement }"
          href="#"
          @click.prevent="activeTabIndex = TabIndex.Statement"
        >
          <font-awesome-icon icon="pencil-alt" class="me-1" />
          <span name="writing" data-problem-creator-tab="statement">
            {{ T.problemCreatorStatement }}</span
          >
        </a>
      </li>
      <li class="nav-item">
        <a
          class="nav-link"
          :class="{ active: activeTabIndex === TabIndex.Code }"
          href="#"
          @click.prevent="activeTabIndex = TabIndex.Code"
        >
          <font-awesome-icon icon="file-code" class="me-1" />
          <span name="code" data-problem-creator-tab="code">
            {{ T.problemCreatorCode }}</span
          >
        </a>
      </li>
      <li class="nav-item">
        <a
          class="nav-link"
          :class="{ active: activeTabIndex === TabIndex.TestCases }"
          href="#"
          @click.prevent="activeTabIndex = TabIndex.TestCases"
        >
          <font-awesome-icon icon="check-circle" class="me-1" />
          <span name="testcases" data-problem-creator-tab="cases">
            {{ T.problemCreatorTestCases }}</span
          >
        </a>
      </li>
      <li class="nav-item">
        <a
          class="nav-link"
          :class="{ active: activeTabIndex === TabIndex.Solution }"
          href="#"
          @click.prevent="activeTabIndex = TabIndex.Solution"
        >
          <font-awesome-icon icon="file-alt" class="me-1" />
          <span name="solution" data-problem-creator-tab="solution">
            {{ T.problemCreatorSolution }}</span
          >
        </a>
      </li>
    </ul>
    <div class="tab-content mt-3">
      <div v-show="activeTabIndex === TabIndex.Statement">
        <omegaup-problem-creator-statement-tab
          ref="statementTab"
          :current-markdown-prop="currentMarkdownProp"
          :active-tab-index="activeTabIndex"
          :hide-save-button="hideSaveButtons"
          @show-update-success-message="
            () => $emit('show-update-success-message')
          "
        />
      </div>
      <div v-show="activeTabIndex === TabIndex.Code">
        <omegaup-problem-creator-code-tab
          ref="codeTab"
          :code-prop="codeProp"
          :extension-prop="extensionProp"
          :active-tab-index="activeTabIndex"
          :hide-save-button="hideSaveButtons"
          @show-update-success-message="
            () => $emit('show-update-success-message')
          "
        />
      </div>
      <div v-show="activeTabIndex === TabIndex.TestCases">
        <omegaup-problem-creator-cases-tab
          :active-tab-index="activeTabIndex"
          :hide-save-button="hideSaveButtons"
          @download-zip-file="
            (zipObject) => $emit('download-zip-file', zipObject)
          "
          @download-input-file="
            (fileObject) => $emit('download-input-file', fileObject)
          "
        />
      </div>
      <div v-show="activeTabIndex === TabIndex.Solution">
        <omegaup-problem-creator-solution-tab
          ref="solutionTab"
          :current-solution-markdown-prop="currentSolutionMarkdownProp"
          :active-tab-index="activeTabIndex"
          :hide-save-button="hideSaveButtons"
          @show-update-success-message="
            () => $emit('show-update-success-message')
          "
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop, Ref } from 'vue-property-decorator';
import problemCreator_CasesTab from './cases/CasesTab.vue';
import problemCreator_StatementTab from './statement/StatementTab.vue';
import problemCreator_CodeTab from './code/CodeTab.vue';
import problemCreator_SolutionTab from './solution/SolutionTab.vue';
import T from '../../../lang';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';

library.add(fas);

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
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class Tabs extends Vue {
  T = T;
  TabIndex = TabIndex;
  activeTabIndex = TabIndex.Statement;

  @Prop({ default: T.problemCreatorEmpty })
  currentSolutionMarkdownProp!: string;
  @Prop({ default: T.problemCreatorEmpty })
  currentMarkdownProp!: string;
  @Prop({ default: T.problemCreatorEmpty })
  codeProp!: string;
  @Prop({ default: T.problemCreatorEmpty })
  extensionProp!: string;
  @Prop({ default: false }) hideSaveButtons!: boolean;

  @Ref('statementTab') statementTabRef!: problemCreator_StatementTab;
  @Ref('codeTab') codeTabRef!: problemCreator_CodeTab;
  @Ref('solutionTab') solutionTabRef!: problemCreator_SolutionTab;

  saveAllDrafts(): void {
    this.statementTabRef?.persistDraft();
    this.codeTabRef?.persistDraft();
    this.solutionTabRef?.persistDraft();
  }
}
</script>
