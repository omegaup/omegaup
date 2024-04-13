<template>
  <div class="mt-4">
    <button
      class="btn btn-primary col-1 col-md-1"
      type="button"
      @click="printPage"
    >
      {{ T.contestAndProblemPrintButtonDesc }}
    </button>
    <omegaup-problem-settings-summary
      :problem="problem"
    ></omegaup-problem-settings-summary>
    <omegaup-markdown
      :markdown="problem.statement.markdown"
      :source-mapping="problem.statement.sources"
      :image-mapping="problem.statement.images"
      :problem-settings="problem.settings"
    ></omegaup-markdown>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import problem_SettingsSummary from './SettingsSummary.vue';
import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-problem-settings-summary': problem_SettingsSummary,
  },
})
export default class ProblemPrint extends Vue {
  @Prop() problem!: types.ProblemInfo;

  T = T;

  printPage(): void {
    window.print();
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

table td {
  padding: 0.5rem;
}
</style>
