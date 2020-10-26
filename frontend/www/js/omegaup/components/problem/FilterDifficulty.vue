<template>
  <div class="card">
    <div class="card-body">
      <h3>{{ T.wordsDifficulty }}</h3>
      <div class="form-check">
        <label class="form-check-label">
          <input
            v-model="selectedDifficulty"
            class="form-check-input"
            type="radio"
          />{{ T.qualityFormDifficultyEasy }}
        </label>
      </div>
      <div class="form-check">
        <label class="form-check-label">
          <input
            v-model="selectedDifficulty"
            class="form-check-input"
            type="radio"
          />{{ T.qualityFormDifficultyMedium }}
        </label>
      </div>
      <div class="form-check">
        <label class="form-check-label">
          <input
            v-model="selectedDifficulty"
            class="form-check-input"
            type="radio"
          />{{ T.qualityFormDifficultyHard }}
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Prop } from 'vue-property-decorator';
import T from '../../lang';

export default class CollectionFilterDifficulty extends Vue {
  @Prop() algo!: string;

  T = T;
  selectedDifficulty = '';
}
</script>
