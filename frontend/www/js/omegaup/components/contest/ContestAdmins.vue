<template>
<div class="panel panel-primary">
    <div class="panel-body">
        <form class="form" id="add-admin-form">
            <div class="form-group">
                <label>{{T.wordsAdmin}}</label>
                <input type="text" size="20" class="form-control" autocomplete="off" />
            </div>

            <div class="form-group">
                <div class="col-xs-5 col-sm-3 col-md-3 action-container">
                    <button class="btn btn-primary" type="submit">{{T.wordsAddAdmin}}</button>
                </div>
                <div class="col-xs-7 col-sm-9 col-md-9 toggle-container">
                    <input type="checkbox" name="toggle-site-admins" id="toggle-site-admins">
                    <label for="toggle-site-admins">{{T.wordsShowSiteAdmins}}</label>
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
        <tbody id="contest-admins"></tbody>
    </table>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
export default {
    props: {
        admins: Object,
    },
    data: function() {
        return {T: T}
    }
}
</script>
