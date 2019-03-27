<template>
  <transition name="modal">
    <div class="modal-mask">
      <div class="modal-container">
        <button class="close" v-on:click="$emit('close')">❌</button>
        <h3 class="panel-title">Problem Finder</h3>
      </div>
    </div>
  </transition>
</template>

<style>
.modal-mask {
  position: fixed;
  z-index: 99999;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, .5);
  transition: opacity .3s ease;
}

.modal-container {
  background: #eee;
  width: 850px;
  height: 38em;
  margin: 2em auto 0 auto;
  border: 2px solid #ccc;
  padding: 1em;
  position: relative;
  overflow: auto;
}
</style>

<script>
import {T} from '../../omegaup.js';

export default {
  data: function() {
    return {
      T,
    }
  },
  methods: {

  },
}
</script>