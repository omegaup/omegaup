<template>
  <b-row class="mb-3">
    <b-col class="d-flex align-items-center">
      <span class="mr-2">{{ T.problemCreatorName }}</span>
      <b-form-input
        v-model="name"
        size="sm"
        :placeholder="T.problemCreatorNewProblem"
      />
    </b-col>
    <b-col class="d-flex justify-content-end">
      <b-button
        class="mr-2"
        variant="success"
        size="sm"
        @click="uploadZipModal = !uploadZipModal"
      >
        <BIconUpload class="mr-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorLoadProblem }}</span
        >
      </b-button>
      <b-modal
        v-model="uploadZipModal"
        :title="T.problemCreatorZipFileUpload"
        :ok-title="T.problemCreatorUploadZip"
        ok-variant="success"
        :cancel-title="T.caseModalBack"
        cancel-variant="danger"
        static
        lazy
        @ok="retrieveStore"
      >
        <div class="mb-4">{{ T.problemCreatorUploadZipMessage }}</div>
        <input
          class="w-100"
          type="file"
          accept=".zip"
          @change="handleZipFile"
        />
      </b-modal>
      <b-button class="mr-2" variant="primary" size="sm">
        <BIconDownload class="mr-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorGenerateProblem }}</span
        >
      </b-button>
      <b-button variant="warning" size="sm" @click="createNewProblem">
        <BIconPlus class="mr-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorNewProblem }}</span
        >
      </b-button>
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Vue, Watch } from 'vue-property-decorator';
import T from '../../../lang';
import * as ui from '../../../ui';
import JSZip from 'jszip';

@Component
export default class Header extends Vue {
  T = T;
  zipFile: File | null = null;
  uploadZipModal: boolean = false;

  nameInternal: string = T.problemCreatorEmpty;

  get name(): string {
    return this.nameInternal;
  }
  set name(newName: string) {
    this.nameInternal = newName;
  }

  readFile(e: HTMLInputElement): File | null {
    return (e.files && e.files[0]) || null;
  }

  handleZipFile(ev: Event): void {
    this.zipFile = this.readFile(ev.target as HTMLInputElement);
  }

  retrieveStore(): void {
    if (!this.zipFile) {
      return;
    }
    const zip = new JSZip();
    zip
      .loadAsync(this.zipFile)
      .then((zipContent) => {
        const cdpDataFile = zipContent.file('cdp.data');
        if (cdpDataFile) {
          cdpDataFile.async('text').then((content) => {
            const storeData = JSON.parse(content);
            this.$emit('upload-zip-file', storeData);
            this.name = storeData.problemName;
            this.$store.replaceState({
              ...this.$store.state,
              problemName: storeData.problemName,
              problemMarkdown: storeData.problemMarkdown,
              problemCodeContent: storeData.problemCodeContent,
              problemCodeExtension: storeData.problemCodeExtension,
              problemSolutionMarkdown: storeData.problemSolutionMarkdown,
            });
            if (storeData.casesStore) {
              this.$store.commit(
                'casesStore/replaceState',
                storeData.casesStore,
              );
            }
          });
        } else {
          ui.error(T.problemCreatorZipFileIsNotComplete);
        }
      })
      .catch(() => {
        ui.error(T.problemCreatorZipFileIsNotValid);
      });
  }

  @Watch('name')
  onNameChanged(newProblemName: string) {
    this.$store.commit('updateName', newProblemName);
  }

  createNewProblem() {
    this.$store.commit('resetStore');
    this.$store.commit('casesStore/resetStore');
    window.location.reload();
  }
}
</script>
