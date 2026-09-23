<template>
  <div v-if="!hideHeaderActions" class="row mb-3">
    <div class="col d-flex align-items-center">
      <span class="me-2">{{ T.problemCreatorName }}</span>
      <input
        v-model="name"
        class="form-control form-control-sm"
        :placeholder="T.problemCreatorNewProblem"
      />
    </div>
    <div class="col d-flex justify-content-end">
      <button
        type="button"
        data-load-problem-button
        class="btn btn-success btn-sm me-2"
        @click="uploadZipModal = !uploadZipModal"
      >
        <font-awesome-icon icon="upload" class="me-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorLoadProblem }}</span
        >
      </button>
      <div v-if="uploadZipModal">
        <div class="modal fade show d-block" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">{{ T.problemCreatorZipFileUpload }}</h5>
                <button
                  type="button"
                  class="btn-close"
                  @click="uploadZipModal = false"
                ></button>
              </div>
              <div class="modal-body">
                <div class="mb-4">{{ T.problemCreatorUploadZipMessage }}</div>
                <input
                  data-upload-zip-file
                  class="w-100"
                  type="file"
                  accept=".zip"
                  @change="handleZipFile"
                />
              </div>
              <footer class="modal-footer">
                <button
                  type="button"
                  class="btn btn-danger"
                  @click="uploadZipModal = false"
                >
                  {{ T.caseModalBack }}
                </button>
                <button
                  type="button"
                  class="btn btn-success"
                  @click="onUploadZip"
                >
                  {{ T.problemCreatorUploadZip }}
                </button>
              </footer>
            </div>
          </div>
        </div>
        <div class="modal-backdrop fade show"></div>
      </div>
      <button
        type="button"
        data-download-zip
        class="btn btn-primary btn-sm me-2"
        @click="generateProblem()"
      >
        <font-awesome-icon icon="download" class="me-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorGenerateProblem }}</span
        >
      </button>
      <button
        type="button"
        class="btn btn-warning btn-sm"
        data-create-new-problem-button
        @click="newProblemConfirmationModal = !newProblemConfirmationModal"
      >
        <font-awesome-icon icon="plus" class="me-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorNewProblem }}</span
        >
      </button>
      <div v-if="newProblemConfirmationModal" data-create-new-problem>
        <div class="modal fade show d-block" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">
                  {{ T.problemCreatorCreateNewProblem }}
                </h5>
                <button
                  type="button"
                  class="btn-close"
                  @click="newProblemConfirmationModal = false"
                ></button>
              </div>
              <div class="modal-body">
                <div class="mb-4">
                  {{ T.problemCreatorCreateNewProblemWarning }}
                </div>
              </div>
              <footer class="modal-footer">
                <button
                  type="button"
                  class="btn btn-success"
                  @click="newProblemConfirmationModal = false"
                >
                  {{ T.problemCreatorCreateNewProblemBack }}
                </button>
                <button
                  type="button"
                  class="btn btn-danger"
                  @click="onCreateNewProblem"
                >
                  {{ T.problemCreatorCreateNewProblemContinue }}
                </button>
              </footer>
            </div>
          </div>
        </div>
        <div class="modal-backdrop fade show"></div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue, Watch } from 'vue-property-decorator';
import JSZip from 'jszip';
import { namespace } from 'vuex-class';
import T from '../../../lang';
import * as ui from '../../../ui';
import { Group, CaseGroupID } from '@/js/omegaup/problem/creator/types';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';

library.add(fas);

const casesStore = namespace('casesStore');

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class Header extends Vue {
  @Prop({ default: false }) hideHeaderActions!: boolean;

  T = T;
  zipFile: File | null = null;
  uploadZipModal: boolean = false;
  newProblemConfirmationModal: boolean = false;

  nameInternal: string = T.problemCreatorEmpty;

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

  async retrieveStore(): Promise<void> {
    if (this.zipFile) {
      await this.importZipFile(this.zipFile);
    }
  }

  async onUploadZip(): Promise<void> {
    await this.retrieveStore();
    this.uploadZipModal = false;
  }

  onCreateNewProblem(): void {
    this.createNewProblem();
    this.newProblemConfirmationModal = false;
  }

  async importZipFile(zipFile: File): Promise<boolean> {
    try {
      const zipContent = await new JSZip().loadAsync(zipFile);
      const cdpDataFile = zipContent.file('cdp.data');
      if (!cdpDataFile) {
        ui.error(T.problemCreatorZipFileIsNotComplete);
        return false;
      }
      const storeData = JSON.parse(await cdpDataFile.async('text'));
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
      return true;
    } catch {
      ui.error(T.problemCreatorZipFileIsNotValid);
      return false;
    }
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
    // A fresh zip is built on every call so files removed from the store
    // since the last generation do not linger in the archive.
    const zip = new JSZip();
    this.getStatement(zip);
    this.getSolution(zip);
    this.getCasesAndTestPlan(zip);

    const problemName: string = this.$store.state.problemName;
    this.$emit('download-zip-file', {
      fileName: problemName.replace(/ /g, '_'),
      zipContent: zip,
    });
  }

  createNewProblem() {
    this.$store.commit('resetStore');
    this.$store.commit('casesStore/resetStore');
    window.location.reload();
  }
}
</script>
