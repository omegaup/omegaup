<template>
  <div class="share-badges-modal">
    <div class="modal-content p-3">
      <div class="d-flex justify-content-end align-items-center mb-3">
        <button class="close" @click.prevent="$emit('close')">❌</button>
      </div>

      <div class="badge-image-container mb-3">
        <div class="position-relative">
          <img class="badge-image" :src="badgeImageUrl" :alt="badgeName" />
          <button
            class="btn btn-sm btn-outline-secondary copy-button"
            :title="T.copyBadgeImage"
            @click.stop="copyImageToClipboard"
          >
            <font-awesome-icon :icon="['fas', 'copy']" />
          </button>
        </div>
      </div>

      <div class="share-options d-flex justify-content-center mb-3">
        <button class="btn btn-outline-primary mx-2" @click="shareOnLinkedIn">
          <font-awesome-icon :icon="['fab', 'linkedin']" class="me-1" />
          LinkedIn
        </button>
        <button class="btn btn-outline-info mx-2" @click="shareOnTwitter">
          <font-awesome-icon :icon="['fab', 'twitter']" class="me-1" />
          Twitter
        </button>
        <button class="btn btn-outline-primary mx-2" @click="shareOnFacebook">
          <font-awesome-icon :icon="['fab', 'facebook-f']" class="me-1" />
          Facebook
        </button>
      </div>
    </div>
  </div>
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

  T = T;
  ui = ui;

  get badgeImageUrl(): string {
    return `/media/dist/badges/${this.badgeName}.svg`;
  }

  get shareText(): string {
    return `I'm excited to share that I've earned the ${this.badgeName} badge on omegaUp!`;
  }

  get shareUrl(): string {
    return `https://omegaup.com/badge/${this.badgeName}`;
  }

  shareOnLinkedIn(): void {
    const text = `${this.shareText}\n\nYou can check it here: ${this.shareUrl}`;
    const linkedinShareUrl = `https://www.linkedin.com/sharing/share-offsite/?text=${encodeURIComponent(
      text,
    )}`;
    window.open(linkedinShareUrl, '_blank', 'noopener,noreferrer');
  }

  shareOnTwitter(): void {
    const text = `${this.shareText}\n\nYou can check it here: ${this.shareUrl}`;
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

  async copyImageToClipboard(): Promise<void> {
    try {
      const canvas = document.createElement('canvas');
      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.src = this.badgeImageUrl;

      await new Promise((resolve, reject) => {
        img.onload = resolve;
        img.onerror = reject;
      });

      canvas.width = img.width;
      canvas.height = img.height;
      const ctx = canvas.getContext('2d');
      if (!ctx) {
        throw new Error('Could not get canvas context');
      }

      ctx.drawImage(img, 0, 0);

      const blob = await new Promise<Blob | null>((resolve) => {
        canvas.toBlob(resolve);
      });

      if (!blob) {
        throw new Error('Could not create blob from canvas');
      }

      const clipboardData: Record<string, Blob> = {
        [blob.type]: blob,
      };

      if ('ClipboardItem' in window) {
        const ClipboardItemConstructor = (window as any).ClipboardItem as {
          new (data: Record<string, Blob>): any;
        };
        await navigator.clipboard.write([
          new ClipboardItemConstructor(clipboardData),
        ]);
        ui.success(T.badgeImageCopiedToClipboard);
      }
    } catch (error) {
      // Show instruction for manual copying on error
      ui.warning(T.badgeImageManualCopyInstructions);
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.share-badges-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background-color: var(--white);
  border-radius: 0.3rem;
  width: 90%;
  max-width: 500px;
}

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

.close {
  font-size: inherit;
  background: none;
  border: none;
  cursor: pointer;
}
</style>
