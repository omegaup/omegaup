<template>
  <omegaup-problem-creator
    ref="creator"
    @download-zip-file="onDownloadZipFile"
    @upload-zip-file="onUploadZipFile"
    @show-update-success-message="onShowUpdateSuccessMessage"
    @download-input-file="onDownloadInputFile"
  />
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import problem_Creator from './creator/Creator.vue';
import creatorStore from '../../problem/creator/store';

@Component({
  components: {
    'omegaup-problem-creator': problem_Creator,
  },
  store: creatorStore,
})
export default class CreatorWrapper extends Vue {
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
