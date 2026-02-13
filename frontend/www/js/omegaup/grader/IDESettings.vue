<template>
  <form class="h-100" :class="theme">
    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="inputTimeLimit">{{ T.settingsTimeLimit }}</label>
        <input
          v-model.number="timeLimit"
          class="form-control"
          max="5.0"
          min="0.1"
          step="0.1"
          type="number"
        />
      </div>
      <div class="form-group col-md-4">
        <label for="inputOverallWallTimeLimit">{{
          T.settingsOverallWallTimeLimit
        }}</label>
        <input
          v-model.number="overallWallTimeLimit"
          class="form-control"
          max="5.0"
          min="0.1"
          step="0.1"
          type="number"
        />
      </div>
      <div class="form-group col-md-4">
        <label for="inputExtraWallTime">{{ T.settingsExtraWallTime }}</label>
        <input
          v-model.number="extraWallTime"
          class="form-control"
          max="5.0"
          min="0.0"
          step="0.1"
          type="number"
        />
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="inputMemoryLimit">{{ T.settingsMemoryLimit }}</label>
        <input
          v-model.number="memoryLimit"
          class="form-control"
          max="1073741824"
          min="33554432"
          step="1048576"
          type="number"
        />
      </div>
      <div class="form-group col-md-6">
        <label for="inputOutputLimit">{{ T.settingsOutputLimit }}</label>
        <input
          v-model.number="outputLimit"
          class="form-control"
          max="104857600"
          min="0"
          step="1024"
          type="number"
        />
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="inputValidator">{{ T.settingsValidator }}</label>
        <select v-model="validator" class="form-control">
          <option value="custom">Custom</option>
          <option value="literal">Literal</option>
          <option value="token">Token</option>
          <option value="token-caseless">Token (Caseless)</option>
          <option value="token-numeric">Token (Numeric)</option>
        </select>
      </div>
      <div v-if="validator == 'token-numeric'" class="form-group col-md-6">
        <label for="inputTolerance">{{ T.settingsTolerance }}</label>
        <input
          v-model.number="tolerance"
          class="form-control"
          max="1"
          min="0"
          type="number"
        />
      </div>
      <div v-if="validator == 'custom'" class="form-group col-md-6">
        <label for="inputValidatorLanguage">{{ T.settingsLanguage }}</label>
        <select v-model="validatorLanguage" class="form-control">
          <option value="cpp17-gcc">C++17</option>
          <option value="py3">Python 3.6</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="autoDetectToggle">{{ T.detectLanguage }}</label>
        <div>
          <input
            id="autoDetectToggle"
            v-model="autoDetectLanguage"
            type="checkbox"
            @change="onAutoDetectChanged"
          />
        </div>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="inputInteractive">{{ T.settingsInteractive }}</label>
        <omegaup-radio-switch
          :value.sync="interactive"
          :selected-value="interactive"
          name="interactive"
        >
        </omegaup-radio-switch>
      </div>
      <div v-if="interactive" class="form-group col-md-4">
        <label for="inputInteractiveModuleName">{{
          T.settingsModuleName
        }}</label>
        <input v-model="interactiveModuleName" class="form-control" />
      </div>
      <div v-if="interactive" class="form-group col-md-4">
        <label for="inputInteractiveLanguage">{{ T.settingsLanguage }}</label>
        <select v-model="interactiveLanguage" class="form-control">
          <option value="cpp17-gcc">C++17</option>
          <option value="py3">Python</option>
        </select>
      </div>
    </div>
  </form>
</template>

<script lang="ts">
// TODO: use mapGetters, mapMutations and mapActions to get auto complete
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

  autoDetectLanguage: boolean = true;

  mounted(): void {
    try {
      const pref = localStorage.getItem('grader:autoDetectLanguage');
      if (pref !== null) this.autoDetectLanguage = pref === 'true';
    } catch (e) {
      // ignore
    }
    this.emitAutoDetectPreference();
  }

  onAutoDetectChanged(): void {
    try {
      localStorage.setItem('grader:autoDetectLanguage', this.autoDetectLanguage ? 'true' : 'false');
    } catch (e) {
      // ignore
    }
    this.emitAutoDetectPreference();
  }

  emitAutoDetectPreference(): void {
    window.dispatchEvent(new CustomEvent('grader:auto-detect-preference', {
      detail: this.autoDetectLanguage
    }));
  }

  get theme(): string {
    return store.getters['theme'];
  }

  get timeLimit(): number {
    return Util.parseDuration(store.state.request.input.limits.TimeLimit);
  }

  set timeLimit(value: number) {
    // convert back the time in seconds
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
    // radio switch triggers an event value, make sure that value is a boolean
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

<style lang="scss">
@import '../../../sass/main.scss';
form {
  &.vs-dark {
    background: var(--vs-dark-background-color);
    color: var(--vs-dark-font-color);

    .form-control {
      background-color: var(--vs-dark-background-color);
      color: var(--vs-dark-font-color);
    }

    .form-control:focus {
      border-color: var(--vs-dark-selected-form-border-color);
      box-shadow: 0 0 0 3px rgba($omegaup-blue, 0.4);
      background-color: var(--vs-dark-background-color);
      color: var(--vs-dark-font-color);
    }
  }
  &.vs {
    background: var(--vs-background-color);
    color: var(--vs-font-color);
  }
}
</style>
