<template>
  <b-row class="mt-3">
    <b-col cols="3" class="border-1 border-right">
      <cases-sidebar
        @toggle-add-window="toggleAddWindow"
        :showWindow="shouldShowAddWindow"
      />
    </b-col>
    <b-col>
      <add-window
        v-show="shouldShowAddWindow"
        @toggle-add-window="toggleAddWindow"
        :showWindow="shouldShowAddWindow"
      />
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Emit, Vue } from 'vue-property-decorator';
import cases_Sidebar from './Sidebar.vue';
import add_Window from './AddWindow.vue';
@Component({
  components: { 'cases-sidebar': cases_Sidebar, 'add-window': add_Window },
})
export default class MainWindow extends Vue {
  shouldShowAddWindow = false;

  toggleAddWindow() {
    this.shouldShowAddWindow = !this.shouldShowAddWindow;
  }
}
</script>
