$('document').ready(function() {
	$(':file').change(function() {
		var file = this.files[0];
		name = file.name;
		size = file.size;
		type = file.type;
	});

	function progressHandlingFunction(e) {
	}

	var formData;

	function addProblemToContest(){
		var a = window.location.pathname.split("/");

		omegaup.addProblemToContest(
			a[a.length-1],
			"alanboy",
			$("#problem_upload_window #alias").val(),
			100,
			function(data){
				console.log("ya llegue de addproblem");
			}
		);
	}

	function sendProb(){
		formData = new FormData($('#newProbForm')[0]);
		formData.append("author_username", "alanboy");
		formData.append("title", $("#problem_upload_window #title").val());
		formData.append("alias", $("#problem_upload_window #alias").val());
		formData.append("source",  $("#problem_upload_window #source").val());
		formData.append("public", "1");
		formData.append("validator", "token"); // token, token-caseless, token-numeric, custom
		formData.append("time_limit",  $("#problem_upload_window #time_limit").val());
		formData.append("memory_limit", $("#problem_upload_window #memory_limit").val());
		formData.append("order", "normal");
		console.log (formData);
		$.ajax({
			url: '/api/problem/create',
			type: 'POST',
			xhr: function() {  // custom xhr
				myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // check if upload property exists
					myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // for handling the progress of the upload
				}
				return myXhr;
			},
			beforeSend: function(){
				console.log("voy a enviar");
			},
			success: function (){
				console.log("ya llegue");
				addProblemToContest();
			},
				//error: errorHandler,
				// Form data
			data: formData,
				//Options to tell JQuery not to process data or worry about content-type
			cache: false,
			contentType: false,
			processData: false
		});

	}

	function renderProblemRow(title, alias, time_limit, memory_limit){
		var html = "";
		html = "<div class='problem-row'>"
			+ "<div class='ptitle'>" + title + "</div>"
			+ "<div class='alias'>" + alias + "</div>"
			+ "<div class='time_limit'>" + time_limit + "</div>"
			+ "<div class='memory_limit'>" + memory_limit + "</div>"
			+ "</div>";
		return html;
	}

	//Load Contest details
	var a = window.location.pathname.split("/");
	omegaup.getContest(a[a.length-1], function(data){
		if( data.status == "error" ){
			switch(data.errorcode){
				case 400:
				case 403:
					$(".hiddeable_on_error").hide();
					$(".showable_on_error").show();
					break;
				default:
			}
		}else{
			var html = "";
			$("#contest_details").removeClass("wait_for_ajax").append(html);
			for(var i in data) {
				$("#main #" + i).val(data[i])
			}

			$("#problem_details").removeClass("wait_for_ajax");
			for(var i in data.problems){
				console.log("agregando problema", data.problems[i]);
				$("#problem_details").append(renderProblemRow(
							data.problems[i].title,
							data.problems[i].alias,
							data.problems[i].time_limit,
							data.problems[i].memory_limit
							));
			}
		}
	});
});
