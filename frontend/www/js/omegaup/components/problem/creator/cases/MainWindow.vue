<template>
  <b-row class="mt-3">
    <b-col cols="3" class="border-1 border-right">
      <Sidebar />
    </b-col>
    <b-col>
      <!-- <div v-if="selected.caseId !== NIL">
        <CaseDetail class="mb-2" />
        <Lines />
      </div> -->
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import { namespace } from 'vuex-class';
import { NIL } from 'uuid';
import { types } from '../../../../problem/creator/types';
import Sidebar from './Sidebar.vue';

const caseStore = namespace('casesStore');

@Component({
  components: { Sidebar },
})
export default class Cases extends Vue {
  @caseStore.State('selected') selected!: types.CaseGroupID;
  NIL = NIL;
}
</script>

<style lang="scss"></style>
