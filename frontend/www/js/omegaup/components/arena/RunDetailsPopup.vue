<template>
  <omegaup-overlay-popup @dismiss="$emit('dismiss')">
    <form data-run-details-view>
      <div v-if="data">
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
                      {{ problemCase.contest_score ?? problemCase.score }}
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
        <omegaup-arena-code-view
          v-else
          :language="data.language"
          :readonly="true"
          :value="data.source"
        ></omegaup-arena-code-view>
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
      </div>
    </form>
  </omegaup-overlay-popup>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import arena_CodeView from './CodeView.vue';
import arena_DiffView from './DiffView.vue';
import omegaup_OverlayPopup from '../OverlayPopup.vue';

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
    'omegaup-arena-code-view': arena_CodeView,
    'omegaup-arena-diff-view': arena_DiffView,
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
})
export default class ArenaRunDetailsPopup extends Vue {
  @Prop() data!: types.RunDetails;

  EMPTY_FIELD = EMPTY_FIELD;
  T = T;
  groupVisible: GroupVisibility = {};

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
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
