(function($,Drupal, drupalSettings,google,MarkerClusterer){

    $(document).ready(function(){

        var map = null;
        var markerCluster = null;
        //auckland
        var centrePoint = new google.maps.LatLng(-36.8484597, 174.7633315);
        var defaultZoom = 7;
        var path_bounds = null;

        $(".netbyte-locator-filter").on("click",function(){

            var type = $(this).data('locator-filter');
            markerCluster.clearMarkers();
            getAllPositions(map, type);
        });

        function getAllPositions(map, type)
        {
            var url = "/locator/" + type;

            $.ajax(url).done(function(data){

                var markers = [];

                var infowindow = new google.maps.InfoWindow({
                    content: 'holding ...'
                });

                path_bounds = new google.maps.LatLngBounds();

                for(var x in data) {


                    var html = getInforWindowText(data[x]);
                    var latLng = new google.maps.LatLng(data[x].lat, data[x].lng);
                    var label = data[x].title + "("+data[x].type+")";

                    path_bounds.extend(latLng);

                    var marker = new MarkerWithLabel({'position': latLng, 'html':html,
                        map: map,
                        labelContent: label,
                        labelClass: "labels", // the CSS class for the label
                        labelStyle: {opacity: 0.75}
                    });


                    google.maps.event.addListener(marker, 'click', function () {
                        infowindow.setContent(this.html);
                        infowindow.open(map, this);
                    });

                    markers.push(marker);
                }

                markerCluster = new MarkerClusterer(map, markers);
                resize();
            });
        }

        function getInforWindowText(data)
        {

            var html = "<div class='infor-window'>";
              html += "<h5> " + data.title + "("+data.type+")" + "</h5>";
              html += "<article>";
                html += "<address>";
                html += "Street: " + data.number + " " + data.street + "<br /> " + data.suburb+ "<br /> " + data.city;
                html += "<br /> Phone: " + data.phone;
                html += "</address>";
              html += "</article>";
            html += "</div>";
            return html;
        }

        function resize() {

            if (path_bounds) {
                map.fitBounds(path_bounds);
                //fitbounds ignore zoom level, following listener reset zoom level.
                var listener = google.maps.event.addListenerOnce(map, "idle", function() {
                    map.setZoom(defaultZoom);
                });
            }
        }


        function initialize() {
            var mapCanvas = document.getElementById('netbyte_locator_map');
            var mapOptions = {
                //center: centrePoint,
                zoom: defaultZoom,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(mapCanvas, mapOptions);
            getAllPositions(map, "all");
        }

        google.maps.event.addDomListener(window, 'load', initialize);
        google.maps.event.addDomListener(window, 'resize', resize);

    });



})(jQuery,Drupal, drupalSettings,google,MarkerClusterer);
