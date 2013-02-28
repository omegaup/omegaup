{include file='head.tpl'}
{include file='mainmenu.tpl'}

<div class="post">
	<div class="copy wait_for_ajax" id="contest_details" >
	</div>
</div>
<script>
	(function(){
		//Load Contest details
		omega.getContest("154c8df00fedbe424efc", function(data){
				console.log(data, "tengo data");
				var html = "tengo data";
				$("#contest_details").removeClass("wait_for_ajax").append(html);
			})
	})();
</script>
{include file='footer.tpl'}
