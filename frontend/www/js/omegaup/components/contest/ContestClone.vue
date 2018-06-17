<template>
<div class="panel panel-primary">
    <div class="panel-body">
        <form class="clone_contest_form">
        <div class="row">
            <div class="form-group col-md-6">
                <label for="title">{#wordsTitle#}</label>
                <input id="title" name="title" value="" type="text" size="30" class="form-control">
            </div>

            <div class="form-group col-md-6">
                <label for="alias">{#contestNewFormShortTitle_alias_#}</label>
                <span aria-hidden="true" data-placement="top" data-toggle="tooltip" title="{#contestNewFormShortTitle_alias_Desc#}" class="glyphicon glyphicon-info-sign"></span>
                <input id="alias_clone" name="alias" value="" type="text" class="form-control" >
            </div>

        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="description">{#contestNewFormDescription#}</label>
                <textarea id="description" name="description" cols="30" rows="10" class="form-control"></textarea>
            </div>
            <div class="form-group col-md-3">
                <label for="start-time">{#contestNewFormStartDate#}</label>
                <span aria-hidden="true" data-placement="top" data-toggle="tooltip" title="{#contestNewFormStartDateDesc#}" class="glyphicon glyphicon-info-sign"></span>
                <input id="start-time" name="start_time" value="" class="form-control" type="text" size ="16">
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">{#wordsCloneContest#}
            </button>
        </div>
        </form>
    </div>
</div>
</template>
<script>
</script>
