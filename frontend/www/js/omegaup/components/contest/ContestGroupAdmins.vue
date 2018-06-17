<template>
<div class="panel panel-primary">
    <div class="panel-body">
        <form class="form" id="add-group-admin-form">
            <div class="form-group">
                <label for="groupalias-admin">{#wordsGroupAdmin#}</label>
                <input id="groupalias-admin" name="alias" value="" type="text" size="20" class="form-control" autocomplete="off" />
            </div>

            <button class="btn btn-primary" type="submit">{#contestAddgroupAddGroup#}</button>
        </form>
    </div>

    <table class="table table-striped">
        <thead>
            <th>{#contestEditRegisteredGroupAdminName#}</th>
            <th>{#contestEditRegisteredAdminRole#}</th>
            <th>{#contestEditRegisteredAdminDelete#}</th>
        </thead>
        <tbody id="contest-group-admins"></tbody>
    </table>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
</script>
