<template>
  <div data-follow>
    <div class="mb-3 text-center">
      <a
        href="#"
        class="follow-count"
        data-following-count
        @click.prevent="$emit('show-list', 'following')"
      >
        <strong>{{ followingCount }}</strong>
        {{ T.profileFollowing }}
      </a>
      <span class="mx-2 text-muted">|</span>
      <a
        href="#"
        class="follow-count"
        data-followers-count
        @click.prevent="$emit('show-list', 'followers')"
      >
        <strong>{{ currentFollowersCount }}</strong>
        {{ T.profileFollowers }}
      </a>
    </div>
    <button
      v-if="canFollow"
      class="btn btn-block mb-3"
      :class="followButtonClass"
      data-follow-button
      @click="toggleFollow"
    >
      {{ followButtonLabel }}
    </button>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';

@Component
export default class UserFollowButton extends Vue {
  @Prop() username!: string;
  @Prop({ default: false }) isFollowing!: boolean;
  @Prop({ default: 0 }) followersCount!: number;
  @Prop({ default: 0 }) followingCount!: number;
  @Prop({ default: false }) isLoggedIn!: boolean;
  @Prop({ default: false }) isOwnProfile!: boolean;

  T = T;
  currentIsFollowing = this.isFollowing;
  currentFollowersCount = this.followersCount;

  get canFollow(): boolean {
    return this.isLoggedIn && !this.isOwnProfile;
  }

  get followButtonLabel(): string {
    return this.currentIsFollowing ? T.profileFollowing : T.profileFollow;
  }

  get followButtonClass(): string {
    return this.currentIsFollowing ? 'btn-outline-primary' : 'btn-primary';
  }

  @Watch('isFollowing')
  onIsFollowingChanged(newValue: boolean): void {
    this.currentIsFollowing = newValue;
  }

  @Watch('followersCount')
  onFollowersCountChanged(newValue: number): void {
    this.currentFollowersCount = newValue;
  }

  toggleFollow(): void {
    if (this.currentIsFollowing) {
      this.$emit('unfollow', {
        username: this.username,
        onSuccess: () => {
          this.currentIsFollowing = false;
          this.currentFollowersCount -= 1;
          ui.success(T.profileUnfollowSuccess);
        },
      });
      return;
    }
    this.$emit('follow', {
      username: this.username,
      onSuccess: () => {
        this.currentIsFollowing = true;
        this.currentFollowersCount += 1;
        ui.success(T.profileFollowSuccess);
      },
    });
  }
}
</script>

<style scoped>
.follow-count {
  color: inherit;
  text-decoration: none;
}

.follow-count:hover {
  text-decoration: underline;
}
</style>
