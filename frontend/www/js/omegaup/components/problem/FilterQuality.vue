<template>
  <div class="card mx-auto mt-0 mt-lg-4 col-6 col-sm-3 col-lg-12">
    <div class="card-body p-0 py-3 p-lg-3">
      <h3>{{ T.wordsQuality }}</h3>
      <div
        v-for="quality in qualityValues"
        :key="quality.id"
        class="form-check"
      >
        <label class="form-check-label">
          <input
            v-model="currentQuality"
            class="form-check-input"
            type="radio"
            name="quality"
            :value="quality.id"
            @click="$emit('change-quality', quality.id)"
          />{{ quality.name }}
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Emit, Watch, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class FilterQuality extends Vue {
  @Prop({ default: 'onlyQualityProblems' }) quality!: string;

  T = T;
  currentQuality = this.quality;

  qualityValues: { [key: string]: { name: string; id: string } } = {
    allProblems: {
      name: T.qualityFormQualityAny,
      id: 'all',
    },
    onlyQualityProblems: {
      name: T.qualityFormQualityOnly,
      id: 'onlyQualityProblems',
    },
  };

  @Emit('change')
  @Watch('currentQuality')
  onCurrentQualityChanged(val: string | null) {
    return val;
  }
}
</script>

<style scoped>
.section-font-size {
  font-size: 1.44rem;
}
</style>
