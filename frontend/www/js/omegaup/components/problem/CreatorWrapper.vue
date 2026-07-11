<template>
  <omegaup-problem-creator
    ref="creator"
    :hide-header-actions="hideHeaderActions"
    :hide-save-buttons="hideSaveButtons"
    @download-zip-file="onDownloadZipFile"
    @upload-zip-file="onUploadZipFile"
    @show-update-success-message="onShowUpdateSuccessMessage"
    @download-input-file="onDownloadInputFile"
  />
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref } from 'vue-property-decorator';
import problem_Creator from './creator/Creator.vue';
import creatorStore from '../../problem/creator/store';

@Component({
  components: {
    'omegaup-problem-creator': problem_Creator,
  },
  store: creatorStore,
})
export default class CreatorWrapper extends Vue {
  @Prop({ default: false }) hideHeaderActions!: boolean;
  @Prop({ default: false }) hideSaveButtons!: boolean;
  @Ref('creator') creatorRef!: problem_Creator;

  generateZip(): void {
    this.creatorRef?.generateZip();
  }

  saveDraft(): void {
    this.creatorRef?.saveDraft();
  }

  onDownloadZipFile(zipObject: unknown): void {
    this.$emit('download-zip-file', zipObject);
  }

  onUploadZipFile(data: unknown): void {
    this.$emit('upload-zip-file', data);
  }

  onShowUpdateSuccessMessage(): void {
    this.$emit('show-update-success-message');
  }

  onDownloadInputFile(fileObject: unknown): void {
    this.$emit('download-input-file', fileObject);
  }
}
</script>
