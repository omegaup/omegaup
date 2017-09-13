<template>
  <img 
    v-bind:src="'/media/flags/'+country.toLowerCase()+'.png'" 
    v-bind:width="width" 
    v-bind:height="height" 
    v-bind:title="country" />
</template>
<script>

export default {
  props:['country'],
  data: function() { return {
  	width: 16,
  	height: 11,
  };},
};
</script>