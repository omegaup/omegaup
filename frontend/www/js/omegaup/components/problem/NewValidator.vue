<template>
  <div>
    <!-- id-lint off -->
    <div class="row">
      <div class="form-group  col-md-6">
        <label for="validator_time_limit">{{
          T.problemEditFormValidatorTimeLimit
        }}</label>
        <input
          id="validator_time_limit"
          name="validator_time_limit"
          v-bind:value="!IS_UPDATE ? VALIDATOR_TIME_LIMIT : ''"
          v-bind:disabled="LANGUAGES === ''"
          type="text"
          class="form-control"
        />
      </div>

      <div class="form-group  col-md-6">
        <label for="time_limit">{{ T.problemEditFormTimeLimit }}</label>
        <input
          id="time_limit"
          name="time_limit"
          v-bind:value="!IS_UPDATE ? TIME_LIMIT : ''"
          v-bind:disabled="LANGUAGES === ''"
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
          id="overall_wall_time_limit"
          name="overall_wall_time_limit"
          v-bind:value="!IS_UPDATE ? OVERALL_WALL_TIME_LIMIT : ''"
          v-bind:disabled="LANGUAGES === ''"
          type="text"
          class="form-control"
        />
      </div>

      <div class="form-group col-md-6">
        <label for="extra_wall_time">{{ T.wordsExtraWallTimeMs }}</label>
        <input
          id="extra_wall_time"
          name="extra_wall_time"
          v-bind:value="!IS_UPDATE ? EXTRA_WALL_TIME : ''"
          v-bind:disabled="LANGUAGES === ''"
          type="text"
          class="form-control"
        />
      </div>
    </div>

    <div class="row">
      <div class="form-group  col-md-6">
        <label for="memory_limit">{{ T.problemEditFormMemoryLimit }}</label>
        <input
          id="memory_limit"
          name="memory_limit"
          v-bind:value="!IS_UPDATE ? MEMORY_LIMIT : ''"
          v-bind:disabled="LANGUAGES === ''"
          type="text"
          class="form-control"
        />
      </div>

      <div class="form-group col-md-3 col-sm-6">
        <label for="output_limit">{{ T.problemEditFormOutputLimit }}</label>
        <input
          id="output_limit"
          name="output_limit"
          v-bind:value="!IS_UPDATE ? OUTPUT_LIMIT : ''"
          v-bind:disabled="LANGUAGES === ''"
          type="text"
          class="form-control"
        />
      </div>
      <div class="form-group col-md-3 col-sm-6">
        <label for="input_limit">{{ T.problemEditFormInputLimit }}</label>
        <input
          id="input_limit"
          name="input_limit"
          v-bind:value="!IS_UPDATE ? INPUT_LIMIT : ''"
          v-bind:disabled="LANGUAGES === ''"
          type="text"
          class="form-control"
        />
      </div>
    </div>
    <!-- id-lint on -->
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class ProblemNewValidator extends Vue {
  @Prop() TIME_LIMIT!: number;
  @Prop() IS_UPDATE!: boolean;
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
