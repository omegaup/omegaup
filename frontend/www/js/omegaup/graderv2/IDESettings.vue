<template>
  <form>
    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="inputTimeLimit">{{ T.settingsTimeLimit }}</label>
        <input
          v-model="timeLimit"
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
          v-model="overallWallTimeLimit"
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
          v-model="extraWallTime"
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
          v-model="memoryLimit"
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
          v-model="outputLimit"
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
          v-model="tolerance"
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
      <div class="form-group col-md-4">
        <label for="inputInteractive">{{ T.settingsInteractive }}</label>
        <omegaup-radio-swtich
          :value.sync="interactive"
          :selected-value="interactive"
          :name="'interactive'"
        >
        </omegaup-radio-swtich>
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
import { Vue, Component, Prop } from 'vue-property-decorator';
import RadioSwitch from '../components/RadioSwitch.vue';
import T from '../lang';

@Component({
  components: {
    'omegaup-radio-switch': RadioSwitch,
  },
})
export default class IDESettings extends Vue {
  @Prop({ required: true }) store!: any;
  @Prop({ required: true }) storeMapping!: any;

  T = T;
  get timeLimit() {
    return this.store.state.request.input.limits.TimeLimit;
  }

  set timeLimit(value: number) {
    this.store.commit('TimeLimit', Number.parseFloat(value.toString()));
  }

  get overallWallTimeLimit() {
    return this.store.state.request.input.limits.OverallWallTimeLimit;
  }

  set overallWallTimeLimit(value: number) {
    this.store.commit(
      'OverallWallTimeLimit',
      Number.parseFloat(value.toString()),
    );
  }

  get extraWallTime() {
    return this.store.state.request.input.limits.ExtraWallTime;
  }

  set extraWallTime(value: number) {
    this.store.commit('ExtraWallTime', Number.parseFloat(value.toString()));
  }

  get memoryLimit() {
    return this.store.state.request.input.limits.MemoryLimit;
  }

  set memoryLimit(value: number) {
    this.store.commit('MemoryLimit', Number.parseInt(value.toString()));
  }

  get outputLimit() {
    return this.store.state.request.input.limits.OutputLimit;
  }

  set outputLimit(value: number) {
    this.store.commit('OutputLimit', Number.parseInt(value.toString()));
  }

  get validator() {
    return this.store.state.request.input.validator.name;
  }

  set validator(value: string) {
    this.store.commit('Validator', value);
  }

  get tolerance() {
    return this.store.state.request.input.validator.tolerance;
  }

  set tolerance(value: number) {
    this.store.commit('Tolerance', value);
  }

  get validatorLanguage() {
    return this.store.state.request.input.validator.custom_validator?.language;
  }

  set validatorLanguage(value: string) {
    this.store.commit('ValidatorLanguage', value);
  }

  get interactive() {
    return this.store.getters.isInteractive;
  }

  set interactive(value: boolean) {
    if (value) this.store.commit('Interactive', {});
    else this.store.commit('Interactive', undefined);
  }

  get interactiveLanguage() {
    return this.store.state.request.input.interactive.language;
  }

  set interactiveLanguage(value: string) {
    this.store.commit('InteractiveLanguage', value);
  }

  get interactiveModuleName() {
    return this.store.state.request.input.interactive.module_name;
  }

  set interactiveModuleName(value: string) {
    this.store.commit('InteractiveModuleName', value);
  }
}
</script>
