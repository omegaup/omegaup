<template>
  <div class="confirmation">
    <form class="panel panel-default popup">
      <button class="close" type="button" v-on:click="$emit('close')">×</button>
      <div class="form-group">
        <div class="question-text">
          {{ question }}
        </div>
        <div class="button-row">
          <button class="btn btn-success" v-on:click="$emit('yes')">
            {{ answerYes }}
          </button>
          <button class="btn btn-danger" v-on:click="$emit('no')">
            {{ answerNo }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<style>
.confirmation .popup {
  position: fixed;
  top: 25%;
  left: 30%;
  z-index: 9999999 !important;
  width: 350px;
  height: 150px;
  margin: 2em auto 0 auto;
  border: 2px solid #ccc;
  padding: 1em;
  overflow: auto;
  background: white;
}

.confirmation .question-text {
  font-weight: bold;
  padding-bottom: 4px;
  text-align: center;
}

.confirmation .button-row {
  width: 100%;
  text-align: center;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';

@Component
export default class Paginator extends Vue {
  @Prop() question!: string;
  @Prop() answerYes!: string;
  @Prop() answerNo!: string;
}
</script>
