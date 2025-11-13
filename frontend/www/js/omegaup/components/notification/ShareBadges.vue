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
    <div class="text-center mb-4">
      <div class="position-relative d-inline-block">
        <img
          class="img-fluid mx-auto"
          style="max-width: 150px; max-height: 150px"
          :src="badgeImageUrl"
          :alt="badgeName"
        />
        <button
          class="btn btn-sm btn-outline-secondary position-absolute"
          style="top: 5px; right: -35px"
          :title="T.copyBadgeImage"
          @click.stop="copyImageToClipboard"
        >
          <font-awesome-icon :icon="['fas', 'copy']" />
        </button>
        <div
          v-if="tooltipVisible"
          class="position-absolute bg-dark text-white px-3 py-2 rounded small"
          style="
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
          "
        >
          {{ tooltipMessage }}
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-center mb-3">
      <button class="btn btn-outline-primary mx-2" @click="shareOnLinkedIn">
        <font-awesome-icon :icon="['fab', 'linkedin']" class="mr-1" />
        {{ T.socialMediaLinkedIn }}
      </button>
      <button class="btn btn-outline-info mx-2" @click="shareOnTwitter">
        <font-awesome-icon :icon="['fab', 'twitter']" class="mr-1" />
        {{ T.socialMediaTwitter }}
      </button>
      <button class="btn btn-outline-primary mx-2" @click="shareOnFacebook">
        <font-awesome-icon :icon="['fab', 'facebook-f']" class="mr-1" />
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
    const origin =
      typeof window !== 'undefined' && window.location?.origin
        ? window.location.origin
        : 'https://omegaup.com';
    return `${origin}/badge/${this.badgeName}`;
  }

  shareOnLinkedIn(): void {
    const text = ui.formatString(T.shareBadgeLink, {
      text: this.shareText,
      url: this.shareUrl,
    });
    const linkedinShareUrl = `https://www.linkedin.com/sharing/share-offsite/?text=${encodeURIComponent(
      text,
    )}`;
    window.open(linkedinShareUrl, '_blank', 'noopener,noreferrer');
  }

  shareOnTwitter(): void {
    const text = ui.formatString(T.shareBadgeLink, {
      text: this.shareText,
      url: this.shareUrl,
    });
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

  async copyImageToClipboard(): Promise<void> {
    try {
      const badgeImageUrl = this.badgeImageUrl;
      const canvas = document.createElement('canvas');
      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.src = badgeImageUrl;

      await new Promise((resolve, reject) => {
        img.onload = resolve;
        img.onerror = reject;
      });

      canvas.width = img.width;
      canvas.height = img.height;
      const context = canvas.getContext('2d');
      if (!context) {
        throw new Error('Could not get canvas context');
      }

      context.drawImage(img, 0, 0);

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
        this.showTooltip(T.badgeImageCopiedToClipboard);
      }
    } catch (error) {
      this.showTooltip(T.badgeImageManualCopyInstructions);
    }
  }
}
</script>
