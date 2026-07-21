<template>
  <div class="card">
    <div class="text-white bg-primary card-header">
      <div class="card-title h4">
        {{ T.omegaupTitleAdminSettings }}
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-12">
          <h4>{{ T.wordsEphemeralGrader }}</h4>
          <div class="form-check">
            <input v-model="enabled" type="checkbox" class="form-check-input" />
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
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class Settings extends Vue {
  T = T;

  @Prop({ default: true }) ephemeralGraderEnabled!: boolean;

  enabled = this.ephemeralGraderEnabled;

  @Watch('enabled')
  onEnabledChanged(newValue: boolean): void {
    this.$emit('update-ephemeral-grader', newValue);
  }
}
</script>
