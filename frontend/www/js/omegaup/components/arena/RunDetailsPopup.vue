<template>
  <omegaup-overlay-popup @dismiss="$emit('dismiss')">
    <div v-if="data">
      <form data-run-details-view>
        <slot
          name="feedback"
          :feedback="data.feedback"
          :guid="data.guid"
          :is-admin="data.admin"
        ></slot>
        <div v-if="data.groups">
          <h3>{{ T.wordsCases }}</h3>
          <div></div>
          <table class="w-100">
            <thead>
              <tr class="text-center">
                <th>{{ T.wordsGroup }}</th>
                <th v-if="data.feedback !== 'summary'">{{ T.wordsCase }}</th>
                <th>{{ T.wordsVerdict }}</th>
                <th colspan="3">{{ T.rankScore }}</th>
                <th width="1"></th>
              </tr>
            </thead>
            <tbody v-for="element in data.groups" :key="element.group">
              <tr class="group border-top">
                <th class="text-center">{{ element.group }}</th>
                <th v-if="element.verdict" class="text-center">
                  {{ element.verdict }}
                </th>
                <th v-else colspan="2">
                  <div
                    class="w-100 h-100 my-0 mx-auto text-center bg-white text-dark rounded"
                    @click="toggle(element.group)"
                  >
                    <font-awesome-icon
                      v-if="groupVisible[element.group]"
                      :icon="['fas', 'chevron-circle-up']"
                    />
                    <font-awesome-icon
                      v-else
                      :icon="['fas', 'chevron-circle-down']"
                    />
                  </div>
                </th>
                <th class="text-right">
                  {{
                    element.contest_score
                      ? element.contest_score
                      : element.score
                  }}
                </th>
                <template v-if="element.max_score">
                  <th class="text-center" width="10">/</th>
                  <th>{{ element.max_score }}</th>
                </template>
              </tr>
              <template v-if="groupVisible[element.group]">
                <template v-for="problemCase in element.cases">
                  <tr :key="problemCase.name">
                    <td></td>
                    <td class="text-center">{{ problemCase.name }}</td>
                    <td class="text-center">{{ problemCase.verdict }}</td>
                    <td class="text-right">
                      {{ contestScore(problemCase) }}
                    </td>
                    <td class="text-center" width="10">
                      {{ problemCase.max_score ? '/' : '' }}
                    </td>
                    <td>
                      {{ problemCase.max_score ? problemCase.max_score : '' }}
                    </td>
                  </tr>
                  <template v-if="shouldShowDiffs(problemCase.name)">
                    <tr :key="`input-title-${problemCase.name}`">
                      <td colspan="6">{{ T.wordsInput }}</td>
                    </tr>
                    <tr :key="`input-${problemCase.name}`">
                      <td colspan="6">
                        <pre>
                          <code>{{
                            showDataCase(data.cases, problemCase.name, 'in')
                          }}</code>
                        </pre>
                      </td>
                    </tr>
                    <tr :key="`diffs-title-${problemCase.name}`">
                      <td colspan="6">{{ T.wordsDifference }}</td>
                    </tr>
                    <tr :key="`diffs-${problemCase.name}`">
                      <td v-if="data.cases" colspan="6">
                        <omegaup-arena-diff-view
                          :left="data.cases[problemCase.name].out"
                          :right="
                            getContestantOutput(data.cases, problemCase.name)
                          "
                        ></omegaup-arena-diff-view>
                      </td>
                      <td v-else colspan="6" class="empty-table-message">
                        {{ EMPTY_FIELD }}
                      </td>
                    </tr>
                  </template>
                </template>
              </template>
            </tbody>
          </table>
        </div>
        <h3>{{ T.wordsSource }}</h3>
        <a v-if="data.source_link" download="data.zip" :href="data.source">{{
          T.wordsDownload
        }}</a>
        <div v-else>
          <div v-for="(code, index) in splittedCode" :key="index">
            <omegaup-arena-code-view
              :language="data.language"
              :lines-per-chunk="getLinesPerCode(index)"
              :enable-feedback="false"
              :readonly="true"
              :value="code"
              @show-feedback-form="onShowFeedbackForm"
            ></omegaup-arena-code-view>
            <div v-if="index != splittedCode.length - 1" class="card">
              <textarea
                :placeholder="T.runDetailsFeedbackPlaceholder"
              ></textarea>
              <div class="form-group my-2">
                <button class="btn-primary mx-2" @click="onSubmit(index)">
                  {{ T.runDetailsFeedbackSubmit }}
                </button>
                <button class="btn-danger mx-2" @click="onCancel(index)">
                  {{ T.runDetailsFeedbackCancel }}
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-if="data.compile_error" class="compile_error">
          <h3>{{ T.wordsCompilerOutput }}</h3>
          <pre class="compile_error">
            <code v-text="data.compile_error"></code>
          </pre>
        </div>
        <div v-if="data.logs" class="logs">
          <h3>{{ T.wordsLogs }}</h3>
          <pre>
            <code v-text="data.logs"></code>
          </pre>
        </div>
        <div class="download">
          <h3>{{ T.wordsDownload }}</h3>
          <ul>
            <li>
              <a
                class="sourcecode"
                :download="data.source_name"
                :href="data.source_url"
                >{{ T.wordsDownloadCode }}</a
              >
            </li>
            <li>
              <a
                v-if="data.admin"
                class="output"
                :href="`/api/run/download/run_alias/${data.guid}/`"
                >{{ T.wordsDownloadOutput }}</a
              >
            </li>
            <li>
              <a
                v-if="data.admin"
                class="details"
                :href="`/api/run/download/run_alias/${data.guid}/complete/true/`"
                >{{ T.wordsDownloadDetails }}</a
              >
            </li>
          </ul>
        </div>
        <div v-if="data.judged_by" class="judged_by">
          <h3>{{ T.wordsJudgedBy }}</h3>
          <pre>
            <code v-text="data.judged_by"></code>
          </pre>
        </div>
      </form>
    </div>
    <div v-else>
      <clip-loader :color="'#678dd7'" :size="'3rem'"></clip-loader>
    </div>
  </omegaup-overlay-popup>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import arena_CodeView from './CodeView.vue';
