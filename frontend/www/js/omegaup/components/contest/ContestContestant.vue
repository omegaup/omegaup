<template>
<div class="panel panel-primary">
    <div class="panel-body">
        <form class="form" id="add-contestant-form">
            <div class="form-group">
                <label for="username-contestant">{#wordsUser#}</label>
                <input id="username-contestant" name="username" value="" type="text" size="20" class="form-control" autocomplete="off" />
            </div>

            <button class="btn btn-primary user-add-single" type="submit">{#contestAdduserAddUser#}</button>

            <hr>

            <div class="form-group">
                <label for="username-contestants">{#wordsMultipleUser#}</label>
                <textarea name="usernames" rows="4" class="form-control"></textarea>
            </div>

            <button class="btn btn-primary user-add-bulk" type="submit">{#contestAdduserAddUsers#}</button>
        </form>
    </div>

    <table class="table table-striped">
        <thead>
            <th>{#wordsUser#}</th>
            <th>{#contestAdduserRegisteredUserTime#}</th>
            <th>{#contestAdduserRegisteredUserDelete#}</th>
        </thead>
        <tbody id="contest-users"></tbody>
    </table>
</div>

<div class="panel panel-primary" id="requests">
    <div class="panel-body">
        {#pendingRegistrations#}
    </div>
    <table id="user-requests-table"  >
        <thead>
        <tr>
            <th>{#wordsUser#}</th>
            <th>{#userEditCountry#}</th>
            <th>{#requestDate#}</th>
            <th>{#currentStatus#}</th>
            <th>{#lastUpdate#}</th>
            <th>{#contestAdduserAddContestant#}</th>
        </tr>
        </thead>
    </table>
</div>
</template>
<script>
export default {
}
</script>
