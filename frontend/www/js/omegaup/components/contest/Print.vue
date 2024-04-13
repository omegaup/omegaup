<template>
  <div>
    <button
      class="btn btn-primary col-1 col-md-1"
      type="button"
      @click="printPage"
    >
      Print
    </button>
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
import problem_SettingsSummary from '../problem/SettingsSummary.vue';
import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-problem-settings-summary': problem_SettingsSummary,
  },
})
export default class ProblemPrint extends Vue {
  @Prop() problems!: types.ProblemDetails[];
  @Prop() contestTitle!: string;

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
