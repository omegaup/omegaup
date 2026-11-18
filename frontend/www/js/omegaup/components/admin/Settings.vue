<template>
  <div>
    <h1 class="h3 mb-3">{{ T.omegaupTitleAdminGlobalSettings }}</h1>
    <div class="card">
      <div class="card-body">
        <div class="setting-card">
          <font-awesome-icon icon="laptop-code" class="setting-icon" />
          <h5 class="setting-title">{{ T.wordsEphemeralGrader }}</h5>
          <p class="setting-description text-muted mb-0">
            {{ T.ephemeralGraderDescription }}
          </p>
          <div class="setting-control">
            <strong class="mr-3">{{
              enabled
                ? T.ephemeralGraderStatusEnabled
                : T.ephemeralGraderStatusDisabled
            }}</strong>
            <omegaup-toggle-switch
              :checked-value="enabled"
              @update:value="enabled = $event"
            >
              <template #switch-text>
                <span class="sr-only">{{ T.wordsEphemeralGrader }}</span>
              </template>
            </omegaup-toggle-switch>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import omegaup_ToggleSwitch from '../ToggleSwitch.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'omegaup-toggle-switch': omegaup_ToggleSwitch,
  },
})
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

<style lang="scss" scoped>
.setting-card {
  display: grid;
  grid-template-columns: auto 1fr auto;
  grid-template-areas:
    'icon title control'
    'icon description control';
  column-gap: 1rem;
  align-items: center;
}

.setting-icon {
  grid-area: icon;
  font-size: 1.5rem;
  color: var(--admin-dashboard-setting-icon-color);
}

.setting-title {
  grid-area: title;
  align-self: end;
  margin-bottom: 0.25rem;
}

.setting-description {
  grid-area: description;
  align-self: start;
}

.setting-control {
  grid-area: control;
  display: flex;
  align-items: center;
  justify-content: flex-end;

  .switch-container {
    width: auto;
    margin-bottom: 0;
    flex-shrink: 0;
  }
}

@media (max-width: 767.98px) {
  .setting-card {
    grid-template-columns: auto 1fr;
    grid-template-areas:
      'icon title'
      'description description'
      'control control';
    row-gap: 0.75rem;
  }

  .setting-title {
    align-self: center;
    margin-bottom: 0;
  }

  .setting-control {
    justify-content: space-between;
    border-top: 1px solid var(--admin-dashboard-sidebar-border-color);
    padding-top: 0.75rem;
  }
}
</style>
