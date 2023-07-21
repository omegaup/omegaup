<template>
  <b-row class="mt-3">
    <b-col cols="3" class="border-1 border-right">
      <cases-sidebar
        :show-window="shouldShowAddWindow"
        @open-add-window="openAddWindow"
      />
    </b-col>
    <b-col>
      <add-panel
        v-if="shouldShowAddWindow"
        :show-window="shouldShowAddWindow"
        @close-add-window="closeAddWindow"
      />
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import cases_Sidebar from './Sidebar.vue';
import add_Panel from './AddPanel.vue';
@Component({
  components: { 'cases-sidebar': cases_Sidebar, 'add-panel': add_Panel },
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
