<template>
  <b-row class="mb-3">
    <b-col class="d-flex align-items-center">
      <span class="mr-2">{{ T.problemCreatorName }}</span>
      <b-form-input
        v-model="name"
        size="sm"
        :placeholder="T.problemCreatorNewProblem"
      />
    </b-col>
    <b-col class="d-flex justify-content-end">
      <b-button class="mr-2" variant="light" size="sm">
        <BIconUpload class="mr-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorLoadProblem }}</span
        >
      </b-button>
      <b-button class="mr-2" variant="primary" size="sm">
        <BIconDownload class="mr-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorGenerateProblem }}</span
        >
      </b-button>
      <b-button variant="warning" size="sm" @click="createNewProblem()">
        <BIconPlus class="mr-1" />
        <span class="d-none d-md-inline">
          {{ T.problemCreatorNewProblem }}</span
        >
      </b-button>
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Vue, Watch } from 'vue-property-decorator';
import T from '../../../lang';

@Component
export default class Header extends Vue {
  T = T;
  name: string = T.problemCreatorEmpty;

  @Watch('name')
  onNameChanged(newProblemName: string) {
    this.$store.commit('updateName', newProblemName);
  }

  createNewProblem() {
    this.$store.commit('resetStore');
    this.$store.commit('casesStore/resetStore');
    window.location.reload();
  }
}
</script>
