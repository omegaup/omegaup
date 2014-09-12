$('document').ready(function() {
	$('#problem-search-box').typeahead({
		ajax: { 
			url: "/api/problem/list/",
			preProcess: function(data) { 
				return data["results"];
			}
		},
		display: 'title',
		val: 'title',
		minLength: 3,
		itemSelected: function(item, val, text) {
			$('#problem-search-box').val(val);
		}
	})
});
