<template>
  <div v-if="showModal">
    <div class="modal fade show d-block" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ T.logoutConfirmationTitle }}</h5>
            <button
              type="button"
              class="btn-close"
              @click="showModal = false"
            ></button>
          </div>
          <div class="modal-body p-0"></div>
          <footer class="modal-footer logout-confirmation-modal">
            <button
              type="button"
              class="btn btn-secondary"
              @click="showModal = false"
            >
              {{ T.wordsNo }}
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="confirmLogout"
            >
              {{ T.wordsYes }}
            </button>
          </footer>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class LogoutConfirmation extends Vue {
  @Prop({ default: false }) value!: boolean;

  T = T;

  get showModal(): boolean {
    return this.value;
  }

  set showModal(value: boolean) {
    this.$emit('input', value);
  }

  confirmLogout(): void {
    window.location.href = '/logout/';
  }
}
</script>
