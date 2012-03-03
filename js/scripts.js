$(document).ready(function(){
	function pingServer(){
		$.get('ajax/ping.php').complete(setTimeout(pingServer, 60000));
	}
	if(window.ping){
		pingServer();
	}	
	$('.vm_disabled').bind('click',function(){
		var elem = $(this);
		if(elem.attr('disabled')){
			var pass = prompt("Geben sie ein VNC Passwort ein.");
			if(pass != ""){
				var vmID = elem.attr("href").substring(1);
				$.get('ajax/setvnc.php?vmID='+vmID+'&pass='+pass,function(data) {
					console.log(data,elem);
					if(data == "OK"){
						elem.attr("disabled","");
					}
				});
			}
		}
	});
});

function addRow(table){
	var rows = table.find(":not(thead) tr");
	var org_row = $(rows[0]);
	if(org_row.find("select") && org_row.find("select").find("option").length == rows.length){
		return;
	}
	org_row = org_row.clone();
	var span = org_row.find('span')[0];
	$(span).html(rows.length+1);
	table.find("tr:last").after(org_row);
}

function refreshUSB(){
	$.get('ajax/refresh_usb.php',function(data) {
		$('#usb_device').html(data);
	});
}

function updateFormBySelect(form){
	form = $(form);
	var name = form.attr('name');
	var val = form.val();
	$('.switchable').addClass('hide');
	form.find("option").each(function(e){
		var elem = $(this);
		var elem_val = elem.val();
		if(val == elem_val){
			if($('#form_'+name+'_'+elem_val).length){
				$('#form_'+name+'_'+elem_val).removeClass('hide');
			}
			else{
				$('#form_'+name+'_default').removeClass('hide');
			}
			return false;
		}
	});
}