<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header d-flex justify-content-center">
        <h1 class="h4 mb-0 font-weight-bold">{{ T.compareUsersTitle }}</h1>
      </div>
      <div class="card-body">
        <!-- User Input Section -->
        <div class="row mb-4">
          <div class="col-md-5">
            <label class="form-label">{{ T.compareUser1Label }}</label>
            <omegaup-common-typeahead
              :existing-options="searchResultUsers1"
              :value.sync="selectedUser1"
              :max-results="10"
              :placeholder="T.compareEnterUsername"
              @update-existing-options="
                (query) =>
                  $emit('update-search-result-users', { query, field: 'user1' })
              "
              @update:value="(user) => $emit('update:selectedUser1', user)"
            ></omegaup-common-typeahead>
          </div>
          <div class="col-md-2 d-flex align-items-end justify-content-center">
            <span class="h4 mb-2 text-muted" aria-hidden="true">VS</span>
          </div>
          <div class="col-md-5">
            <label class="form-label">{{ T.compareUser2Label }}</label>
            <omegaup-common-typeahead
              :existing-options="searchResultUsers2"
              :value.sync="selectedUser2"
              :max-results="10"
              :placeholder="T.compareEnterUsername"
              @update-existing-options="
                (query) =>
                  $emit('update-search-result-users', { query, field: 'user2' })
              "
              @update:value="(user) => $emit('update:selectedUser2', user)"
            ></omegaup-common-typeahead>
          </div>
        </div>
        <div class="text-center mb-4">
          <button
            class="btn btn-primary px-4 py-2"
            :disabled="!canCompare"
            @click="fetchComparison"
          >
            {{ T.compareButton }}
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">{{ T.spinnerLoadingMessage }}</span>
          </div>
        </div>

        <!-- Comparison Results -->
        <div v-else-if="user1 || user2" class="row">
          <!-- User 1 Card -->
          <div class="col-md-6 mb-3">
            <omegaup-user-compare-card
              v-if="user1"
              :profile="user1.profile"
              :solved-problems-count="user1.solvedProblemsCount"
              :contests-count="user1.contestsCount"
              :comparison-class="getComparisonClass(1)"
            ></omegaup-user-compare-card>
            <div v-else class="card h-100">
              <div class="card-body text-center text-muted">
                <p>{{ T.compareUserNotFound }}</p>
              </div>
            </div>
          </div>
          <!-- User 2 Card -->
          <div class="col-md-6 mb-3">
            <omegaup-user-compare-card
              v-if="user2"
              :profile="user2.profile"
              :solved-problems-count="user2.solvedProblemsCount"
              :contests-count="user2.contestsCount"
              :comparison-class="getComparisonClass(2)"
            ></omegaup-user-compare-card>
            <div v-else class="card h-100">
              <div class="card-body text-center text-muted">
                <p>{{ T.compareUserNotFound }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-5 text-muted">
          <p>{{ T.compareDescription }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import user_CompareCard from './CompareCard.vue';
import common_Typeahead from '../common/Typeahead.vue';

interface UserCompareData {
  profile: types.UserProfileInfo;
  solvedProblemsCount: number | null;
  contestsCount: number | null;
}

@Component({
  components: {
    'omegaup-user-compare-card': user_CompareCard,
    'omegaup-common-typeahead': common_Typeahead,
  },
})
export default class CompareUsers extends Vue {
  @Prop({ default: null }) user1!: UserCompareData | null;
  @Prop({ default: null }) user2!: UserCompareData | null;
  @Prop({ default: null }) username1!: string | null;
  @Prop({ default: null }) username2!: string | null;
  @Prop({ default: false }) isLoading!: boolean;
  @Prop({ default: () => [] }) searchResultUsers1!: types.ListItem[];
  @Prop({ default: () => [] }) searchResultUsers2!: types.ListItem[];
  @Prop({ default: null }) selectedUser1!: types.ListItem | null;
  @Prop({ default: null }) selectedUser2!: types.ListItem | null;

  T = T;

  get canCompare(): boolean {
    return this.selectedUser1 !== null && this.selectedUser2 !== null;
  }

  fetchComparison(): void {
    if (!this.canCompare) return;
    this.$emit('compare', {
      username1: this.selectedUser1?.key,
      username2: this.selectedUser2?.key,
    });
  }

  getComparisonClass(userNumber: number): string {
    if (!this.user1 || !this.user2) return '';

    const solved1 = this.user1.solvedProblemsCount;
    const solved2 = this.user2.solvedProblemsCount;

    // If either count is null (private profile), don't show comparison styling
    if (solved1 === null || solved2 === null) return '';

    if (userNumber === 1) {
      if (solved1 > solved2) return 'compare-winner';
      if (solved1 < solved2) return 'compare-loser';
    } else {
      if (solved2 > solved1) return 'compare-winner';
      if (solved2 < solved1) return 'compare-loser';
    }
    return 'compare-tie';
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.compare-winner {
  border-color: var(--user-compare-winner-border-color) !important;
  border-width: 2px !important;
}

.compare-loser {
  border-color: var(--user-compare-loser-border-color) !important;
}

.compare-tie {
  border-color: var(--user-compare-tie-border-color) !important;
}
</style>
