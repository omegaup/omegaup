<template>
  <b-collapse :visible="visible" class="w-100 mt-2">
    <form enctype="multipart/form-data" method="post" @submit="onSubmit">
      <div class="p-3 border rounded bg-light item-active-for-delete">
        <div class="form-group">
          <label class="control-label">{{ T.problemEditCommitMessage }}</label>
          <input v-model="commitMessage" class="form-control" />
        </div>

        <input type="hidden" name="request" value="deleteGroupCase" />
        <input type="hidden" name="problem_alias" :value="alias" />
        <input type="hidden" name="message" :value="commitMessage" />
        <input type="hidden" name="contents" :value="contentsPayload" />

        <div class="button-container mt-3">
          <button
            class="btn btn-danger"
            type="submit"
            :disabled="commitMessage === ''"
          >
            {{ T.problemEditConfirmDeletion }}
          </button>

          <button class="btn btn-secondary" type="button" @click="handleCancel">
            {{ T.wordsCancel }}
          </button>
        </div>
      </div>
    </form>
  </b-collapse>
</template>

<script lang="ts">
import { Component, Prop, Vue, Watch, Inject } from 'vue-property-decorator';
import T from '../../../../lang';
import * as ui from '@/js/omegaup/ui';
@Component
export default class DeleteConfirmationForm extends Vue {
  @Inject('problemAlias') readonly alias!: string;
  @Prop({ required: true, type: Boolean }) visible!: boolean;
  @Prop({ required: true, type: String }) itemName!: string;
  @Prop({ required: false, type: String }) itemId!: string;
  @Prop({ required: true, type: Function }) onCancel!: () => void;
  T = T;
  commitMessage: string = '';
  @Watch('visible')
  onVisibleChange(newValue: boolean) {
    if (newValue) {
      this.commitMessage = `${T.problemEditDeletingPrefix} ${this.itemName}`;
    } else {
      this.commitMessage = '';
    }
  }
  get contentsPayload(): string {
    return JSON.stringify({
      id: this.itemId,
    });
  }
  onSubmit(e: Event) {
    if (!this.commitMessage.trim()) {
      ui.error(T.editFieldRequired);
      e.preventDefault();
      return;
    }
  }
  handleCancel() {
    this.onCancel();
    this.commitMessage = '';
  }
}
</script>

<style scoped>
.item-active-for-delete {
  border-left: 3px solid #dc3545 !important;
}

.button-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.button-container .btn {
  flex: 1 1 140px;
  margin: 0 !important;
  white-space: normal;
}
</style>
