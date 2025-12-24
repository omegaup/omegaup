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
            <input
              v-model="inputUsername1"
              type="text"
              class="form-control"
              :placeholder="T.compareEnterUsername"
              @keyup.enter="fetchComparison"
            />
          </div>
          <div class="col-md-2 d-flex align-items-end justify-content-center">
            <span class="h4 mb-2 text-muted">VS</span>
          </div>
          <div class="col-md-5">
            <label class="form-label">{{ T.compareUser2Label }}</label>
            <input
              v-model="inputUsername2"
              type="text"
              class="form-control"
              :placeholder="T.compareEnterUsername"
              @keyup.enter="fetchComparison"
            />
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

        <!-- Error Message -->
        <div v-if="errorMessage" class="alert alert-danger mb-4" role="alert">
          {{ errorMessage }}
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
import * as api from '../../api';
import { types } from '../../api_types';
import T from '../../lang';
import user_CompareCard from './CompareCard.vue';

interface UserCompareData {
  profile: types.UserProfileInfo;
  solvedProblemsCount: number;
  contestsCount: number;
}

@Component({
  components: {
    'omegaup-user-compare-card': user_CompareCard,
  },
})
export default class CompareUsers extends Vue {
  @Prop({ default: null }) initialUser1!: UserCompareData | null;
  @Prop({ default: null }) initialUser2!: UserCompareData | null;
  @Prop({ default: null }) initialUsername1!: string | null;
  @Prop({ default: null }) initialUsername2!: string | null;

  T = T;
  user1: UserCompareData | null = this.initialUser1;
  user2: UserCompareData | null = this.initialUser2;
  inputUsername1: string = this.initialUsername1 || '';
  inputUsername2: string = this.initialUsername2 || '';
  isLoading = false;
  errorMessage: string | null = null;

  get canCompare(): boolean {
    return (
      this.inputUsername1.trim() !== '' || this.inputUsername2.trim() !== ''
    );
  }

  fetchComparison(): void {
    if (!this.canCompare) return;

    // Trim usernames
    const trimmedUsername1 = this.inputUsername1.trim();
    const trimmedUsername2 = this.inputUsername2.trim();

    this.isLoading = true;
    this.errorMessage = null;

    // Update URL
    const url = new URL(window.location.href);
    if (trimmedUsername1) {
      url.searchParams.set('username1', trimmedUsername1);
    } else {
      url.searchParams.delete('username1');
    }
    if (trimmedUsername2) {
      url.searchParams.set('username2', trimmedUsername2);
    } else {
      url.searchParams.delete('username2');
    }
    window.history.pushState({}, '', url.toString());

    api.User.compare({
      username1: trimmedUsername1 || undefined,
      username2: trimmedUsername2 || undefined,
    })
      .then((response) => {
        this.user1 = response.user1 as UserCompareData | null;
        this.user2 = response.user2 as UserCompareData | null;
        this.errorMessage = null;
      })
      .catch((error: { message?: string }) => {
        console.error('Compare error:', error);
        this.errorMessage =
          error.message || T.compareUsersError;
      })
      .finally(() => {
        this.isLoading = false;
      });
  }

  getComparisonClass(userNumber: number): string {
    if (!this.user1 || !this.user2) return '';

    const solved1 = this.user1.solvedProblemsCount;
    const solved2 = this.user2.solvedProblemsCount;

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
.compare-winner {
  border-color: #28a745 !important;
  border-width: 2px !important;
}

.compare-loser {
  border-color: #6c757d !important;
}

.compare-tie {
  border-color: #ffc107 !important;
}
</style>
