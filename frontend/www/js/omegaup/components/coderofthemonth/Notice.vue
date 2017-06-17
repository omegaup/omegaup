<template>
  <div class="alert alert-warning"
       id="notice"
       v-if="show">
    {{ UI.formatString(T.coderOfTheMonthNotice, {username: coderUsername}) }}
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {coderUsername: String, currentUsername: String},
  computed: {
    show() {
      return this.currentUsername && this.coderUsername &&
             this.coderUsername == this.currentUsername;
    }
  },
  data: function() { return {T: T, UI: UI};},
};
</script>
