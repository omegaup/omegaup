<template>
  <div>
    <h1 class="card-title">{{ T.collectionTitle }}</h1>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';

@Component
export default class CollectionDetails extends Vue {
  @Prop() data!: types.CollectionDetailsPayload;

  T = T;
}
</script>
