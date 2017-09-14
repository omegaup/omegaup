<template>
  <ul  class="list-group" id="my-next-contests-list">
    <a v-for="item in list"
      v-if="item.public == 0"
      v-bind:href="contestUrl + item.alias" 
      class="list-group-item">
        {{ item.title }}
    </a>
  </ul>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {
    list: Array,
  },
  computed: {
    contestUrl: function() {
      return '/arena/';
    }
  },
  data: function() { return {T: T, UI: UI};},
};
</script>