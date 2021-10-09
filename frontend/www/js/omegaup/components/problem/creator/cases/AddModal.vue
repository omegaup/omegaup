<template>
  <b-modal
    id="add-problem"
    title="Agregar"
    ok-title="Agregar"
    cancel-title="Cancelar"
    hide-header-close
    @ok="handleOk"
    @keydown.enter="handleSubmit"
  >
    <b-tabs v-model="tabIndex" small pills>
      <b-tab title="Caso">
        <form @submit.stop.prevent="handleSubmit">
          <CaseInput ref="caseInput" />
          <input type="submit" style="visibility: hidden" />
          <!-- Trick to be able to submit when pressing 'enter'-->
        </form>
      </b-tab>
      <b-tab title="Múltiples Casos">
        <form @submit.stop.prevent="handleSubmit">
          <MulticaseInput ref="multiInput" />
          <input type="submit" style="visibility: hidden" />
        </form>
      </b-tab>
      <b-tab title="Grupo">
        <form @submit.stop.prevent="handleSubmit">
          <GroupInput ref="groupInput" />
          <input type="submit" style="visibility: hidden" />
        </form>
      </b-tab>
    </b-tabs>
  </b-modal>
</template>

<script lang="ts">
import { Component, Ref, Vue } from 'vue-property-decorator';
import { types } from '../../../../problem/creator/types';
import { v4 } from 'uuid';
import { namespace } from 'vuex-class';
import GroupInput from './GroupInput.vue';
import MulticaseInput from './MulticaseInput.vue';
import CaseInput from './CaseInput.vue';

const caseStore = namespace('casesStore');

@Component({
  components: { GroupInput, MulticaseInput, CaseInput },
})
export default class AddModal extends Vue {
  @caseStore.State('groups') groups!: types.Group[];
  @caseStore.State('layout') layout!: types.InLine[];
  @caseStore.Getter('getCasesFromGroup') getCasesFromGroup!: (
    groupId: string,
  ) => types.Case[];

  @caseStore.Mutation('addGroup') addGroup!: (payload: types.Group) => void;
  @caseStore.Mutation('addCase') addCase!: (payload: types.Case) => void;
  @caseStore.Action('addMultipleCases') addMultipleCases!: (
    payload: types.MultipleCaseAdd,
  ) => void;

  @Ref('caseInput') readonly caseInput!: CaseInput;
  @Ref('groupInput') readonly groupInput!: GroupInput;
  @Ref('multiInput') readonly multicaseInput!: MulticaseInput;

  tabIndex = 0;

  handleOk(bvModalEvt: any) {
    bvModalEvt.preventDefault();
    this.handleSubmit();
  }

  handleSubmit() {
    switch (this.tabIndex) {
      case 0: {
        const validName = this.makeValidName(this.caseInput.caseName);
        const nameTaken = this.getCasesFromGroup(this.caseInput.caseGroup).find(
          (_case) => _case.name === validName,
        );
        if (nameTaken !== undefined) {
          this.$bvToast.toast(
            'No puedes tener dos casos en un mismo grupo con el mismo nombre',
            {
              title: 'Error',
              toaster: 'b-toaster-bottom-center',
              variant: 'danger',
              solid: true,
            },
          );
          break;
        }
        const layoutWithNewIds = this.layout.map((layoutLine) => {
          return { ...layoutLine, lineId: v4() };
        });
        const payload: types.Case = {
          caseId: v4(),
          groupId: this.caseInput.caseGroup,
          name: validName,
          points:
            this.caseInput.casePoints === '' ? 0 : this.caseInput.casePoints,
          defined: !this.caseInput.autoPoints,
          lines: layoutWithNewIds,
        };
        this.addCase(payload);
        this.hideModal();
        break;
      }
      case 1: {
        const payload: types.MultipleCaseAdd = {
          prefix: this.multicaseInput.prefix,
          suffix: this.multicaseInput.suffix,
          number: this.multicaseInput.caseNum,
          groupId: this.multicaseInput.casesGroup,
        };
        this.addMultipleCases(payload);
        this.hideModal();

        break;
      }
      case 2: {
        const validName = this.makeValidName(this.groupInput.groupName);
        const nameTaken = this.groups.find((group) => group.name === validName);
        if (nameTaken !== undefined) {
          this.$bvToast.toast(
            'No puedes tener dos grupos con el mismo nombre',
            {
              title: 'Error',
              toaster: 'b-toaster-bottom-center',
              variant: 'danger',
              solid: true,
            },
          );
          return;
        }
        const payload: types.Group = {
          groupId: v4(),
          name: validName,
          points:
            this.groupInput.groupPoints === ''
              ? 0
              : this.groupInput.groupPoints,
          defined: !this.groupInput.autoPoints,
          cases: [],
        };
        this.addGroup(payload);
        this.hideModal();
      }
    }
  }

  makeValidName(name: string) {
    return name.toLowerCase().replace(/ /g, '_');
  }

  hideModal() {
    this.$nextTick(() => {
      this.$bvModal.hide('add-problem');
    });
  }
}
</script>

<style lang="scss"></style>
