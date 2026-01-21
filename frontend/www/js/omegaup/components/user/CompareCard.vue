<template>
  <div class="card h-100" :class="comparisonClass">
    <div class="card-body">
      <!-- User Header -->
      <div class="text-center mb-3">
        <a :href="profileUrl">
          <img
            v-if="profile.gravatar_92"
            :src="profile.gravatar_92"
            :alt="profileImageAlt"
            class="rounded-circle mb-2"
            width="92"
            height="92"
          />
        </a>
        <h5 class="card-title mb-1">
          <omegaup-user-username
            :classname="profile.classname"
            :username="profile.username"
            :linkify="true"
          ></omegaup-user-username>
        </h5>
        <p v-if="profile.name" class="text-muted small mb-0">
          {{ profile.name }}
        </p>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <!-- Rank -->
        <div class="stat-item">
          <div class="stat-label">{{ T.profileRank }}</div>
          <div class="stat-value" :class="profile.classname">
            {{ rankDisplay }}
          </div>
        </div>

        <!-- Problems Solved -->
        <div class="stat-item">
          <div class="stat-label">{{ T.profileSolvedProblems }}</div>
          <div class="stat-value">
            <strong>{{
              solvedProblemsCount !== null
                ? solvedProblemsCount
                : T.wordsNotApplicable
            }}</strong>
          </div>
        </div>

        <!-- Contests -->
        <div class="stat-item">
          <div class="stat-label">{{ T.profileContests }}</div>
          <div class="stat-value">
            <strong>{{
              contestsCount !== null ? contestsCount : T.wordsNotApplicable
            }}</strong>
          </div>
        </div>

        <!-- Author Ranking -->
        <div
          v-if="profile.rankinfo && profile.rankinfo.author_ranking"
          class="stat-item"
        >
          <div class="stat-label">{{ T.authorRank }}</div>
          <div class="stat-value">
            <strong>#{{ profile.rankinfo.author_ranking }}</strong>
          </div>
        </div>

        <!-- Country -->
        <div v-if="profile.country" class="stat-item">
          <div class="stat-label">{{ T.profileCountry }}</div>
          <div class="stat-value">
            {{ profile.country }}
          </div>
        </div>

        <!-- School -->
        <div v-if="profile.school" class="stat-item">
          <div class="stat-label">{{ T.profileSchool }}</div>
          <div class="stat-value small">
            {{ profile.school }}
          </div>
        </div>
      </div>

      <!-- Private Profile Notice -->
      <div v-if="profile.is_private" class="alert alert-info mt-3 mb-0">
        <small>{{ T.profileIsPrivate }}</small>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as UI from '../../ui';
import user_Username from './Username.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class CompareCard extends Vue {
  @Prop() profile!: types.UserProfileInfo;
  @Prop({ default: null }) solvedProblemsCount!: number | null;
  @Prop({ default: null }) contestsCount!: number | null;
  @Prop({ default: '' }) comparisonClass!: string;

  T = T;

  get profileUrl(): string {
    return `/profile/${encodeURIComponent(this.profile.username || '')}/`;
  }

  get profileImageAlt(): string {
    if (this.profile.username) {
      return UI.formatString(T.profileImageAltWithUsername, {
        username: this.profile.username,
      });
    }
    return T.profileImageAltGeneric;
  }

  get rankDisplay(): string {
    if (!this.profile.rankinfo) return T.profileRankUnrated;
    const rank = this.profile.rankinfo.rank;
    if (rank == null) return T.profileRankUnrated;
    return `#${rank}`;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.stat-item {
  text-align: center;
  padding: 0.5rem;
  background-color: var(--user-compare-stat-item-background-color);
  border-radius: 0.25rem;
}

.stat-label {
  font-size: 0.75rem;
  color: var(--user-compare-stat-label-font-color);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 1rem;
  margin-top: 0.25rem;
}

.card {
  transition: border-color 0.3s ease;
}
</style>
