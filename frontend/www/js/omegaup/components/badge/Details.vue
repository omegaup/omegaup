<template>
  <div class="panel badge-details-panel">
    <h1 class="text-center">{{ name }}</h1>
    <figure class="badge-info-grid">
      <img class="badge-icon"
              v-bind:src="iconUrl">
      <figcaption class="badge-description">
        {{ description }}
      </figcaption>
    </figure>
    <div class="badge-details-grid">
      <div class="badge-detail">
        <div class="badge-detail-data">
          {{ ownersPercentage }}
        </div>
        <div class="badge-detail-text">
          <span class="badge-detail-text-icon">👥</span> {{ this.T['badgeOwnersPercentageMessage']
          }}
        </div>
      </div>
      <div class="badge-detail">
        <div class="badge-detail-data">
          {{ firstAssignationDate }}
        </div>
        <div class="badge-detail-text">
          <span class="badge-detail-text-icon">📅</span> {{ this.T['badgeFirstAssignationMessage']
          }}
        </div>
      </div>
      <div class="badge-detail">
        <div class="badge-detail-data">
          {{ assignationDate }}
        </div>
        <div class="badge-detail-text"
             v-html="ownedMessage"></div>
      </div>
    </div>
  </div>
</template>

<style>
.badge-details-panel {
  padding: 15px;
}

.badge-info-grid,
.badge-details-grid {
  display: grid;
  justify-content: center;
  justify-items:center;
  align-items: center;
  text-align: center;
  row-gap: 40px;
  column-gap: 20px;
  padding: 30px 20px;
}

.badge-info-grid {
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
}

.badge-details-grid {
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.badge-icon {
  max-width: 300px;
}

.badge-description {
  font-size: 20px;
}

.badge-detail {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.badge-detail-data {
  font-size: 45px;
  font-weight: bold;
}

.badge-detail-text {
  font-size: 18px;
}

.badge-detail-text-icon {
  font-size: 30px;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';

@Component
export default class BadgeDetails extends Vue {
  @Prop() badge!: omegaup.Badge;

  T = T;
  UI = UI;

  get name(): string {
    return this.T[`badge_${this.badge.badge_alias}_name`];
  }

  get description(): string {
    return this.T[`badge_${this.badge.badge_alias}_description`];
  }

  get iconUrl(): string {
    return this.unlockedBadge()
      ? `/media/dist/badges/${this.badge.badge_alias}.svg`
      : '/media/locked_badge.svg';
  }

  get ownedMessage(): string {
    return this.unlockedBadge()
      ? `<span class="badge-detail-text-icon">😁</span> ${this.T['badgeAssignationTimeMessage']}`
      : `<span class="badge-detail-text-icon">😞</span> ${this.T['badgeNotAssignedMessage']}`;
  }

  get firstAssignationDate(): string {
    return this.badge.first_assignation
      ? this.UI.formatDate(new Date(this.badge.first_assignation * 1000))
      : '';
  }

  get assignationDate(): string {
    return this.unlockedBadge() && this.badge.assignation_time
      ? this.UI.formatDate(new Date(this.badge.assignation_time * 1000))
      : '';
  }

  get ownersPercentage(): string {
    return this.badge.owners_percentage
      ? `${this.badge.owners_percentage.toFixed(2)}%`
      : '';
  }

  unlockedBadge(): boolean {
    return this.badge.assignation_time !== -1 && !!this.badge.assignation_time;
  }
}

</script>
