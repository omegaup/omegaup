<template>
  <div class="card">
    <div class="table-responsive mb-0">
      <table class="table">
        <thead>
          <tr class="sticky-top bg-white text-center">
            <th scope="col" class="align-middle text-nowrap">
              <span
                >{{ T.wordsID }}
                <omegaup-common-sort-controls
                  column="problem_id"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @apply-filter="
                    (columnName, sortOrder) =>
                      $emit('apply-filter', columnName, sortOrder)
                  "
                ></omegaup-common-sort-controls
              ></span>
            </th>
            <th scope="col" class="align-middle text-nowrap">
              <span>{{ T.wordsTitle }}</span>
              <span
                v-if="showProblemTags"
                class="badge custom-badge custom-badge-quality mr-1 ml-1 p-2"
                >{{ T.tagSourceLevel }}</span
              >
              <span
                v-if="showProblemTags"
                class="badge custom-badge custom-badge-owner mr-1 p-2"
                >{{ T.tagSourceOwner }}</span
              >
              <span
                v-if="showProblemTags"
                class="badge custom-badge custom-badge-voted p-2"
                >{{ T.tagSourceVoted }}</span
              >
              <omegaup-common-sort-controls
                column="title"
                :column-type="omegaup.ColumnType.String"
                :sort-order="sortOrder"
                :column-name="columnName"
                @apply-filter="
                  (columnName, sortOrder) =>
                    $emit('apply-filter', columnName, sortOrder)
                "
              ></omegaup-common-sort-controls>
            </th>
            <th scope="col" class="align-middle text-nowrap">
              <span
                >{{ T.wordsQuality }}
                <omegaup-common-sort-controls
                  column="quality"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @apply-filter="
                    (columnName, sortOrder) =>
                      $emit('apply-filter', columnName, sortOrder)
                  "
                ></omegaup-common-sort-controls
              ></span>
            </th>
            <th scope="col" class="align-middle text-nowrap">
              <span
                >{{ T.wordsDifficulty }}
                <omegaup-common-sort-controls
                  column="difficulty"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @apply-filter="
                    (columnName, sortOrder) =>
                      $emit('apply-filter', columnName, sortOrder)
                  "
                ></omegaup-common-sort-controls
              ></span>
            </th>
            <th scope="col" class="align-middle text-nowrap">
              <span
                >{{ T.wordsRatio }}
                <omegaup-common-sort-controls
                  column="ratio"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @apply-filter="
                    (columnName, sortOrder) =>
                      $emit('apply-filter', columnName, sortOrder)
                  "
                ></omegaup-common-sort-controls
              ></span>
            </th>
            <th v-if="loggedIn" scope="col" class="align-middle text-nowrap">
              <span
                >{{ T.wordsMyScore }}
                <omegaup-common-sort-controls
                  column="score"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @apply-filter="
                    (columnName, sortOrder) =>
                      $emit('apply-filter', columnName, sortOrder)
                  "
                ></omegaup-common-sort-controls
              ></span>
            </th>
            <th v-if="loggedIn && showNotes" scope="col" class="align-middle text-nowrap">
              <span>{{ T.wordsNotes }}</span>
            </th>
            <th scope="col" class="align-middle text-nowrap">
              <span>
                <a
                  data-toggle="tooltip"
                  :href="UserRankingFeatureGuideURL"
                  rel="tooltip"
                  :title="T.wordsPointsForRank"
                  :data-original-title="T.wordsPointsForRankTooltip"
                  ><img src="/media/question.png" :alt="T.wordsPointsForRank"
                /></a>
                <omegaup-common-sort-controls
                  column="points"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @apply-filter="
                    (columnName, sortOrder) =>
                      $emit('apply-filter', columnName, sortOrder)
                  "
                ></omegaup-common-sort-controls>
              </span>
            </th>
          </tr>
        </thead>
        <tbody data-problems>
          <tr v-for="problem in problems" :key="problem.problem_id">
            <td class="align-middle">{{ problem.problem_id }}</td>
            <td class="align-middle">
              <a
                :href="`/arena/problem/${problem.alias}/`"
                class="mr-2"
                data-problem-title-list
                >{{ problem.title }}</a
              >
              <font-awesome-icon
                v-if="problem.qualitySeal || problem.visibility === 3"
                :title="T.wordsHighQualityProblem"
                :icon="['fas', 'medal']"
                color="gold"
              />
              <font-awesome-icon
                v-else-if="problem.visibility === -1"
                :title="T.wordsWarningProblem"
                :icon="['fas', 'exclamation-triangle']"
              />
              <font-awesome-icon
                v-else-if="problem.visibility <= -3"
                :title="T.wordsBannedProblem"
                :icon="['fas', 'ban']"
              />
              <font-awesome-icon
                v-else-if="problem.visibility === 0"
                :title="T.wordsPrivate"
                :icon="['fas', 'eye-slash']"
              />
              <a
                v-for="tag in problem.tags"
                :key="tag.name"
                :class="`badge custom-badge custom-badge-${
                  tag.source.includes('quality') ? 'owner' : tag.source
                } ${
                  tag.name.includes('problemLevel')
                    ? 'custom-badge-quality'
                    : ''
                } m-1 p-2`"
                :href="hrefForProblemTag(selectedTags, tag.name)"
                >{{
                  Object.prototype.hasOwnProperty.call(T, tag.name)
                    ? T[tag.name]
                    : tag.name
                }}</a
              >
            </td>
            <td
              v-if="problem.quality !== null"
              class="text-center align-middle tooltip_column"
            >
              <span
                v-tooltip="
                  `${ui.formatString(T.wordsOutOf4, {
                    Score: problem.quality.toFixed(1),
                  })}`
                "
              >
                {{ QUALITY_TAGS[Math.round(problem.quality)] }}
              </span>
            </td>
            <td v-else class="text-right align-middle">—</td>
            <td
              v-if="problem.difficulty !== null"
              class="text-center align-middle"
            >
              <span
                v-tooltip="
                  `${ui.formatString(T.wordsOutOf4, {
                    Score: problem.difficulty.toFixed(1),
                  })}`
                "
              >
                {{ DIFFICULTY_TAGS[Math.round(problem.difficulty)] }}
              </span>
            </td>
            <td v-else class="text-center align-middle">—</td>
            <td class="text-right align-middle">
              {{ (100.0 * problem.ratio).toFixed(2) }}%<br />({{
                problem.accepted
              }}/{{ problem.submissions }})
            </td>
            <td v-if="loggedIn" class="text-right align-middle">
              <span
                :title="getProblemStatusTitle(problem)"
                :class="['badge', getProblemStatusClass(problem)]"
              >
                {{ problem.score.toFixed(2) }}
              </span>
            </td>
            <td v-if="loggedIn && showNotes" class="text-center align-middle">
              <button
                class="btn btn-link p-0"
                :title="
                  notes[problem.problem_id]
                    ? T.problemNoteEdit
                    : T.problemNoteAdd
                "
                @click="openNoteModal(problem.alias, problem.problem_id)"
              >
                <font-awesome-icon
                  :icon="['fas', 'sticky-note']"
                  class="note-icon"
                  :class="{
                    'note-active': !!notes[problem.problem_id],
                    'note-inactive': !notes[problem.problem_id],
                  }"
                />
              </button>
            </td>
            <td class="text-right align-middle">
              {{ problem.points.toFixed(2) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      <omegaup-common-paginator
        :pager-items="pagerItems"
      ></omegaup-common-paginator>
    </div>
    <omegaup-problem-note-modal
      v-if="showNotes && showNoteModal"
      :initial-note-text="noteModalText"
      :problem-alias="noteModalAlias"
      :operation-failed="noteOperationFailed"
      @save-note="onSaveNote"
      @delete-note="onDeleteNote"
      @close="showNoteModal = false"
    ></omegaup-problem-note-modal>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';

import common_Paginator from '../common/Paginator.vue';
import common_SortControls from '../common/SortControls.vue';
import problem_NoteModal from './NoteModal.vue';

import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';
import { getBlogUrl } from '../../urlHelper';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faEyeSlash,
  faMedal,
  faExclamationTriangle,
  faBan,
  faStickyNote,
} from '@fortawesome/free-solid-svg-icons';
library.add(faEyeSlash, faMedal, faExclamationTriangle, faBan, faStickyNote);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-common-paginator': common_Paginator,
    'omegaup-common-sort-controls': common_SortControls,
    'omegaup-problem-note-modal': problem_NoteModal,
  },
  directives: {
    tooltip: VTooltip,
  },
})
export default class BaseList extends Vue {
  @Prop() problems!: omegaup.Problem[];
  @Prop() loggedIn!: boolean;
  @Prop() selectedTags!: string[];
  @Prop() pagerItems!: types.PageItem[];
  @Prop() wizardTags!: omegaup.Tag[];
  @Prop() language!: string;
  @Prop() languages!: string[];
  @Prop() keyword!: string;
  @Prop() modes!: string[];
  @Prop() columns!: string[];
  @Prop() mode!: string;
  @Prop() column!: string;
  @Prop() tags!: string[];
  @Prop() sortOrder!: string;
  @Prop() columnName!: string;
  @Prop() path!: string;
  @Prop({ default: true }) showProblemTags!: boolean;
  @Prop({ default: false }) showNotes!: boolean;
  @Prop({ default: () => ({}) }) notes!: { [key: number]: string };
  @Prop({ default: 0 }) noteOperationFailed!: number;
  @Prop({ default: () => [] }) solvedProblemAliases!: string[];
  @Prop({ default: () => [] }) attemptedProblemAliases!: string[];

  T = T;
  ui = ui;
  omegaup = omegaup;
  showFinderWizard = false;
  showNoteModal = false;
  noteModalAlias = '';
  noteModalText = '';

  get solvedProblemAliasesSet(): Set<string> {
    return new Set(this.solvedProblemAliases);
  }

  get attemptedProblemAliasesSet(): Set<string> {
    return new Set(this.attemptedProblemAliases);
  }

  getProblemStatusTitle(problem: omegaup.Problem): string {
    if (this.solvedProblemAliasesSet.has(problem.alias)) {
      return T.problemStatusSolved;
    }
    if (this.attemptedProblemAliasesSet.has(problem.alias)) {
      return T.problemStatusAttempted;
    }
    return T.problemStatusUnattempted;
  }

  getProblemStatusClass(problem: omegaup.Problem): string {
    if (this.solvedProblemAliasesSet.has(problem.alias)) {
      return 'badge-success';
    }
    if (this.attemptedProblemAliasesSet.has(problem.alias)) {
      return 'badge-warning';
    }
    return 'badge-secondary';
  }

  QUALITY_TAGS = [
    T.qualityFormQualityVeryBad,
    T.qualityFormQualityBad,
    T.qualityFormQualityFair,
    T.qualityFormQualityGood,
    T.qualityFormQualityVeryGood,
  ];
  DIFFICULTY_TAGS = [
    T.qualityFormDifficultyVeryEasy,
    T.qualityFormDifficultyEasy,
    T.qualityFormDifficultyMedium,
    T.qualityFormDifficultyHard,
    T.qualityFormDifficultyVeryHard,
  ];

  hrefForProblemTag(selectedTags: string[], problemTag: string): string {
    if (!selectedTags) return `${this.path}?tag[]=${problemTag}`;
    let tags = selectedTags.slice();
    if (!tags.includes(problemTag)) tags.push(problemTag);
    return `${this.path}?tag[]=${tags.join('&tag[]=')}`;
  }

  @Watch('notes', { deep: true })
  onNotesChanged(): void {
    if (this.showNoteModal) {
      this.showNoteModal = false;
    }
  }

  openNoteModal(problemAlias: string, problemId: number): void {
    this.noteModalAlias = problemAlias;
    this.noteModalText = this.notes[problemId] || '';
    this.showNoteModal = true;
  }

  onSaveNote(alias: string, text: string): void {
    this.$emit('save-note', alias, text);
  }

  onDeleteNote(alias: string): void {
    this.$emit('delete-note', alias);
  }

  get UserRankingFeatureGuideURL(): string {
    return getBlogUrl('UserRankingFeatureGuideURL');
  }
}
</script>

<style lang="scss" scoped>
.sticky-offset {
  top: 4rem;
}
.sticky-top {
  z-index: 1;
}
.card {
  border-top: none;
  border-radius: 0rem 0rem 0.25rem 0.25rem;
}

table {
  border-collapse: separate;
  border-spacing: 0;
}

thead tr th {
  border: none;
}

.note-icon {
  font-size: 1.2em;
}

.note-active {
  color: var(--note-icon-active-color, #678dd7);
}

.note-inactive {
  color: var(--note-icon-inactive-color, #ccc);
  opacity: 0.5;
}
</style>
