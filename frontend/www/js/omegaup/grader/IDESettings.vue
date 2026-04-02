<template>
  <div class="ide-settings" :class="theme">
    <div class="settings-header">
      <span class="header-title">
        <i class="fas fa-cog" aria-hidden="true"></i> Runner Settings
      </span>
    </div>

    <div class="settings-content">
      <section class="settings-section">
        <h4 class="section-title">
          <i class="fas fa-sliders-h" aria-hidden="true"></i> Limits
        </h4>

        <label class="form-group">
          <span class="label-text">Time Limit</span>
          <div class="input-wrapper">
            <i class="fas fa-stopwatch input-icon" aria-hidden="true"></i>
            <input v-model="timeLimit" type="text" placeholder="e.g., 1s" />
          </div>
        </label>

        <label class="form-group">
          <span class="label-text">Overall Wall Time Limit</span>
          <div class="input-wrapper">
            <i class="fas fa-clock input-icon" aria-hidden="true"></i>
            <input
              v-model="overallWallTimeLimit"
              type="text"
              placeholder="e.g., 1m"
            />
          </div>
        </label>

        <label class="form-group">
          <span class="label-text">Extra Wall Time</span>
          <div class="input-wrapper">
            <i class="fas fa-hourglass-half input-icon" aria-hidden="true"></i>
            <input v-model="extraWallTime" type="text" placeholder="e.g., 0s" />
          </div>
        </label>

        <label class="form-group">
          <span class="label-text">Memory Limit (Bytes)</span>
          <div class="input-wrapper">
            <i class="fas fa-microchip input-icon" aria-hidden="true"></i>
            <input
              v-model.number="memoryLimit"
              type="number"
              placeholder="e.g., 33554432"
            />
          </div>
        </label>

        <label class="form-group">
          <span class="label-text">Output Limit (Bytes)</span>
          <div class="input-wrapper">
            <i class="fas fa-file-export input-icon" aria-hidden="true"></i>
            <input
              v-model.number="outputLimit"
              type="number"
              placeholder="e.g., 10240"
            />
          </div>
        </label>
      </section>

      <section class="settings-section">
        <h4 class="section-title">
          <i class="fas fa-check-double" aria-hidden="true"></i> Validator
        </h4>

        <label class="form-group">
          <span class="label-text">Name</span>
          <div class="input-wrapper">
            <select v-model="validatorName" class="custom-select">
              <option value="token-numeric">token-numeric</option>
              <option value="token">token</option>
              <option value="token-caseless">token-caseless</option>
              <option value="literal">literal</option>
              <option value="custom">custom</option>
            </select>
          </div>
        </label>

        <label v-if="validatorName === 'token-numeric'" class="form-group">
          <span class="label-text">Tolerance</span>
          <div class="input-wrapper">
            <i class="fas fa-percentage input-icon" aria-hidden="true"></i>
            <input
              v-model.number="validatorTolerance"
              type="number"
              step="1e-9"
              placeholder="e.g., 1e-9"
            />
          </div>
        </label>
      </section>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import store from './GraderStore';

@Component
export default class IDESettings extends Vue {
  get theme(): string {
    return store.getters['theme'];
  }

  // --- Limits Management ---
  get limits(): Record<string, string | number> {
    return store.getters['limits'] || {};
  }

  get timeLimit(): string {
    return (this.limits.TimeLimit as string) || '1s';
  }
  set timeLimit(value: string) {
    store.dispatch('limits', { ...this.limits, TimeLimit: value });
  }

  get overallWallTimeLimit(): string {
    return (this.limits.OverallWallTimeLimit as string) || '1m';
  }
  set overallWallTimeLimit(value: string) {
    store.dispatch('limits', { ...this.limits, OverallWallTimeLimit: value });
  }

  get extraWallTime(): string {
    return (this.limits.ExtraWallTime as string) || '0s';
  }
  set extraWallTime(value: string) {
    store.dispatch('limits', { ...this.limits, ExtraWallTime: value });
  }

  get memoryLimit(): number {
    return (this.limits.MemoryLimit as number) || 33554432;
  }
  set memoryLimit(value: number) {
    store.dispatch('limits', { ...this.limits, MemoryLimit: value });
  }

  get outputLimit(): number {
    return (this.limits.OutputLimit as number) || 10240;
  }
  set outputLimit(value: number) {
    store.dispatch('limits', { ...this.limits, OutputLimit: value });
  }

  // --- Validator Management ---
  get validatorName(): string {
    return store.getters['Validator'] || 'token-numeric';
  }
  set validatorName(value: string) {
    store.dispatch('Validator', value);
  }

  get validatorTolerance(): number {
    return store.getters['Tolerance'] || 1e-9;
  }
  set validatorTolerance(value: number) {
    store.dispatch('Tolerance', value);
  }
}
</script>

<style lang="scss" scoped>
.ide-settings {
  display: flex;
  flex-direction: column;
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #fff;
  overflow-y: auto;

  &.vs-dark {
    background: #1e1e1e;
    color: #d4d4d4;
  }
}

.settings-header {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 10;

  .vs-dark & {
    background: #252525;
    border-bottom-color: #333;
  }
}

.header-title {
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  gap: 8px;

  i {
    color: #9ca3af;
  }

  .vs-dark & {
    color: #9ca3af;
    i {
      color: #6b7280;
    }
  }
}

.settings-content {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.settings-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.section-title {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 8px;

  i {
    color: #3b82f6;
  }

  .vs-dark & {
    color: #e5e5e5;
    border-bottom-color: #333;

    i {
      color: #60a5fa;
    }
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  cursor: pointer;

  .label-text {
    font-size: 12px;
    font-weight: 500;
    color: #4b5563;

    .vs-dark & {
      color: #9ca3af;
    }
  }
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;

  .input-icon {
    position: absolute;
    left: 10px;
    color: #9ca3af;
    font-size: 13px;

    .vs-dark & {
      color: #6b7280;
    }
  }

  input,
  .custom-select {
    width: 100%;
    padding: 8px 12px 8px 32px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    color: #1f2937;
    background: #fff;
    transition: all 0.15s;

    &:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    &::placeholder {
      color: #9ca3af;
    }

    .vs-dark & {
      background: #2a2a2a;
      border-color: #404040;
      color: #e5e5e5;

      &:focus {
        border-color: #3b82f6;
      }

      &::placeholder {
        color: #6b7280;
      }
    }
  }

  .custom-select {
    padding-left: 12px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%236b7280' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;

    .vs-dark & {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%239ca3af' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
    }
  }
}
</style>
