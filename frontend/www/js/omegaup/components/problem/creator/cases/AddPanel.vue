<template>
  <b-card :title="T.problemCreatorAdd">
    <form ref="form" @submit.prevent="addItemToStore">
      <div class="h-100">
        <b-tabs v-model="tabIndex" small pills lazy>
          <b-tab :title="T.problemCreatorCase" name="modal-form">
            <b-alert
              v-model="invalidName"
              variant="danger"
              class="mt-2"
              dismissible
            >
              {{ T.problemCreatorCannotHaveSameName }}</b-alert
            >
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
      <b-button
        variant="light"
        size="sm"
        class="mr-2"
        @click="$emit('close-add-window')"
        >{{ T.wordsCancel }}</b-button
      >
      <b-button type="submit" variant="primary" size="sm">{{
        T.problemCreatorAdd
      }}</b-button>
    </form>
  </b-card>
</template>

<script lang="ts">
import { Component, Ref, Vue } from 'vue-property-decorator';
import T from '../../../../lang';
import cases_CaseInput from './CaseInput.vue';
import cases_MultipleCasesInput from './MultipleCasesInput.vue';
import cases_GroupInput from './GroupInput.vue';
import { namespace } from 'vuex-class';
import { Group, CaseRequest } from '@/js/omegaup/problem/creator/types';
import { NIL, v4 as uuid } from 'uuid';

const casesStore = namespace('casesStore');

@Component({
  components: {
    'case-input': cases_CaseInput,
    'multiple-cases-input': cases_MultipleCasesInput,
    'group-input': cases_GroupInput,
  },
})
export default class AddPanel extends Vue {
  tabIndex = 0;

  invalidName = false;
  T = T;

  @Ref('form') formRef!: HTMLFormElement;
  @casesStore.Mutation('addCase') addCase!: (caseRequest: CaseRequest) => Group;
  @casesStore.State('groups') groups!: Group[];

  addItemToStore() {
    this.invalidName = false;
    const formData = new FormData(this.formRef);

    if (this.tabIndex === 0) {
      // Case Input
      const caseName = formData.get('case-name') as string;
      const caseGroup = formData.get('case-group') as string;
      const casePointsString = formData.get('case-points') as string; // FormData converts everything to string
      const autoPointsString = formData.get('auto-points') as string | null; // A Checkbox is an exception, if it is checked it return 'true' otherwise null

      // Check if there is a group/case with the same name already
      if (caseGroup === NIL) {
        // In this case we just need to check if there is a group with the same name. Since everytime a new ungrouped case is created, a coressponding group is created too
        const nameAlreadyExists = this.groups.find((g) => g.name === caseName);
        if (nameAlreadyExists) {
          this.invalidName = true;
          return;
        }
      } else {
        const group = this.groups.find((g) => g.groupID === caseGroup);
        if (!group) return;
        const nameAlreadyExists = group.cases.find((c) => c.name === caseName);
        if (nameAlreadyExists) {
          this.invalidName = true;
          return;
        }
      }

      const casePoints =
        casePointsString === '' ? null : parseInt(casePointsString, 10);
      const autoPoints = autoPointsString === 'true';

      this.addCase({
        caseID: uuid(),
        groupID: caseGroup,
        name: caseName,
        points: casePoints,
        autoPoints: autoPoints,
      });
    }
    this.$emit('close-add-window');
  }
}
</script>
