<template>
  <div class="search-bar">
    <form action="/problem/" method="GET">
      <div class="form-inline">
        <div class="form-group" v-if="tags.length !== 0" v-for="tag in tags">
          <input type="hidden" name="tag[]" v-bind:value="tag" />
          <span class="tag">{{ tag }}</span>
          <a class="remove-all-tags" href="/problem/"
            ><span class="glyphicon glyphicon-remove"></span
          ></a>
        </div>
        <div class="form-group">
          <omegaup-autocomplete
            class="form-control"
            v-bind:init="el => typeahead.problemTypeahead(el)"
            v-model="keyword"
            v-bind:placeholder="T.wordsKeyword"
            name="query"
          ></omegaup-autocomplete>
        </div>

        <div class="form-group">
          <label class="control-label"
            >{{ T.wordsFilterByLanguage }}
            <select
              name="language"
              class="form-control problem-search-language"
              v-model="language"
            >
              <option v-for="language in languages" v-bind:value="language">
                {{ getLanguageText(language) }}</option
              >
            </select>
          </label>
        </div>

        <div class="form-group">
          <label class="control-label"
            >{{ T.wordsOrderBy }}
            <select
              name="order_by"
              class="form-control problem-search-order"
              v-model="column"
            >
              <option v-for="column in columns" v-bind:value="column">
                {{ getColumnText(column) }}</option
              >
            </select>
          </label>
        </div>

        <div class="form-group">
          <label class="control-label"
            >{{ T.wordsMode }}
            <select
              name="mode"
              class="form-control problem-search-mode"
              v-model="mode"
            >
              <option v-for="mode in modes" v-bind:value="mode">
                {{ getModeText(mode) }}</option
              >
            </select>
          </label>
        </div>

        <input
          class="btn btn-primary btn-lg active problem-search-button"
          type="submit"
          v-bind:value="T.wordsSearch"
        />
      </div>
    </form>
  </div>
</template>

<style>
.search-bar {
  margin-bottom: 10px;
}

.search-bar .problem-search-language {
  width: 120px;
  margin-left: 2px;
}

.search-bar .problem-search-order {
  width: 160px;
  margin-left: 2px;
}

.search-bar .problem-search-mode {
  width: 130px;
  margin-left: 2px;
}

.search-bar .problem-search-button {
  height: 32px;
  margin-left: 5px;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class ProblemSearchBar extends Vue {
  @Prop() tags!: string[];
  @Prop() initialKeyword!: string;
  @Prop() initialLanguage!: string;
  @Prop() initialColumn!: string;
  @Prop() initialMode!: string;
  @Prop() languages!: string[];
  @Prop() modes!: string[];
  @Prop() columns!: string[];

  T = T;
  typeahead = typeahead;

  keyword = this.initialKeyword;
  language = this.initialLanguage;
  column = this.initialColumn;
  mode = this.initialMode;

  getLanguageText(language: string): string {
    if (language === 'all') return T.wordsAll;
    if (language === 'en') return T.wordsEnglish;
    if (language === 'es') return T.wordsSpanish;
    return T.wordsPortuguese;
  }

  getColumnText(column: string): string {
    if (column === 'title') return T.wordsTitle;
    if (column === 'quality') return T.wordsQuality;
    if (column === 'difficulty') return T.wordsDifficulty;
    if (column === 'submissions') return T.wordsRuns;
    if (column === 'accepted') return T.wordsAccepted;
    if (column === 'ratio') return T.wordsRatio;
    if (column === 'points') return T.wordsPointsForRank;
    if (column === 'score') return T.wordsMyScore;
    return T.codersOfTheMonthDate;
  }

  getModeText(mode: string): string {
    if (mode === 'asc') return T.wordsModeAsc;
    return T.wordsModeDesc;
  }
}
</script>
