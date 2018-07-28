<template>
  <form>
    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="inputTimeLimit">Time Limit</label> <!-- id-lint off -->
         <input class="form-control"
             id="inputTimeLimit"
             max="5.0"
             min="0.1"
             step="0.1"
             type="number"
             v-model="timeLimit"> <!-- id-lint on -->
      </div>
      <div class="form-group col-md-4">
        <label for="inputOverallWallTimeLimit">Overall Wall Time Limit</label> <!-- id-lint off -->
         <input class="form-control"
             id="inputOverallWallTimeLimit"
             max="5.0"
             min="0.1"
             step="0.1"
             type="number"
             v-model="overallWallTimeLimit"> <!-- id-lint on -->
      </div>
      <div class="form-group col-md-4">
        <label for="inputExtraWallTime">Extra Wall Time</label> <!-- id-lint off -->
         <input class="form-control"
             id="inputExtraWallTime"
             max="5.0"
             min="0.0"
             step="0.1"
             type="number"
             v-model="extraWallTime"> <!-- id-lint on -->
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="inputMemoryLimit">Memory Limit</label> <!-- id-lint off -->
         <input class="form-control"
             id="inputMemoryLimit"
             max="1073741824"
             min="33554432"
             step="1048576"
             type="number"
             v-model="memoryLimit"> <!-- id-lint on -->
      </div>
      <div class="form-group col-md-6">
        <label for="inputOutputLimit">Output Limit</label> <!-- id-lint off -->
         <input class="form-control"
             id="inputOutputLimit"
             max="104857600"
             min="0"
             step="1024"
             type="number"
             v-model="outputLimit"> <!-- id-lint on -->
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="inputValidator">Validator</label> <!-- id-lint off -->
         <select class="form-control"
             id="inputValidator"
             v-model="validator">
          <!-- id-lint on -->
          <option value="custom">
            Custom
          </option>
          <option value="literal">
            Literal
          </option>
          <option value="token">
            Token
          </option>
          <option value="token-caseless">
            Token (Caseless)
          </option>
          <option value="token-numeric">
            Token (Numeric)
          </option>
        </select>
      </div>
      <div class="form-group col-md-6"
           v-if="validator == 'token-numeric'">
        <label for="inputTolerance">Tolerance</label> <!-- id-lint off -->
         <input class="form-control"
             id="inputTolerance"
             max="1"
             min="0"
             type="number"
             v-model="tolerance"> <!-- id-lint on -->
      </div>
      <div class="form-group col-md-6"
           v-if="validator == 'custom'">
        <label for="inputValidatorLanguage">Language</label> <!-- id-lint off -->
         <select class="form-control"
             id="inputValidatorLanguage"
             v-model="validatorLanguage">
          <!-- id-lint on -->
          <option value="cpp">
            C++
          </option>
          <option value="py">
            Python
          </option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="inputInteractive">Interactive</label> <!-- id-lint off -->
         <select class="form-control"
             id="inputInteractive"
             v-model="interactive">
          <!-- id-lint on -->
          <option v-bind:value="false">
            No
          </option>
          <option v-bind:value="true">
            Yes
          </option>
        </select>
      </div>
      <div class="form-group col-md-4"
           v-if="interactive">
        <label for="inputInteractiveModuleName">Module Name</label> <!-- id-lint off -->
         <input class="form-control"
             id="inputInteractiveModuleName"
             v-model="interactiveModuleName"> <!-- id-lint on -->
      </div>
      <div class="form-group col-md-4"
           v-if="interactive">
        <label for="inputInteractiveLanguage">Language</label> <!-- id-lint off -->
         <select class="form-control"
             id="inputInteractiveLanguage"
             v-model="interactiveLanguage">
          <!-- id-lint on -->
          <option value="cpp11">
            C++
          </option>
          <option value="py">
            Python
          </option>
        </select>
      </div>
    </div>
  </form>
</template>

<script>
import * as Util from './util';

export default {
  props: {
    store: Object,
    storeMapping: Object,
  },
  data: function() {
    return {
      title: 'settings',
    };
  },
  computed: {
    timeLimit: {
      get() { return this.store.state.request.input.limits.TimeLimit;},
      set(value) { this.store.commit('TimeLimit', Number.parseFloat(value));},
    },
    overallWallTimeLimit: {
      get() {
        return this.store.state.request.input.limits.OverallWallTimeLimit;
      },
      set(value) {
        this.store.commit('OverallWallTimeLimit', Number.parseFloat(value));
      },
    },
    extraWallTime: {
      get() { return this.store.state.request.input.limits.ExtraWallTime;},
      set(value) {
        this.store.commit('ExtraWallTime', Number.parseFloat(value));
      },
    },
    memoryLimit: {
      get() { return this.store.state.request.input.limits.MemoryLimit;},
      set(value) { this.store.commit('MemoryLimit', Number.parseInt(value));},
    },
    outputLimit: {
      get() { return this.store.state.request.input.limits.OutputLimit;},
      set(value) { this.store.commit('OutputLimit', Number.parseInt(value));},
    },
    validator: {
      get() { return this.store.state.request.input.validator.name},
      set(value) { this.store.commit('Validator', value);},
    },
    tolerance: {
      get() { return this.store.state.request.input.validator.tolerance;},
      set(value) { this.store.commit('Tolerance', value);},
    },
    validatorLanguage: {
      get() {
        return this.store.state.request.input.validator.custom_validator
            .language;
      },
      set(value) { this.store.commit('ValidatorLanguage', value);},
    },
    interactive: {
      get() { return this.store.getters.isInteractive;},
      set(value) { this.store.commit('Interactive', value);},
    },
    interactiveLanguage: {
      get() { return this.store.state.request.input.interactive.language;},
      set(value) { this.store.commit('InteractiveLanguage', value);},
    },
    interactiveModuleName: {
      get() { return this.store.state.request.input.interactive.module_name;},
      set(value) { this.store.commit('InteractiveModuleName', value);},
    },
  },
};
</script>
