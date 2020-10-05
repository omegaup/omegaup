<template>
  <div class="omegaup-course-assignmentlist card">
    <div class="card-header">
      <h3>{{ T.wordsCourseContent }}</h3>
    </div>
    <div class="card-body">
      <div v-if="content.length === 0" class="card-body">
        <div class="empty-table-message">
          {{ T.courseContentEmpty }}
        </div>
      </div>
      <table v-else class="table table-striped">
        <thead>
          <tr>
            <td></td>
            <th>{{ T.wordsContentType }}</th>
            <th>{{ T.wordsName }}</th>
            <th class="text-center">{{ T.wordsActions }}</th>
          </tr>
        </thead>
        <tbody v-sortable="{ onUpdate: sortContent }">
          <tr v-for="assignment in content" :key="assignment.alias">
            <td>
              <button class="btn btn-link" :title="T.courseAssignmentReorder">
                <font-awesome-icon icon="arrows-alt" />
              </button>
            </td>
            <td class="align-middle">
              <template v-if="assignment.assignment_type === 'homework'">
                <font-awesome-icon icon="file-alt" />
                <span class="ml-2">{{ T.wordsHomework }}</span>
              </template>
              <template v-else-if="assignment.assignment_type === 'lesson'">
                <font-awesome-icon icon="chalkboard-teacher" />
                <span class="ml-2">{{ T.wordsLesson }}</span>
              </template>
              <template v-else>
                <font-awesome-icon icon="list-alt" />
                <span class="ml-2">{{ T.wordsExam }}</span>
              </template>
            </td>
            <td class="align-middle">
              <a :href="assignmentUrl(assignment)">{{ assignment.name }}</a>
            </td>
            <td class="text-center">
              <button
                class="btn btn-link"
                :title="T.courseAssignmentEdit"
                @click="$emit('emit-edit', assignment)"
              >
                <font-awesome-icon icon="edit" />
              </button>
              <button
                class="btn btn-link"
                :title="T.courseAddProblemsAdd"
                @click="$emit('emit-add-problems', assignment)"
              >
                <font-awesome-icon icon="list-alt" />
              </button>
              <font-awesome-icon
                v-if="assignment.has_runs"
                :title="T.assignmentRemoveAlreadyHasRuns"
                icon="trash"
                class="disabled"
              />
              <button
                v-else
                class="btn btn-link"
                :title="T.courseAssignmentDelete"
                @click="$emit('emit-delete', assignment)"
              >
                <font-awesome-icon icon="trash" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div>
        <button
          v-if="content.length > 1"
          class="btn btn-primary"
          :class="{ disabled: !contentOrderChanged }"
          role="button"
          @click="saveNewOrder"
        >
          {{ T.wordsSaveNewOrder }}
        </button>
      </div>
    </div>
    <div class="card-footer">
      <form class="new">
        <div class="row">
          <div class="form-group col-md-12">
            <div class="text-right">
              <button
                v-if="assignmentFormMode === AssignmentFormMode.Default"
                class="btn btn-primary"
                type="submit"
                @click.prevent="$emit('emit-new')"
              >
                {{ T.courseAddContent }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';

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
export default class CourseAssignmentList extends Vue {
  @Prop() content!: types.CourseAssignment[];
  @Prop() courseAlias!: string;
  @Prop() assignmentFormMode!: omegaup.AssignmentFormMode;

  contentOrderChanged = false;
  T = T;
  AssignmentFormMode = omegaup.AssignmentFormMode;
  currentContent: types.CourseAssignment[] = this.content;

  assignmentUrl(assignment: omegaup.Assignment): string {
    return `/course/${this.courseAlias}/assignment/${assignment.alias}/`;
  }

  sortContent(event: any): void {
    this.currentContent.splice(
      event.newIndex,
      0,
      this.currentContent.splice(event.oldIndex, 1)[0],
    );
    this.contentOrderChanged = true;
  }

  saveNewOrder(): void {
    this.contentOrderChanged = false;
    this.$emit(
      'emit-sort-content',
      this.courseAlias,
      this.currentContent.map(
        (assignment: types.CourseAssignment) => assignment.alias,
      ),
    );
  }

  @Watch('content')
  onContentChanged(newValue: types.CourseAssignment[]): void {
    this.currentContent = newValue;
  }
}
</script>

<style lang="scss" scoped>
.disabled {
  color: lightgrey;
}

.table td {
  vertical-align: middle;
}
</style>
