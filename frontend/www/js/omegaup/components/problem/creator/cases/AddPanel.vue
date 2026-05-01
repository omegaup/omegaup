<template>
  <b-card :title="T.problemCreatorAdd">
    <form ref="form" @submit.prevent="addItemToStore">
      <div class="h-100">
        <b-tabs small pills lazy>
          <b-tab
            :active="tab === 'case'"
            name="modal-form"
            @click="tab = 'case'"
          >
            <template #title>
              <span name="group" data-problem-creator-add-panel-tab="case">
                {{ T.problemCreatorCase }}</span
              >
            </template>
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
            name="modal-form"
            @click="tab = 'group'"
          >
            <template #title>
              <span name="group" data-problem-creator-add-panel-tab="group">
                {{ T.problemCreatorGroup }}</span
              >
            </template>
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
            name="modal-form"
            @click="tab = 'multiplecases'"
          >
            <template #title>
              <span
                name="multiple-cases"
                data-problem-creator-add-panel-tab="multiple-cases"
              >
                {{ T.problemCreatorMultipleCases }}</span
              >
            </template>
            <b-alert
              v-model="invalidCaseName"
              variant="danger"
              class="mt-2"
              dismissible
            >
              {{ T.problemCreatorCannotHaveSameName }}</b-alert
            >
            <omegaup-problem-creator-multiple-cases-input
              ref="multiple-cases-input"
            />
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
      <b-button
        data-problem-creator-add-panel-submit
        type="submit"
        variant="success"
        size="sm"
        >{{ T.problemCreatorAdd }}</b-button
      >
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
  MultipleCaseAddRequest,
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

  @Ref('multiple-cases-input')
  multipleCasesInputRef!: problemCreator_Cases_MultipleCasesInput;
  @Ref('case-input') caseInputRef!: problemCreator_Cases_CaseInput;
  @Ref('group-input') groupInputRef!: problemCreator_Cases_GroupInput;

  @casesStore.Mutation('addCase') addCase!: (caseRequest: CaseRequest) => void;
  @casesStore.Mutation('addGroup') addGroup!: (groupRequest: Group) => void;
  @casesStore.Action('addMultipleCases') addMultipleCases!: (
    multipleCaseRequest: MultipleCaseAddRequest,
  ) => void;
  @casesStore.State('groups') groups!: Group[];

  addItemToStore() {
    this.invalidCaseName = false;
    this.invalidGroupName = false;

    if (this.tab === 'case') {
      // Case Input
      const caseName = this.caseInputRef.caseName;
      const caseGroup = this.caseInputRef.caseGroup;
      const casePoints = this.caseInputRef.casePoints;
      const caseAutoPoints = this.caseInputRef.caseAutoPoints;

      // Check if there is a group/case with the same name already
      if (caseGroup === NIL) {
        // In this case we just need to check if there is a group with the same name. Since every time a new ungrouped case is created, a corresponding group is created too
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
      const groupAutoPoints = this.groupInputRef.groupAutoPoints;

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
    } else if (this.tab === 'multiplecases') {
      const multipleCasesPrefix = this.multipleCasesInputRef
        .multipleCasesPrefix;
      const multipleCasesSuffix = this.multipleCasesInputRef
        .multipleCasesSuffix;
      const multipleCasesCount = this.multipleCasesInputRef.multipleCasesCount;
      const multipleCasesGroup = this.multipleCasesInputRef.multipleCasesGroup;

      const multipleCaseNameArray = Array.from(
        { length: multipleCasesCount },
        (_, i) => multipleCasesPrefix + `${i + 1}` + multipleCasesSuffix,
      );

      const multipleCaseRequest: MultipleCaseAddRequest = {
        groupID: multipleCasesGroup,
        numberOfCases: multipleCasesCount,
        prefix: multipleCasesPrefix,
        suffix: multipleCasesSuffix,
      };

      if (multipleCasesGroup === NIL) {
        // In this case we just need to check if there is a group with the same name. Since every time a new ungrouped case is created, a corresponding group is created too
        const nameAlreadyExists = this.groups.find((g) =>
          multipleCaseNameArray.includes(g.name),
        );
        if (nameAlreadyExists) {
          this.invalidCaseName = true;
          return;
        }
        this.addMultipleCases(multipleCaseRequest);
        this.$emit('close-add-window');
        return;
      }
      const group = this.groups.find((g) => g.groupID === multipleCasesGroup);
      if (!group) return;
      const nameAlreadyExists = group.cases.find((c) =>
        multipleCaseNameArray.includes(c.name),
      );
      if (nameAlreadyExists) {
        this.invalidCaseName = true;
        return;
      }
      this.addMultipleCases(multipleCaseRequest);
      this.$emit('close-add-window');
      return;
    }
    this.$emit('close-add-window');
  }
}
</script>
