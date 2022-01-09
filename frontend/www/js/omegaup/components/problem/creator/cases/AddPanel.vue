<template>
  <b-card :title="T.problemCreatorAdd">
    <div class="h-100">
      <b-tabs v-model="tabIndex" small pills>
        <b-tab :title="T.problemCreatorCase" name="modal-form">
          <case-input />
        </b-tab>
        <b-tab :title="T.problemCreatorGroup" name="modal-form">
          <group-input />
        </b-tab>
        <b-tab :title="T.problemCreatorMultipleCases" name="modal-form">
          <multiple-cases-input />
        </b-tab>
      </b-tabs>
    </div>
    <template #footer class="d-flex">
      <b-button
        variant="light"
        size="sm"
        class="mr-2"
        @click="$emit('close-add-window')"
        >{{ T.wordsCancel }}</b-button
      >
      <b-button
        variant="primary"
        size="sm"
        @click="$emit('close-add-window')"
        >{{ T.problemCreatorAdd }}</b-button
      >
    </template>
  </b-card>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import T from '../../../../lang';
import cases_CaseInput from './CaseInput.vue';
import cases_MultipleCasesInput from './MultipleCasesInput.vue';
import cases_GroupInput from './GroupInput.vue';

@Component({
  components: {
    'case-input': cases_CaseInput,
    'multiple-cases-input': cases_MultipleCasesInput,
    'group-input': cases_GroupInput,
  },
})
export default class AddPanel extends Vue {
  tabIndex = 0;

  T = T;
}
</script>
