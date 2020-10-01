<template>
  <div class="card">
    <div class="card-body">
      <h5 class="card-header">{{ data.title }}</h5>
    </div>
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
