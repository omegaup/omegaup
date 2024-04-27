<template>
  <div>
    <a @click="() => $emit('print-page')">
      <font-awesome-icon
        :title="T.contestAndProblemPrintButtonDesc"
        :icon="['fas', 'print']"
    /></a>
    <div v-for="problem in problems" :key="problem.alias" class="mt-3">
      <omegaup-problem-settings-summary
        :problem="problem"
        :problemset-title="contestTitle"
      ></omegaup-problem-settings-summary>
      <omegaup-markdown
        :markdown="problem.statement.markdown"
        :source-mapping="problem.statement.sources"
        :image-mapping="problem.statement.images"
        :problem-settings="problem.settings"
      ></omegaup-markdown>
      <hr />
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import problem_SettingsSummary from '../problem/SettingsSummary.vue';
import omegaup_Markdown from '../Markdown.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faPrint } from '@fortawesome/free-solid-svg-icons';
library.add(faPrint);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-problem-settings-summary': problem_SettingsSummary,
  },
})
export default class ProblemPrint extends Vue {
  @Prop() problems!: types.ProblemDetails[];
  @Prop() contestTitle!: string;

  T = T;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

table td {
  padding: 0.5rem;
}
</style>
