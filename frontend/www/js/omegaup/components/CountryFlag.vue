<template>
  <span v-if="country && country !== ''" class="pr-1">
    <img
      height="11"
      :src="flagUrl"
      :title="displayTitle"
      alt=""
      aria-hidden="true"
      width="16"
    />
  </span>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import T from '../lang';

export default defineComponent({
  name: 'CountryFlag',
  props: {
    country: {
      type: String,
      default: '',
    },
  },
  computed: {
    flagUrl(): string {
      if (!this.country || this.country === '') return '';
      return `/media/flags/${this.country.toLowerCase()}.png`;
    },
    displayTitle(): string {
      if (this.country === 'xx') {
        return T.countryNotSet;
      }
      return this.country;
    },
  },
});
</script>
