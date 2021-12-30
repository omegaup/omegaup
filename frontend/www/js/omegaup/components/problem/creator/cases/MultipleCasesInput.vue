<template>
  <div class="mt-3">
    <b-row>
      <b-col>
        <b-form-group
          data-prefix
          :label="T.problemCreatorPrefix"
          label-for="prefix"
          class="mb-4"
        >
          <b-form-input v-model="multipleCasesPrefix" autocomplete="off" />
        </b-form-group>
      </b-col>
      <b-col>
        <b-form-group
          :label="T.problemCreatorSuffix"
          label-for="suffix"
          class="mb-4"
        >
          <b-form-input
            id="suffix"
            v-model="multipleCasesSuffix"
            autocomplete="off"
          />
        </b-form-group>
      </b-col>
    </b-row>
    <b-form-group
      :label="T.problemCreatorNumberOfCases"
      :description="`${T.problemCreatorNumberOfCasesHelper} ${caseNamePreview}`"
      label-for="case-points"
    >
      <b-form-input
        type="number"
        number
        :min="0"
        id="case-num"
        v-model="multipleCasesCount"
      />
    </b-form-group>
    <b-form-group :label="T.problemCreatorGroupName" label-for="case-group">
      <b-form-select v-model="multipleCasesGroup" :options="options" />
    </b-form-group>
  </div>
</template>

<script lang="ts">
import { GroupID } from '@/js/omegaup/problem/creator/types';
import { NIL } from 'uuid';
import { Component, Vue } from 'vue-property-decorator';
import T from '../../../../lang';

@Component
export default class MultipleCasesInput extends Vue {
  multipleCasesPrefix = '';
  multipleCasesSuffix = '';
  multipleCasesCount = 0;
  multipleCasesGroup: GroupID = NIL;

  T = T;

  get caseNamePreview() {
    return `${this.multipleCasesPrefix + '1' + this.multipleCasesSuffix}, ${
      this.multipleCasesPrefix + '2' + this.multipleCasesSuffix
    }, ...`;
  }
}
</script>
