<template>
  <b-modal
    v-model="showModal"
    :title="T.logoutConfirmationTitle"
    :ok-title="T.wordsYes"
    :no-title="T.wordsNo"
    ok-variant="primary"
    no-variant="secondary"
    static
    lazy
    @ok="confirmLogout"
  >
    <div class="mb-4">{{ T.logoutConfirmationMessage }}</div>
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
