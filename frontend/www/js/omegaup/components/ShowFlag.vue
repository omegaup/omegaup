<template>
  <img v-bind:height="height"
        v-bind:src="'/media/flags/'+country.toLowerCase()+'.png'"
        v-bind:title="country"
        v-bind:width="width">
</template>

<script>

export default {
  props: ['country'],
  data: function() {
    return {
      width: 16,
      height: 11,
    };
  },
};
</script>
