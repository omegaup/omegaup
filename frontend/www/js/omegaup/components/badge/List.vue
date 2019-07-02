<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ title }} <span class="badge">{{ badges.length }}</span> <a class=
      "badges-link"
         href="/badge/list/"
         v-if="this.forProfile">{{ this.T.wordsBadgesSeeAll }}</a></h2>
    </div>
    <div class="panel-body">
      <div class="badges-container">
        <omegaup-badge v-bind:alias="badge.badge_alias"
             v-bind:key="badge.badge_alias"
             v-bind:unlocked="badge.unlocked"
             v-for="badge in badges"></omegaup-badge>
      </div>
    </div>
    <div v-show="!badges"><img src="/media/wait.gif"></div>
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
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import Badge from '../badge/Badge.vue';

@Component({
  components: {
    'omegaup-badge': Badge,
  },
})
export default class BadgeList extends Vue {
  @Prop() allBadges!: Set<string>;
  @Prop() visitorBadges!: Set<string>;
  @Prop() forProfile!: boolean;

  T = T;

  get badges(): omegaup.Badge[] {
    return Array.from(this.allBadges)
      .map((badge: string) => {
        return {
          badge_alias: badge,
          unlocked: this.visitorBadges.has(badge),
        };
      })
      .sort((a: omegaup.Badge, b: omegaup.Badge) => {
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
    return this.forProfile ? T.wordsBadgesObtained : T.omegaupTitleBadges;
  }

  getBadgeName(alias: string): string {
    return this.T[`badge_${alias}_name`];
  }
}

</script>
