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
            class="form-check-input"
            type="radio"
            name="difficulty"
            :value="difficulty"
            @change="$emit('change', $event.target.value)"
          />{{ T[difficulty] }}
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Model } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class FilterDifficulty extends Vue {
  @Model('change', { type: String }) readonly selectedDifficulty:
    | null
    | string = null;

  T = T;
  difficulties: string[] = [
    'qualityFormDifficultyEasy',
    'qualityFormDifficultyMedium',
    'qualityFormDifficultyHard',
  ];
}
</script>
