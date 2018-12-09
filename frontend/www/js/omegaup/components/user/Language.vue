<template>
  <li class="dropdown">
    <a class="dropdown-toggle"
        data-toggle="dropdown"
        href="#"><img v-bind:src="showFlagUrl(selectedLanguage)"> <span class="caret"></span></a>
    <ul class="dropdown-menu">
      <li v-for="language in availableLanguages">
        <a href='#'
            v-on:click="onChangeLanguage(language)"><img v-bind:src="showFlagUrl(language)"> {{
            showStatementLanguage(language) }}</a>
      </li>
    </ul>
  </li>
</template>

<script>
import {T} from '../../omegaup.js';
export default {
  props: {
    selectedLanguage: String,
    availableLanguages: Array,
  },
  methods: {
    showFlagUrl: function(language) {
      if (language == 'en') {
        language = 'gb';
      }
      return '/media/flags/' + language + '.png';
    },
    showStatementLanguage: function(language) {
      if (language == 'es') {
        return T.statementLanguageEs;
      } else if (language == 'en') {
        return T.statementLanguageEn;
      } else if (language == 'pt') {
        return T.statementLanguagePt;
      }
      return T.statementLanguageEs;
    },
    onChangeLanguage: function(language) {
      if (language == this.selectedLanguage) {
        return;
      }
      this.$emit('change-language', language);
    },
  },
  data: function() {
    return { T: T, }
  },
}
</script>
