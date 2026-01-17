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
            data-problem-creator-multiple-cases-input="prefix"
            lazy-formatter
            :formatter="formatter"
            name="multiple-cases-prefix"
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
            data-problem-creator-multiple-cases-input="suffix"
            lazy-formatter
            :formatter="formatter"
            name="multiple-cases-suffix"
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
        data-problem-creator-multiple-cases-input="count"
        lazy-formatter
        :formatter="numberFormatter"
        name="multiple-cases-count"
        type="number"
        number
      />
    </b-form-group>
    <b-form-group :label="T.problemCreatorGroupName" label-for="case-group">
      <b-form-select
        v-model="multipleCasesGroup"
        :options="options"
        name="multiple-cases-group"
      />
    </b-form-group>
  </div>
</template>

<script lang="ts">
import { GroupID } from '../../../../problem/creator/types';
import { NIL } from 'uuid';
import { Component, Vue } from 'vue-property-decorator';
import { namespace } from 'vuex-class';
import T from '../../../../lang';

const casesStore = namespace('casesStore');

@Component
export default class MultipleCasesInput extends Vue {
  multipleCasesPrefix = '';
  multipleCasesSuffix = '';
  multipleCasesCount = 1;
  multipleCasesGroup: GroupID = NIL;

  T = T;

  @casesStore.Getter('getGroupIdsAndNames') storedGroups!: {
    value: string;
    text: string;
  }[];

  get options() {
    const noGroup = { value: NIL, text: T.problemCreatorNoGroup };
    if (!this.storedGroups) {
      return [noGroup];
    }
    return [noGroup, ...this.storedGroups];
  }

  get caseNamePreview() {
    return `${this.formatter(this.multipleCasesPrefix)}1${this.formatter(
      this.multipleCasesSuffix,
    )}, ${this.formatter(this.multipleCasesPrefix)}2${this.formatter(
      this.multipleCasesSuffix,
    )}...`;
  }

  // Ensure that the prefix and suffix always contain alphanumeric characters in addition to _ and -
  formatter(text: string) {
    return text.toLowerCase().replace(/[^a-zA-Z0-9_-]/g, '');
  }

  // Ensures the number is always above 1
  numberFormatter(number: number) {
    return Math.max(number, 1);
  }
}
</script>
