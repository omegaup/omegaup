<template>
  <div class="card">
    <div v-if="!isProfile" class="card-header">
      <h4 class="card-title">
        {{ T.omegaupTitleBadges }}
        <span class="badge badge-secondary">{{ badges.length }} </span>
      </h4>
    </div>
    <div class="card-body">
      <div class="container-fluid">
        <div class="row">
          <omegaup-badge
            v-for="(badge, idx) in badges"
            :key="idx"
            :badge="badge"
          ></omegaup-badge>
        </div>
      </div>
    </div>
    <div v-if="showAllBadgesLink" class="card-footer">
      <a v-if="showAllBadgesLink" class="badges-link" href="/badge/list/">{{
        T.wordsBadgesSeeAll
      }}</a>
    </div>
    <div v-show="!badges"><img src="/media/wait.gif" alt="Loading" /></div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import badge_Badge from '../badge/Badge.vue';

@Component({
  components: {
    'omegaup-badge': badge_Badge,
  },
})
export default class BadgeList extends Vue {
  @Prop() allBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;
  @Prop() showAllBadgesLink!: boolean;

  T = T;
  isProfile = this.showAllBadgesLink;

  get badges(): types.Badge[] {
    return Array.from(this.allBadges)
      .map((badge: string) => ({
        badge_alias: badge,
        unlocked: this.visitorBadges.has(badge),
        assignation_time: new Date(),
        total_users: 0,
        owners_count: 0,
      }))
      .sort((a: types.Badge, b: types.Badge) => {
        // Alphabetical order BY NAME, not alias.
        const aName = this.getBadgeName(a.badge_alias);
        const bName = this.getBadgeName(b.badge_alias);
        if (aName == bName) {
          return 0;
        }
        return aName < bName ? -1 : 1;
      });
  }

  getBadgeName(alias: string): string {
    return T[`badge_${alias}_name`];
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
a.badges-link {
  color: var(--badges-link-font-color);
  font-size: 1rem;
}
</style>
