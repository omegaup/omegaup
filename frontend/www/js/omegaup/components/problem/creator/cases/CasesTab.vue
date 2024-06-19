<template>
  <b-row class="mt-3">
    <b-col cols="3" class="border-1 border-right">
      <omegaup-problem-creator-cases-sidebar
        :show-window="shouldShowAddWindow"
        @open-add-window="openAddWindow"
      />
    </b-col>
    <b-col>
      <omegaup-problem-creator-cases-add-panel
        v-if="shouldShowAddWindow"
        :show-window="shouldShowAddWindow"
        @close-add-window="closeAddWindow"
      />
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import problemCreator_Cases_Sidebar from './Sidebar.vue';
import probleCreator_Cases_AddPanel from './AddPanel.vue';
@Component({
  components: {
    'omegaup-problem-creator-cases-sidebar': problemCreator_Cases_Sidebar,
    'omegaup-problem-creator-cases-add-panel': probleCreator_Cases_AddPanel,
  },
})
export default class CasesTab extends Vue {
  shouldShowAddWindow = false;

  closeAddWindow() {
    this.shouldShowAddWindow = false;
  }

  openAddWindow() {
    this.shouldShowAddWindow = true;
  }
}
</script>
