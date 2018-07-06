<template>
<div>
    <div class="page-header">
        <h1><span>{{T.frontPageLoading}}</span> <small></small></h1>
    </div>

    <ul class="nav nav-tabs nav-justified">
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
            <contest-add-problem
                v-bind:problems="problems"></contest-add-problem>
        </div>

        <div v-if="showTab === 'publish'" class="tab-pane active">
            <contest-publish v-bind:contest="contest"></contest-publish>
        </div>

        <div v-if="showTab === 'contestants'" class="tab-pane active">
            <contest-contestant v-bind:users="users"></contest-contestant>
        </div>

        <div v-if="showTab === 'admins'" class="tab-pane active">
            <contest-admins v-bind:admins="admins"></contest-admins>
        </div>

        <div v-if="showTab === 'group_admins'" class="tab-pane active">
            <contest-group-admins v-bind:groupAdmins="groupAdmins"></contest-group-admins>
        </div>

        <div v-if="showTab === 'links'" class="tab-pane active">
            <contest-links v-bind:contest="contest"></contest-links>
        </div>

        <div v-if="showTab === 'clone'" class="tab-pane active">
            <contest-clone></contest-clone>
        </div>
    </div>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
import ContestNewForm from './ContestNewForm.vue';
import ContestAddProblem from './ContestAddProblem.vue';
import ContestPublish from './ContestPublish.vue';
import ContestContestant from './ContestContestant.vue';
import ContestAdmins from './ContestAdmins.vue';
import ContestGroupAdmins from './ContestGroupAdmins.vue';
import ContestLinks from './ContestLinks.vue';
import ContestClone from './ContestClone.vue';

export default {
    props: {
        contest: Object,
        problems: Array,
        users: Array,
        admins: Array,
        groupAdmins: Array
    },
    data: function() {
        return {
            showTab: "new_form",
            T: T
        }
    },
    mounted: function() {
        console.log(this.problems)
        console.log(this.contest)
        console.log(this.users);
    },
    components: {
        'contest-new-form': ContestNewForm,
        'contest-add-problem': ContestAddProblem,
        'contest-publish': ContestPublish,
        'contest-contestant': ContestContestant,
        'contest-admins': ContestAdmins,
        'contest-group-admins': ContestGroupAdmins,
        'contest-links': ContestLinks,
        'contest-clone': ContestClone
    }
}
</script>
