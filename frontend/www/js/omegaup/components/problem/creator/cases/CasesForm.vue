<template>
  <div class="card-body py-3 px-4">
    <hr class="my-3" />
    <form enctype="multipart/form-data" method="post" @submit="onSubmit">
      <div class="form-group col-md-12">
        <label class="control-label">{{ T.problemEditCommitMessage }}</label>
        <input v-model="commitMessage" class="form-control" />
      </div>

      <div v-if="isTruncatedInput" class="form-group col-md-6">
        <label class="control-label">{{ T.problemEditInputFile }}</label>
        <input
          type="file"
          class="form-control"
          name="input_file"
          accept=".in,.txt"
        />
      </div>

      <div v-if="isTruncatedOutput" class="form-group col-md-6">
        <label class="control-label">{{ T.problemEditOutputFile }}</label>
        <input
          type="file"
          class="form-control"
          name="output_file"
          accept=".out,.txt"
        />
      </div>

      <input type="hidden" name="request" value="cases" />
      <input type="hidden" name="problem_alias" :value="alias" />
      <input type="hidden" name="message" :value="commitMessage" />
      <input type="hidden" name="contents" :value="contentsPayload" />

      <div class="col-md-12 mt-3">
        <button
          v-show="!isEmbedded"
          ref="submitButton"
          class="btn btn-primary"
          type="submit"
          :disabled="!commitMessage.trim()"
        >
          {{ T.problemEditSaveCase }}
        </button>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop, Inject, Watch } from 'vue-property-decorator';
import T from '../../../../lang';
import { namespace } from 'vuex-class';
import { Case, Group, CaseLine } from '@/js/omegaup/problem/creator/types';
import * as ui from '@/js/omegaup/ui';

const casesStore = namespace('casesStore');

@Component
export default class CasesForm extends Vue {
  @Inject('problemAlias') readonly alias!: string;
  @Prop({ default: false }) readonly isTruncatedInput!: boolean;
  @Prop({ default: false }) readonly isTruncatedOutput!: boolean;
  @Prop({ default: false }) readonly isCaseEdit!: boolean;
  @Prop({ default: false }) readonly isEmbedded!: boolean;
  @Prop({ default: false }) readonly triggerSubmit!: boolean;
  @Prop({ default: null }) readonly editGroup!: Group;

  T = T;
  commitMessage = this.isCaseEdit
    ? T.problemEditUpdatingCase
    : T.problemEditUpdatingGroup;

  @casesStore.Getter('getSelectedCase') getSelectedCase!: Case;
  @casesStore.Getter('getSelectedGroup') getSelectedGroup!: Group;
  @casesStore.Getter('getLinesFromSelectedCase')
  getLinesFromSelectedCase!: CaseLine[];

  get inputText(): string {
    return (this.getLinesFromSelectedCase || [])
      .map((l) => l.data.value || '')
      .join('\n');
  }

  get contentsPayload(): string {
    if (this.isCaseEdit) {
      return JSON.stringify({
        group: this.getSelectedGroup,
        case: this.getSelectedCase,
      });
    }

    const groupToSend =
      this.editGroup !== null ? this.editGroup : this.getSelectedGroup;

    return JSON.stringify({
      group: groupToSend,
    });
  }

  @Watch('triggerSubmit')
  onTriggerSubmitChange(newVal: boolean) {
    if (newVal && this.isEmbedded) {
      this.$nextTick(() => {
        const btn = this.$refs.submitButton as HTMLButtonElement | undefined;
        btn?.click();
      });
    }
  }

  onSubmit(e: Event) {
    if (!this.commitMessage.trim()) {
      ui.error(T.editFieldRequired);
      e.preventDefault();
    }
  }
}
</script>
