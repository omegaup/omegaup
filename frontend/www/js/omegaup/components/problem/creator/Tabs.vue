<template>
  <b-tabs small>
    <b-tab>
      <template #title>
        <BIconPencil class="mr-1" />
        <span> {{ T.problemCreatorWriting }}</span>
      </template>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconFileCode class="mr-1" />
        <span> {{ T.problemCreatorCode }}</span>
      </template>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconCheckCircle class="mr-1" />
        <span> {{ T.problemCreatorTestCases }}</span>
      </template>
    </b-tab>
    <b-tab>
      <template #title>
        <BIconArrowUpRight class="mr-1" />
        <span> {{ T.problemCreatorSolution }}</span>
      </template>
    </b-tab>
  </b-tabs>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import T from '../../../lang';
@Component({})
export default class Tabs extends Vue {
  T = T;
}
</script>
