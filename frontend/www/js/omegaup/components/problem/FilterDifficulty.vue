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
            :selectedDifficulty="difficulty"
            class="form-check-input"
            type="radio"
            @change="$emit('change', $event.target.difficulty)"
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
  @Model('change') selectedDifficulty: string[] = [];

  difficulties: string[] = [
    'qualityFormDifficultyEasy',
    'qualityFormDifficultyMedium',
    'qualityFormDifficultyHard',
  ];

  T = T;
}
</script>
