<template>
  <div class="mb-3">
    <form action="/problem/" method="GET" class="form-inline">
      <div v-if="tags.length !== 0" class="form-group mr-2">
        <div v-for="tag in tags" class="mr-1">
          <input type="hidden" name="tag[]" v-bind:value="tag" />
          <span class="badge badge-secondary m-1 p-2">{{
            T[tag] ? T[tag] : tag
          }}</span>
        </div>
        <a class="remove-all-tags" href="/problem/">
          <font-awesome-icon v-bind:icon="['fas', 'times']" />
        </a>
      </div>
      <div class="form-group mr-2 mt-1">
        <omegaup-autocomplete
          v-model="keyword"
          class="form-control"
          v-bind:init="(el) => typeahead.problemTypeahead(el)"
          v-bind:placeholder="T.wordsKeywordSearch"
          name="query"
        ></omegaup-autocomplete>
      </div>
      <div class="form-group mr-2 mt-1">
        <label>
          {{ T.wordsFilterByLanguage }}
          <select v-model="language" name="language" class="ml-1 form-control">
            <option v-for="language in languages" v-bind:value="language">
              {{ getLanguageText(language) }}
            </option>
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
  @Prop() languages!: string[];

  T = T;
  typeahead = typeahead;

  keyword = this.initialKeyword;
  language = this.initialLanguage;

  getLanguageText(language: string): string {
    if (language === 'all') return T.wordsAll;
    if (language === 'en') return T.wordsEnglish;
    if (language === 'es') return T.wordsSpanish;
    return T.wordsPortuguese;
  }
}
</script>
