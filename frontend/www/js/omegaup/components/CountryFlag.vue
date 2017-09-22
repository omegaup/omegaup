<template>
  <img height="11"
        v-bind:src="flagUrl"
        v-bind:title="country"
        width="16">
</template>

<script>

export default {
  props: ['country'],
  computed: {
    flagUrl: function() {
      if (this.country != null) {
        return '/media/flags/' + this.country.toLowerCase() + '.png';
      }
      return '';
    }
  },
  data: function() { return {};},
};
</script>
