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

<script>
export default {
  props: {
    store: {
      type: Object,
      required: true,
    },
    storeMapping: {
      type: Object,
      required: true,
    },
  },
  data: function () {
    return {
      title: 'settings',
    };
  },
  computed: {
    timeLimit: {
      get() {
        return this.store.state.request.input.limits.TimeLimit;
      },
      set(value) {
        this.store.commit('TimeLimit', Number.parseFloat(value));
      },
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
      get() {
        return this.store.state.request.input.limits.ExtraWallTime;
      },
      set(value) {
        this.store.commit('ExtraWallTime', Number.parseFloat(value));
      },
    },
    memoryLimit: {
      get() {
        return this.store.state.request.input.limits.MemoryLimit;
      },
      set(value) {
        this.store.commit('MemoryLimit', Number.parseInt(value));
      },
    },
    outputLimit: {
      get() {
        return this.store.state.request.input.limits.OutputLimit;
      },
      set(value) {
        this.store.commit('OutputLimit', Number.parseInt(value));
      },
    },
    validator: {
      get() {
        return this.store.state.request.input.validator.name;
      },
      set(value) {
        this.store.commit('Validator', value);
      },
    },
    tolerance: {
      get() {
        return this.store.state.request.input.validator.tolerance;
      },
      set(value) {
        this.store.commit('Tolerance', value);
      },
    },
    validatorLanguage: {
      get() {
        return this.store.state.request.input.validator.custom_validator
          .language;
      },
      set(value) {
        this.store.commit('ValidatorLanguage', value);
      },
    },
    interactive: {
      get() {
        return this.store.getters.isInteractive;
      },
      set(value) {
        if (value) this.store.commit('Interactive', {});
        else this.store.commit('Interactive', undefined);
      },
    },
    interactiveLanguage: {
      get() {
        return this.store.state.request.input.interactive.language;
      },
      set(value) {
        this.store.commit('InteractiveLanguage', value);
      },
    },
    interactiveModuleName: {
      get() {
        return this.store.state.request.input.interactive.module_name;
      },
      set(value) {
        this.store.commit('InteractiveModuleName', value);
      },
    },
  },
};
</script>
