<template>
<div class="omegaup-histogram-container">
  <p class="omegaup-histogram-title">Calidad</p>
  <div class="omegaup-histogram">
    <div class="omegaup-histogram-1">
      <p class="omegaup-histogram-score">4.2</p>
      <p class="omegaup-histogram-votes">👥 29 en total</p>
    </div>
    <div class="omegaup-histogram-2">
      <div class="omegaup-histogram-item">
        <div class="omegaup-bar-name">Very Good</div>
        <div class="omegaup-bar omegaup-bar-1">2</div>
      </div>
      <div class="omegaup-histogram-item">
        <div class="omegaup-bar-name">Good</div>
        <div class="omegaup-bar omegaup-bar-2">7</div>
      </div>
      <div class="omegaup-histogram-item">
        <div class="omegaup-bar-name">Regular</div>
        <div class="omegaup-bar omegaup-bar-3">10</div>
      </div>
      <div class="omegaup-histogram-item">
        <div class="omegaup-bar-name">Bad</div>
        <div class="omegaup-bar omegaup-bar-4">4</div>
      </div>
      <div class="omegaup-histogram-item">
        <div class="omegaup-bar-name">Very Bad</div>
        <div class="omegaup-bar omegaup-bar-5">6</div>
      </div>
    </div>
  </div>
</div>
</template>

<style>
.omegaup-histogram-title {
  margin: 30px 0 10px;
  font-size: 1.25em;
  font-weight: bold;
  text-align: center;
}

.omegaup-histogram {
  display: grid;
  grid-template-columns: 1fr 2fr;
  margin: 0 auto;
  width: 320px;
  height: 150px;
}

.omegaup-histogram-1 {
  display: grid;
  padding: 20px;
}

.omegaup-histogram-1 p {
  margin: 0;
}

.omegaup-histogram-score,
.omegaup-histogram-votes {
  display: flex;
  justify-content: center;
  align-items: center;
}

.omegaup-histogram-score {
  font-size: 70px;
}

.omegaup-histogram-2 {
  display: grid;
  grid-template-rows: repeat(5, 1fr);
}

.omegaup-histogram-item {
  display: grid;
  align-items: center;
  grid-auto-flow: column;
  grid-template-columns: 60px 1fr;
  grid-gap: 10px;
}

.omegaup-bar-name {
  font-size: 12px;
}

@keyframes leftToRight {
  to {
    background-position: left bottom;
  }
}

.omegaup-bar {
  display: flex;
  align-items: center;
  padding: 0 0 0 5px;
  font-size: 15px;
  font-weight: bold;
  background-size: 200% 100%;
  background-position: right bottom;
  animation: leftToRight .5s ease .65s forwards;
}

.omegaup-bar-1 {
  background-image: linear-gradient(to right, #4FA2EB 50%, transparent 50%);
  width: 10%;
}

.omegaup-bar-2 {
  background-image: linear-gradient(to right, #C2DDEB 50%, transparent 50%);
  width: 80%;
}

.omegaup-bar-3 {
  background-image: linear-gradient(to right, #DDDCDB 50%, transparent 50%);
  width: 100%;
}

.omegaup-bar-4 {
  background-image: linear-gradient(to right, #FACCB4 50%, transparent 50%);
  width: 50%;
}

.omegaup-bar-5 {
  background-image: linear-gradient(to right, #DF3E4B 50%, transparent 50%);
  width: 75%;
}
</style>

<script>
import {T} from '../../omegaup.js';
export default {
  props: {
    size: String,
    type: String,

  },
  data: function() {
    return {
      T,
    }
  },
}
</script>

