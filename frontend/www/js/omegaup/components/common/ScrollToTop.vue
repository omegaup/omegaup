<template>
  <button v-show="isVisible" class="scroll-to-top-button" @click="scrollToTop">
    <font-awesome-icon :icon="['fas', 'chevron-up']" />
  </button>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronUp } from '@fortawesome/free-solid-svg-icons';
library.add(faChevronUp);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class ScrollToTop extends Vue {
  isVisible = false;

  mounted(): void {
    window.addEventListener('scroll', this.toggleVisibility);
  }

  onUnmounted(): void {
    window.removeEventListener('scroll', this.toggleVisibility);
  }

  // Display the button when the page is scrolled more than 400px downward
  toggleVisibility(): void {
    this.isVisible = window.scrollY > 400;
  }

  scrollToTop(): void {
    window.scrollTo({
      top: 0,
      behavior: 'smooth',
    });
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.scroll-to-top-button {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 1000;
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  background-color: $omegaup-blue;
  color: $omegaup-white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;

  &:hover {
    background-color: $omegaup-primary--darker;
    transform: translateY(-2px);
  }

  &:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba($omegaup-blue, 0.4);
  }

  // Moves the button down slightly to give a "pressed" effect on click
  &:active {
    transform: translateY(1px);
  }
}
</style>
