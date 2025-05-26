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
        data-load-problem-button
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
          data-upload-zip-file
          class="w-100"
          type="file"
          accept=".zip"
          @change="handleZipFile"
        />
      </b-modal>
      <b-button
        data-download-zip
        class="mr-2"
        variant="primary"
        size="sm"
        @click="generateProblem()"
      >
        <BIconDownload class="mr-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorGenerateProblem }}</span
        >
      </b-button>
      <b-button
        variant="warning"
        data-create-new-problem-button
        size="sm"
        @click="newProblemConfirmationModal = !newProblemConfirmationModal"
      >
        <BIconPlus class="mr-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorNewProblem }}</span
        >
      </b-button>
      <b-modal
        v-model="newProblemConfirmationModal"
        data-create-new-problem
        :title="T.problemCreatorCreateNewProblem"
        :ok-title="T.problemCreatorCreateNewProblemContinue"
        ok-variant="danger"
        :cancel-title="T.problemCreatorCreateNewProblemBack"
        cancel-variant="success"
        static
        lazy
        @ok="createNewProblem"
      >
        <div class="mb-4">{{ T.problemCreatorCreateNewProblemWarning }}</div>
      </b-modal>
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Vue, Watch } from 'vue-property-decorator';
import JSZip from 'jszip';
import { namespace } from 'vuex-class';
import T from '../../../lang';
import * as ui from '../../../ui';
import { Group, CaseGroupID } from '@/js/omegaup/problem/creator/types';

const casesStore = namespace('casesStore');

@Component
export default class Header extends Vue {
  T = T;
  zipFile: File | null = null;
  uploadZipModal: boolean = false;
  newProblemConfirmationModal: boolean = false;

  nameInternal: string = T.problemCreatorEmpty;
  zip: JSZip = new JSZip();

  @casesStore.State('groups') groups!: Group[];
  @casesStore.Getter('getStringifiedLinesFromCaseGroupID')
  getStringifiedLinesFromCaseGroupID!: (caseGroupID: CaseGroupID) => string;

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
    const zipUploaded = new JSZip();
    zipUploaded
      .loadAsync(this.zipFile)
      .then((zipContent) => {
        const cdpDataFile = zipContent.file('cdp.data');
        if (!cdpDataFile) {
          ui.error(T.problemCreatorZipFileIsNotComplete);
          return;
        }
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
            this.$store.commit('casesStore/replaceState', storeData.casesStore);
          }
        });
      })
      .catch(() => {
        ui.error(T.problemCreatorZipFileIsNotValid);
      });
  }

  @Watch('name')
  onNameChanged(newProblemName: string) {
    this.$store.commit('updateName', newProblemName);
  }

  getStatement(zip: JSZip) {
    const folder = zip.folder('statements');
    const markdownData = this.$store.state.problemMarkdown;
    folder?.file('es.markdown', markdownData);
  }

  getSolution(zip: JSZip) {
    const folder = zip.folder('solutions');
    const solutionMarkdownData = this.$store.state.problemSolutionMarkdown;
    folder?.file('es.markdown', solutionMarkdownData);
  }

  getCasesAndTestPlan(zip: JSZip) {
    const folder = zip.folder('cases');
    let testPlanData: string = '';

    this.groups.forEach((_group) => {
      _group.cases.forEach((_case) => {
        let fileName = _case.name;
        if (_group.ungroupedCase === false) {
          fileName = _group.name + '.' + fileName;
        }
        const caseGroupID: CaseGroupID = {
          groupID: _group.groupID,
          caseID: _case.caseID,
        };
        const input = this.getStringifiedLinesFromCaseGroupID(caseGroupID);
        folder?.file(fileName + '.in', input);
        folder?.file(fileName + '.out', _case.output);
        testPlanData += fileName + ' ' + _case.points + '\n';
      });
    });

    zip.file('testplan', testPlanData);
    zip.file('cdp.data', JSON.stringify(this.$store.state));
  }

  generateProblem() {
    this.getStatement(this.zip);
    this.getSolution(this.zip);
    this.getCasesAndTestPlan(this.zip);

    const problemName: string = this.$store.state.problemName;
    this.$emit('download-zip-file', {
      fileName: problemName.replace(/ /g, '_'),
      zipContent: this.zip,
    });
  }

  createNewProblem() {
    this.$store.commit('resetStore');
    this.$store.commit('casesStore/resetStore');
    window.location.reload();
  }
}
</script>
