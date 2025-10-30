<template>
  <div class="card-body">
    <hr class="my-3" />
    <form enctype="multipart/form-data" method="post" @submit="onSubmit">

      <div class="form-group col-md-12">
        <label class="control-label">{{ T.problemEditCommitMessage }}</label>
        <input v-model="commitMessage" class="form-control" />
      </div>

      <!-- Campos ocultos del formulario -->
      <input type="hidden" name="request" value="delete" />
      <input type="hidden" name="problem_alias" :value="alias" />
      <input type="hidden" name="message" :value="commitMessage" />
      <input type="hidden" name="contents" :value="contentsPayload" />

      <div class="d-flex flex-column flex-sm-row mt-3">
        <button
          class="btn btn-danger w-100 w-sm-auto mb-2 mb-sm-0"
          type="submit"
          :disabled="commitMessage === ''"
        >
          {{ 'Confirmar eliminación' }}
        </button>

        <button
          class="btn btn-secondary w-100 w-sm-auto ml-sm-2"
          type="button"
          @click="handleCancel"
        >
          {{ 'Cancelar' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue, Watch, Inject } from 'vue-property-decorator';
import T from '../../../../lang';

@Component
export default class DeleteConfirmationForm extends Vue {
  @Inject('problemAlias') readonly alias!: string;

  @Prop({ required: true, type: Boolean }) visible!: boolean;
  @Prop({ required: true, type: String }) itemName!: string;
  @Prop({ required: true, type: Function }) onCancel!: () => void;

  T = T;
  commitMessage: string = '';

  @Watch('visible')
  onVisibleChange(newValue: boolean) {
    if (newValue) {
      this.commitMessage = `Eliminando ${this.itemName}`;
    } else {
      this.commitMessage = '';
    }
  }

  get contentsPayload(): string {
    return JSON.stringify({
      name: this.itemName,
    });
  }

  onSubmit(e: Event) {
    if (!this.commitMessage.trim()) {
      e.preventDefault();
      alert('El mensaje de commit es obligatorio.');
      return;
    }
  }

  handleCancel() {
    this.onCancel();
    this.commitMessage = '';
  }
}
</script>

<style scoped>
.card-body {
  padding: 1rem 1.5rem;
}
</style>
