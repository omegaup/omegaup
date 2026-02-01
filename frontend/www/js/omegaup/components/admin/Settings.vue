<template>
  <div class="card">
    <div class="text-white bg-primary card-header">
      <div class="card-title h4">
        {{ T.omegaupTitleAdminSettings }}
      </div>
    </div>
    <div class="card-body">
      <div v-if="loading" class="text-center">
        <div class="spinner-border" role="status">
          <span class="sr-only">Loading...</span>
        </div>
      </div>
      <div v-else class="row mb-3">
        <div class="col-md-12">
          <h4>{{ T.wordsEphemeralGrader }}</h4>
          <div class="form-check">
            <input
              v-model="ephemeralGraderEnabled"
              type="checkbox"
              class="form-check-input"
            />
            <label class="form-check-label">
              {{ T.ephemeralGraderEnable }}
            </label>
          </div>
          <small class="form-text text-muted">
            {{ T.ephemeralGraderDescription }}
          </small>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Watch } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import * as api from '../../api';

@Component
export default class Settings extends Vue {
  T = T;
  ui = ui;

  ephemeralGraderEnabled: boolean | null = null;
  loading = true;
  private updating = false;

  async mounted(): Promise<void> {
    try {
      const response = await api.Admin.getSystemSettings();
      if (response && response.settings) {
        this.ephemeralGraderEnabled = Boolean(
          response.settings.ephemeralGraderEnabled,
        );
      }
    } catch (error: any) {
      this.ui.error(error);
    } finally {
      this.loading = false;
    }
  }

  @Watch('ephemeralGraderEnabled')
  async onEphemeralGraderEnabledChanged(
    newValue: boolean | null,
    oldValue: boolean | null,
  ): Promise<void> {
    if (
      this.loading ||
      this.updating ||
      newValue === null ||
      oldValue === null
    ) {
      return;
    }

    this.updating = true;

    try {
      await api.Admin.updateSystemSettings({
        ephemeral_grader_enabled: newValue,
      });
      this.ui.success(
        newValue ? 'Ephemeral Grader enabled' : 'Ephemeral Grader disabled',
      );
    } catch (error: any) {
      this.ui.error(error);
      // Rollback on failure
      this.ephemeralGraderEnabled = oldValue;
    } finally {
      this.updating = false;
    }
  }
}
</script>
