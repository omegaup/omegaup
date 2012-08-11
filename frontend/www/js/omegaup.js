var DEBUG = true;





	if(window.TabPage !== undefined){

		

		if(window.location.hash.length == 0){
			//no hay tab seleccionado
			//$('#tab_'+TabPage.tabs[0]).setStyle('display', 'block');
			$('#atab_'+TabPage.tabs[0]).toggleClass('selected');
			TabPage.currentTab = TabPage.tabs[0];

		}else{
			//si hay
			TabPage.currentTab = window.location.hash.substr(1);

			//$('#tab_'+TabPage.currentTab).setStyle('display', 'block');

			$('#atab_'+TabPage.currentTab).toggleClass('selected');
			
		}


		//hide the other ones
		for (var t = TabPage.tabs.length - 1; t >= 0; t--) {

			/* 
					$('#tab_'+TabPage.tabs[t]).setVisibilityMode(Ext.Element.VISIBILITY);
			*/


			
			//TabPage.tabsH[ TabPage.tabs.length - t - 1 ] = $('#tab_'+TabPage.tabs[t]).getHeight()
			//$('#tab_'+TabPage.tabs[t]).setVisibilityMode(Ext.Element.DISPLAY);

			if(TabPage.currentTab == TabPage.tabs[t]) {  continue; }

			$('#tab_'+TabPage.tabs[t]).height(0);
			$('#tab_'+TabPage.tabs[t]).hide();
			
		};

		


		if ( 'onhashchange' in window ) {
			

			window.onhashchange = function() {

				if((TabPage.currentTab.length > 0) && ($('#tab_'+TabPage.currentTab) != null)){
					//ocultar la que ya esta
					
				
					$('#tab_'+TabPage.currentTab).hide();
					$('#tab_'+TabPage.currentTab).height(0);
					$('#atab_'+TabPage.currentTab).toggleClass('selected');


				}

				//currentTab = window.location.hash.substr(1);
				TabPage.currentTab = window.location.hash.substr(1);

				$('#tab_'+TabPage.currentTab).show();
				for (var ti = 0; ti < TabPage.tabs.length; ti++) {
					if( TabPage.tabs[ti] == TabPage.currentTab){
						$('#tab_'+TabPage.currentTab).height('auto');
					
					}
				};
				$('#atab_'+TabPage.currentTab).toggleClass('selected');
			}
		}else{
			console.log('`onhashchange` NOT Available....');

		}

	}//if(window.TabPage !== undefined)
	



/*


$(document).ready(function() {

  	if(DEBUG){
		console.log("JS loaded ! Starting app now...");
	}


	if($("#_start_time").length == 1) {
		$("#_start_time").datetimepicker();
	}
	
	if($("#_finish_time").length == 1) {
		$("#_finish_time").datetimepicker();
	}
});
*/