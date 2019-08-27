<template>
  <div class="omegaup-course-problemlist panel">
    <div class="panel-heading">
      <form class="problemlist">
        <div class="row">
          <div class="form-group col-md-8">
            <label>{{ T.wordsAssignments }} <select class="form-control"
                    name="assignments"
                    v-model="assignment">
              <option v-bind:value="a"
                      v-for="a in assignments">
                {{ a.name }}
              </option>
            </select></label>
          </div>
          <div class="form-group col-md-4 pull-right"
               v-show="assignment.alias">
            <label>&nbsp; <button class="form-control btn btn-primary"
                    v-on:click.prevent="onShowForm">{{ T.courseEditAddProblems }}</button></label>
          </div>
        </div>
      </form>
    </div>
    <div class="table-body"
         v-if="assignmentProblems.length == 0">
      <div class="empty-category">
        {{ T.courseAssignmentProblemsEmpty }}
      </div>
    </div>
    <table class="table table-striped"
           v-else="">
      <thead>
        <tr>
          <th>{{ T.contestAddproblemProblemOrder }}</th>
          <th>{{ T.contestAddproblemProblemName }}</th>
          <th>{{ T.contestAddproblemProblemRemove }}</th>
        </tr>
      </thead>
      <tbody v-sortable="{ onUpdate: sort }">
        <tr v-bind:key="problem.letter"
            v-for="problem in assignmentProblems">
          <td>
            <a v-bind:title="T.courseAssignmentProblemReorder"><span aria-hidden="true"
                  class="glyphicon glyphicon-move handle"></span></a>
          </td>
          <td>{{ problem.title }}</td>
          <td class="button-column">
            <a v-bind:title="T.courseAssignmentProblemRemove"
                v-on:click="$emit('remove', assignment, problem)"><span aria-hidden="true"
                  class="glyphicon glyphicon-remove"></span></a>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="panel-footer"
         v-show="showForm">
      <form>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>{{ T.wordsTopics }} <select class="form-control"
                      multiple
                      v-model="topics">
                <!-- TODO: How do we do this in general? -->
                <option value="binary-search">
                  {{ T.problemTopicBinarySearch }}
                </option>
                <option value="graph-theory">
                  {{ T.problemTopicGraphTheory }}
                </option>
                <option value="sorting">
                  {{ T.problemTopicSorting }}
                </option>
              </select></label>
            </div>
            <div class="form-group">
              <label>{{ T.wordsLevels }} <select class="form-control"
                      v-model="level">
                <option value="intro">
                  {{ T.problemLevelIntro }}
                </option>
                <option value="easy">
                  {{ T.problemLevelEasy }}
                </option>
                <option value="medium">
                  {{ T.problemLevelMedium }}
                </option>
                <option value="hard">
                  {{ T.problemLevelHard }}
                </option>
              </select></label>
            </div>
          </div>
          <div class="col-md-8">
            <div class="row">
              <div class="form-group col-md-12">
                <label>{{ T.wordsProblems }} <select class="form-control"
                        size="15"
                        v-model="taggedProblemAlias">
                  <option v-bind:value="problem.alias"
                          v-for="problem in taggedProblems">
                    {{ problem.title }}
                  </option>
                </select></label>
              </div>
            </div>
            <div class="row">
              <div class="form-group col-md-12">
                <label>{{ T.wordsProblem }} <omegaup-autocomplete class="form-control"
                                      v-bind:init="el =&gt; UI.problemTypeahead(el)"
                                      v-model="problemAlias"></omegaup-autocomplete></label>
                <p class="help-block">{{ T.courseAddProblemsAssignmentsDesc }}</p>
              </div>
            </div>
            <div class="form-group pull-right">
              <button class="btn btn-primary"
                   type="submit"
                   v-bind:disabled="problemAlias.length == 0"
                   v-on:click.prevent="$emit('add-problem', assignment, problemAlias)">{{
                   T.courseAddProblemsAdd }}</button> <button class="btn btn-secondary"
                   type="reset"
                   v-on:click.prevent="showForm = false">{{ T.wordsCancel }}</button>
            </div>
          </div>
        </div>
      </form>
    </div><!-- panel-body -->
  </div><!-- panel -->
</template>

<style>
.omegaup-course-problemlist .form-group>label {
  width: 100%;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import Autocomplete from '../Autocomplete.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class CourseProblemList extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() assignmentProblems!: omegaup.AssignmentProblem[];
  @Prop() taggedProblems!: omegaup.Problem[];

  UI = UI;
  T = T;
  assignment: Partial<omegaup.Assignment> = {};
  showForm = false;
  level = 'intro';
  topics: string[] = [];
  taggedProblemAlias = '';
  problemAlias = '';

  get tags(): string[] {
    let t = this.topics.slice();
    t.push(this.level);
    return t;
  }

  onShowForm(): void {
    this.showForm = true;
    this.problemAlias = '';
    this.level = 'intro';
    this.topics = [];
  }

  sort(event: any) {
    this.assignmentProblems.splice(
      event.newIndex,
      0,
      this.assignmentProblems.splice(event.oldIndex, 1)[0],
    );
    this.$emit('sort', this.assignment, this.assignmentProblems);
  }

  @Watch('assignment')
  onAssignmentChange(newVal: omegaup.Assignment): void {
    this.$emit('assignment', newVal);
  }

  @Watch('taggedProblemAlias')
  onTaggedProblemAliasChange() {
    this.problemAlias = this.taggedProblemAlias;
  }

  @Watch('tags')
  onTagsChange() {
    this.$emit('tags', this.tags);
  }
}

</script>
