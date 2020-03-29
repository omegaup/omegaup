<template>
  <div>
    <div class="alert alert-info" v-if="privateProblemsAlert">
      <span class="message">{{ T.messageMakeYourProblemsPublic }}</span>
    </div>

    <div class="wait_for_ajax panel panel-default no-bottom-margin">
      <div class="panel-heading">
        <h3 class="panel-title">{{ T.myproblemsListMyProblems }}</h3>
      </div>
      <div class="panel-body">
        <div class="checkbox btn-group">
          <label v-if="">
            <input
              type="checkbox"
              v-model="shouldShowAllProblems.value"
              v-on:change.prevent="
                onChangeStatement($event, shouldShowAllProblems.selected)
              "
            />
            <span>{{ statementShowAllProblems }}</span>
          </label>
        </div>
        <div class="btn-group">
          <button
            type="button"
            class="btn btn-default dropdown-toggle"
            data-toggle="dropdown"
          >
            {{ T.forSelectedItems }}<span class="caret"></span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li>
              <a v-on:click="onChangeVisibility(selectedProblems, 1)">{{
                T.makePublic
              }}</a>
            </li>
            <li>
              <a v-on:click="onChangeVisibility(selectedProblems, 0)">{{
                T.makePrivate
              }}</a>
            </li>
          </ul>
        </div>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th></th>
            <th>{{ T.wordsTitle }}</th>
            <th>{{ T.wordsEdit }}</th>
            <th>{{ T.wordsStatistics }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="problem in problems">
            <td>
              <input
                type="checkbox"
                v-model="selectedProblems"
                v-bind:disabled="problem.visibility === -10"
                v-bind:value="problem.alias"
                v-bind:id="problem.alias"
              />
            </td>

            <td>
              <a
                class="title"
                v-bind:href="`/arena/problem/${problem.alias}/`"
                >{{ problem.title }}</a
              >
              <span
                class="glyphicon private"
                v-bind:class="{
                  'glyphicon-eye-close':
                    problem.visibility <= 0 && problem.visibility > -10,
                  'glyphicon-trash': problem.visibility === -10,
                }"
                v-bind:title="
                  problem.visibility === -10 ? T.wordsDeleted : T.wordsPrivate
                "
                v-show="problem.visibility <= 0"
              ></span>
              <div
                class="tag-list"
                v-if="problem.tags && problem.tags.length > 0"
              >
                <a
                  class="tag pull-left"
                  v-bind:class="{ tagVoted: tag.source === 'voted' }"
                  v-for="tag in problem.tags"
                  v-bind:href="`/problem/?tag[]=${tag.name}`"
                >
                  {{ tag.name }}
                </a>
              </div>
            </td>
            <td>
              <a
                class="glyphicon glyphicon-edit edit"
                v-bind:href="`/problem/${problem.alias}/edit/`"
              ></a>
            </td>
            <td>
              <a
                class="glyphicon glyphicon-stats stats"
                v-bind:href="`/problem/${problem.alias}/stats/`"
              ></a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <omegaup-common-paginator
      v-bind:pagerItems="pagerItems"
      v-on:page-changed="page => $emit('go-to-page', page)"
    ></omegaup-common-paginator>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import common_Paginator from '../common/Paginator.vue';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-common-paginator': common_Paginator,
  },
})
export default class ProblemMine extends Vue {
  @Prop() problems!: omegaup.Problem[];
  @Prop() pagerItems!: types.PageItem[];
  @Prop() privateProblemsAlert!: boolean;
  @Prop() isSysadmin!: boolean;

  T = T;
  shouldShowAllProblems = {};
  selectedProblems = [];

  get statementShowAllProblems(): string {
    return this.isSysadmin
      ? T.problemListShowAdminProblemsAndDeleted
      : T.problemListShowAdminProblems;
  }

  @Emit('change-show-all-problems')
  onChangeStatement(
    ev: Event,
    statement: omegaup.StatementProblems,
  ): omegaup.Selectable<omegaup.StatementProblems> {
    return {
      value: statement,
      selected: (<HTMLInputElement>ev.target).checked,
    };
  }

  @Emit('change-visibility')
  onChangeVisibility(visibiliy: number): number {
    this.selectedProblems = [];
    return visibiliy;
  }
}
</script>
