{include file='redirect.tpl'}
{assign var="htmlTitle" value="My courses"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<script type="text/javascript" src="/third_party/js/knockout-4.3.0.js?ver=059d58"></script>
<script type="text/javascript" src="/third_party/js/knockout-secure-binding.min.js?ver=81a2a3"></script>
<script type="text/javascript" src="/js/course.list.js?ver=d656aa"></script>

<template id="course-list">
<div class="panel panel-primary">
    <div class="panel-heading" data-bind="text: listName"></div>
    <table class="course-table">
        <thead>
            <tr>
                <td>{#wordsName#}</td>
                <td>{#wordsEndTime#}</td>
                <td>{#wordsNumHomeworks#}</td>
                <td>{#wordsNumTests#}</td>
            </tr>
        </thead>
        <tbody data-bind="foreach: course">
            <tr>
                <td><a data-bind="text: name, attr: { href: courseURL }" /></td>
                <td data-bind="text: endDate"></td>
                <td data-bind="text: numHomeworks"></td>
                <td data-bind="text: numTests"></td>
            </tr>
        </tbody>
    </table>
</div>
</template>

<div id="admin-courses-current"
     data-bind="template: { name: 'course-list',
                            if: adminCoursesCurrent().length > 0,
                            data: { listName: '{#courseListAdminCurrentCourses#}',
                                    course: adminCoursesCurrent() }  }"></div>
<div id="admin-courses-past"
     data-bind="template: { name: 'course-list',
                            if: adminCoursesPast().length > 0,
                            data: { listName: '{#courseListAdminPastCourses#}',
                                    course: adminCoursesPast() } }"></div>
<div id="student-courses-current"
     data-bind="template: { name: 'course-list',
                            if: studentCoursesCurrent().length > 0,
                            data: { listName: '{#courseListStudentCurrentCourses#}',
                                    course: studentCoursesCurrent() }  }"></div>
<div id="student-courses-past"
     data-bind="template: { name: 'course-list',
                            if: studentCoursesPast().length > 0,
                            data: { listName: '{#courseListStudentPastCourses#}',
                                    course: studentCoursesPast() }  }"></div>

{include file='footer.tpl'}
