<template>
  <div class="card">
    <h4 class="card-header">{{ archiveHeaderTitle }}</h4>
    <div class="card-body">
      <p class="card-text">
        {{ archiveHelpText }}
      </p>
      <button
        type="button"
        class="btn btn-primary d-block mx-auto"
        data-bs-toggle="modal"
        data-bs-target=".modal"
      >
        {{ archiveButtonDescription }}
      </button>
      <div class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-body">
              {{ archiveConfirmText }}
              <div class="d-flex justify-content-around mt-4">
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                >
                  {{ T.wordsClose }}
                </button>
                <button
                  type="button"
                  class="btn btn-primary"
                  data-bs-dismiss="modal"
                  @click="onArchive"
                >
                  {{ T.wordsConfirm }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class Archive extends Vue {
  @Prop() alreadyArchived!: boolean;
  @Prop() archiveHelpText!: string;
  @Prop() archiveHeaderTitle!: string;
  @Prop() archiveButtonDescription!: string;
  @Prop() archiveConfirmText!: string;

  T = T;

  onArchive(): void {
    this.$emit('archive', !this.alreadyArchived);
  }
}
</script>
