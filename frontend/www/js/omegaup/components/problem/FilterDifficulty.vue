<template>
  <div class="card mt-4">
    <div class="card-body">
      <h3>{{ T.wordsDifficulty }}</h3>
      <div class="form-check">
        <label class="form-check-label">
          <input
            v-model="selectedDifficulty"
            value="0"
            class="form-check-input"
            type="radio"
          />{{ T.qualityFormDifficultyEasy }}
        </label>
      </div>
      <div class="form-check">
        <label class="form-check-label">
          <input
            v-model="selectedDifficulty"
            value="1"
            class="form-check-input"
            type="radio"
          />{{ T.qualityFormDifficultyMedium }}
        </label>
      </div>
      <div class="form-check">
        <label class="form-check-label">
          <input
            v-model="selectedDifficulty"
            value="2"
            class="form-check-input"
            type="radio"
          />{{ T.qualityFormDifficultyHard }}
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class CollectionFilterDifficulty extends Vue {

  T = T;
  selectedDifficulty = '';
}
</script>
