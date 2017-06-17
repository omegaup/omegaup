<template>
  <div class="alert alert-warning"
       id="notice"
       v-if="currentUsername &amp;&amp; coderUsername == currentUsername">
    {{ UI.formatString(T.coderOfTheMonthNotice, {username: coderUsername}) }}
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {coderUsername: String, currentUsername: String},
  data: function() { return {T: T, UI: UI};},
};
</script>
