<template>
  <ephemeral-ide
    :accepted-languages="acceptedLanguages"
    :initial-language="initialLanguage"
    :problem="problem"
    :should-show-submit-button="canSubmit"
    :can-run="canRun"
    :is-embedded="isEmbedded"
    :initial-theme="initialTheme"
    :next-submission-timestamp="nextSubmissionTimestamp"
    :next-execution-timestamp="nextExecutionTimestamp"
    @execute-run="() => this.$emit('execute-run')"
  >
  </ephemeral-ide>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { types } from '../../api_types';
import * as Util from '../../grader/util';
import Ephemeral from '../../grader/Ephemeral.vue';

export default defineComponent({
  name: 'EphemeralGrader',
  components: {
    'ephemeral-ide': Ephemeral,
  },
  props: {
    problem: {
      type: Object as PropType<types.ProblemInfo>,
      default: () => ({ ...Util.DUMMY_PROBLEM }),
    },
    canSubmit: {
      type: Boolean,
      default: false,
    },
    canRun: {
      type: Boolean,
      default: true,
    },
    acceptedLanguages: {
      type: Array as PropType<string[]>,
      default: () =>
        Object.values(Util.supportedLanguages).map(
          (languageInfo) => languageInfo.language,
        ),
    },
    preferredLanguage: {
      type: String as PropType<string | null>,
      default: 'cpp17-gcc',
    },
    isEmbedded: {
      type: Boolean,
      default: true,
    },
    initialTheme: {
      type: String as PropType<Util.MonacoThemes>,
      default: Util.MonacoThemes.VSLight,
    },
    nextSubmissionTimestamp: {
      type: Date as PropType<null | Date>,
      default: null,
    },
    nextExecutionTimestamp: {
      type: Date as PropType<null | Date>,
      default: null,
    },
  },
  emits: ['execute-run'],
  computed: {
    // note: initial source is for the IDE is also supported
    initialLanguage(): string {
      if (
        !this.preferredLanguage ||
        !this.acceptedLanguages.includes(this.preferredLanguage)
      ) {
        return this.acceptedLanguages[0];
      }
      return this.preferredLanguage;
    },
  },
});
</script>
