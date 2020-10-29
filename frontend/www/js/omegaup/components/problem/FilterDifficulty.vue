<template>
  <div class="card mt-4">
    <div class="card-body">
      <h3>{{ T.wordsDifficulty }}</h3>
      <div
        v-for="(difficulty, index) in difficulties"
        :key="index"
        class="form-check"
      >
        <label class="form-check-label">
          <input
            v-model="currentDifficulty"
            class="form-check-input"
            type="radio"
            name="difficulty"
            :value="index"
          />{{ difficulty }}
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Model, Emit, Watch } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class FilterDifficulty extends Vue {
  @Model('change', { type: String }) readonly selectedDifficulty:
    | null
    | string = null;

  T = T;
  difficulties: { [key: string]: string } = {
    qualityFormDifficultyEasy: T.qualityFormDifficultyEasy,
    qualityFormDifficultyMedium: T.qualityFormDifficultyMedium,
    qualityFormDifficultyHard: T.qualityFormDifficultyHard,
  };

  currentDifficulty = this.selectedDifficulty;

  @Watch('selectedDifficulty')
  onSelectedDifficultyChanged(val: string | null) {
    this.currentDifficulty = val;
  }

  @Emit('change')
  @Watch('currentDifficulty')
  onCurrentDifficultyChanged(val: string | null) {
    return val;
  }
}
</script>
