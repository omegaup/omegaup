<template>
  <div>
    <div class="row">
      <div class="form-group col-md-6">
        <label>{{ T.problemEditFormLanguages }}</label>
        <select name="languages" class="form-control" v-model="languages">
          <option
            v-for="(languageText, languageIndex) in validLanguages"
            v-bind:value="languageIndex"
          >
            {{ languageText }}</option
          >
        </select>
      </div>
      <div class="form-group col-md-6">
        <label>{{ T.problemEditFormValidatorType }}</label>
        <select
          name="validator"
          class="form-control"
          v-model="validator"
          v-bind:disabled="languages === ''"
        >
          <option
            v-for="(validatorText, validatorIndex) in validatorTypes"
            v-bind:value="validatorIndex"
          >
            {{ validatorText }}</option
          >
        </select>
      </div>
    </div>
    <div class="row">
      <div class="form-group  col-md-6">
        <label for="validator_time_limit">{{
          T.problemEditFormValidatorTimeLimit
        }}</label>
        <input
          name="validator_time_limit"
          v-bind:value="validatorTimeLimit"
          v-bind:disabled="languages === ''"
          type="text"
          class="form-control"
        />
      </div>

      <div class="form-group  col-md-6">
        <label for="time_limit">{{ T.problemEditFormTimeLimit }}</label>
        <input
          name="time_limit"
          v-bind:value="timeLimit"
          v-bind:disabled="languages === ''"
          type="text"
          class="form-control"
        />
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-6">
        <label for="overall_wall_time_limit">{{
          T.problemEditFormWallTimeLimit
        }}</label>
        <input
          name="overall_wall_time_limit"
          v-bind:value="overallWallTimeLimit"
          v-bind:disabled="languages === ''"
          type="text"
          class="form-control"
        />
      </div>

      <div class="form-group col-md-6">
        <label for="extra_wall_time">{{ T.wordsExtraWallTimeMs }}</label>
        <input
          name="extra_wall_time"
          v-bind:value="extraWallTime"
          v-bind:disabled="languages === ''"
          type="text"
          class="form-control"
        />
      </div>
    </div>

    <div class="row">
      <div class="form-group  col-md-6">
        <label for="memory_limit">{{ T.problemEditFormMemoryLimit }}</label>
        <input
          name="memory_limit"
          v-bind:value="memoryLimit"
          v-bind:disabled="languages === ''"
          type="text"
          class="form-control"
        />
      </div>

      <div class="form-group col-md-3 col-sm-6">
        <label for="output_limit">{{ T.problemEditFormOutputLimit }}</label>
        <input
          name="output_limit"
          v-bind:value="outputLimit"
          v-bind:disabled="languages === ''"
          type="text"
          class="form-control"
        />
      </div>
      <div class="form-group col-md-3 col-sm-6">
        <label for="input_limit">{{ T.problemEditFormInputLimit }}</label>
        <input
          name="input_limit"
          v-bind:value="inputLimit"
          v-bind:disabled="languages === ''"
          type="text"
          class="form-control"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class ProblemSettings extends Vue {
  @Prop() timeLimit!: number;
  @Prop() extraWallTime!: number;
  @Prop() memoryLimit!: number;
  @Prop() outputLimit!: number;
  @Prop() inputLimit!: number;
  @Prop() overallWallTimeLimit!: number;
  @Prop() validatorTimeLimit!: number;
  @Prop() initialLanguage!: string;
  @Prop() validLanguages!: Array<string>;
  @Prop() initialValidator!: string;
  @Prop() validatorTypes!: Array<string>;

  T = T;

  validator = this.initialValidator;
  languages = this.initialLanguage;

  @Watch('initialValidator')
  onInitialValidatorChange(newInitial: string): void {
    this.validator = newInitial;
  }

  @Watch('initialLanguage')
  onInitialLanguageChange(newInitial: string): void {
    this.languages = newInitial;
  }
}
</script>
