<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">
        {{ title }} <span class="badge">{{ badges.length }}</span>
        <a
          class="badges-link"
          href="/badge/list/"
          v-if="this.showAllBadgesLink"
          >{{ T.wordsBadgesSeeAll }}</a
        >
      </h2>
    </div>
    <div class="panel-body">
      <div class="badges-container">
        <omegaup-badge
          v-bind:badge="badge"
          v-bind:key="badge.badge_alias"
          v-for="badge in badges"
        ></omegaup-badge>
      </div>
    </div>
    <div v-show="!badges"><img src="/media/wait.gif" /></div>
  </div>
</template>

<style>
.badges-container {
  display: grid;
  justify-content: space-between;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  grid-auto-rows: 180px;
}

a.badges-link {
  color: #337ab7;
  font-size: 14px;
}
</style>

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

  get badges(): types.Badge[] {
    return Array.from(this.allBadges)
      .map((badge: string) => {
        return {
          badge_alias: badge,
          unlocked: this.visitorBadges.has(badge),
          assignation_time: new Date(),
          total_users: 0,
          owners_count: 0,
        };
      })
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

  get title(): string {
    return this.showAllBadgesLink
      ? T.wordsBadgesObtained
      : T.omegaupTitleBadges;
  }

  getBadgeName(alias: string): string {
    return T[`badge_${alias}_name`];
  }
}
</script>
