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
      <b-button class="mr-2" variant="light" size="sm">
        <BIconUpload class="mr-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorLoadProblem }}</span
        >
      </b-button>
      <b-button
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
import { namespace } from 'vuex-class';
import T from '../../../lang';
import { Group, CaseGroupID } from '@/js/omegaup/problem/creator/types';

const casesStore = namespace('casesStore');
import JSZip from 'jszip';

@Component
export default class Header extends Vue {
  T = T;
  name: string = T.problemCreatorEmpty;

  @casesStore.State('groups') groups!: Group[];
  @casesStore.Getter('getStringifiedLinesFromCaseGroupID')
  getStringifiedLinesFromCaseGroupID!: (caseGroupID: CaseGroupID) => string;

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
    const solutionMarkdownData = this.$store.state.problemMarkdown;
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
    let zip = new JSZip();
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
