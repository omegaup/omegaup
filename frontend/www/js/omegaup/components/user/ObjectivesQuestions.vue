<template>
  <b-modal v-model="showModal" hide-footer>
    <template #modal-title>
      <h5 class="modal-title font-weight-bold">
        {{ T.userObjectivesModalTitle }}
      </h5>
    </template>
    <p class="text-right text-primary">
      {{
        ui.formatString(T.userObjectivesModalPageCounter, {
          current: currentModalPage,
          last: lastModalPage,
        })
      }}
    </p>
    <p class="font-weight-bold">{{ description }}</p>
    <div v-if="currentModalPage === 1" class="mb-3">
      <label class="d-block"
        ><input
          v-model="objective"
          class="mr-3"
          type="radio"
          :value="ObjectivesAnswers.Learning"
        />{{ T.userObjectivesModalAnswerLearning }}</label
      >
      <label class="d-block"
        ><input
          v-model="objective"
          class="mr-3"
          type="radio"
          :value="ObjectivesAnswers.Teaching"
        />{{ T.userObjectivesModalAnswerTeaching }}</label
      >
      <label class="d-block"
        ><input
          v-model="objective"
          class="mr-3"
          type="radio"
          :value="ObjectivesAnswers.LearningAndTeaching"
        />{{ T.userObjectivesModalAnswerLearningAndTeaching }}</label
      >
      <label class="d-block"
        ><input
          v-model="objective"
          class="mr-3"
          type="radio"
          :value="ObjectivesAnswers.None"
        />{{ T.userObjectivesModalAnswerNone }}</label
      >
    </div>
    <div v-else class="mb-3">
      <label class="d-block"
        ><input
          v-model="objective"
          class="mr-3"
          type="radio"
          :value="ObjectivesAnswers.Scholar"
        />{{ T.userObjectivesModalAnswerScholar }}</label
      >
      <label class="d-block"
        ><input
          v-model="objective"
          class="mr-3"
          type="radio"
          :value="ObjectivesAnswers.Competitive"
        />{{ T.userObjectivesModalAnswerCompetitive }}</label
      >
      <label class="d-block"
        ><input
          v-model="objective"
          class="mr-3"
          type="radio"
          :value="ObjectivesAnswers.ScholarAndCompetitive"
        />{{ T.userObjectivesModalAnswerScholarAndCompetitive }}</label
      >
      <label class="d-block"
        ><input
          v-model="objective"
          class="mr-3"
          type="radio"
          :value="ObjectivesAnswers.Other"
        />{{ T.userObjectivesModalAnswerOther }}</label
      >
    </div>
    <button
      v-if="currentModalPage === 1 && objective !== ObjectivesAnswers.None"
      type="button"
      class="btn btn-next-previous float-right pr-0"
      @click="onNextModalPage"
    >
      {{ T.userObjectivesModalButtonNext }}
      <font-awesome-icon class="ml-1" icon="greater-than" />
    </button>
    <div v-else>
      <button
        v-if="objective !== ObjectivesAnswers.None"
        type="button"
        class="btn btn-next-previous float-left pl-0"
        @click="onPreviousModalPage"
      >
        <font-awesome-icon class="mr-1" icon="less-than" />
        {{ T.userObjectivesModalButtonPrevious }}
      </button>
      <button
        type="button"
        class="btn btn-primary float-right w-25"
        data-dismiss="modal"
        @click="onSubmit"
      >
        {{ T.userObjectivesModalButtonSend }}
      </button>
    </div>
  </b-modal>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';

import 'bootstrap-vue/dist/bootstrap-vue.css';
import { ModalPlugin } from 'bootstrap-vue';
Vue.use(ModalPlugin);

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

export enum ObjectivesAnswers {
  Learning = 'learning',
  Teaching = 'teaching',
  LearningAndTeaching = 'learningAndTeaching',
  None = 'none',
  Scholar = 'scholar',
  Competitive = 'competitive',
  ScholarAndCompetitive = 'scholarAndcompetitive',
  Other = 'other',
}

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
  objective = ObjectivesAnswers.Learning;
  previousObjective = ObjectivesAnswers.Learning;
  hasCompetitiveObjective = false;
  hasLearningObjective = false;
  hasScholarObjective = false;
  hasTeachingObjective = false;
  currentModalPage = 1;
  ObjectivesAnswers = ObjectivesAnswers;
  showModal = true;

  get lastModalPage(): number {
    if (this.objective !== ObjectivesAnswers.None) {
      return 2;
    }
    return this.currentModalPage;
  }

  get description(): string {
    if (this.currentModalPage === 1) {
      return this.T.userObjectivesModalDescriptionUsage;
    }
    if (this.hasLearningObjective && this.hasTeachingObjective) {
      return this.T.userObjectivesModalDescriptionLearningAndTeaching;
    }
    if (this.hasLearningObjective) {
      return this.T.userObjectivesModalDescriptionLearning;
    }
    return this.T.userObjectivesModalDescriptionTeaching;
  }

  setFirstModalPageObjectives(): void {
    switch (this.objective) {
      case ObjectivesAnswers.Learning:
        this.hasLearningObjective = true;
        this.hasTeachingObjective = false;
        break;
      case ObjectivesAnswers.Teaching:
        this.hasLearningObjective = false;
        this.hasTeachingObjective = true;
        break;
      case ObjectivesAnswers.LearningAndTeaching:
        this.hasLearningObjective = true;
        this.hasTeachingObjective = true;
        break;
      case ObjectivesAnswers.None:
        this.hasLearningObjective = false;
        this.hasTeachingObjective = false;
        break;
    }
  }

  onNextModalPage(): void {
    this.setFirstModalPageObjectives();
    this.previousObjective = this.objective;
    this.objective = ObjectivesAnswers.Scholar;
    this.currentModalPage++;
  }

  onPreviousModalPage(): void {
    this.objective = this.previousObjective;
    this.currentModalPage--;
  }

  onSubmit(): void {
    if (this.currentModalPage !== 1) {
      switch (this.objective) {
        case ObjectivesAnswers.Scholar:
          this.hasScholarObjective = true;
          this.hasCompetitiveObjective = false;
          break;
        case ObjectivesAnswers.Competitive:
          this.hasScholarObjective = false;
          this.hasCompetitiveObjective = true;
          break;
        case ObjectivesAnswers.ScholarAndCompetitive:
          this.hasScholarObjective = true;
          this.hasCompetitiveObjective = true;
          break;
        case ObjectivesAnswers.Other:
          this.hasScholarObjective = false;
          this.hasCompetitiveObjective = false;
          break;
      }
    } else {
      this.setFirstModalPageObjectives();
    }

    this.showModal = false;
    this.$emit('submit', {
      hasCompetitiveObjective: this.hasCompetitiveObjective,
      hasLearningObjective: this.hasLearningObjective,
      hasScholarObjective: this.hasScholarObjective,
      hasTeachingObjective: this.hasTeachingObjective,
    });
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

>>> .modal-dialog {
  max-width: 330px;
}

>>> .modal-header {
  border-bottom: 0;
}

.btn-next-previous {
  color: var(--btn-next-previous-font-color);
}

.btn-next-previous:focus,
.btn-next-previous.focus {
  box-shadow: 0 0 0 0;
}
</style>
