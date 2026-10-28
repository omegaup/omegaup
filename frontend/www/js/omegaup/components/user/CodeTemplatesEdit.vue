<template>
  <div data-code-templates class="card-body">
    <p>{{ T.codeTemplatesDescription }}</p>
    <div class="form-group">
      <label>{{ T.wordsLanguage }}</label>
      <select
        v-model="selectedLanguage"
        data-code-templates-language
        class="custom-select"
      >
        <option
          v-for="[language, name] in Object.entries(programmingLanguages)"
          :key="language"
          :value="language"
        >
          {{ name }}
        </option>
      </select>
    </div>
    <div class="form-group">
      <textarea
        v-model="source"
        data-code-templates-source
        class="form-control text-monospace"
        rows="16"
      ></textarea>
    </div>
    <div class="mt-3">
      <button
        type="button"
        class="btn btn-primary mr-2"
        data-code-templates-save
        @click="onSave"
      >
        {{ T.wordsSaveChanges }}
      </button>
      <button
        type="button"
        class="btn btn-secondary"
        data-code-templates-restore
        @click="onRestoreDefault"
      >
        {{ T.codeTemplatesRestoreDefault }}
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import type { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import {
  getCodeTemplate,
  removeCodeTemplate,
  setCodeTemplate,
} from '../../code_templates';
import { sourceTemplates } from '../../grader/GraderTemplates';
import { supportedLanguages } from '../../grader/util';

export default defineComponent({
  name: 'UserCodeTemplatesEdit',
  props: {
    profile: {
      type: Object as PropType<types.UserProfileInfo>,
      required: true,
    },
  },
  data(): {
    T: typeof T;
    selectedLanguage: null | string;
    source: string;
  } {
    const programmingLanguages = this.profile.programming_languages ?? {};
    const languages = Object.keys(programmingLanguages);
    let initialLanguage: null | string = null;
    if (
      this.profile.preferred_language &&
      languages.includes(this.profile.preferred_language)
    ) {
      initialLanguage = this.profile.preferred_language;
    } else {
      initialLanguage = languages[0] ?? null;
    }

    return {
      T,
      selectedLanguage: initialLanguage,
      source: '',
    };
  },
  computed: {
    programmingLanguages(): { [key: string]: string } {
      return this.profile.programming_languages ?? {};
    },
    username(): null | string {
      return this.profile.username ?? null;
    },
  },
  watch: {
    selectedLanguage: {
      immediate: true,
      handler(language: null | string): void {
        if (!language) {
          this.source = '';
          return;
        }
        const extension = this.getExtension(language);
        const customTemplate = getCodeTemplate(extension, this.username);
        if (customTemplate !== null) {
          this.source = customTemplate;
          return;
        }
        this.source = sourceTemplates[extension] ?? '';
      },
    },
  },
  methods: {
    getExtension(language: string): string {
      const languageInfo = supportedLanguages[language];
      if (languageInfo) {
        return languageInfo.extension;
      }
      return language;
    },
    onSave(): void {
      if (!this.selectedLanguage) {
        return;
      }
      const extension = this.getExtension(this.selectedLanguage);
      if (setCodeTemplate(extension, this.source, this.username)) {
        ui.success(T.codeTemplatesSaveSuccess);
      }
    },
    onRestoreDefault(): void {
      if (!this.selectedLanguage) {
        return;
      }
      const extension = this.getExtension(this.selectedLanguage);
      removeCodeTemplate(extension, this.username);
      this.source = sourceTemplates[extension] ?? '';
      ui.success(T.codeTemplatesRestoreSuccess);
    },
  },
});
</script>
