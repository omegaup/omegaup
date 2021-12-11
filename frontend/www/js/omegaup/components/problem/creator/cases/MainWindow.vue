<template>
  <b-row class="mt-3">
    <b-col cols="3" class="border-1 border-right"> <cases-sidebar /> </b-col>
    <b-col>
      <!-- Line editor -->
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import cases_Sidebar from './Sidebar.vue';
@Component({
  components: { 'cases-sidebar': cases_Sidebar },
})
export default class MainWindow extends Vue {}
</script>
