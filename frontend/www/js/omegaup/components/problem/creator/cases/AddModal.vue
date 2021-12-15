<template>
  <b-modal
    ref="addModal"
    :title="T.problemCreatorAdd"
    :ok-title="T.problemCreatorAdd"
    :cancel-title="T.wordsCancel"
    hide-header-close
  >
    <b-tabs v-model="tabIndex" small pills>
      <b-tab :title="T.problemCreatorCase" name="modal-form"></b-tab>
      <b-tab :title="T.problemCreatorMulticase" name="modal-form"> </b-tab>
      <b-tab :title="T.problemCreatorGroup" name="modal-form"> </b-tab>
    </b-tabs>
  </b-modal>
</template>

<script lang="ts">
import { BModal } from 'bootstrap-vue';
import { Component, Ref, Vue } from 'vue-property-decorator';
import T from '../../../../lang';

@Component
export default class AddModal extends Vue {
  @Ref('addModal') addCaseModalRef!: BModal;

  tabIndex = 0;

  T = T;

  openModal() {
    this.addCaseModalRef.show();
  }
}
</script>
