<template>
  <div class="omegaup-course-administrators panel">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-6">
          <form class="form"
                v-on:submit.prevent="onAddAdmin">
            <div class="form-group">
              <label>{{ T.wordsAdmin }}</label> <span aria-hidden="true"
                   class="glyphicon glyphicon-info-sign"
                   data-placement="top"
                   data-toggle="tooltip"
                   v-bind:title="T.courseEditAddStudentsTooltip"></span> <input autocomplete="off"
                   class="form-control typeahead"
                   size="20"
                   type="text">
            </div>
            <div class="form-group pull-right">
              <button class="btn btn-primary"
                   type="submit">{{ T.wordsAddAdmin }}</button> <button class="btn btn-secondary"
                   type="reset"
                   v-on:click.prevent="onCancel">{{ T.wordsCancel }}</button>
            </div>
          </form>
          <div v-if="admins.length == 0">
            <div class="empty-category">
              {{ T.courseEditAdminsEmpty }}
            </div>
          </div>
          <table class="table table-striped table-over"
                 v-else="">
            <thead>
              <tr>
                <th>{{ T.wordsUser }}</th>
                <th class="align-right">{{ T.contestEditRegisteredAdminDelete }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="admin in admins">
                <td>
                  <a v-bind:href="studentProgressUrl(student)">{{ student.name || student.username
                  }}</a>
                </td>
                <td><button class="close"
                        type="button"
                        v-on:click="onRemove(student)">×</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <form class="form"
                v-on:submit.prevent="onAddAdmin">
            <div class="form-group">
              <label>{{ T.wordsGroupAdmin }}</label> <span aria-hidden="true"
                   class="glyphicon glyphicon-info-sign"
                   data-placement="top"
                   data-toggle="tooltip"
                   v-bind:title="T.courseEditAddStudentsTooltip"></span> <input autocomplete="off"
                   class="form-control typeahead"
                   size="20"
                   type="text">
            </div>
            <div class="form-group pull-right">
              <button class="btn btn-primary"
                   type="submit">{{ T.wordsAddGroupAdmin }}</button> <button class=
                   "btn btn-secondary"
                   type="reset"
                   v-on:click.prevent="onCancel">{{ T.wordsCancel }}</button>
            </div>
          </form>
          <div v-if="groupAdmins.length == 0">
            <div class="empty-category">
              {{ T.courseEditGroupAdminsEmpty }}
            </div>
          </div>
          <table class="table table-striped table-over"
                 v-else="">
            <thead>
              <tr>
                <th>{{ T.wordsUser }}</th>
                <th class="align-right">{{ T.contestEditRegisteredAdminDelete }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="admin in admins">
                <td>
                  <a v-bind:href="studentProgressUrl(student)">{{ student.name || student.username
                  }}</a>
                </td>
                <td><button class="close"
                        type="button"
                        v-on:click="onRemove(student)">×</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    admins: Array,
    groupAdmins: Array,
  },
  data: function() {
    return {
      T: T,
    };
  },
};
</script>
