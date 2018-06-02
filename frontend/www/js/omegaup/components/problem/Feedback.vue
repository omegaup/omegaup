<template>
  <div class="row omegaup-feedback-row">
    <h5 class="omegaup-feedback-title">Opiniones de los usuarios</h5>
    <div class="col-md-6">Acá va el histograma de calidad</div>
    <div class="col-md-6">Acá va el histograma de dificultad</div>
  </div>
</template>

<style>
.omegaup-feedback-row {
  margin:  30px auto 0;
}
.omegaup-feedback-title {
  font-weight: bold;
  color: gray;
}
</style>

<script>
import {T} from '../../omegaup.js';

export default {
  data: function() {
    return { T: T, }
  },
}
</script>