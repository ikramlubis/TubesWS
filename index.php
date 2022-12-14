<?php
    require ('vendor/autoload.php');

    $sparql_endpoint = 'https://dbpedia.org/sparql';
    $sparql = new \EasyRdf\Sparql\Client($sparql_endpoint);

   \EasyRdf\RdfNamespace::set( 'dbp','http://dbpedia.org/property/' );
   \EasyRdf\RdfNamespace::set( 'dbo','http://dbpedia.org/ontology/' );
   \EasyRdf\RdfNamespace::set( 'dbr','http://dbpedia.org/resource/' );
   \EasyRdf\RdfNamespace::set( 'rdf','http://www.w3.org/1999/02/22-rdf-syntax-ns#' );
   \EasyRdf\RdfNamespace::set( 'rdfs','http://www.w3.org/2000/01/rdf-schema#' );
   \EasyRdf\RdfNamespace::set( 'xsd','http://www.w3.org/2001/XMLSchema#' );

    $query = "
        Select * WHERE {
        ?hewan rdfs:label 'Komodo dragon'@en.
        ?hewan dbo:abstract ?deskripsi.
        ?hewan dbo:thumbnail ?gambar.
        FILTER( LANG (?deskripsi) = 'en')
        }";

    $result = $sparql->query($query);
    
    $detail = [];
    foreach ( $result as $row) {
        $detail = [
            'deskripsi' => $row->deskripsi, //Deskripsi Komodo
            'gambar' => $row->gambar , //Gambar Komodo
        ];

        break;
    }

    echo $detail['deskripsi']; //Memanggil Deskripsi Komodo
    echo "<br>";
    echo "<img src=".$detail['gambar'].">" //Memanggil gambar komodo

?>
<!DOCTYPE html>
<html>
<head>
    <!--Setting leaflet.css-->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"/> 
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <!----------------------->
    <style>
        #map {
            width: 300px;
            height: 200px;
        }
    </style>
</head>
<body>
<div id="map"></div>
<script>
    //------------------------------------Inisialisasi Mapbox------------------------------------

    var map = L.map('map').setView([51.5, -0.09], 18);

    L.tileLayer('https://api.maptiler.com/maps/streets-v2/{z}/{x}/{y}.png?key=R502YF4wURp0CyIf120D', {
        attribution:'<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
    }).addTo(map);

    var marker = L.marker([51.5, -0.09]).addTo(map);

    //-------------------------------------------------------------------------------------------
</script>
</body>
</html>