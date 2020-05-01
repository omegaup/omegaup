<template>
  <div class="mask">
    <div class="confirmation">
      <form class="panel panel-default popup">
        <button class="close" type="button" v-on:click.prevent="$emit('close')">
          ×
        </button>
        <div class="form-group">
          <div class="question-text">
            {{ question }}
          </div>
          <div class="button-row">
            <button class="btn btn-success" v-on:click.prevent="$emit('yes')">
              {{ answerYes }}
            </button>
            <button class="btn btn-danger" v-on:click.prevent="$emit('no')">
              {{ answerNo }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<style>
.mask {
  position: fixed;
  z-index: 99999;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  transition: opacity 0.3s ease;
}

.confirmation .popup {
  position: absolute;
  width: 350px;
  top: 50%;
  left: 50%;
  margin: 2em auto 0 auto;
  border: 2px solid #ccc;
  padding: 1em;
  overflow: auto;
  background: white;
  margin-top: -75px; /* height/2 = 500px / 2 */
  margin-left: -175px;
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
