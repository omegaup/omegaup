<template>
  <div class="card">
    <div class="card-body">
      <h5 class="card-header">{{ T.collectionTitle }}</h5>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class Collection extends Vue {
  T = T;
}
</script>
