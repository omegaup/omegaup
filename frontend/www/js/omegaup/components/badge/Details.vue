<template>
  <div class="container-lg p-5">
    <h1 class="text-center">{{ name }}</h1>
    <figure
      class="px-2 py-4 row justify-content-center align-items-center text-center"
    >
      <div class="col-lg-6 d-flex justify-content-center">
        <badge-3d class="badge-icon-wrapper">
          <img
            :class="{ 'badge-icon-gray': !badge.assignation_time }"
            :src="iconUrl"
            :alt="name"
            class="badge-icon-img"
          />
        </badge-3d>
      </div>
      <figcaption class="col-lg-6 p-0 mt-4 mt-lg-0 badge-description">
        {{ description }}
      </figcaption>
    </figure>
    <div class="row justify-content-center align-items-center text-center">
      <div class="col-sm-6 col-md-4">
        <div class="font-weight-bold badge-data">
          {{ ownersNumber }}
        </div>
        <div class="badge-text">
          <span class="badge-text-icon">👥</span>
          {{ T.badgeOwnersMessage }}
        </div>
      </div>
      <div class="col-sm-6 col-md-4 mt-3 mt-md-0">
        <div class="font-weight-bold badge-data">
          {{ firstAssignationDate }}
        </div>
        <div class="badge-text">
          <span class="badge-text-icon">📅</span>
          {{ T.badgeFirstAssignationMessage }}
        </div>
      </div>
      <div class="col-sm-6 col-md-4 mt-3 mt-md-0">
        <div class="font-weight-bold badge-data">
          {{ assignationDate }}
        </div>
        <div class="badge-text">
          <omegaup-markdown :markdown="ownedMessage"></omegaup-markdown>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';
import omegaup_Markdown from '../Markdown.vue';
import Badge3D from './Badge3D.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'badge-3d': Badge3D,
  },
})
export default class BadgeDetails extends Vue {
  @Prop() badge!: types.Badge;

  T = T;

  get name(): string {
    return T[`badge_${this.badge.badge_alias}_name`];
  }

  get description(): string {
    return T[`badge_${this.badge.badge_alias}_description`];
  }

  get iconUrl(): string {
    return `/media/dist/badges/${this.badge.badge_alias}.svg`;
  }

  get ownedMessage(): string {
    return this.badge.assignation_time
      ? `<span class="badge-text-icon">😁</span> ${T.badgeAssignationTimeMessage}`
      : `<span class="badge-text-icon">😞</span> ${T.badgeNotAssignedMessage}`;
  }

  get firstAssignationDate(): string {
    return this.badge.first_assignation
      ? time.formatDate(this.badge.first_assignation)
      : '';
  }

  get assignationDate(): string {
    return this.badge.assignation_time
      ? time.formatDate(this.badge.assignation_time)
      : '';
  }

  get ownersNumber(): string {
    return `${this.badge.owners_count}/${this.badge.total_users}`;
  }
}
</script>

<style lang="scss" scoped>
.badge {
  &-icon-wrapper {
    width: 250px;
    height: 250px;
    margin: 0 auto;
  }

  &-icon-img {
    width: 100%;
    height: 100%;
  }

  &-icon-gray {
    filter: grayscale(100%);
  }

  &-description {
    font-size: 1.2em;
  }

  &-data {
    font-size: 2.5em;
  }

  &-text {
    font-size: 1.1em;

    >>> &-icon {
      font-size: 30px;
    }
  }
}
</style>
