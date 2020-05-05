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
    <a class="show-finder-button" v-on:click="showFinderWizard = true">{{
      T.wizardLinkText
    }}</a>
    <omegaup-problem-finder
      v-bind:possible-tags="wizardTags"
      v-on:close="showFinderWizard = false"
      v-on:search-problems="wizardSearch"
      v-show="showFinderWizard"
    ></omegaup-problem-finder>
    <div class="wait_for_ajax panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">{{ T.wordsProblems }}</h3>
      </div>
      <table class="table problem-list">
        <thead>
          <tr>
            <th class="contains-long-desc">
              {{ T.wordsTitle }}
              <div>
                <span class="tag tag-quality">{{ T.tagSourceQuality }}</span>
                <span class="tag tag-owner">{{ T.tagSourceOwner }}</span>
                <span class="tag tag-voted">{{ T.tagSourceVoted }}</span>
              </div>
            </th>
            <th class="numericColumn">{{ T.wordsQuality }}</th>
            <th class="numericColumn">{{ T.wordsDifficulty }}</th>
            <th class="numericColumn">{{ T.wordsRatio }}</th>
            <th class="numericColumn">
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
            <th class="numericColumn" v-if="loggedIn">{{ T.wordsMyScore }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="problem in problems">
            <td>
              <a v-bind:href="`/arena/problem/${problem.alias}/`">{{
                problem.title
              }}</a
              ><span
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
                v-if="problem.visibility == -1"
                v-bind:class="`glyphicon glyphicon-eye-close`"
                v-bind:title="T.wordsPrivate"
              ></span>
              <div class="tag-list" v-if="problem.tags.length">
                <a
                  v-bind:class="`tag tag-${tag.source}`"
                  v-bind:href="hrefForProblemTag(currentTags, tag.name)"
                  v-for="tag in problem.tags"
                  >{{ T.hasOwnProperty(tag.name) ? T[tag.name] : tag.name }}</a
                >
              </div>
            </td>
            <td class="numericColumn" v-if="problem.quality === null">—</td>
            <td class="tooltip_column" v-else="">
              <span
                data-wenk-pos="right"
                v-bind:data-wenk="
                  `${UI.formatString(T.wordsOutOf4, {
                    Score: problem.quality.toFixed(1),
                  })}`
                "
                >{{ QUALITY_TAGS[Math.round(problem.quality)] }}</span
              >
            </td>
            <td class="numericColumn" v-if="problem.difficulty === null">—</td>
            <td class="tooltip_column" v-else="">
              <span
                data-wenk-pos="right"
                v-bind:data-wenk="
                  `${UI.formatString(T.wordsOutOf4, {
                    Score: problem.difficulty.toFixed(1),
                  })}`
                "
                >{{ DIFFICULTY_TAGS[Math.round(problem.difficulty)] }}</span
              >
            </td>
            <td class="numericColumn">
              {{ (100.0 * problem.ratio).toFixed(2) }}%
            </td>
            <td class="numericColumn">{{ problem.points.toFixed(2) }}</td>
            <td class="numericColumn" v-if="loggedIn">
              {{ problem.score.toFixed(2) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <omegaup-common-paginator
      v-bind:pagerItems="pagerItems"
    ></omegaup-common-paginator>
  </div>
</template>

<style>
.tag {
  margin-right: 0.25em;
  font-weight: normal;
}

.tag-quality {
  background: #ffeb3b;
}

.omegaup-quality-badge {
  width: 18px;
  height: 18px;
  background: url('/media/quality-badge-sm.png') center/contain no-repeat;
}

.show-finder-button {
  display: block;
  color: #337ab7;
  margin-bottom: 15px;
  cursor: pointer;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { types } from '../../api_types';
import * as UI from '../../ui';
import common_Paginator from '../common/Paginator.vue';
import problem_FinderWizard from './FinderWizard.vue';
import problem_SearchBar from './SearchBar.vue';

@Component({
  components: {
    'omegaup-problem-finder': problem_FinderWizard,
    'omegaup-common-paginator': common_Paginator,
    'omegaup-problem-search-bar': problem_SearchBar,
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
