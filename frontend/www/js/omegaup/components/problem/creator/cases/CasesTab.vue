<template>
  <b-row class="mt-3">
    <b-col cols="3" class="border-1 border-right">
      <omegaup-problem-creator-cases-sidebar
        data-cases-sidebar
        :show-window="shouldShowAddWindow"
        @open-add-window="openAddWindow"
        @open-case-edit-window="openCaseEditWindow"
        @download-zip-file="
          (zipObject) => $emit('download-zip-file', zipObject)
        "
      />
    </b-col>
    <b-col data-cases-add-panel>
      <omegaup-problem-creator-cases-add-panel
        v-if="shouldShowAddWindow"
        :show-window="shouldShowAddWindow"
        @close-add-window="closeAddWindow"
      />
      <omegaup-problem-creator-cases-case-edit
        v-if="shouldShowCaseEditWindow"
        @download-input-file="
          (fileObject) => $emit('download-input-file', fileObject)
        "
      />
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Vue, Watch, Prop } from 'vue-property-decorator';
import problemCreator_Cases_CaseEdit from './CaseEdit.vue';
import problemCreator_Cases_Sidebar from './Sidebar.vue';
import probleCreator_Cases_AddPanel from './AddPanel.vue';
import introJs from 'intro.js';
import 'intro.js/introjs.css';
import VueCookies from 'vue-cookies';
import T from '../../../../lang';
import { TabIndex } from '../Tabs.vue';

Vue.use(VueCookies, { expire: -1 });

@Component({
  components: {
    'omegaup-problem-creator-cases-sidebar': problemCreator_Cases_Sidebar,
    'omegaup-problem-creator-cases-add-panel': probleCreator_Cases_AddPanel,
    'omegaup-problem-creator-cases-case-edit': problemCreator_Cases_CaseEdit,
  },
})
export default class CasesTab extends Vue {
  @Prop() activeTabIndex!: TabIndex;

  shouldShowAddWindow = false;
  shouldShowCaseEditWindow = false;

  openCaseEditWindow() {
    this.shouldShowAddWindow = false;
    this.shouldShowCaseEditWindow = true;
  }

  closeCaseEditWindow() {
    this.shouldShowCaseEditWindow = false;
  }

  closeAddWindow() {
    this.shouldShowAddWindow = false;
  }

  openAddWindow() {
    this.shouldShowCaseEditWindow = false;
    this.shouldShowAddWindow = true;
  }

  @Watch('activeTabIndex')
  onActiveTabIndexChanged(newIndex: TabIndex) {
    if (newIndex === TabIndex.TestCases) {
      this.startIntroGuide();
    }
  }

  startIntroGuide() {
    if (!this.$cookies.get('has-visited-cases-tab')) {
      this.$nextTick(() => {
        const intro = introJs();

        intro.setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps: [
            {
              title: T.problemCreatorCasesTabIntroSidebarTitle,
              intro: T.problemCreatorCasesTabIntroSidebarIntro,
              element: document.querySelector(
                '[data-cases-sidebar]',
              ) as HTMLElement | null,
            },
            {
              title: T.problemCreatorCasesTabIntroAddPanelTitle,
              intro: T.problemCreatorCasesTabIntroAddPanelIntro,
              element: document.querySelector(
                '[data-cases-add-panel]',
              ) as HTMLElement | null,
            },
          ],
        });

        intro.onbeforechange(() => {
          var currentStep = intro.currentStep();
          if (currentStep === 1) {
            this.openAddWindow();
          }
        });

        intro.start();
        this.$cookies.set('has-visited-cases-tab', true, -1);
      });
    }
  }
}
</script>
