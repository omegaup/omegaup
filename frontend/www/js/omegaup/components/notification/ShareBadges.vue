<template>
  <b-modal
    v-model="showModal"
    :title="badgeName"
    hide-footer
    body-class="p-3"
    static
    lazy
    centered
    content-class="share-badge-modal-content"
  >
    <div class="badge-image-container mb-3">
      <div class="position-relative">
        <img class="badge-image" :src="badgeImageUrl" :alt="badgeName" />
        <button
          ref="copyButton"
          class="btn btn-sm btn-outline-secondary copy-button"
          :title="T.copyBadgeImage"
          @click.stop="copyImageToClipboard"
        >
          <font-awesome-icon :icon="['fas', 'copy']" />
        </button>
        <div v-if="tooltipVisible" class="tooltip-message">
          {{ tooltipMessage }}
        </div>
      </div>
    </div>

    <div class="share-options d-flex justify-content-center mb-3">
      <button class="btn btn-outline-primary mx-2" @click="shareOnLinkedIn">
        <font-awesome-icon :icon="['fab', 'linkedin']" class="me-1" />
        {{ T.socialMediaLinkedIn }}
      </button>
      <button class="btn btn-outline-info mx-2" @click="shareOnTwitter">
        <font-awesome-icon :icon="['fab', 'twitter']" class="me-1" />
        {{ T.socialMediaTwitter }}
      </button>
      <button class="btn btn-outline-primary mx-2" @click="shareOnFacebook">
        <font-awesome-icon :icon="['fab', 'facebook-f']" class="me-1" />
        {{ T.socialMediaFacebook }}
      </button>
    </div>
  </b-modal>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faCopy } from '@fortawesome/free-solid-svg-icons';
import {
  faLinkedin,
  faFacebookF,
  faTwitter,
} from '@fortawesome/free-brands-svg-icons';

library.add(faCopy, faLinkedin, faFacebookF, faTwitter);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class ShareBadges extends Vue {
  @Prop() badgeName!: string;
  @Prop({ default: false }) value!: boolean;

  tooltipVisible = false;
  tooltipMessage = '';

  T = T;
  ui = ui;

  get showModal(): boolean {
    return this.value;
  }

  set showModal(value: boolean) {
    this.$emit('input', value);
  }

  get badgeImageUrl(): string {
    return `/media/dist/badges/${this.badgeName}.svg`;
  }

  get shareText(): string {
    return ui.formatString(T.badgeShareText, {
      badgeName: this.badgeName,
    });
  }

  get shareUrl(): string {
    return `https://omegaup.com/badge/${this.badgeName}`;
  }

  shareOnLinkedIn(): void {
    const text = `${this.shareText}\n\n${ui.formatString(T.shareBadgeLink, {
      url: this.shareUrl,
    })}`;
    const linkedinShareUrl = `https://www.linkedin.com/sharing/share-offsite/?text=${encodeURIComponent(
      text,
    )}`;
    window.open(linkedinShareUrl, '_blank', 'noopener,noreferrer');
  }

  shareOnTwitter(): void {
    const text = `${this.shareText}\n\n${ui.formatString(T.shareBadgeLink, {
      url: this.shareUrl,
    })}`;
    const twitterShareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(
      text,
    )}`;
    window.open(twitterShareUrl, '_blank', 'noopener,noreferrer');
  }

  shareOnFacebook(): void {
    //Facebook's sharing URL (sharer.php) only allows sharing a URL and ignores any additional text.
    const facebookShareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(
      this.shareUrl,
    )}`;
    window.open(facebookShareUrl, '_blank', 'noopener,noreferrer');
  }

  showTooltip(message: string): void {
    this.tooltipMessage = message;
    this.tooltipVisible = true;

    // Auto-hide the tooltip after 3 seconds
    setTimeout(() => {
      this.tooltipVisible = false;
    }, 3000);
  }

  copyImageToClipboard(): void {
    // Emit an event to the parent component to handle the copy operation
    this.$emit('copy-badge-image', this.badgeName);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.badge-image-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.badge-image {
  max-width: 150px;
  max-height: 150px;
}

.copy-button {
  position: absolute;
  top: 5px;
  right: -35px;
  padding: 0.2rem 0.5rem;
  opacity: 0.8;
  transition: opacity 0.2s;

  &:hover {
    opacity: 1;
  }
}

.share-badge-modal-content {
  margin-top: 4rem;
}

.tooltip-message {
  position: absolute;
  bottom: -40px;
  left: 50%;
  transform: translateX(-50%);
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 0.8rem;
  white-space: nowrap;
  animation: fade-in-out 3s ease-in-out;
}
</style>
