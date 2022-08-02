<template>
  <div class="mb-3">
    <form action="/problem/" method="GET" class="form-inline">
      <div v-if="tags.length !== 0" class="form-group mr-2">
        <div v-for="tag in tags" :key="tag" class="mr-1">
          <input type="hidden" name="tag[]" :value="tag" />
          <span class="badge badge-secondary m-1 p-2">{{
            T[tag] ? T[tag] : tag
          }}</span>
        </div>
        <a class="remove-all-tags" href="/problem/">
          <font-awesome-icon :icon="['fas', 'times']" />
        </a>
      </div>
      <div class="form-group mr-2 mt-1">
        <omegaup-common-typeahead
          :only-existing-tags="false"
          :max-results="10"
          :existing-options="searchResultProblems"
          :options="searchResultProblems"
          :value.sync="currentKeyword"
          :placeholder="T.wordsKeywordSearch"
          @update-existing-options="
            (query) => $emit('update-search-result-problems', query)
          "
        ></omegaup-common-typeahead>
        <input type="hidden" name="query" :value="currentKeywordValue" />
      </div>
      <div class="form-group mr-2 mt-1">
        <label>
          {{ T.wordsFilterByLanguage }}
          <select
            v-model="currentLanguage"
            name="language"
            class="ml-1 form-control"
          >
            <option
              v-for="language in languages"
              :key="language"
              :value="language"
            >
              {{ getLanguageText(currentLanguage) }}
            </option>
          </select>
        </label>
      </div>
      <input
        class="btn btn-primary mt-1"
        type="submit"
        :value="T.wordsSearch"
      />
    </form>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import common_Typeahead from '../common/Typeahead.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTimes } from '@fortawesome/free-solid-svg-icons';
library.add(faTimes);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-common-typeahead': common_Typeahead,
  },
})
export default class ProblemSearchBar extends Vue {
  @Prop() tags!: string[];
  @Prop() keyword!: string;
  @Prop() language!: string;
  @Prop() languages!: string[];
  @Prop() searchResultProblems!: types.ListItem[];

  T = T;

  currentKeyword: types.ListItem = { key: this.keyword, value: this.keyword };
  currentLanguage = this.language;

  getLanguageText(language: string): string {
    if (language === 'all') return T.wordsAll;
    if (language === 'en') return T.wordsEnglish;
    if (language === 'es') return T.wordsSpanish;
    return T.wordsPortuguese;
  }

  get currentKeywordValue(): null | string {
    return this.currentKeyword?.value ?? null;
  }
}
</script>

<style lang="scss" scoped>
.form-group {
  label {
    font-weight: bold;
  }
}
</style>
