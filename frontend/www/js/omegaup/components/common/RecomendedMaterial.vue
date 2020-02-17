<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{{ T.frontPageMaterial }}</h3>
    </div>
    <div class="panel-body">
      <a class="text-center center" href="https://omegaup.com/img/libropre3.pdf"
        >{{ T.downloadPDF }}
        <img
          class="center top-margin"
          src="https://omegaup.com/img/libroluis.gif"
          width="75%"
        />
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';

@Component
export default class RecomendedMaterial extends Vue {
  T = T;
}
</script>
