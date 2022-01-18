<template>
  <b-card :title="T.problemCreatorAdd">
    <form ref="form" @submit.prevent="addItemToStore">
      <div class="h-100">
        <b-tabs small pills lazy>
          <b-tab
            @click="tab = 'case'"
            :active="tab === 'case'"
            :title="T.problemCreatorCase"
            name="modal-form"
          >
            <b-alert
              v-model="invalidName"
              variant="danger"
              class="mt-2"
              dismissible
            >
              {{ T.problemCreatorCannotHaveSameName }}</b-alert
            >
            <case-input ref="case-input" />
          </b-tab>
          <b-tab
            @click="tab = 'group'"
            :active="tab === 'group'"
            :title="T.problemCreatorGroup"
            name="modal-form"
          >
            <group-input />
          </b-tab>
          <b-tab
            @click="tab = 'multiplecases'"
            :active="tab === 'multiplecases'"
            :title="T.problemCreatorMultipleCases"
            name="modal-form"
          >
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
import {
  Group,
  CaseRequest,
  AddTabTypes,
} from '@/js/omegaup/problem/creator/types';
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
  tab: AddTabTypes = 'case';

  invalidName = false;
  T = T;

  @Ref('case-input') caseInputRef!: cases_CaseInput;

  @casesStore.Mutation('addCase') addCase!: (caseRequest: CaseRequest) => Group;
  @casesStore.State('groups') groups!: Group[];

  addItemToStore() {
    this.invalidName = false;

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

      this.addCase({
        caseID: uuid(),
        groupID: caseGroup,
        name: caseName,
        points: casePoints,
        autoPoints: caseAutoPoints,
      });
    }
    this.$emit('close-add-window');
  }
}
</script>
