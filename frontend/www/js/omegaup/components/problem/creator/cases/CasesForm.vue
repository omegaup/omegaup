<template>
    <div class="card-body">
      <hr class="my-3" />
      <form enctype="multipart/form-data" method="post" @submit="onSubmit">

        <div class="form-group col-md-12">
          <label class="control-label">{{ T.problemEditCommitMessage }}</label>
          <input v-model="commitMessage" class="form-control" />
        </div>

        <div v-if="isTruncatedInput" class="form-group col-md-6">
          <label class="control-label">Archivo de entrada (.in)</label>
          <input
            type="file"
            class="form-control"
            name="input_file"
            accept=".in,.txt"
          />
        </div>

        <div v-if="isTruncatedOutput" class="form-group col-md-6">
          <label class="control-label">Archivo de salida (.out)</label>
          <input
            type="file"
            class="form-control"
            name="output_file"
            accept=".out,.txt"
          />
        </div>

        <input type="hidden" name="request" value="cases" />
        <input type="hidden" name="problem_alias" :value="alias" />
        <input type="hidden" name="message" :value="commitMessage" />
        <input type="hidden" name="contents" :value="contentsPayload"/>

        <div class="col-md-12 mt-3">
          <button
            class="btn btn-primary"
            type="submit"
            :disabled="commitMessage === ''"
          >
            {{ "Save case" }}
          </button>
        </div>
      </form>
    </div>

</template>

<script lang="ts">
import { Component, Vue, Prop, Inject } from 'vue-property-decorator';
import T from '../../../../lang';
import { namespace } from 'vuex-class';
import { Case, Group, CaseLine } from '@/js/omegaup/problem/creator/types';


const casesStore = namespace('casesStore');

@Component
export default class CasesForm extends Vue {
  
  @Inject('originalCasesMap') readonly cases!:  Map<string, any>;
  @Inject('problemAlias') readonly alias!: string;
  @Prop({ default: false }) readonly isTruncatedInput!: boolean;
  @Prop({ default: false }) readonly isTruncatedOutput!: boolean;

  T = T;
  commitMessage = "Updating case";

  @casesStore.Getter('getSelectedCase') getSelectedCase!: Case;
  @casesStore.Getter('getSelectedGroup') getSelectedGroup!: Group;
  @casesStore.Getter('getLinesFromSelectedCase')
  getLinesFromSelectedCase!: CaseLine[];

  get inputText(): string {
    return (this.getLinesFromSelectedCase || [])
      .map((l) => l.data.value || '')
      .join('\n');
  }

  get contentsPayload(): string {
    const oldCase = this.cases?.get(this.getSelectedCase.caseID) ?? null;
    const payload = {
      group_name: this.getSelectedGroup.name,
      case_name: this.getSelectedCase.name,
      oldCase,
      input: this.inputText || '',
      output: this.isTruncatedOutput ? '' : (this.getSelectedCase.output || ''),
    };
    return JSON.stringify(payload);
  }

  onSubmit(e: Event) {
    if (!this.commitMessage)  {
      e.preventDefault();
      alert('El mensaje de commit es obligatorio.');
    }
  }
}
</script>


<style scoped>
.card-body {
  padding: 1rem 1.5rem;
}
</style>
