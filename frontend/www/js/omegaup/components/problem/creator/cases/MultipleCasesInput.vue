<template>
  <div class="mt-3">
    <div class="row">
      <div class="col">
        <div data-prefix class="form-group mb-4">
          <label>{{ T.problemCreatorPrefix }}</label>
          <input
            v-model="multipleCasesPrefix"
            data-problem-creator-multiple-cases-input="prefix"
            class="form-control"
            name="multiple-cases-prefix"
            autocomplete="off"
            @change="multipleCasesPrefix = formatter(multipleCasesPrefix)"
          />
          <div class="invalid-feedback" role="alert" aria-live="assertive">
            {{ T.problemCreatorOnlyNumbers }}
          </div>
        </div>
      </div>
      <div class="col">
        <div class="form-group mb-4">
          <label>{{ T.problemCreatorSuffix }}</label>
          <input
            v-model="multipleCasesSuffix"
            data-problem-creator-multiple-cases-input="suffix"
            class="form-control"
            name="multiple-cases-suffix"
            autocomplete="off"
            @change="multipleCasesSuffix = formatter(multipleCasesSuffix)"
          />
        </div>
      </div>
    </div>
    <div class="form-group">
      <label>{{ T.problemCreatorNumberOfCases }}</label>
      <input
        v-model.number="multipleCasesCount"
        data-problem-creator-multiple-cases-input="count"
        class="form-control"
        name="multiple-cases-count"
        type="number"
        @change="multipleCasesCount = numberFormatter(multipleCasesCount)"
      />
      <small class="form-text text-muted">
        {{ T.problemCreatorNumberOfCasesHelper }} {{ caseNamePreview }}
      </small>
    </div>
    <div class="form-group">
      <label>{{ T.problemCreatorGroupName }}</label>
      <select
        v-model="multipleCasesGroup"
        name="multiple-cases-group"
        class="custom-select"
      >
        <option
          v-for="option in options"
          :key="option.value"
          :value="option.value"
        >
          {{ option.text }}
        </option>
      </select>
    </div>
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
