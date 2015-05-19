<link rel="stylesheet" href="plugins/leaflet/leaflet.css" />
<div class="row">
	<div class="col-xs-12">
		<div class="box">
			<div id="full-map" class="box-content fullscreenmap">
			</div>
		</div>
	</div>
</div>
<script>
listMarker = [];
// Create a function that the hub can call to broadcast messages.
chat.client.getPos = function (uid, pos) {
	var latlng = pos.split(',');

	if(listMarker[uid] == undefined) {
	    marker = L.marker([latlng[0], latlng[1]], {icon: myIcon}).addTo(map);
	    listMarker[uid] = marker;
	} else {
	    listMarker[uid].setLatLng([latlng[0], latlng[1]]);
	    listMarker[uid].update();
	}

};
$.connection.hub.start().done(function () {
	chat.server.connect("0");
});

// Dynamically load  Leaflet Plugin
// homepage: http://leafletjs.com
//
function LoadLeafletScript(callback){
	if (!$.fn.L){
		$.getScript('plugins/leaflet/leaflet.js', callback);
	}
	else {
		if (callback && typeof(callback) === "function") {
			callback();
		}
	}
}

/*-------------------------------------------
	Function for Fullscreen Leaflet map page (map_leaflet.html)
---------------------------------------------*/
//
// Create Leaflet Fullscreen Map
//
function FullScreenLeafletMap(){
	map = L.map('full-map').setView([16.435077, 107.631705], 13);
		L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
		maxZoom: 18
	}).addTo(map);

	myIcon = L.icon({
	 iconUrl: 'http://iconizer.net/files/Google_Maps_Icons/orig/motorbike.png',
	});
}
// Add class for fullscreen view
$('#content').addClass('full-content');
// Set height of block
SetMinBlockHeight($('.fullscreenmap'));
// Run Leaflet
LoadLeafletScript(FullScreenLeafletMap);
$(document).ready(function() {
	
});
</script>