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
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../lang';

@Component
export default class CountryFlag extends Vue {
  @Prop() country!: string;

  get flagUrl(): string {
    if (!this.country || this.country === '') return '';
    return `/media/flags/${this.country.toLowerCase()}.png`;
  }

  get displayTitle(): string {
    if (this.country === 'xx') {
      return T.countryNotSet;
    }
    return this.country;
  }
}
</script>
