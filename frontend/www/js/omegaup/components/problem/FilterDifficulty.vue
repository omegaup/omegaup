<template>
  <div class="card mt-4">
    <div class="card-body">
      <h3>{{ T.wordsDifficulty }}</h3>
      <div
        v-for="difficulty in difficulties"
        :key="difficulty.id"
        class="form-check"
      >
        <label class="form-check-label">
          <input
            v-model="currentDifficulty"
            class="form-check-input"
            type="radio"
            name="difficulty"
            :value="difficulty.id"
            @click="$emit('change-difficulty', difficulty.id)"
          />{{ difficulty.name }}
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Emit, Watch, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class FilterDifficulty extends Vue {
  @Prop() selectedDifficulty!: string;

  T = T;
  currentDifficulty = this.selectedDifficulty;

  difficulties: { [key: string]: { name: string; id: string } } = {
    anyDifficulty: {
      name: T.qualityFormDifficultyAny,
      id: 'all',
    },
    difficultyEasy: {
      name: T.qualityFormDifficultyEasy,
      id: 'easy',
    },
    difficultyMedium: {
      name: T.qualityFormDifficultyMedium,
      id: 'medium',
    },
    difficultyHard: {
      name: T.qualityFormDifficultyHard,
      id: 'hard',
    },
  };

  @Emit('change')
  @Watch('currentDifficulty')
  onCurrentDifficultyChanged(val: string | null) {
    return val;
  }
}
</script>
