<!-- components/LogoutConfirmation.vue -->
<template>
  <div
    class="modal fade"
    id="logoutConfirmationModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="logoutConfirmationModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logoutConfirmationModalLabel">
            {{ T.logoutConfirmationTitle }}
          </h5>
          <button
            type="button"
            class="close"
            data-dismiss="modal"
            aria-label="Close"
          >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          {{ T.logoutConfirmationMessage }}
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-dismiss="modal"
          >
            {{ T.no }}
          </button>
          <button
            type="button"
            class="btn btn-primary"
            @click="confirmLogout"
          >
            {{ T.yes }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class LogoutConfirmation extends Vue {
  T = T;

  confirmLogout(): void {
    // Close the modal
    ($('#logoutConfirmationModal') as any).modal('hide');
    // Navigate to logout URL
    window.location.href = '/logout/';
  }
}
</script>