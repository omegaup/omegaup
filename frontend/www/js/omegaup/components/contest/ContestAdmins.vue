<template>
<div class="panel panel-primary">
    <div class="panel-body">
        <form class="form" v-on:submit.prevent="onSubmit">
            <div class="form-group">
                <label>{{T.wordsAdmin}}</label>
                <autocomplete-user v-model="user"></autocomplete-user>
            </div>

            <div class="form-group">
                <div class="col-xs-5 col-sm-3 col-md-3 action-container">
                    <button class="btn btn-primary" type="submit">{{T.wordsAddAdmin}}</button>
                </div>
                <div class="col-xs-7 col-sm-9 col-md-9 toggle-container">
                    <input type="checkbox" v-model="showSiteAdmin">
                    <label>{{T.wordsShowSiteAdmins}}</label>
                </div>
            </div>
        </form>
    </div>

    <table class="table table-striped">
        <thead>
            <th>{{T.contestEditRegisteredAdminUsername}}</th>
            <th>{{T.contestEditRegisteredAdminRole}}</th>
            <th>{{T.contestEditRegisteredAdminDelete}}</th>
        </thead>
        <tbody>
            <tr v-for="admin in admins" v-if="(admin.role != 'site-admin') || showSiteAdmin">
                <td><a v-bind:href="`/profile/${admin.username}/`">{{admin.username}}</a></td>
                <td>{{admin.role}}</td>
                <td><button type="button" class="close">x</button></td>
            </tr>
        </tbody>
    </table>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
import AutocompleteUser from '../AutocompleteUser.vue';

export default {
    props: {
        admins: Array,
    },
    data: function() {
        return {
            T: T,
            user: "",
            showSiteAdmin: false
        }
    },
    methods: {
        onSubmit: function() {
            this.$parent.$emit('addAdmin', this);
        }
    },
    components: {
        'autocomplete-user': AutocompleteUser
    }
}
</script>
