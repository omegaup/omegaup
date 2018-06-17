<template>
<div>
    <div class="page-header">
        <h1><span>{{T.frontPageLoading}}</span> <small></small></h1>
    </div>

    <ul class="nav nav-tabs nav-justified" id="sections">
        <li v-on:click="showTab = 'new_form'" class="active"><a data-toggle="tab">{{T.contestEdit}}</a></li>
        <li v-on:click="showTab = 'problems'"><a data-toggle="tab">{{T.wordsAddProblem}}</a></li>
        <li v-on:click="showTab = 'publish'"><a data-toggle="tab">{{T.makePublic}}</a></li>
        <li v-on:click="showTab = 'contestants'"><a data-toggle="tab">{{T.contestAdduserAddContestant}}</a></li>
        <li v-on:click="showTab = 'admins'"><a data-toggle="tab">{{T.omegaupTitleContestAddAdmin}}</a></li>
        <li v-on:click="showTab = 'group_admins'"><a data-toggle="tab">{{T.omegaupTitleContestAddGroupAdmin}}</a></li>
        <li v-on:click="showTab = 'links'"><a data-toggle="tab">{{T.showLinks}}</a></li>
        <li v-on:click="showTab = 'clone'"><a data-toggle="tab">{{T.courseEditClone}}</a></li>
    </ul>
    <div class="tab-content">
        <div v-if="showTab === 'new_form'" class="tab-pane active">
            <contest-new-form
                v-bind:update="true"
                v-bind:contest="contest"></contest-new-form>
        </div>

        <div v-if="showTab === 'problems'" class="tab-pane active">
            <contest-add-problem v-bind:problems="problems"></contest-add-problem>
        </div>

        <div v-if="showTab === 'publish'" class="tab-pane active">
            <!--<contest-publish></contest-publish>-->
        </div>

        <div v-if="showTab === 'contestants'" class="tab-pane active">
            <!--<contest-contestants></contest-contestants>-->
        </div>

        <div v-if="showTab === 'admins'" class="tab-pane active">
            <!--<contest-admins></contest-admins>-->
        </div>

        <div v-if="showTab === 'group_admins'" class="tab-pane active">
            <!--<contest-group-admins></contest-group-admins>-->
        </div>

        <div v-if="showTab === 'links'" class="tab-pane active">
            <!--<contest-links></contest-links>-->
        </div>

        <div v-if="showTab === 'clone'" class="tab-pane active">
            <!--<contest-clone></contest-clone>-->
        </div>
    </div>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
import ContestNewForm from './ContestNewForm.vue';
import ContestAddProblem from './ContestAddProblem.vue';
export default {
    props: {
        contest: Object,
        problems: Array,
    },
    data: function() {
        return {
            showTab: "new_form",
            T: T
        }
    },
    components: {
        'contest-new-form': ContestNewForm,
        'contest-add-problem': ContestAddProblem
    }
}
</script>
