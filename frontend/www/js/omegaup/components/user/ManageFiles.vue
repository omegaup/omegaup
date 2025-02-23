<template>
  <div>
    <!-- File Upload Form -->
    <form @submit.prevent="onUploadFile">
      <div class="form-group">
        <label>{{ T.userManageFilesUploadFile }}</label>
        <input
          ref="fileInput"
          type="file"
          required
          class="form-control"
          @change="handleFileSelection"
        />
      </div>
      <button
        type="submit"
        class="btn btn-primary mr-2"
        :disabled="!selectedFile"
      >
        {{ T.wordsUpload }}
      </button>
    </form>

    <!-- File List -->
    <div v-if="files.length > 0" class="mt-4">
      <h4>{{ T.userManageFilesListTitle }}</h4>
      <ul class="list-group">
        <li
          v-for="(file, index) in files"
          :key="index"
          class="list-group-item d-flex justify-content-between align-items-center"
        >
          <span>{{ file }}</span>
          <div>
            <button
              class="btn btn-primary btn-sm mr-2"
              @click="onDownloadFile(file)"
            >
              {{ T.wordsDownload }}
            </button>

            <button class="btn btn-danger btn-sm" @click="onDeleteFile(file)">
              {{ T.wordsDelete }}
            </button>
          </div>
        </li>
      </ul>
    </div>
    <div v-else class="mt-4">
      <p>{{ T.userManageFilesNoFiles }}</p>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class ManageFiles extends Vue {
  @Prop({ type: Array, required: true }) files!: string[];

  T = T;
  selectedFile: File | null = null;

  mounted() {
    this.$emit('fetch-files');
  }

  handleFileSelection(event: Event) {
    const input = event.target as HTMLInputElement;
    this.selectedFile = input.files ? input.files[0] : null;
  }

  onUploadFile() {
    if (!this.selectedFile) return;

    this.$emit('add-file', this.selectedFile);
    this.selectedFile = null;
    (this.$refs.fileInput as HTMLInputElement).value = '';
  }

  onDeleteFile(fileName: string) {
    this.$emit('delete-file', fileName);
  }

  onDownloadFile(filename: string) {
    this.$emit('download-file', filename);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
