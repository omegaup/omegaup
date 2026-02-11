<template>
  <!-- id-lint off -->
  <div id="carousel-display" class="carousel slide" data-ride="carousel">
    <!-- id-lint on -->
    <ol class="carousel-indicators">
      <li
        v-for="(_, index) in slides"
        :key="index"
        data-target="#carousel-display"
        :data-slide-to="index"
        :class="{ active: !index }"
      ></li>
    </ol>
    <div class="carousel-inner text-center py-5 py-md-0">
      <div
        v-for="(slide, index) in slides"
        :key="index"
        class="carousel-item"
        :class="{ active: !index }"
      >
        <omegaup-homepage-slide
          :title="slide.title[T.locale]"
          :description="slide.description[T.locale]"
          :image-src="slide.image"
          :button="slide.button"
        ></omegaup-homepage-slide>
      </div>
    </div>
    <a
      class="carousel-control-prev"
      href="#carousel-display"
      role="button"
      data-slide="prev"
    >
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">{{ T.wordsPrevious }}</span>
    </a>
    <a
      class="carousel-control-next"
      href="#carousel-display"
      role="button"
      data-slide="next"
    >
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">{{ T.wordsNext }}</span>
    </a>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import homepageSlide from './Slide.vue';
import * as api from '../../api';
import { types } from '../../api_types';

interface SlideData {
  image: string;
  title: {
    en: string;
    es: string;
    pt: string;
  };
  description: {
    en: string;
    es: string;
    pt: string;
  };
  button?: {
    text: {
      en: string;
      es: string;
      pt: string;
    };
    href: string;
    target: string;
  };
}

@Component({
  components: {
    'omegaup-homepage-slide': homepageSlide,
  },
})
export default class Carousel extends Vue {
  T = T;
  slides: SlideData[] = [];

  mounted(): void {
    this.loadCarouselItems();
  }

  parseJsonField(field: string | undefined): { [key: string]: string } {
    if (!field) {
      return { en: '', es: '', pt: '' };
    }
    try {
      const parsed = typeof field === 'string' ? JSON.parse(field) : field;
      return {
        en: parsed.en || '',
        es: parsed.es || '',
        pt: parsed.pt || '',
      };
    } catch (e) {
      // If it's not valid JSON, treat it as a plain string for English
      return {
        en: typeof field === 'string' ? field : '',
        es: '',
        pt: '',
      };
    }
  }

  isExternalUrl(url: string): boolean {
    try {
      const urlObj = new URL(url, window.location.origin);
      return urlObj.origin !== window.location.origin;
    } catch (e) {
      // If URL parsing fails, assume it's relative (internal)
      return false;
    }
  }

  transformCarouselItem(item: types.CarouselItem): SlideData | null {
    const title = this.parseJsonField(item.title);
    const description = this.parseJsonField(item.excerpt);
    const buttonTitle = this.parseJsonField(item.button_title);

    // Check if we have at least one language with content
    const hasContent =
      (title.en || title.es || title.pt) &&
      (description.en || description.es || description.pt);

    if (!hasContent || !item.image_url) {
      return null;
    }

    const slide: SlideData = {
      image: item.image_url,
      title: {
        en: title.en || title.es || title.pt || '',
        es: title.es || title.en || title.pt || '',
        pt: title.pt || title.en || title.es || '',
      },
      description: {
        en: description.en || description.es || description.pt || '',
        es: description.es || description.en || description.pt || '',
        pt: description.pt || description.en || description.es || '',
      },
    };

    // Add button if we have a link and button title
    if (item.link && (buttonTitle.en || buttonTitle.es || buttonTitle.pt)) {
      const isExternal = this.isExternalUrl(item.link);
      slide.button = {
        text: {
          en: buttonTitle.en || buttonTitle.es || buttonTitle.pt || '',
          es: buttonTitle.es || buttonTitle.en || buttonTitle.pt || '',
          pt: buttonTitle.pt || buttonTitle.en || buttonTitle.es || '',
        },
        href: item.link,
        target: isExternal ? '_blank' : '_self',
      };
    }

    return slide;
  }

  loadCarouselItems(): void {
    api.CarouselItems.listActive({})
      .then((response) => {
        const transformedSlides = response.carouselItems
          .map((item) => this.transformCarouselItem(item))
          .filter((slide): slide is SlideData => slide !== null);

        // Reverse so newer items appear first (matching old behavior)
        this.slides = transformedSlides.reverse();

        // Fallback to empty array if no slides
        if (this.slides.length === 0) {
          this.slides = [];
        }
      })
      .catch((error) => {
        console.error('Error loading carousel items:', error);
        // On error, set empty array (carousel won't display)
        this.slides = [];
      });
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.carousel {
  background: var(--homepage-carousel-background-color) !important;
  color: var(--homepage-carousel-font-color);
}
</style>
