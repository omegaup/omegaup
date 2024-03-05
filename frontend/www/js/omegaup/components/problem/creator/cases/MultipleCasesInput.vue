<template>
  <div class="mt-3">
    <b-row>
      <b-col>
        <b-form-group
          data-prefix
          :label="T.problemCreatorPrefix"
          label-for="prefix"
          invalid-feedback="Solo numeros"
          class="mb-4"
        >
          <b-form-input
            v-model="multipleCasesPrefix"
            name="multiple-cases-prefix"
            :formatter="formatter"
            autocomplete="off"
          />
        </b-form-group>
      </b-col>
      <b-col>
        <b-form-group
          :label="T.problemCreatorSuffix"
          label-for="suffix"
          class="mb-4"
        >
          <b-form-input
            v-model="multipleCasesSuffix"
            name="multiple-cases-suffix"
            :formatter="formatter"
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
        v-model="multipleCasesCount"
        name="multiple-cases-count"
        :formatter="numberFormatter"
        type="number"
        number
      />
    </b-form-group>
    <b-form-group :label="T.problemCreatorGroupName" label-for="case-group">
      <b-form-select
        v-model="multipleCasesGroup"
        name="multiple-cases-group"
        :options="options"
      />
    </b-form-group>
  </div>
</template>

<script lang="ts">
import { GroupID } from '../../../../problem/creator/types';
import { NIL } from 'uuid';
import { Component, Vue } from 'vue-property-decorator';
import T from '../../../../lang';
import { namespace } from 'vuex-class';

const casesStore = namespace('casesStore');

@Component
export default class MultipleCasesInput extends Vue {
  @casesStore.Getter('getGroupIdsAndNames') storedGroups!: {
    value: string;
    text: string;
  }[];

  multipleCasesPrefix = '';
  multipleCasesSuffix = '';
  multipleCasesCount = 1;
  multipleCasesGroup: GroupID = NIL;

  T = T;

  get options() {
    return [
      { value: NIL, text: T.problemCreatorNoGroup },
      ...this.storedGroups,
    ];
  }

  get caseNamePreview() {
    return `${this.multipleCasesPrefix}1${this.multipleCasesSuffix}, ${this.multipleCasesPrefix}2${this.multipleCasesSuffix}...`;
  }

  // Ensure that the prefix and suffix always contain alpha-numeric characters in addition to _ and -
  formatter(text: string) {
    return text.toLowerCase().replace(/[^a-zA-Z0-9_-]/g, '');
  }

  // Ensures the numebr is always above 1
  numberFormatter(number: number) {
    return Math.max(number, 0);
  }
}
</script>
