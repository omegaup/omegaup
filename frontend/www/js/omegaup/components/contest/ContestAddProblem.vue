<template>
<div class="panel panel-primary">
    <div class="panel-body">
        <form class="form" v-on:submit.prevent="onSubmit">
            <div class="form-group">
                <label>{{T.wordsProblem}}</label>
                <omegaup-autocomplete-problems class="typeahead form-control" v-bind:problems="problems" v-model="alias" />
            </div>

            <div class="form-group">
                <label>{{T.contestAddproblemProblemPoints}}</label>
                <input size="3" v-model="points" class="form-control" />
            </div>

            <div class="form-group">
                <label>{{T.contestAddproblemContestOrder}}</label>
                <input size="2" v-model="order" class="form-control" />
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">{{T.wordsAddProblem}}</button>
            </div>
        </form>
    </div>

    <table class="table table-striped">
        <thead>
            <th>{{T.contestAddproblemContestOrder}}</th>
            <th>{{T.contestAddproblemProblemName}}</th>
            <th>{{T.contestAddproblemProblemPoints}}</th>
            <th>{{T.contestAddproblemProblemRemove}}</th>
        </thead>
        <tbody></tbody>
    </table>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
import AutocompleteProblems from '../AutocompleteProblems.vue';
export default {
    props: {
        problems: Array
    },
    data: function() {
        return {
            alias: "",
            aliases: [],
            titles: [],
            points: 100,
            order: 1,
            T: T
        }
    },
    methods:{
        onSubmit: function() {
            this.$parent.$emit('addProblem', this);
        }
    },
    mounted: function(){
        /*var self = this;
        for (var problem of self.problems){
            aliases.push(problem.alias);
            titles.push(problem.title);
        }*/
    },
    components: {
        'omegaup-autocomplete-problems': AutocompleteProblems
    }
}
</script>
