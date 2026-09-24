<template>
  <omegaup-overlay-popup @dismiss="$emit('close')">
    <h4>{{ T.problemAddToContestOrCourse }}</h4>

    <!-- Tab selector -->
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <a
          class="nav-link"
          :class="{ active: activeTab === 'course' }"
          href="#"
          @click.prevent="activeTab = 'course'"
        >
          {{ T.problemAddToCourse }}
        </a>
      </li>
      <li class="nav-item">
        <a
          class="nav-link"
          :class="{ active: activeTab === 'contest' }"
          href="#"
          @click.prevent="activeTab = 'contest'"
        >
          {{ T.problemAddToContest }}
        </a>
      </li>
    </ul>

    <!-- Course tab -->
    <div v-if="activeTab === 'course'">
      <div v-if="!adminCourses || adminCourses.length === 0">
        <p class="text-muted">{{ T.problemAddNoCourses }}</p>
      </div>
      <div v-else>
        <div class="form-group">
          <label>{{ T.problemAddSelectCourse }}</label>
          <select
            v-model="selectedCourseAlias"
            class="form-control"
            @change="selectedAssignmentAlias = ''"
          >
            <option value="">—</option>
            <option
              v-for="course in adminCourses"
              :key="course.alias"
              :value="course.alias"
            >
              {{ course.name }}
            </option>
          </select>
        </div>
        <div v-if="selectedCourseAlias" class="form-group">
          <label>{{ T.problemAddSelectAssignment }}</label>
          <select v-model="selectedAssignmentAlias" class="form-control">
            <option value="">—</option>
            <option
              v-for="assignment in selectedCourseAssignments"
              :key="assignment.alias"
              :value="assignment.alias"
            >
              {{ assignment.name }}
              <template v-if="assignment.assignment_type === 'exam'">
                ({{ T.wordsExam }})
              </template>
              <template v-else-if="assignment.assignment_type === 'lesson'">
                ({{ T.wordsLecture }})
              </template>
              <template v-else> ({{ T.wordsAssignment }}) </template>
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Contest tab -->
    <div v-if="activeTab === 'contest'">
      <div v-if="!adminContests || adminContests.length === 0">
        <p class="text-muted">{{ T.problemAddNoContests }}</p>
      </div>
      <div v-else>
        <div class="form-group">
          <label>{{ T.problemAddSelectContest }}</label>
          <select v-model="selectedContestAlias" class="form-control">
            <option value="">—</option>
            <option
              v-for="contest in adminContests"
              :key="contest.alias"
              :value="contest.alias"
            >
              {{ contest.title }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Footer buttons -->
    <div class="d-flex justify-content-end mt-3">
      <button
        type="button"
        class="btn btn-secondary mr-2"
        @click="$emit('close')"
      >
        {{ T.wordsCancel }}
      </button>
      <button
        type="button"
        class="btn btn-primary"
        :disabled="!canSubmit"
        @click="onSubmit"
      >
        {{ T.wordsAdd }}
      </button>
    </div>
  </omegaup-overlay-popup>
</template>

<script lang="ts">
import Vue from 'vue';
import T from '../../lang';
import omegaup_OverlayPopup from '../OverlayPopup.vue';

interface AdminCourse {
  alias: string;
  name: string;
  assignments: { alias: string; name: string; assignment_type: string }[];
}

interface AdminContest {
  alias: string;
  title: string;
}

export default Vue.extend({
  components: {
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
  props: {
    adminCourses: {
      type: Array as () => AdminCourse[],
      default: (): AdminCourse[] => [],
    },
    adminContests: {
      type: Array as () => AdminContest[],
      default: (): AdminContest[] => [],
    },
  },
  data() {
    return {
      T,
      activeTab: 'course' as 'course' | 'contest',
      selectedCourseAlias: '',
      selectedAssignmentAlias: '',
      selectedContestAlias: '',
    };
  },
  computed: {
    selectedCourseAssignments(): {
      alias: string;
      name: string;
      assignment_type: string;
    }[] {
      const course = this.adminCourses.find(
        (course) => course.alias === this.selectedCourseAlias,
      );
      return course ? course.assignments : [];
    },
    canSubmit(): boolean {
      if (this.activeTab === 'course') {
        return (
          this.selectedCourseAlias !== '' && this.selectedAssignmentAlias !== ''
        );
      }
      return this.selectedContestAlias !== '';
    },
  },
  methods: {
    onSubmit(): void {
      if (!this.canSubmit) {
        return;
      }
      if (this.activeTab === 'course') {
        this.$emit('add-to-course', {
          courseAlias: this.selectedCourseAlias,
          assignmentAlias: this.selectedAssignmentAlias,
        });
        return;
      }
      this.$emit('add-to-contest', {
        contestAlias: this.selectedContestAlias,
      });
    },
  },
});
</script>
