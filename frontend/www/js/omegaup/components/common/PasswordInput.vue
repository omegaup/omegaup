<template>
  <div class="password-input-wrapper">
    <input
      v-bind="$attrs"
      :value="value"
      :type="showPassword ? 'text' : 'password'"
      :name="name"
      :class="['form-control', inputClass]"
      :tabindex="tabindex"
      :autocomplete="autocomplete"
      :size="size"
      :required="required"
      @input="$emit('input', $event.target.value)"
    />
    <button
      type="button"
      class="password-toggle-btn"
      :aria-label="
        showPassword ? T.passwordHidePassword : T.passwordShowPassword
      "
      :title="showPassword ? T.passwordHidePassword : T.passwordShowPassword"
      @click="togglePasswordVisibility"
    >
      <font-awesome-icon :icon="['fas', showPassword ? 'eye-slash' : 'eye']" />
    </button>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';

library.add(faEye, faEyeSlash);

@Component({
  inheritAttrs: false,
  components: {
    FontAwesomeIcon,
  },
})
export default class PasswordInput extends Vue {
  @Prop({ required: true }) value!: string;
  @Prop({ default: '' }) name!: string;
  @Prop({ default: '' }) inputClass!: string;
  @Prop({ default: null }) tabindex!: number | null;
  @Prop({ default: 'current-password' }) autocomplete!: string;
  @Prop({ default: null }) size!: number | null;
  @Prop({ default: false }) required!: boolean;

  T = T;
  showPassword = false;

  togglePasswordVisibility(): void {
    this.showPassword = !this.showPassword;
  }
}
</script>

<style lang="scss" scoped>
.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;

  input {
    padding-right: 40px;
  }

  .password-toggle-btn {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    color: var(--password-toggle-btn-color);
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
      color: var(--password-toggle-btn-color--hover);
    }

    &:focus {
      outline: none;
      color: var(--password-toggle-btn-color--hover);
    }
  }
}
</style>
