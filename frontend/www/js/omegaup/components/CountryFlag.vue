<template>
  <img height="11"
        v-bind:src="flagUrl"
        v-bind:title="country"
        width="16">
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class CountryFlag extends Vue {
  @Prop() country!: string;

  get flagUrl(): string {
    if (this.country == null) return '';
    return '/media/flags/' + this.country.toLowerCase() + '.png';
  }
}

</script>
