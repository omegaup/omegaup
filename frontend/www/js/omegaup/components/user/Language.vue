<template>
  <li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
      <img v-bind:src="flagUrl" /> 
      <span class="caret"></span></a>
    <ul class="dropdown-menu">
      <li v-for="language in availableLanguages">
        <a href='#' v-on:click="onChangeLanguage(language)">
          <img v-bind:src="showFlagUrl(language)"> {{ showStatementLanguage(language) }}
        </a>
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
  computed: {
    flagUrl: function() {
      let language = '';
      if (this.selectedLanguage == null) {
        return '';
      } else if (this.selectedLanguage == 'en') {
        language = 'gb';
      } else {
        language = this.selectedLanguage;
      }
      return '/media/flags/' + language + '.png';
    },
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
    onChangeLanguage: function (language) {
      if (language != this.selectedLanguage) {
        this.$emit('change-language', language);
      }  
    },
  },
  data: function() {
    return { T: T, }
  },
}
</script>
