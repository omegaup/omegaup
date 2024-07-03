<template>
  <b-row class="mt-3">
    <b-col cols="3" class="border-1 border-right">
      <omegaup-problem-creator-cases-sidebar
        :show-window="shouldShowAddWindow"
        @open-add-window="openAddWindow"
        @open-case-edit-window="openCaseEditWindow"
      />
    </b-col>
    <b-col>
      <omegaup-problem-creator-cases-add-panel
        v-if="shouldShowAddWindow"
        :show-window="shouldShowAddWindow"
        @close-add-window="closeAddWindow"
      />
      <omegaup-problem-creator-cases-case-edit
        v-if="shouldShowCaseEditWindow"
      />
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import problemCreator_Cases_CaseEdit from './CaseEdit.vue';
import problemCreator_Cases_Sidebar from './Sidebar.vue';
import probleCreator_Cases_AddPanel from './AddPanel.vue';
@Component({
  components: {
    'omegaup-problem-creator-cases-sidebar': problemCreator_Cases_Sidebar,
    'omegaup-problem-creator-cases-add-panel': probleCreator_Cases_AddPanel,
    'omegaup-problem-creator-cases-case-edit': problemCreator_Cases_CaseEdit,
  },
})
export default class CasesTab extends Vue {
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
}
</script>
