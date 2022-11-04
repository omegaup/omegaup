<template>
  <form>
    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="inputTimeLimit">Time Limit</label>
        <!-- id-lint off -->
        <input
          id="inputTimeLimit"
          v-model="timeLimit"
          class="form-control"
          max="5.0"
          min="0.1"
          step="0.1"
          type="number"
        />
        <!-- id-lint on -->
      </div>
      <div class="form-group col-md-4">
        <label for="inputOverallWallTimeLimit">Overall Wall Time Limit</label>
        <!-- id-lint off -->
        <input
          id="inputOverallWallTimeLimit"
          v-model="overallWallTimeLimit"
          class="form-control"
          max="5.0"
          min="0.1"
          step="0.1"
          type="number"
        />
        <!-- id-lint on -->
      </div>
      <div class="form-group col-md-4">
        <label for="inputExtraWallTime">Extra Wall Time</label>
        <!-- id-lint off -->
        <input
          id="inputExtraWallTime"
          v-model="extraWallTime"
          class="form-control"
          max="5.0"
          min="0.0"
          step="0.1"
          type="number"
        />
        <!-- id-lint on -->
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="inputMemoryLimit">Memory Limit</label>
        <!-- id-lint off -->
        <input
          id="inputMemoryLimit"
          v-model="memoryLimit"
          class="form-control"
          max="1073741824"
          min="33554432"
          step="1048576"
          type="number"
        />
        <!-- id-lint on -->
      </div>
      <div class="form-group col-md-6">
        <label for="inputOutputLimit">Output Limit</label>
        <!-- id-lint off -->
        <input
          id="inputOutputLimit"
          v-model="outputLimit"
          class="form-control"
          max="104857600"
          min="0"
          step="1024"
          type="number"
        />
        <!-- id-lint on -->
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="inputValidator">Validator</label>
        <!-- id-lint off -->
        <select id="inputValidator" v-model="validator" class="form-control">
          <!-- id-lint on -->
          <option value="custom">Custom</option>
          <option value="literal">Literal</option>
          <option value="token">Token</option>
          <option value="token-caseless">Token (Caseless)</option>
          <option value="token-numeric">Token (Numeric)</option>
        </select>
      </div>
      <div v-if="validator == 'token-numeric'" class="form-group col-md-6">
        <label for="inputTolerance">Tolerance</label>
        <!-- id-lint off -->
        <input
          id="inputTolerance"
          v-model="tolerance"
          class="form-control"
          max="1"
          min="0"
          type="number"
        />
        <!-- id-lint on -->
      </div>
      <div v-if="validator == 'custom'" class="form-group col-md-6">
        <label for="inputValidatorLanguage">Language</label>
        <!-- id-lint off -->
        <select
          id="inputValidatorLanguage"
          v-model="validatorLanguage"
          class="form-control"
        >
          <!-- id-lint on -->
          <option value="cpp17-gcc">C++17</option>
          <option value="py3">Python 3.6</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="inputInteractive">Interactive</label>
        <!-- id-lint off -->
        <select
          id="inputInteractive"
          v-model="interactive"
          class="form-control"
        >
          <!-- id-lint on -->
          <option :value="false">No</option>
          <option :value="true">Yes</option>
        </select>
      </div>
      <div v-if="interactive" class="form-group col-md-4">
        <label for="inputInteractiveModuleName">Module Name</label>
        <!-- id-lint off -->
        <input
          id="inputInteractiveModuleName"
          v-model="interactiveModuleName"
          class="form-control"
        />
        <!-- id-lint on -->
      </div>
      <div v-if="interactive" class="form-group col-md-4">
        <label for="inputInteractiveLanguage">Language</label>
        <!-- id-lint off -->
        <select
          id="inputInteractiveLanguage"
          v-model="interactiveLanguage"
          class="form-control"
        >
          <!-- id-lint on -->
          <option value="cpp17-gcc">C++17</option>
          <option value="py3">Python</option>
        </select>
      </div>
    </div>
  </form>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { Store } from 'vuex';
import { State, StoreMapping } from './CaseSelectorTypescript.vue';

@Component
export default class GraderSettings extends Vue {
  @Prop({ required: true }) store!: Store<State>;
  @Prop({ required: true }) storeMapping!: StoreMapping;

  title = 'diff';

  get timeLimit(): string {
    return this.store.state.request.input.limits.TimeLimit;
  }
  set timeLimit(value: string) {
    this.store.commit('TimeLimit', Number.parseFloat(value));
  }

  get overallWallTimeLimit(): string {
    return this.store.state.request.input.limits.OverallWallTimeLimit;
  }
  set overallWallTimeLimit(value: string) {
    this.store.commit('OverallWallTimeLimit', Number.parseFloat(value));
  }

  get extraWallTime(): string {
    return this.store.state.request.input.limits.ExtraWallTime;
  }
  set extraWallTime(value: string) {
    this.store.commit('ExtraWallTime', Number.parseFloat(value));
  }

  get memoryLimit(): string {
    return this.store.state.request.input.limits.MemoryLimit;
  }
  set memoryLimit(value: string) {
    this.store.commit('MemoryLimit', Number.parseInt(value));
  }

  get outputLimit(): string {
    return this.store.state.request.input.limits.OutputLimit;
  }
  set outputLimit(value: string) {
    this.store.commit('OutputLimit', Number.parseInt(value));
  }

  get validator(): string {
    return this.store.state.request.input.validator.name;
  }
  set validator(value: string) {
    this.store.commit('Validator', value);
  }

  get tolerance(): string {
    return this.store.state.request.input.validator.tolerance;
  }
  set tolerance(value: string) {
    this.store.commit('Tolerance', value);
  }

  get validatorLanguage(): string {
    return this.store.state.request.input.validator.custom_validator.language;
  }
  set validatorLanguage(value: string) {
    this.store.commit('ValidatorLanguage', value);
  }

  get interactive(): string {
    return this.store.getters.isInteractive;
  }
  set interactive(value: string) {
    this.store.commit('Interactive', value);
  }

  get interactiveLanguage(): string {
    return this.store.state.request.input.interactive.language;
  }
  set interactiveLanguage(value: string) {
    this.store.commit('InteractiveLanguage', value);
  }

  get interactiveModuleName(): string {
    return this.store.state.request.input.interactive.module_name;
  }
  set interactiveModuleName(value: string) {
    this.store.commit('InteractiveModuleName', value);
  }
}
</script>
