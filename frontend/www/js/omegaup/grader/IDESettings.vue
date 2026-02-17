<template>
  <div class="settings-panel" :class="theme">
    <div class="settings-content">
      <!-- Time Limits -->
      <div class="settings-section">
        <h3 class="section-title">Time Limits</h3>
        <div class="settings-grid">
          <div class="form-field">
            <label>Time Limit (seconds)</label>
            <input
              v-model.number="timeLimit"
              class="form-input"
              type="number"
              min="0.1"
              max="5.0"
              step="0.1"
            />
          </div>
          <div class="form-field">
            <label>Overall Wall Time (seconds)</label>
            <input
              v-model.number="overallWallTimeLimit"
              class="form-input"
              type="number"
              min="0.1"
              max="5.0"
              step="0.1"
            />
          </div>
          <div class="form-field">
            <label>Extra Wall Time (seconds)</label>
            <input
              v-model.number="extraWallTime"
              class="form-input"
              type="number"
              min="0.0"
              max="5.0"
              step="0.1"
            />
          </div>
        </div>
      </div>

      <!-- Resource Limits -->
      <div class="settings-section">
        <h3 class="section-title">Resource Limits</h3>
        <div class="settings-grid">
          <div class="form-field">
            <label>Memory Limit (bytes)</label>
            <input
              v-model.number="memoryLimit"
              class="form-input"
              type="number"
              min="33554432"
              max="1073741824"
              step="1048576"
            />
          </div>
          <div class="form-field">
            <label>Output Limit (bytes)</label>
            <input
              v-model.number="outputLimit"
              class="form-input"
              type="number"
              min="0"
              max="104857600"
              step="1024"
            />
          </div>
        </div>
      </div>

      <!-- Validator -->
      <div class="settings-section">
        <h3 class="section-title">Validator</h3>
        <div class="settings-grid">
          <div class="form-field">
            <label>Validator Type</label>
            <select v-model="validator" class="form-select">
              <option value="custom">Custom</option>
              <option value="literal">Literal</option>
              <option value="token">Token</option>
              <option value="token-caseless">Token (Caseless)</option>
              <option value="token-numeric">Token (Numeric)</option>
            </select>
          </div>
          <div v-if="validator === 'token-numeric'" class="form-field">
            <label>Tolerance</label>
            <input
              v-model.number="tolerance"
              class="form-input"
              type="number"
              min="0"
              max="1"
              step="0.000001"
            />
          </div>
          <div v-if="validator === 'custom'" class="form-field">
            <label>Language</label>
            <select v-model="validatorLanguage" class="form-select">
              <option value="cpp17-gcc">C++17 (GCC)</option>
              <option value="py3">Python 3.6</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Interactive -->
      <div class="settings-section">
        <h3 class="section-title">Interactive Mode</h3>
        <div class="settings-grid">
          <div class="form-field">
            <label>Enable Interactive</label>
            <omegaup-radio-switch
              :value.sync="interactive"
              :selected-value="interactive"
              name="interactive"
            />
          </div>
          <div v-if="interactive" class="form-field">
            <label>Module Name</label>
            <input
              v-model="interactiveModuleName"
              class="form-input"
              type="text"
              placeholder="Enter module name"
            />
          </div>
          <div v-if="interactive" class="form-field">
            <label>Language</label>
            <select v-model="interactiveLanguage" class="form-select">
              <option value="cpp17-gcc">C++17 (GCC)</option>
              <option value="py3">Python</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import RadioSwitch from '../components/RadioSwitch.vue';
import store from './GraderStore';
import * as Util from './util';
import T from '../lang';

@Component({
  components: {
    'omegaup-radio-switch': RadioSwitch,
  },
})
export default class IDESettings extends Vue {
  @Prop({ required: true }) storeMapping!: { [key: string]: any };

  T = T;

  get theme(): string {
    return store.getters['theme'];
  }

  get timeLimit(): number {
    return Util.parseDuration(store.state.request.input.limits.TimeLimit);
  }

  set timeLimit(value: number) {
    store.dispatch('limits', {
      ...store.state.request.input.limits,
      TimeLimit: `${value}s`,
    });
  }

