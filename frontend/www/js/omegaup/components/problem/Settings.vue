<template>
  <div>
    <div class="row">
      <div class="form-group col-md-6">
        <label for="validator_time_limit">{{
          T.problemEditFormValidatorTimeLimit
        }}</label>
        <input
          name="validator_time_limit"
          :value="validatorTimeLimit"
          :disabled="currentLanguages === '' || validator !== 'custom'"
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
          :disabled="currentLanguages === ''"
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
        }} ch test</label>
        <input
          v-model="currentOverallWallTimeLimit"
          name="overall_wall_time_limit"
          :class="{
            'is-invalid': errors.includes('overall_wall_time_limit'),
          }"
          :disabled="currentLanguages === ''"
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
          :disabled="currentLanguages === ''"
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
          :disabled="currentLanguages === ''"
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
          :disabled="currentLanguages === ''"
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
          :disabled="currentLanguages === ''"
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
  @Prop() errors!: Array<string>;
  @Prop() currentLanguages!: string;
  @Prop() validator!: string;

  T = T;

  currentOverallWallTimeLimit = this.overallWallTimeLimit;

  @Watch('currentOverallWallTimeLimit')
  onOverallWallTimeLimitChanged(newVal: number) {
    if (newVal > 60000) {
      this.currentOverallWallTimeLimit = 60000;
    }
  }
}
</script>
