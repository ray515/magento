jQuery(document).ready(function($){
	$("#expanderHead").click(function(){
		$("#expanderContent").slideToggle();
		if ($("#expanderSign").text() == "+"){
			$("#expanderSign").html("−");
		}else{
			$("#expanderSign").text("+");
		}
	});
});