  get overallWallTimeLimit(): number {
    return Util.parseDuration(
      store.state.request.input.limits.OverallWallTimeLimit,
    );
  }

  set overallWallTimeLimit(value: number) {
    store.dispatch('limits', {
      ...store.state.request.input.limits,
      OverallWallTimeLimit: `${value}s`,
    });
  }

  get extraWallTime(): number {
    return Util.parseDuration(store.state.request.input.limits.ExtraWallTime);
  }

  set extraWallTime(value: number) {
    store.dispatch('limits', {
      ...store.state.request.input.limits,
      ExtraWallTime: `${value}s`,
    });
  }

  get memoryLimit(): number {
    return Util.parseDuration(store.state.request.input.limits.MemoryLimit);
  }

  set memoryLimit(value: number) {
    store.dispatch('limits', {
      ...store.state.request.input.limits,
      MemoryLimit: value,
    });
  }

  get outputLimit(): number {
    return Util.parseDuration(store.state.request.input.limits.OutputLimit);
  }

  set outputLimit(value: number) {
    store.dispatch('limits', {
      ...store.state.request.input.limits,
      OutputLimit: value,
    });
  }

  get validator(): string {
    return store.getters['Validator'];
  }

  set validator(value: string) {
    store.dispatch('Validator', value);
  }

  get tolerance(): number {
    return store.getters['Tolerance'];
  }

  set tolerance(value: number) {
    store.dispatch('Tolerance', value);
  }

  get validatorLanguage(): string {
    return store.getters['request.input.validator.custom_validator.language'];
  }

  set validatorLanguage(value: string) {
    store.dispatch('request.input.validator.custom_validator.language', value);
  }

  get interactive(): boolean {
    return store.getters['isInteractive'];
  }

  set interactive(value: boolean) {
    if (typeof value !== 'boolean') return;
    if (value) store.dispatch('Interactive', {});
    else store.dispatch('Interactive', undefined);
  }

  get interactiveLanguage(): string {
    return store.getters['request.input.interactive.language'];
  }

  set interactiveLanguage(value: string) {
    store.dispatch('request.input.interactive.language', value);
  }

  get interactiveModuleName(): string {
    return store.getters['moduleName'];
  }

  set interactiveModuleName(value: string) {
    store.dispatch('moduleName', value);
  }
}
</script>

<style lang="scss" scoped>
.settings-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #fff;
  color: #1a1a1a;

  &.vs-dark {
    background: #1e1e1e;
    color: #d4d4d4;
  }
}

.settings-content {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.settings-section {
  margin-bottom: 32px;

  &:last-child {
    margin-bottom: 0;
  }
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0 0 16px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid #e5e7eb;

  .vs-dark & {
    color: #e5e5e5;
    border-bottom-color: #333;
  }
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;

  label {
    font-size: 12px;
    font-weight: 500;
    color: #4b5563;
    margin: 0;

    .vs-dark & {
      color: #9ca3af;
    }
  }
}

.form-input,
.form-select {
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 8px 12px;
  font-size: 13px;
  outline: none;
  transition: all 0.15s;
  background: #fff;
  color: #1a1a1a;
  width: 100%;

  &::placeholder {
    color: #9ca3af;
  }

  &:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .vs-dark & {
    background: #2a2a2a;
    border-color: #404040;
    color: #d4d4d4;

    &:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
  }
}

.form-select {
  appearance: none;
  -webkit-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%234b5563' d='M1.41 0L6 4.58 10.59 0 12 1.41l-6 6-6-6z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 36px;
  cursor: pointer;

  .vs-dark & {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%239ca3af' d='M1.41 0L6 4.58 10.59 0 12 1.41l-6 6-6-6z'/%3E%3C/svg%3E");
  }
}

/* Number input styling */
input[type='number']::-webkit-inner-spin-button,
input[type='number']::-webkit-outer-spin-button {
  opacity: 1;
}

/* Scrollbar styling */
.settings-content::-webkit-scrollbar {
  width: 8px;
}

.settings-content::-webkit-scrollbar-track {
  background: transparent;
}

.settings-content::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 4px;

  &:hover {
    background: #9ca3af;
  }

  .vs-dark & {
    background: #404040;

    &:hover {
      background: #525252;
    }
  }
}
</style>
