<template>
<div class="panel panel-primary">
    <div class="panel-body">
        <form class="clone_contest_form" v-on:submit.prevent="onSubmit">
        <div class="row">
            <div class="form-group col-md-6">
                <label>{{T.wordsTitle}}</label>
                <input type="text" size="30" class="form-control" v-model="title">
            </div>

            <div class="form-group col-md-6">
                <label>{{T.contestNewFormShortTitle_alias_}}</label>
                <span aria-hidden="true" data-placement="top" data-toggle="tooltip" title="T.contestNewFormShortTitle_alias_Desc" class="glyphicon glyphicon-info-sign"></span>
                <input type="text" class="form-control" v-model="alias">
            </div>

        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label>{{T.contestNewFormDescription}}</label>
                <textarea cols="30" rows="10" class="form-control" v-model="description"></textarea>
            </div>
            <div class="form-group col-md-3">
                <label>{{T.contestNewFormStartDate}}</label>
                <span aria-hidden="true" data-placement="top" data-toggle="tooltip" title="T.contestNewFormStartDateDesc" class="glyphicon glyphicon-info-sign"></span>
                <omegaup-datetime v-model="startTime"></omegaup-datetime>
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">{{T.wordsCloneContest}}
            </button>
        </div>
        </form>
    </div>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
import DateTime from '../DateTimePicker.vue';

export default {
    data: function() {
        return {
            T: T,
            title: "",
            alias: "",
            description: "",
            startTime: new Date()
        }
    },
    methods: {
        onSubmit: function() {
            this.$parent.$emit('cloneContest', this);
        }
    },
    components: {
        'omegaup-datetime': DateTime
    }
}
</script>
