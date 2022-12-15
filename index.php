<?php
//-------------------------------Inisialisasi Setting rdf/rdf--------------------------------
    require 'vendor/autoload.php';
//-------------------------------Inisialisasi arah sparql untuk dbpedia akan dijalankan--------------------
    $sparql_endpoint = 'https://dbpedia.org/sparql';
    $sparql_dbpedia = new \EasyRdf\Sparql\Client($sparql_endpoint);
//-------------------------------Inisialisasi arah sparql untuk rdf (jena fuseki) akan dijalankan----------
    $sparql_jena = new \EasyRdf\Sparql\Client('http://localhost:3030/komodo/query');
//-------------------------------Setting namespace--------------------------------------------------------
   \EasyRdf\RdfNamespace::set( 'dbp','http://dbpedia.org/property/' );
   \EasyRdf\RdfNamespace::set( 'dbo','http://dbpedia.org/ontology/' );
   \EasyRdf\RdfNamespace::set( 'dbr','http://dbpedia.org/resource/' );
   \EasyRdf\RdfNamespace::set( 'rdf','http://www.w3.org/1999/02/22-rdf-syntax-ns#' );
   \EasyRdf\RdfNamespace::set( 'rdfs','http://www.w3.org/2000/01/rdf-schema#' );
   \EasyRdf\RdfNamespace::set( 'xsd','http://www.w3.org/2001/XMLSchema#' );
   \EasyRdf\RdfNamespace::set( 'geo', 'http://www.opengis.net/ont/geosparql#');
   \EasyRDf\RdfNamespace::setDefault('og');
//-------------------------------Query untuk mengambil gambar, deskripsi, gambar komodo dari DBpedia-----------
    $query_dbpedia = "
        Select * WHERE {
        ?hewan rdfs:label 'Komodo dragon'@en.
        ?hewan dbo:abstract ?deskripsi.
        ?hewan dbo:thumbnail ?gambar.
        FILTER( LANG (?deskripsi) = 'en')
        }";

    $result_dbpedia = $sparql_dbpedia->query($query_dbpedia);
  //----------------------------Menyimpan hasil query kedalam array dbpedia[]--------------------  
    $dbpedia = [];
    foreach ( $result_dbpedia as $row) {
        $dbpedia = [
            'deskripsi' => $row->deskripsi, //Deskripsi Komodo
            'gambar' => $row->gambar , //Gambar Komodo
        ];

        break;
    }
    //-----------------------Query untuk mencari populasi komodo dari RDF------------------------
    $sparql_query = "SELECT ?populasi1 ?populasi2 ?populasi3 ?populasi4 ?populasi5 ?rangeMap
    WHERE {
      	?subject dbo:number ?populasi1.
  		?subject dbo:number ?populasi2.
  		?subject dbo:number ?populasi3.
  		?subject dbo:number ?populasi4.
		?subject dbo:number ?populasi5.
        ?subject dbp:rangeMap ?rangeMap.
        FILTER( (?populasi1) = '2430')
        FILTER( (?populasi2) = '2884')
        FILTER( (?populasi3) = '2897')
        FILTER( (?populasi4) = '3023')
        FILTER( (?populasi5) = '3163')
    }";

    $result_rdf = $sparql_jena->query($sparql_query);

    // var_dump($result_rdf);

    $rdf = [];
    //---------------------------menyimpan hasil query populasi ke dalam rdf[]-----------
    foreach($result_rdf as $row) {
        $rdf = [
            'populasi1' => $row->populasi1,
            'populasi2' => $row->populasi2,
            'populasi3' => $row->populasi3,
            'populasi4' => $row->populasi4,
            'populasi5' => $row->populasi5,
            'peta'      => $row->rangeMap,
        ];
    }
    //-----------------------------Query untuk mencari lat, long, name untuk map----------- 
    $sparql_query2 = 'SELECT ?lat ?long ?name WHERE {
        ?subject geo:lat ?lat;
        geo:long ?long;
        dbp:name ?name.
        }';

    $result_rdf2 = $sparql_jena->query($sparql_query2);

    $rdf2 =[];
    //---------------------------Menyimpan hasil query lat,long, name di rdf2[]-----------
    foreach($result_rdf2 as $row) {
    $rdf2 = [
        'lat' => $row->lat,
        'long' => $row->long,
        'name' => $row->name,
    ];
    }

    echo $dbpedia['deskripsi']; //Memanggil Deskripsi Komodo
    echo "<br>";
    echo "<img src=".$dbpedia['gambar'].">"; //Memanggil gambar komodo
    echo "<img src=".$rdf['peta'].">";//Memanggil gambar distribusi komodo


?>
<!DOCTYPE html>
<html>
<head>
    
    <!--------------------------------Setting leaflet.js------------------------------------------->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"/> 
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <!----------------------------------Setting Google Chart--------------------------------------->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {packages: ['corechart', 'bar']});
google.charts.setOnLoadCallback(drawMaterial);

function drawMaterial() {
      var data = google.visualization.arrayToDataTable([
        ['Year', 'Population', { role: 'style' }],
        ['2016', <?= $rdf['populasi1'] ?>, 'color: #FD1D05'],
        ['2017', <?= $rdf['populasi2'] ?>, 'color: #FD7505'],
        ['2018', <?= $rdf['populasi3'] ?>, 'color: #FAFD05'],
        ['2019', <?= $rdf['populasi4'] ?>, 'color: #77FD05'],
        ['2020', <?= $rdf['populasi5'] ?>, 'color: #20FD05']
      ]);

      var materialOptions = {
        chart: {
          title: 'Komodo Population',
        },
        hAxis: {
          title: 'Total Population',
          minValue: 0,
        },
        vAxis: {
          title: 'Year'
        },
        bars: 'vertical'
      };
      var materialChart = new google.charts.Bar(document.getElementById('chart_div'));
      materialChart.draw(data, materialOptions);
    }
    </script>
    <style>
        #map {
            width: 300px;
            height: 200px;
        }

        #chart_div {
            width: 500px;
        }
    </style>
</head>
<body>
<div id="map"></div>
<div id="chart_div"></div>
<!------------------------------------Inisialisasi Mapbox------------------------------------>
<script>
    

    var map = L.map('map').setView([<?= $rdf2['lat'].",".$rdf2['long']?>], 10);

    L.tileLayer('https://api.maptiler.com/maps/streets-v2/{z}/{x}/{y}.png?key=R502YF4wURp0CyIf120D', {
        attribution:'<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
    }).addTo(map);

    var marker = L.marker([<?= $rdf2['lat'].",".$rdf2['long']?>]).addTo(map).bindPopup('<?= $rdf2['name'] ?>.')
    .openPopup();

   
</script>
<!------------------------------------------------------------------------------------------->
</body>
</html>