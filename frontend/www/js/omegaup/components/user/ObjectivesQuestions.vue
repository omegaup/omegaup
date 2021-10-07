<template>
  <div class="modal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-body">
          <h5 class="modal-title font-weight-bold mb-3">
            {{ T.userObjectivesModalTitle }}
          </h5>
          <p v-if="isFirstModal" class="text-right text-primary">
            {{
              ui.formatString(T.userObjectivesModalCounter, {
                current: 1,
                last: 2,
              })
            }}
          </p>
          <p v-else class="text-right text-primary">
            {{
              ui.formatString(T.userObjectivesModalCounter, {
                current: 2,
                last: 2,
              })
            }}
          </p>
          <p class="font-weight-bold">{{ description }}</p>
          <div v-if="isFirstModal" class="mb-3">
            <label class="d-block"
              ><input
                class="mr-3"
                v-model="objective"
                type="radio"
                :value="value_learning"
              />{{ T.userObjectivesModalAnswerLearning }}</label
            >
            <label class="d-block"
              ><input
                class="mr-3"
                v-model="objective"
                type="radio"
                :value="value_teaching"
              />{{ T.userObjectivesModalAnswerTeaching }}</label
            >
            <label class="d-block"
              ><input
                class="mr-3"
                v-model="objective"
                type="radio"
                :value="value_learning_teaching"
              />{{ T.userObjectivesModalAnswerLearningAndTeaching }}</label
            >
            <label class="d-block"
              ><input
                class="mr-3"
                v-model="objective"
                type="radio"
                :value="value_none"
              />{{ T.userObjectivesModalAnswerNone }}</label
            >
          </div>
          <div v-else class="mb-3">
            <label class="d-block"
              ><input
                class="mr-3"
                v-model="objective"
                type="radio"
                :value="value_scholar"
              />{{ T.userObjectivesModalAnswerScholar }}</label
            >
            <label class="d-block"
              ><input
                class="mr-3"
                v-model="objective"
                type="radio"
                :value="value_competitive"
              />{{ T.userObjectivesModalAnswerCompetitive }}</label
            >
            <label class="d-block"
              ><input
                class="mr-3"
                v-model="objective"
                type="radio"
                :value="value_scholar_competitive"
              />{{ T.userObjectivesModalAnswerScholarAndCompetitive }}</label
            >
            <label class="d-block"
              ><input
                class="mr-3"
                v-model="objective"
                type="radio"
                :value="value_other"
              />{{ T.userObjectivesModalAnswerOther }}</label
            >
          </div>
          <button
            v-if="isFirstModal"
            type="button"
            class="btn btn-next-previous float-right pr-0"
            @click="onChangeModal"
          >
            {{ T.userObjectivesModalButtonNext }}
            <font-awesome-icon class="ml-1" icon="greater-than" />
          </button>
          <div v-else>
            <button
              type="button"
              class="btn btn-next-previous float-left pl-0"
              @click="onPrevious"
            >
              <font-awesome-icon class="mr-1" icon="less-than" />
              {{ T.userObjectivesModalButtonPrevious }}
            </button>
            <button
              type="button"
              class="btn btn-primary float-right w-25"
              @click="onSubmit"
              data-dismiss="modal"
            >
              {{ T.userObjectivesModalButtonSend }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class UserObjectivesQuestions extends Vue {
  T = T;
  ui = ui;
  value_learning = 0;
  value_teaching = 1;
  value_learning_teaching = 2;
  value_none = 3;
  value_scholar = 4;
  value_competitive = 5;
  value_scholar_competitive = 6;
  value_other = 7;
  objective = this.value_learning;
  previousObjective = this.value_learning;
  has_competitive_objective = false;
  has_learning_objective = false;
  has_scholar_objective = false;
  has_teaching_objective = false;
  isFirstModal = true;

  get description(): string {
    if (this.isFirstModal) {
      return this.T.userObjectivesModalDescUsage;
    }
    if (this.has_learning_objective && this.has_teaching_objective) {
      return this.T.userObjectivesModalDescLearningAndTeaching;
    }
    if (this.has_learning_objective) {
      return this.T.userObjectivesModalDescLearning;
    }
    if (this.has_teaching_objective) {
      return this.T.userObjectivesModalDescTeaching;
    }
    return this.T.userObjectivesModalDescUsageWhenNone;
  }

  onChangeModal(): void {
    switch (this.objective) {
      case this.value_learning:
        this.has_learning_objective = true;
        this.has_teaching_objective = false;
        break;
      case this.value_teaching:
        this.has_learning_objective = false;
        this.has_teaching_objective = true;
        break;
      case this.value_learning_teaching:
        this.has_learning_objective = true;
        this.has_teaching_objective = true;
        break;
      case this.value_none:
        this.has_learning_objective = false;
        this.has_teaching_objective = false;
        break;
    }
    this.previousObjective = this.objective;
    this.objective = this.value_scholar;
    this.isFirstModal = false;
  }

  onPrevious(): void {
    this.objective = this.previousObjective;
    this.isFirstModal = true;
  }

  onSubmit(): void {
    switch (this.objective) {
      case this.value_scholar:
        this.has_scholar_objective = true;
        this.has_competitive_objective = false;
        break;
      case this.value_competitive:
        this.has_scholar_objective = false;
        this.has_competitive_objective = true;
        break;
      case this.value_scholar_competitive:
        this.has_scholar_objective = true;
        this.has_competitive_objective = true;
        break;
      case this.value_other:
        this.has_scholar_objective = false;
        this.has_competitive_objective = false;
        break;
    }
    this.$emit('submit', {
      has_competitive_objective: this.has_competitive_objective,
      has_learning_objective: this.has_learning_objective,
      has_scholar_objective: this.has_scholar_objective,
      has_teaching_objective: this.has_teaching_objective,
    });
  }
}
</script>

<style lang="scss" scoped>
.modal-dialog {
  max-width: 330px;
}

.btn-next-previous {
  color: #6c757d;
  background-color: white;
  border-color: white;
}
</style>
