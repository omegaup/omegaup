<template>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title mb-0">{{ T.contestCertificatesGenerate }}</h4>
    </div>
    <div class="card-body px-2 px-sm-4">
      <label>{{ T.contestCertificatesCutoff }}</label>
      <input
        v-model="certificateCutoff"
        :disabled="
          certificatesDetails.certificatesStatus !== 'uninitiated' &&
          certificatesDetails.certificatesStatus !== 'retryable_error'
        "
        class="form-control"
        type="text"
        required="required"
      />
      <p class="help-block">
        {{ T.contestCertificatesCutoffHelp }}
      </p>
      <button
        :disabled="
          certificatesDetails.certificatesStatus !== 'uninitiated' &&
          certificatesDetails.certificatesStatus !== 'retryable_error'
        "
        type="button"
        class="btn btn-primary d-block mx-auto"
        data-toggle="modal"
        data-target=".modal"
      >
        {{ T.contestCertificatesGenerate }}
      </button>
      <div class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-body">
              {{ T.contestCertificatesConfirmGenerate }}
              <div class="d-flex justify-content-around mt-4">
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-dismiss="modal"
                >
                  {{ T.wordsClose }}
                </button>
                <button
                  type="button"
                  class="btn btn-primary"
                  data-dismiss="modal"
                  @click="$emit('generate', certificateCutoff)"
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
import { types } from '../../api_types';
import T from '../../lang';

@Component
export default class Certificates extends Vue {
  @Prop() certificatesDetails!: types.ContestCertificatesAdminDetails;

  T = T;
  certificateCutoff = this.certificatesDetails.certificateCutoff;
}
</script>
