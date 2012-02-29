$(document).ready(function(){
	function pingServer(){
		$.get('ajax/ping.php').complete(setTimeout(pingServer, 60000));
	}
	if(window.ping){
		pingServer();
	}	
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