<template>
  <img height="11"
        v-bind:src="normalizeCountry"
        v-bind:title="country"
        width="16">
</template>

<script>

export default {
  props: ['country'],
  computed: {
    normalizeCountry: function() {
      return '/media/flags/' + this.country.toLowerCase() + '.png';
    }
  },
  data: function() { return {};},
};
</script>
