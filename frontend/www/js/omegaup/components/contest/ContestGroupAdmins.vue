<template>
<div class="panel panel-primary">
    <div class="panel-body">
        <form class="form" v-on:submit.prevent="onSubmit">
            <div class="form-group">
                <label>{{T.wordsGroupAdmin}}</label>
                <autocomplete-group-admins v-model="groupName"></autocomplete-group-admins>
            </div>

            <button class="btn btn-primary" type="submit">{{T.contestAddgroupAddGroup}}</button>
        </form>
    </div>

    <table class="table table-striped">
        <thead>
            <th>{{T.contestEditRegisteredGroupAdminName}}</th>
            <th>{{T.contestEditRegisteredAdminRole}}</th>
            <th>{{T.contestEditRegisteredAdminDelete}}</th>
        </thead>
        <tbody>
            <tr v-for="group in groupAdmins">
                <td><a v-bind:href="`/group/${group.alias}/edit/`"></a>{{group.name}}</td>
                <td>{{group.role}}</td>
                <td>
                    <button type="button" class="close"
                        v-if="group.name != 'admin'">x</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
import AutocompleteGroupAdmins from '../AutocompleteGroupAdmins.vue';


export default {
    props: {
        groupAdmins: Array
    },
    data: function() {
        return {
            T: T,
            groupName: ""
        }
    },
    methods: {
        onSubmit: function() {
            this.$parent.$emit('addGroupAdmin', this);
        }
    },
    components: {
        'autocomplete-group-admins': AutocompleteGroupAdmins
    }
}
</script>
