<template>
  <div class="mt-3">
    <b-row>
      <b-col>
        <b-form-group
          :label="T.problemCreatorPrefix"
          label-for="prefix"
          class="mb-4"
        >
          <b-form-input v-model="prefix" autocomplete="off" />
        </b-form-group>
      </b-col>
      <b-col>
        <b-form-group
          :label="T.problemCreatorSuffix"
          label-for="suffix"
          class="mb-4"
        >
          <b-form-input v-model="suffix" autocomplete="off" />
        </b-form-group>
      </b-col>
    </b-row>
    <b-form-group
      :label="T.problemCreatorCaseNumber"
      :description="T.problemCreatorCaseNumberHint + ' ' + caseNamePreview"
      label-for="case-points"
    >
      <b-form-input v-model="caseNum" type="number" number min="0" />
    </b-form-group>
    <b-form-group :label="T.problemCreatorGroupName" label-for="case-group">
      <b-form-select v-model="casesGroup" :options="options" />
    </b-form-group>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import { types } from '../../../../problem/creator/types';
import { NIL } from 'uuid';
import { namespace } from 'vuex-class';
import T from '../../../../lang';

const caseStore = namespace('casesStore');

@Component({})
export default class MulticaseInput extends Vue {
  @caseStore.Getter('getGroupIdsAndNames') options!: types.Option[];

  prefix = '';
  suffix = '';
  caseNum = 1;
  casesGroup = NIL;

  T = T;

  get caseNamePreview() {
    return `${this.prefix + '1' + this.suffix}, ${
      this.prefix + '2' + this.suffix
    }, ...`;
  }
}
</script>
