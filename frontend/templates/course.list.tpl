{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="My courses"}

<script type="text/javascript" src="{version_hash src="/js/course.list.js"}"></script>

<template id="course-list">
<div class="panel">
    <div class="panel-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{#wordsName#}</th>
                    <th>{#wordsStartTime#}</th>
                    <th>{#wordsEndTime#}</th>
                    <th>{#wordsNumHomeworks#}</th>
                    <th>{#wordsNumTests#}</th>
                </tr>
            </thead>
            <tbody data-bind="foreach: course">
                <tr>
                    <td><a data-bind="text: name, attr: { href: courseURL }" /></td>
                    <td data-bind="text: startDate"></td>
                    <td data-bind="text: endDate"></td>
                    <td data-bind="text: numHomeworks"></td>
                    <td data-bind="text: numTests"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</template>

<div class="panel">
    <div class="page-header">
        <div class="pull-right">
          <a class="btn btn-primary" href="/course/new/">{#courseNew#}</a>
        </div>
        <h1><span>{#courseList#}</span> <small></small></h1>
    </div>
    <!-- Default to hidden to avoid FOUC -->
    <div class="panel-body tab-container" style="display:none">
        <ul class="nav nav-tabs">
            <li class="nav-item"
                data-bind="if: adminCoursesCurrent().length > 0">
                <a class="nav-link" href="#tab-admin-courses-current"
                   data-toggle="tab">{#courseListAdminCurrentCourses#}</a></li>
            <li class="nav-item"
                data-bind="if: adminCoursesPast().length > 0">
                <a class="nav-link" href="#tab-admin-courses-past"
                   data-toggle="tab">{#courseListAdminPastCourses#}</a></li>
            <li class="nav-item"
                data-bind="if: studentCoursesCurrent().length > 0">
                <a class="nav-link" href="#tab-student-courses-current"
                   data-toggle="tab">{#courseListStudentCurrentCourses#}</a></li>
            <li class="nav-item"
                data-bind="if: studentCoursesPast().length > 0">
                <a class="nav-link" href="#tab-student-courses-past"
                   data-toggle="tab">{#courseListStudentPastCourses#}</a></li>
        </ul>

        <div class="tab-content">
                <div class="tab-pane" id="tab-admin-courses-current">
                    <div id="admin-courses-current"
             data-bind="template: { name: 'course-list',
                                    if: adminCoursesCurrent().length > 0,
                                    data: { listName: '{#courseListAdminCurrentCourses#}',
                                            course: adminCoursesCurrent() }  }"></div>
                </div>
                <div class="tab-pane" id="tab-admin-courses-past">
                    <div id="admin-courses-past"
             data-bind="template: { name: 'course-list',
                                    if: adminCoursesPast().length > 0,
                                    data: { listName: '{#courseListAdminPastCourses#}',
                                            course: adminCoursesPast() } }"></div>
                </div>
                <div class="tab-pane" id="tab-student-courses-current">
                    <div id="student-courses-current"
             data-bind="template: { name: 'course-list',
                                    if: studentCoursesCurrent().length > 0,
                                    data: { listName: '{#courseListStudentCurrentCourses#}',
                                            course: studentCoursesCurrent() }  }"></div>
                </div>
                <div class="tab-pane" id="tab-student-courses-current">
                    <div id="student-courses-past"
             data-bind="template: { name: 'course-list',
                                    if: studentCoursesPast().length > 0,
                                    data: { listName: '{#courseListStudentPastCourses#}',
                                            course: studentCoursesPast() }  }"></div>
                </div>
            </div> <!-- tab-content -->
        </div> <!-- panel-body -->
</div> <!-- panel -->

{include file='footer.tpl'}
