<template>
  <b-card :title="T.problemCreatorAdd">
    <div class="h-100">
      <b-tabs v-model="tabIndex" small pills>
        <b-tab :title="T.problemCreatorCase" name="modal-form"></b-tab>
        <b-tab :title="T.problemCreatorMultipleCases" name="modal-form">
        </b-tab>
        <b-tab :title="T.problemCreatorGroup" name="modal-form"> </b-tab>
      </b-tabs>
    </div>
    <template #footer class="d-flex">
      <b-button
        variant="light"
        size="sm"
        class="mr-2"
        @click="$emit('close-add-window')"
        >{{ T.wordsCancel }}</b-button
      >
      <b-button
        variant="primary"
        size="sm"
        @click="$emit('close-add-window')"
        >{{ T.problemCreatorAdd }}</b-button
      >
    </template>
  </b-card>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import T from '../../../../lang';

@Component
export default class AddWindow extends Vue {
  tabIndex = 0;

  T = T;
}
</script>
