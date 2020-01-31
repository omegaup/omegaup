<template>
  <div>
    <div class="row">
      <div class="form-group  col-md-6">
        <label for="validator_time_limit">{{
          T.problemEditFormValidatorTimeLimit
        }}</label>
        <input
          name="validator_time_limit"
          v-bind:value="VALIDATOR_TIME_LIMIT"
          type="text"
          class="form-control"
        />
      </div>

      <div class="form-group  col-md-6">
        <label for="time_limit">{{ T.problemEditFormTimeLimit }}</label>
        <input
          name="time_limit"
          v-bind:value="TIME_LIMIT"
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
          v-bind:value="OVERALL_WALL_TIME_LIMIT"
          type="text"
          class="form-control"
        />
      </div>

      <div class="form-group col-md-6">
        <label for="extra_wall_time">{{ T.wordsExtraWallTimeMs }}</label>
        <input
          name="extra_wall_time"
          v-bind:value="EXTRA_WALL_TIME"
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
          v-bind:value="MEMORY_LIMIT"
          type="text"
          class="form-control"
        />
      </div>

      <div class="form-group col-md-3 col-sm-6">
        <label for="output_limit">{{ T.problemEditFormOutputLimit }}</label>
        <input
          name="output_limit"
          v-bind:value="OUTPUT_LIMIT"
          type="text"
          class="form-control"
        />
      </div>
      <div class="form-group col-md-3 col-sm-6">
        <label for="input_limit">{{ T.problemEditFormInputLimit }}</label>
        <input
          name="input_limit"
          v-bind:value="INPUT_LIMIT"
          type="text"
          class="form-control"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class ProblemValidator extends Vue {
  @Prop() TIME_LIMIT!: number;
  @Prop() EXTRA_WALL_TIME!: number;
  @Prop() MEMORY_LIMIT!: number;
  @Prop() OUTPUT_LIMIT!: number;
  @Prop() OVERALL_WALL_TIME_LIMIT!: number;
  @Prop() INPUT_LIMIT!: number;
  @Prop() VALIDATOR_TIME_LIMIT!: number;
  @Prop() LANGUAGES!: string;

  T = T;
  UI = UI;
}
</script>
