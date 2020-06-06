<template>
  <div class="mb-3">
    <form action="/problem/" method="GET" class="form-inline">
      <div class="form-group mr-2" v-if="tags.length !== 0">
        <div class="mr-1" v-for="tag in tags">
          <input type="hidden" name="tag[]" v-bind:value="tag" />
          <span class="badge badge-secondary">{{ tag }}</span>
        </div>
        <a class="remove-all-tags" href="/problem/">
          <font-awesome-icon v-bind:icon="['fas', 'times']" />
        </a>
      </div>
      <div class="form-group mr-2 mt-1">
        <omegaup-autocomplete
          class="form-control"
          v-bind:init="el => typeahead.problemTypeahead(el)"
          v-model="keyword"
          v-bind:placeholder="T.wordsKeyword"
          name="query"
        ></omegaup-autocomplete>
      </div>
      <div class="form-group mr-2 mt-1">
        <label>
          {{ T.wordsFilterByLanguage }}
          <select name="language" class="ml-1 form-control" v-model="language">
            <option v-for="language in languages" v-bind:value="language">
              {{ getLanguageText(language) }}</option
            >
          </select>
        </label>
      </div>
      <div class="form-group mr-2 mt-1" v-show="false">
        <label>
          {{ T.wordsOrderBy }}
          <select name="order_by" class="ml-1 form-control" v-model="column">
            <option v-for="column in columns" v-bind:value="column">
              {{ getColumnText(column) }}</option
            >
          </select>
        </label>
      </div>
      <div class="form-group mr-2 mt-1" v-show="false">
        <label>
          {{ T.wordsMode }}
          <select name="mode" class="ml-1 form-control" v-model="mode">
            <option v-for="mode in modes" v-bind:value="mode">
              {{ getModeText(mode) }}</option
            >
          </select>
        </label>
      </div>
      <input
        class="btn btn-primary mt-1"
        type="submit"
        v-bind:value="T.wordsSearch"
      />
    </form>
  </div>
</template>

<style lang="scss" scoped>
.form-group {
  label {
    font-weight: bold;
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTimes } from '@fortawesome/free-solid-svg-icons';
library.add(faTimes);

@Component({
  components: {
    FontAwesomeIcon,
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
