<template>
  <label
    class="switch-container font-weight-bold"
    :class="[size, { disabled }]"
  >
    <div class="switch">
      <input
        v-model="currentCheckedValue"
        :value="currentCheckedValue"
        :disabled="disabled"
        type="checkbox"
      />
      <span class="slider round"></span>
    </div>
    <slot name="switch-text">
      <span class="switch-text">
        {{ textDescription }}
      </span>
    </slot>
  </label>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';

export enum ToggleSwitchSize {
  Small = 'small',
  Large = 'large',
}

export default defineComponent({
  name: 'ToggleSwitch',
  props: {
    textDescription: {
      type: String,
      default: 'Check',
    },
    checkedValue: {
      type: Boolean,
      default: true,
    },
    size: {
      type: String as PropType<ToggleSwitchSize>,
      default: ToggleSwitchSize.Large,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      currentCheckedValue: this.checkedValue,
    };
  },
  watch: {
    currentCheckedValue(newValue: boolean): void {
      this.$emit('update:value', newValue);
    },
    checkedValue(newValue: boolean): void {
      this.currentCheckedValue = newValue;
    },
  },
});
</script>

<style scoped lang="scss">
@import '../../../sass/main.scss';

.switch {
  position: relative;
  display: inline-block;

  input {
    opacity: 0;
    width: 0;
    height: 0;
  }
}

label[class*='large'] {
  .switch {
    width: 60px;
    height: 34px;
  }

  .slider {
    &::before {
      height: 26px;
      width: 26px;
      left: 4px;
    }
  }

  input {
    &:checked {
      + {
        .slider {
          &::before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
          }
        }
      }
    }
  }
}

label[class*='small'] {
  .switch {
    width: 40px;
    height: 26px;
  }

  .slider {
    &::before {
      height: 18px;
      width: 18px;
      left: 3px;
    }
  }

  input {
    &:checked {
      + {
        .slider {
          &::before {
            -webkit-transform: translateX(15px);
            -ms-transform: translateX(15px);
            transform: translateX(15px);
          }
        }
      }
    }
  }
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--toggle-switch-slider-background-color);
  -webkit-transition: 0.4s;
  transition: 0.4s;

  &::before {
    position: absolute;
    content: '';
    bottom: 4px;
    background-color: var(--toggle-switch-slider-background-color--before);
    -webkit-transition: 0.4s;
    transition: 0.4s;
  }
}

input {
  &:checked {
    + {
      .slider {
        background-color: var(
          --toggle-switch-input-checked-slider-background-color
        );
      }
    }
  }

  &:focus {
    + {
      .slider {
        box-shadow: 0 0 1px
          var(--toggle-switch-input-focus-slider-background-color);
      }
    }
  }
}

.slider.round {
  border-radius: 34px;

  &::before {
    border-radius: 50%;
  }
}

.switch-container {
  width: 100%;
  position: relative;

  span.switch-text {
    margin: 0;
    position: absolute;
    top: 50%;
    margin-left: 5px;
    -ms-transform: translateY(-50%);
    transform: translateY(-50%);
  }
}

.switch-container.disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.switch-container.disabled .slider {
  cursor: not-allowed;
}

.switch-container.disabled input {
  pointer-events: none;
}
</style>
