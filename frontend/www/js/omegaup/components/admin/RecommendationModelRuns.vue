<template>
  <div>
    <h5 class="mt-4">{{ T.cronControlPlaneModelHeading }}</h5>
    <p class="small text-muted">{{ T.cronControlPlaneModelScoreInfo }}</p>
    <table v-if="modelRuns.length" class="table table-sm" data-cron-model-runs>
      <thead>
        <tr>
          <th>{{ T.cronControlPlaneModelTrained }}</th>
          <th>{{ T.cronControlPlaneModelScore }}</th>
          <th>{{ T.cronControlPlaneModelDataset }}</th>
          <th>{{ T.cronControlPlaneModelSeed }}</th>
          <th>{{ T.cronControlPlaneModelPublished }}</th>
          <th>{{ T.cronControlPlaneModelSkipReason }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="modelRun in modelRuns" :key="modelRun.model_run_id">
          <td>{{ formatDate(modelRun.created_at) }}</td>
          <td>
            <code>{{ formatMapScore(modelRun.map_score) }}</code>
            <small class="d-block text-muted">{{
              describeMapScore(modelRun.map_score)
            }}</small>
          </td>
          <td :title="String(modelRun.dataset_size)">
            {{ formatCount(modelRun.dataset_size) }}
          </td>
          <td>{{ seedLabel(modelRun.rng_seed) }}</td>
          <td>
            <span :class="publishedClass(modelRun.published)">{{
              publishedLabel(modelRun.published)
            }}</span>
          </td>
          <td>{{ modelRun.skip_reason || '—' }}</td>
        </tr>
      </tbody>
    </table>
    <span v-else>{{ T.cronControlPlaneModelNoRuns }}</span>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as time from '../../time';
import { types } from '../../api_types';

@Component
export default class RecommendationModelRuns extends Vue {
  T = T;
  @Prop({ default: () => [] }) modelRuns!: types.RecommendationModelRun[];

  formatDate(date: Date): string {
    return time.formatDateTime(date);
  }

  formatMapScore(score: number): string {
    return score.toFixed(4);
  }

  describeMapScore(score: number): string {
    return score.toLocaleString(T.locale, {
      style: 'percent',
      maximumFractionDigits: 1,
    });
  }

  formatCount(count: number): string {
    return count.toLocaleString(T.locale);
  }

  // Without a seed every run splits its users differently, so say so.
  seedLabel(seed?: number | null): string {
    return typeof seed === 'number'
      ? String(seed)
      : T.cronControlPlaneModelSeedUnset;
  }

  publishedLabel(published: boolean): string {
    return published ? T.wordsYes : T.wordsNo;
  }

  publishedClass(published: boolean): string {
    return published ? 'badge badge-success' : 'badge badge-secondary';
  }
}
</script>
