<template>
  <div>
    <div v-if="isProblemPage">
      <omegaup-problem-search-bar
        :initial-language="language"
        :languages="languages"
        :initial-keyword="keyword"
        :tags="tags"
      ></omegaup-problem-search-bar>
      <a
        href="#"
        class="d-inline-block mb-3"
        role="button"
        @click="showFinderWizard = true"
      >
        {{ T.wizardLinkText }}
      </a>
      <!-- TODO: Migrar el problem finder a BS4 (solo para eliminar algunos estilos) -->
      <omegaup-problem-finder
        v-show="showFinderWizard"
        :possible-tags="wizardTags"
        @close="showFinderWizard = false"
        @search-problems="wizardSearch"
      ></omegaup-problem-finder>
    </div>
    <div class="card">
      <h5 class="card-header">
        {{ T.wordsProblems }}
      </h5>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th scope="col" class="align-middle text-nowrap">
                <span
                  >{{ T.wordsID }}
                  <omegaup-common-sort-controls
                    column="problem_id"
                    :sort-order="sortOrder"
                    :column-name="columnName"
                    @emit-apply-filter="
                      (columnName, sortOrder) =>
                        $emit('apply-filter', columnName, sortOrder)
                    "
                  ></omegaup-common-sort-controls
                ></span>
              </th>
              <th scope="col" class="align-middle text-nowrap">
                <span>{{ T.wordsTitle }}</span>
                <span
                  class="badge custom-badge custom-badge-quality mr-1 ml-1 p-2"
                  >{{ T.tagSourceLevel }}</span
                >
                <span class="badge custom-badge custom-badge-owner mr-1 p-2">{{
                  T.tagSourceOwner
                }}</span>
                <span class="badge custom-badge custom-badge-voted p-2">{{
                  T.tagSourceVoted
                }}</span>
                <omegaup-common-sort-controls
                  column="title"
                  :column-type="omegaup.ColumnType.String"
                  :sort-order="sortOrder"
                  :column-name="columnName"
                  @emit-apply-filter="
                    (columnName, sortOrder) =>
                      $emit('apply-filter', columnName, sortOrder)
                  "
                ></omegaup-common-sort-controls>
              </th>
              <th scope="col" class="text-center align-middle text-nowrap">
                <span
                  >{{ T.wordsQuality }}
                  <omegaup-common-sort-controls
                    column="quality"
                    :sort-order="sortOrder"
                    :column-name="columnName"
                    @emit-apply-filter="
                      (columnName, sortOrder) =>
                        $emit('apply-filter', columnName, sortOrder)
                    "
                  ></omegaup-common-sort-controls
                ></span>
              </th>
              <th scope="col" class="text-center align-middle text-nowrap">
                <span
                  >{{ T.wordsDifficulty }}
                  <omegaup-common-sort-controls
                    column="difficulty"
                    :sort-order="sortOrder"
                    :column-name="columnName"
                    @emit-apply-filter="
                      (columnName, sortOrder) =>
                        $emit('apply-filter', columnName, sortOrder)
                    "
                  ></omegaup-common-sort-controls
                ></span>
              </th>
              <th scope="col" class="text-right align-middle text-nowrap">
                <span
                  >{{ T.wordsRatio }}
                  <omegaup-common-sort-controls
                    column="ratio"
                    :sort-order="sortOrder"
                    :column-name="columnName"
                    @emit-apply-filter="
                      (columnName, sortOrder) =>
                        $emit('apply-filter', columnName, sortOrder)
                    "
                  ></omegaup-common-sort-controls
                ></span>
              </th>
              <th
                v-if="loggedIn"
                scope="col"
                class="text-right align-middle text-nowrap"
              >
                <span
                  >{{ T.wordsMyScore }}
                  <omegaup-common-sort-controls
                    column="score"
                    :sort-order="sortOrder"
                    :column-name="columnName"
                    @emit-apply-filter="
                      (columnName, sortOrder) =>
                        $emit('apply-filter', columnName, sortOrder)
                    "
                  ></omegaup-common-sort-controls
                ></span>
              </th>
              <th scope="col" class="text-right align-middle text-nowrap">
                <span>
                  <a
                    data-toggle="tooltip"
                    href="https://blog.omegaup.com/el-nuevo-ranking-de-omegaup/"
                    rel="tooltip"
                    :title="T.wordsPointsForRank"
                    :data-original-title="T.wordsPointsForRankTooltip"
                    ><img src="/media/question.png"
                  /></a>
                  <omegaup-common-sort-controls
                    column="points"
                    :sort-order="sortOrder"
                    :column-name="columnName"
                    @emit-apply-filter="
                      (columnName, sortOrder) =>
                        $emit('apply-filter', columnName, sortOrder)
                    "
                  ></omegaup-common-sort-controls>
                </span>
              </th>
            </tr>
          </thead>
          <tbody data-problems>
            <tr v-for="problem in problems">
              <td>{{ problem.problem_id }}</td>
              <td>
                <a :href="`/arena/problem/${problem.alias}/`">{{
                  problem.title
                }}</a>
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
                  :class="`badge custom-badge custom-badge-${tag.source} ${
                    hrefForProblemTag(currentTags, tag.name).includes('Level')
                      ? 'custom-badge-quality'
                      : ''
                  } m-1 p-2`"
                  :href="hrefForProblemTag(currentTags, tag.name)"
                  >{{
                    Object.prototype.hasOwnProperty.call(T, tag.name)
                      ? T[tag.name]
                      : tag.name
                  }}</a
                >
              </td>
              <td
                v-if="problem.quality !== null"
                class="text-center tooltip_column"
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
              <td v-else class="text-right">—</td>
              <td v-if="problem.difficulty !== null" class="text-center">
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
              <td v-else class="text-center">—</td>
              <td class="text-right">
                {{ (100.0 * problem.ratio).toFixed(2) }}%
              </td>
              <td v-if="loggedIn" class="text-right">
                {{ problem.score.toFixed(2) }}
              </td>
              <td class="text-right">{{ problem.points.toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        <omegaup-common-paginator
          :pager-items="pagerItems"
        ></omegaup-common-paginator>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';

import common_Paginator from '../common/Paginatorv2.vue';
import common_SortControls from '../common/SortControls.vue';
import problem_FinderWizard from './FinderWizard.vue';
import problem_SearchBar from './SearchBar.vue';

import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faEyeSlash,
  faMedal,
  faExclamationTriangle,
  faBan,
} from '@fortawesome/free-solid-svg-icons';
library.add(faEyeSlash, faMedal, faExclamationTriangle, faBan);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-problem-finder': problem_FinderWizard,
    'omegaup-common-paginator': common_Paginator,
    'omegaup-common-sort-controls': common_SortControls,
    'omegaup-problem-search-bar': problem_SearchBar,
  },
  directives: {
    tooltip: VTooltip,
  },
})
export default class ProblemList extends Vue {
  @Prop() problems!: omegaup.Problem[];
  @Prop() loggedIn!: boolean;
  @Prop() currentTags!: string[];
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
  @Prop({ default: true }) isProblemPage!: boolean;

  T = T;
  ui = ui;
  omegaup = omegaup;
  showFinderWizard = false;
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

  hrefForProblemTag(currentTags: string[], problemTag: string): string {
    if (!currentTags) return `/problem/?tag[]=${problemTag}`;
    let tags = currentTags.slice();
    if (!tags.includes(problemTag)) tags.push(problemTag);
    return `/problem/?tag[]=${tags.join('&tag[]=')}`;
  }

  wizardSearch(queryParameters: omegaup.QueryParameters): void {
    this.$emit('wizard-search', queryParameters);
  }
}
</script>
