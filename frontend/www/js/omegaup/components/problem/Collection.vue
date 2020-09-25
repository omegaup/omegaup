<template>
  <div>
    <h1 class="card-title">{{ T.collectionTitle }}</h1>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';

export default class problemCollection extends Vue {
  T = T;
  ui = ui;
  omegaup = omegaup;
}
</script>
