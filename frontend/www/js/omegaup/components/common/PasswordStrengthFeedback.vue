<template>
  <div class="password-strength-feedback">
    <div :class="['requirement', { met: hasMinLength }]">
      <font-awesome-icon
        :icon="hasMinLength ? 'check-circle' : 'times-circle'"
      />
      {{ T.passwordRequirementMinLength }}
    </div>
    <div :class="['requirement', { met: hasUppercase }]">
      <font-awesome-icon
        :icon="hasUppercase ? 'check-circle' : 'times-circle'"
      />
      {{ T.passwordRequirementUppercase }}
    </div>
    <div :class="['requirement', { met: hasLowercase }]">
      <font-awesome-icon
        :icon="hasLowercase ? 'check-circle' : 'times-circle'"
      />
      {{ T.passwordRequirementLowercase }}
    </div>
    <div :class="['requirement', { met: hasDigit }]">
      <font-awesome-icon :icon="hasDigit ? 'check-circle' : 'times-circle'" />
      {{ T.passwordRequirementDigit }}
    </div>
    <div :class="['requirement', { met: hasSpecialChar }]">
      <font-awesome-icon
        :icon="hasSpecialChar ? 'check-circle' : 'times-circle'"
      />
      {{ T.passwordRequirementSpecialChar }}
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCheckCircle, faTimesCircle } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';

library.add(faCheckCircle, faTimesCircle);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class PasswordStrengthFeedback extends Vue {
  @Prop({ required: true }) password!: string;

  T = T;

  get hasMinLength(): boolean {
    return this.password.length >= 8;
  }

  get hasUppercase(): boolean {
    return /[A-Z]/.test(this.password);
  }

  get hasLowercase(): boolean {
    return /[a-z]/.test(this.password);
  }

  get hasDigit(): boolean {
    return /[0-9]/.test(this.password);
  }

  get hasSpecialChar(): boolean {
    return /[!@#$%^&*(),.?":{}|<>]/.test(this.password);
  }

  get isValid(): boolean {
    return (
      this.hasMinLength &&
      this.hasUppercase &&
      this.hasLowercase &&
      this.hasDigit &&
      this.hasSpecialChar
    );
  }

  @Watch('isValid', { immediate: true })
  onIsValidChange(newValue: boolean): void {
    this.$emit('validity-change', newValue);
  }
}
</script>

<style lang="scss" scoped>
.password-strength-feedback {
  margin-top: 8px;
  font-size: 0.875rem;

  .requirement {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 2px 0;
    color: var(--password-requirement-unmet-color, #dc3545);

    &.met {
      color: var(--password-requirement-met-color, #28a745);
    }

    svg {
      width: 14px;
      height: 14px;
    }
  }
}
</style>
