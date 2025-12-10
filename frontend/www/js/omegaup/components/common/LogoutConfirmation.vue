<template>
  <b-modal
    v-model="showModal"
    :title="T.logoutConfirmationTitle"
    :ok-title="T.wordsYes"
    :cancel-title="T.wordsNo"
    ok-variant="primary"
    cancel-variant="secondary"
    footer-class="logout-confirmation-modal"
    body-class="p-0"
    static
    lazy
    @ok="confirmLogout"
  >
  </b-modal>
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