import arena_DiffView from './DiffView.vue';
import omegaup_OverlayPopup from '../OverlayPopup.vue';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faChevronCircleUp,
  faChevronCircleDown,
} from '@fortawesome/free-solid-svg-icons';
library.add(faChevronCircleUp);
library.add(faChevronCircleDown);

interface GroupVisibility {
  [name: string]: boolean;
}

const EMPTY_FIELD = '∅';

@Component({
  components: {
    FontAwesomeIcon,
    'clip-loader': ClipLoader,
    'omegaup-arena-code-view': arena_CodeView,
    'omegaup-arena-diff-view': arena_DiffView,
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
})
export default class ArenaRunDetailsPopup extends Vue {
  @Prop() data!: types.RunDetails;
  @Prop({ default: () => [] }) feedbackLines!: number[];

  EMPTY_FIELD = EMPTY_FIELD;
  T = T;
  groupVisible: GroupVisibility = {};
  currentFeedbackLines = this.feedbackLines;

  get numberOfLines(): number {
    const lines = this.data.source?.split('\n') ?? [];
    return lines.length;
  }

  get splittedCode(): string[] {
    const ranges: { start: number; end: number }[] = [];
    let previousLine = 0;
    const splittedCode: string[][] = [];
    const splittedCodeString: string[] = [];
    const codeSplittedByLine = this.data.source?.split('\n') ?? [];
    for (const item of this.currentFeedbackLines) {
      let start = 0;
      if (previousLine != 0) {
        start = previousLine + 1;
      }
      const range = { start: start, end: item + 1 };
      ranges.push(range);
      previousLine = item;
      splittedCode.push(codeSplittedByLine.slice(range.start, range.end));
    }

    if (!splittedCode.length) {
      splittedCode.push(codeSplittedByLine.splice(0));
    } else {
      splittedCode.push(
        codeSplittedByLine.splice(this.currentFeedbackLines.slice(-1)[0] + 1),
      );
    }
    for (const code of splittedCode) {
      const chunk = code.join('\n');
      splittedCodeString.push(chunk);
    }
    return splittedCodeString;
  }

  getLinesPerCode(index: number): number[] {
    let start = 0;
    const linesPerChunk: number[][] = [];
    for (const lines of this.currentFeedbackLines) {
      linesPerChunk.push(this.numberRange(start + 1, lines + 2));
      start = lines + 1;
    }
    linesPerChunk.push(this.numberRange(start + 1, this.numberOfLines + 2));

    return linesPerChunk[index];
  }

  numberRange(start: number, end: number): number[] {
    return new Array(end - start).fill(0).map((_d, i) => i + start);
  }

  onShowFeedbackForm(line: number): void {
    this.currentFeedbackLines.push(line - 1);
    this.currentFeedbackLines.sort();
  }

  onCancel(index: number): void {
    this.currentFeedbackLines.splice(index, 1);
  }

  onSubmit(index: number): void {
    console.log(`Sending feedback for ${index}...`);
  }

  toggle(group: string): void {
    const visible = this.groupVisible[group];
    this.$set(this.groupVisible, group, !visible);
  }

  showDataCase(
    cases: types.ProblemCasesContents,
    caseName: string,
    caseType: 'in' | 'out' | 'contestantOutput',
  ): string {
    return cases[caseName]?.[caseType] ?? EMPTY_FIELD;
  }

  shouldShowDiffs(caseName: string): boolean {
    return (
      this.data.show_diff === 'all' ||
      (caseName === 'sample' && this.data.show_diff === 'examples')
    );
  }

  getContestantOutput(cases: types.ProblemCasesContents, name: string): string {
    return cases[name]?.contestantOutput ?? '';
  }

  contestScore(problemCase: types.CaseResult): number {
    return problemCase.contest_score ?? problemCase.score;
  }
}
</script>
