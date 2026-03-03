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
        <div class="source_code">
          <h3>{{ T.wordsCode }}</h3>
          <a v-if="data.source_link" download="data.zip" :href="data.source">{{
            T.wordsDownload
          }}</a>
          <slot v-else name="code-view" :guid="data.guid">
            <omegaup-arena-feedback-code-view
              :language="language"
              :value="source"
              :feedback-map="feedbackMap"
              :feedback-thread-map="feedbackThreadMap"
              @save-feedback-list="
                (feedbackList) => onSaveFeedbackList(feedbackList, data.guid)
              "
              @submit-feedback-thread="
                (feedback) => onSubmitFeedbackThread(feedback, data.guid)
              "
            ></omegaup-arena-feedback-code-view>
          </slot>
        </div>
        <div v-if="data.groups" class="cases">
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
        <div class="guid">
          <h3>{{ T.runGUID }}</h3>
          <acronym :title="data.guid" data-run-guid>
            <tt>{{ shortGuid }}</tt>
          </acronym>
        </div>
      </form>
    </div>
    <div v-else>
      <div class="spinner-border text-info big" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>
  </omegaup-overlay-popup>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import arena_DiffView from './DiffView.vue';
import omegaup_OverlayPopup from '../OverlayPopup.vue';
import { ArenaCourseFeedback } from './Feedback.vue';
import arena_FeedbackCodeView from './FeedbackCodeView.vue';

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
    'omegaup-arena-diff-view': arena_DiffView,
    'omegaup-overlay-popup': omegaup_OverlayPopup,
    'omegaup-arena-feedback-code-view': arena_FeedbackCodeView,
  },
})
export default class ArenaRunDetailsPopup extends Vue {
  @Prop() data!: types.RunDetails;
  @Prop({ default: () => new Map<number, ArenaCourseFeedback>() })
  feedbackMap!: Map<number, ArenaCourseFeedback>;
  @Prop({ default: () => new Map<number, ArenaCourseFeedback>() })
  feedbackThreadMap!: Map<number, ArenaCourseFeedback>;

  EMPTY_FIELD = EMPTY_FIELD;
  T = T;
  groupVisible: GroupVisibility = {};

  get language(): string | undefined {
    return this.data?.language;
  }

  get source(): string | undefined {
    return this.data?.source;
  }

  get shortGuid(): string {
    return this.data.guid.substring(0, 8);
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

  onSaveFeedbackList(
    feedbackList: { lineNumber: number; feedback: string }[],
    guid: string,
  ) {
    this.$parent?.$parent?.$parent?.$parent?.$emit('save-feedback-list', {
      feedbackList,
      guid,
    });
  }

  onSubmitFeedbackThread(feedback: ArenaCourseFeedback, guid: string) {
    this.$parent?.$parent?.$parent?.$parent?.$emit('submit-feedback-thread', {
      feedback,
      guid,
    });
  }
}
</script>

<style lang="scss" scoped>
.big {
  height: 3rem;
  width: 3rem;
}
</style>
