<template>
<div class="panel panel-primary">
    <div class='panel-body'>
        <form class='contest-publish-form' v-on:submit.prevent="onSubmit">
            <div class="form-group">
                <label>{{T.contestNewFormPublic}}</label>
                <select v-model="public" class="form-control">
                    <option value='0'>{{T.wordsNo}}</option>
                    <option value='1'>{{T.wordsYes}}</option>
                </select>
                <p class="help-block">{{T.contestNewFormPublicDesc}}</p>
            </div>

            <button class="btn btn-primary" type="submit">{{T.wordsSaveChanges}}</button>
        </form>
    </div>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
export default {
    props: {
        contest: Object
    },
    data: function() {
        return {
            public: this.contest.public,
            T: T
        }
    },
    methods: {
        onSubmit: function() {
            this.$parent.$emit('updatePublic', this);
        }
    }
}
</script>
