<template>
  <b-card :title="T.problemCreatorAdd">
    <form ref="form" @submit.prevent="addItemToStore">
      <div class="h-100">
        <b-tabs small pills lazy>
          <b-tab
            :active="tab === 'case'"
            :title="T.problemCreatorCase"
            name="modal-form"
            @click="tab = 'case'"
          >
            <b-alert
              v-model="invalidCaseName"
              variant="danger"
              class="mt-2"
              dismissible
            >
              {{ T.problemCreatorCannotHaveSameName }}</b-alert
            >
            <omegaup-problem-creator-case-input ref="case-input" />
          </b-tab>
          <b-tab
            :active="tab === 'group'"
            :title="T.problemCreatorGroup"
            name="modal-form"
            @click="tab = 'group'"
          >
            <b-alert
              v-model="invalidGroupName"
              variant="danger"
              class="mt-2"
              dismissible
            >
              {{ T.problemCreatorCannotHaveSameName }}</b-alert
            >
            <omegaup-problem-creator-group-input ref="group-input" />
          </b-tab>
          <b-tab
            :active="tab === 'multiplecases'"
            :title="T.problemCreatorMultipleCases"
            name="modal-form"
            @click="tab = 'multiplecases'"
          >
            <omegaup-problem-creator-multiple-cases-input />
          </b-tab>
        </b-tabs>
      </div>
      <b-button
        variant="danger"
        size="sm"
        class="mr-2"
        @click="$emit('close-add-window')"
        >{{ T.wordsCancel }}</b-button
      >
      <b-button type="submit" variant="success" size="sm">{{
        T.problemCreatorAdd
      }}</b-button>
    </form>
  </b-card>
</template>

<script lang="ts">
import { Component, Ref, Vue } from 'vue-property-decorator';
import T from '../../../../lang';
import problemCreator_Cases_CaseInput from './CaseInput.vue';
import problemCreator_Cases_MultipleCasesInput from './MultipleCasesInput.vue';
import problemCreator_Cases_GroupInput from './GroupInput.vue';
import { namespace } from 'vuex-class';
import {
  Group,
  CaseRequest,
  AddTabTypes,
} from '@/js/omegaup/problem/creator/types';
import { NIL, v4 as uuid } from 'uuid';

const casesStore = namespace('casesStore');

@Component({
  components: {
    'omegaup-problem-creator-case-input': problemCreator_Cases_CaseInput,
    'omegaup-problem-creator-multiple-cases-input': problemCreator_Cases_MultipleCasesInput,
    'omegaup-problem-creator-group-input': problemCreator_Cases_GroupInput,
  },
})
export default class AddPanel extends Vue {
  tab: AddTabTypes = 'case';

  invalidCaseName = false;
  invalidGroupName = false;
  T = T;

  @Ref('case-input') caseInputRef!: problemCreator_Cases_CaseInput;
  @Ref('group-input') groupInputRef!: problemCreator_Cases_GroupInput;

  @casesStore.Mutation('addCase') addCase!: (caseRequest: CaseRequest) => void;
  @casesStore.Mutation('addGroup') addGroup!: (groupRequest: Group) => void;
  @casesStore.State('groups') groups!: Group[];

  addItemToStore() {
    this.invalidCaseName = false;
    this.invalidGroupName = false;

    if (this.tab === 'case') {
      // Case Input
      const caseName = this.caseInputRef.caseName;
      const caseGroup = this.caseInputRef.caseGroup;
      const casePoints = this.caseInputRef.casePoints;
      const caseAutoPoints = casePoints === null;

      // Check if there is a group/case with the same name already
      if (caseGroup === NIL) {
        // In this case we just need to check if there is a group with the same name. Since everytime a new ungrouped case is created, a coressponding group is created too
        const nameAlreadyExists = this.groups.find((g) => g.name === caseName);
        if (nameAlreadyExists) {
          this.invalidCaseName = true;
          return;
        }
      } else {
        const group = this.groups.find((g) => g.groupID === caseGroup);
        if (!group) return;
        const nameAlreadyExists = group.cases.find((c) => c.name === caseName);
        if (nameAlreadyExists) {
          this.invalidCaseName = true;
          return;
        }
      }

      this.addCase({
        caseID: uuid(),
        groupID: caseGroup,
        name: caseName,
        points: casePoints,
        autoPoints: caseAutoPoints,
      });
    } else if (this.tab === 'group') {
      const groupName = this.groupInputRef.groupName;
      const groupPoints = this.groupInputRef.groupPoints;
      const groupAutoPoints = groupPoints === null;

      // Check if there is a group with the same name already
      const nameAlreadyExists = this.groups.find((g) => g.name === groupName);
      if (nameAlreadyExists) {
        this.invalidGroupName = true;
        return;
      }

      this.addGroup({
        groupID: uuid(),
        name: groupName,
        points: groupPoints,
        autoPoints: groupAutoPoints,
        ungroupedCase: false,
        cases: [],
      });
    }
    this.$emit('close-add-window');
  }
}
</script>
