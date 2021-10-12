<template>
  <b-modal
    ref="modal"
    :title="`${T.problemCreatorEditGroup} | ${name}`"
    :ok-title="T.problemCreatorEdit"
    :cancel-title="T.problemCreatorCancel"
    hide-header-close
    @ok="handleOk"
    @keydown.enter="handleSubmit"
  >
    <form @submit.stop.prevent="handleSubmit">
      <GroupInput
        ref="groupInput"
        :name="name"
        :points="points"
        :defined="defined"
      />
      <input type="submit" style="visibility: hidden" />
    </form>
  </b-modal>
</template>

<script lang="ts">
import { Component, Prop, Ref, Vue } from 'vue-property-decorator';
import { namespace } from 'vuex-class';
import { types } from '../../../../problem/creator/types';
import GroupInput from './GroupInput.vue';
import T from '../../../../lang';

const caseStore = namespace('casesStore');

@Component({
  components: { GroupInput },
})
export default class EditGroupModal extends Vue {
  @caseStore.State('groups') groups!: types.Group[];
  @caseStore.Mutation('editGroup') editGroup!: (payload: types.Group) => void;

  @Prop() readonly name!: string;
  @Prop() readonly points!: number;
  @Prop() readonly defined!: boolean;
  @Prop() readonly groupId!: string;

  @Ref('groupInput') readonly groupInput!: GroupInput;

  @Ref('modal') readonly modal!: any;

  T = T;

  handleOk(bvModalEvt: any) {
    bvModalEvt.preventDefault();
    this.handleSubmit();
  }

  handleSubmit() {
    const validName = this.makeValidName(this.groupInput.groupName);
    if (this.groupInput.groupName !== this.name) {
      // The user changed the name
      const nameTaken = this.groups.find((group) => group.name === validName);
      if (nameTaken !== undefined) {
        this.$bvToast.toast('No puedes tener dos grupos con el mismo nombre', {
          title: 'Error',
          toaster: 'b-toaster-bottom-center',
          variant: 'danger',
          solid: true,
        });
        return;
      }
    }
    const payload: types.Group = {
      groupId: this.groupId,
      name: validName,
      points:
        this.groupInput.groupPoints === '' ? 0 : this.groupInput.groupPoints,
      defined: !this.groupInput.autoPoints,
      cases: [],
    };
    this.editGroup(payload);
    this.hideModal();
  }

  makeValidName(name: string) {
    return name.toLowerCase().replace(/ /g, '_');
  }

  hideModal() {
    this.$nextTick(() => {
      this.modal.hide();
      // this.$bvModal.hide(`edit-group-${this.groupId}`);
    });
  }
}
</script>
