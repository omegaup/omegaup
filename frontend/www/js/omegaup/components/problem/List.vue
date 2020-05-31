<template>
  <div>
    <omegaup-problem-search-bar
      v-bind:initialLanguage="language"
      v-bind:languages="languages"
      v-bind:initialKeyword="keyword"
      v-bind:modes="modes"
      v-bind:columns="columns"
      v-bind:initialMode="mode"
      v-bind:initialColumn="column"
      v-bind:tags="tags"
    ></omegaup-problem-search-bar>
    <a href="#" class="d-inline-block mb-3" role="button" v-on:click="showFinderWizard = true">
      {{ T.wizardLinkText }}
    </a>
    <!-- TODO: Migrar el problem finder a BS4 (solo para eliminar algunos estilos) -->
    <omegaup-problem-finder
      v-bind:possible-tags="wizardTags"
      v-on:close="showFinderWizard = false"
      v-on:search-problems="wizardSearch"
      v-show="showFinderWizard"
    ></omegaup-problem-finder>
    <div class="card">
      <h5 class="card-header">
        {{ T.wordsProblems }}
      </h5>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th scope="col" class="align-middle">
                {{ T.wordsTitle }}
                <div>
                  <span class="badge badge-quality mr-1">{{ T.tagSourceQuality }}</span>
                  <span class="badge badge-owner mr-1">{{ T.tagSourceOwner }}</span>
                  <span class="badge badge-voted">{{ T.tagSourceVoted }}</span>
                </div>
              </th>
              <th scope="col" class="text-center align-middle">{{ T.wordsQuality }}</th>
              <th scope="col" class="text-center align-middle">{{ T.wordsDifficulty }}</th>
              <th scope="col" class="text-right align-middle">{{ T.wordsRatio }}</th>
              <th scope="col" class="text-right align-middle">
                {{ T.wordsPointsForRank }}
                <a
                  data-toggle="tooltip"
                  href="https://blog.omegaup.com/el-nuevo-ranking-de-omegaup/"
                  rel="tooltip"
                  title=""
                  v-bind:data-original-title="T.wordsPointsForRankTooltip"
                  ><img src="/media/question.png"
                /></a>
              </th>
              <th scope="col" class="text-right align-middle" v-if="loggedIn">{{ T.wordsMyScore }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="problem in problems">
              <td>
                <a v-bind:href="`/arena/problem/${problem.alias}/`">
                  {{ problem.title }}
                </a>
                <!--TODO: manejar esto-->
                <span
                  v-bind:class="
                    `glyphicon ${iconClassForProblem(
                      problem.quality_seal,
                      problem.visibility,
                    )}`
                  "
                  v-bind:title="
                    iconTitleForProblem(problem.quality_seal, problem.visibility)
                  "
                ></span>
                <span
                  v-if="problem.visibility === -1"
                  v-bind:class="`glyphicon glyphicon-eye-close`"
                  v-bind:title="T.wordsPrivate"
                ></span>
                <!-- hasta aqui -->
                <div v-if="problem.tags.length">
                  <a
                    v-bind:class="`badge badge-${tag.source} mr-1`"
                    v-bind:href="hrefForProblemTag(currentTags, tag.name)"
                    v-for="tag in problem.tags"
                    >{{ T.hasOwnProperty(tag.name) ? T[tag.name] : tag.name }}</a
                  >
                </div>
              </td>
              <td class="text-center tooltip_column" v-if="problem.quality !== null">
                <span
                  data-wenk-pos="right"
                  v-bind:data-wenk="
                    `${UI.formatString(T.wordsOutOf4, {
                      Score: problem.difficulty.toFixed(1),
                    })}`
                  "
                  >{{ QUALITY_TAGS[Math.round(problem.quality)] }}</span
                >
              </td>
              <td class="text-right" v-else="">—</td>
              <td
                class="text-center"
                v-if="true || problem.difficulty !== null"
                v-tooltip="`${
                  UI.formatString(T.wordsOutOf4, {
                    Score: 2.3,
                  })
                }`"
              >
                <span
                  >{{ DIFFICULTY_TAGS[Math.round(2.3)] }}</span
                >
              </td>
              <td class="text-center" v-else="">—</td>
              <td class="text-right">
                {{ (100.0 * problem.ratio).toFixed(2) }}%
              </td>
              <td class="text-right">{{ problem.points.toFixed(2) }}</td>
              <td class="text-right" v-if="loggedIn">
                {{ problem.score.toFixed(2) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        <omegaup-common-paginator
          v-bind:pagerItems="pagerItems"
        ></omegaup-common-paginator>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.badge {
  color: black;

  &:hover {
    background-color: rgba(black, .35);
  }

  &-owner {
    background-color: #ccc;
  }

  &-quality {
    background-color: #ffeb3b;
  }

  &-voted {
    background-color: #99c2ff;
  }
}

.omegaup-quality-badge {
  width: 18px;
  height: 18px;
  background: url('/media/quality-badge-sm.png') center/contain no-repeat;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { types } from '../../api_types';
import * as UI from '../../ui';
import { VTooltip } from 'v-tooltip';
import common_Paginator from '../common/Paginatorv2.vue';
import problem_FinderWizard from './FinderWizard.vue';
import problem_SearchBar from './SearchBar.vue';

@Component({
  components: {
    'omegaup-problem-finder': problem_FinderWizard,
    'omegaup-common-paginator': common_Paginator,
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

  T = T;
  UI = UI;
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

  iconClassForProblem(qualitySeal: boolean, visibility: number): string {
    if (qualitySeal || visibility == 3) return 'omegaup-quality-badge';
    else if (visibility == 1 || visibility == -1)
      return 'glyphicon-warning-sign';
    else if (visibility <= -2) return 'glyphicon-ban-circle';
    else if (visibility == 0) return 'glyphicon-eye-close';
    return '';
  }

  iconTitleForProblem(qualitySeal: boolean, visibility: number): string {
    if (qualitySeal || visibility == 3) return T.wordsHighQualityProblem;
    else if (visibility == 1 || visibility == -1) return T.wordsWarningProblem;
    else if (visibility <= -3) return T.wordsBannedProblem;
    else if (visibility == 0) return T.wordsPrivate;
    return '';
  }

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
