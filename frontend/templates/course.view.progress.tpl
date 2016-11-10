<script type="text/javascript" src="/third_party/js/knockout-4.3.0.js?ver=059d58"></script>
<script type="text/javascript" src="/third_party/js/knockout-secure-binding.min.js?ver=81a2a3"></script>
<script type="text/javascript" src="/js/course.view.progress.js?ver=d656aa"></script>

<template id="student-list">
<div class="panel">
    <div class="panel-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{#wordsName#}</th>
                    <th>{#wordsNumHomeworks#}</th>
                    <th>{#wordsNumTests#}</th>
                </tr>
            </thead>
            <tbody data-bind="foreach: student">
                <tr>
                    <td><a data-bind="text: name, attr: { href: profile }" /></td>
                    <td data-bind="text: totalHomeworks"></td>
                    <td data-bind="text: totalTests"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</template>

<div class="panel">
    <div class="page-header">
        <h2><span>{#courseStudentsProgress#}</span> <small></small></h2>
    </div>
    <div class="panel-body">                
	    <div 
	    	id="students-list-table"
			data-bind="template: { name: 'student-list',
	                    data: { listName: '{#courseListAdminCurrentCourses#}',
	                            student: getStudentsList() }  }">
		</div>        
    </div> <!-- panel-body -->
</div> <!-- panel -->

