<template>
  <div>
    <div class="row">
      <div class="form-group col-md-6">
        <label>{{ T.problemEditFormLanguages }}</label>
        <select
          v-model="languages"
          name="languages"
          class="form-control"
          :class="{ 'is-invalid': errors.includes('languages') }"
          required
        >
          <option
            v-for="(languageText, languageIndex) in validLanguages"
            :key="languageIndex"
            :value="languageIndex"
          >
            {{ languageText }}
          </option>
        </select>
      </div>
      <div class="form-group col-md-6">
        <label>{{ T.problemEditFormValidatorType }}</label>
        <select
          v-model="validator"
          name="validator"
          class="form-control"
          :class="{ 'is-invalid': errors.includes('validator') }"
          :disabled="languages === ''"
          required
        >
          <option
            v-for="(validatorText, validatorIndex) in validatorTypes"
            :key="validatorIndex"
            :value="validatorIndex"
          >
            {{ validatorText }}
          </option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="form-group col-md-6">
        <label for="validator_time_limit">{{
          T.problemEditFormValidatorTimeLimit
        }}</label>
        <input
          name="validator_time_limit"
          :value="validatorTimeLimit"
          :disabled="languages === '' || validator !== 'custom'"
          type="text"
          class="form-control"
          :class="{
            'is-invalid': errors.includes('validator_time_limit'),
          }"
          required
        />
      </div>

      <div class="form-group col-md-6">
        <label for="time_limit">{{ T.problemEditFormTimeLimit }}</label>
        <input
          name="time_limit"
          :value="timeLimit"
          :disabled="languages === ''"
          type="text"
          class="form-control"
          :class="{ 'is-invalid': errors.includes('time_limit') }"
          required
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
          :class="{
            'is-invalid': errors.includes('overall_wall_time_limit'),
          }"
          :value="overallWallTimeLimit"
          :disabled="languages === ''"
          type="text"
          class="form-control"
          required
        />
      </div>

      <div class="form-group col-md-6">
        <label for="extra_wall_time">{{ T.wordsExtraWallTimeMs }}</label>
        <input
          name="extra_wall_time"
          :value="extraWallTime"
          :disabled="languages === ''"
          type="text"
          class="form-control"
          :class="{ 'is-invalid': errors.includes('extra_wall_time') }"
          required
        />
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-6">
        <label for="memory_limit">{{ T.problemEditFormMemoryLimit }}</label>
        <input
          name="memory_limit"
          :value="memoryLimit"
          :disabled="languages === ''"
          type="text"
          class="form-control"
          :class="{ 'is-invalid': errors.includes('memory_limit') }"
          required
        />
      </div>

      <div class="form-group col-md-3 col-sm-6">
        <label for="output_limit">{{ T.problemEditFormOutputLimit }}</label>
        <input
          name="output_limit"
          :value="outputLimit"
          :disabled="languages === ''"
          type="text"
          class="form-control"
          :class="{ 'is-invalid': errors.includes('output_limit') }"
          required
        />
      </div>
      <div class="form-group col-md-3 col-sm-6">
        <label for="input_limit">{{ T.problemEditFormInputLimit }}</label>
        <input
          name="input_limit"
          :value="inputLimit"
          :disabled="languages === ''"
          type="text"
          class="form-control"
          :class="{ 'is-invalid': errors.includes('input_limit') }"
          required
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class Settings extends Vue {
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
  @Prop() errors!: Array<string>;

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
