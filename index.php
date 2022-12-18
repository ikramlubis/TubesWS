<?php
//-------------------------------Inisialisasi Setting rdf/rdf--------------------------------
require 'vendor/autoload.php';
//-------------------------------Inisialisasi arah sparql untuk dbpedia akan dijalankan--------------------
$sparql_endpoint = 'https://dbpedia.org/sparql';
$sparql_dbpedia = new \EasyRdf\Sparql\Client($sparql_endpoint);
//-------------------------------Inisialisasi arah sparql untuk rdf (jena fuseki) akan dijalankan----------
$sparql_jena = new \EasyRdf\Sparql\Client('http://localhost:3030/komodo/query');
//-------------------------------Setting namespace--------------------------------------------------------
\EasyRdf\RdfNamespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
\EasyRdf\RdfNamespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
\EasyRdf\RdfNamespace::set('dbp', 'http://dbpedia.org/property/');
\EasyRdf\RdfNamespace::set('dbo', 'http://dbpedia.org/ontology/');
\EasyRdf\RdfNamespace::set('dbr', 'http://dbpedia.org/resource/');
\EasyRdf\RdfNamespace::set('xsd', 'http://www.w3.org/2001/XMLSchema#');
\EasyRdf\RdfNamespace::set('geo', 'http://www.opengis.net/ont/geosparql#');
\EasyRdf\RdfNamespace::set('foaf', 'http://xmlns.com/foaf/0.1/');
\EasyRdf\RdfNamespace::set('dc', 'http://purl.org/dc/elements/1.1/');
\EasyRDf\RdfNamespace::setDefault('og');

//---------------------------------Mengambil RDF LANGSUNG-------------------------------------------------
$uri_rdf = 'http://localhost/TubesWS/Komodo.rdf';
    $data = \EasyRdf\Graph::newAndLoad($uri_rdf);
    $doc = $data->primaryTopic();
//---------------------------------Mengambil isi tag dc:source dan foaf:homepage di Komodo.rdf-------------    
    $project_url1 = $doc->get('dc:source');
    $project_url2 = $doc->get('foaf:homepage'); 
    $project_url3 = $doc->get('geo:loc'); 

    $ogp1 = \EasyRdf\Graph::newAndLoad($project_url1);
    $ogp2 = \EasyRdf\Graph::newAndLoad($project_url2);
    $ogp3 = \EasyRdf\Graph::newAndLoad($project_url3);
//-------------------------------Query untuk mengambil gambar, deskripsi, gambar komodo dari DBpedia-----------
$query_dbpedia = "
        Select * WHERE {
        ?hewan rdfs:label 'Komodo dragon'@en.
        ?hewan dbo:abstract ?deskripsi.
        ?hewan dbo:thumbnail ?gambar.
        ?hewan dbp:name ?nama.
        FILTER( LANG (?deskripsi) = 'en')
        }";

$result_dbpedia = $sparql_dbpedia->query($query_dbpedia);
//----------------------------Menyimpan hasil query kedalam array dbpedia[]--------------------  
$dbpedia = [];
foreach ($result_dbpedia as $row)
{
    $dbpedia = [
        'deskripsi' => $row->deskripsi, //Deskripsi Komodo
        'gambar' => $row->gambar, //Gambar Komodo
        'nama' => $row->nama,
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
foreach ($result_rdf as $row)
{
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

$rdf2 = [];
//---------------------------Menyimpan hasil query lat,long, name di rdf2[]-----------
foreach ($result_rdf2 as $row)
{
    $rdf2 = [
        'lat' => $row->lat,
        'long' => $row->long,
        'name' => $row->name,
    ];
}




?>


<!DOCTYPE html>
<html>

<head>
    <!-----------------------------------Cascading Style Sheet------------------------------------->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="src/images/css/style.css">

    <!--------------------------------Setting leaflet.js------------------------------------------->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

    <!----------------------------------Setting Google Chart--------------------------------------->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {
            packages: ['corechart', 'bar']
        });
        google.charts.setOnLoadCallback(drawMaterial);

        function drawMaterial() {
            var data = google.visualization.arrayToDataTable([
                ['Year', 'Population', ],
                ['2016', <?= $rdf['populasi1'] ?>, ],
                ['2017', <?= $rdf['populasi2'] ?>, ],
                ['2018', <?= $rdf['populasi3'] ?>, ],
                ['2019', <?= $rdf['populasi4'] ?>, ],
                ['2020', <?= $rdf['populasi5'] ?>, ]
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
                bars: 'horizontal'
            };
            var materialChart = new google.charts.Bar(document.getElementById('chart_div'));
            materialChart.draw(data, materialOptions);
        }
    </script>

    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <style>
        #map {
            width: 100%;
            height: 500px;
            z-index: 0;
        }

        #chart_div {
            width: 100%;
            height: 400px;
        }
        .card-img {
            width: 100%;
            object-fit: cover; 
        }
    </style>
</head>

<body class="bg-peachblue">

    <header class="header">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <nav class="header__menu">
                        <ul>
                            <li><a href="">Semantic WebPedia&ensp;<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-umbrella-fill" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 0a.5.5 0 0 1 .5.5v.514C12.625 1.238 16 4.22 16 8c0 0 0 .5-.5.5-.149 0-.352-.145-.352-.145l-.004-.004-.025-.023a3.484 3.484 0 0 0-.555-.394A3.166 3.166 0 0 0 13 7.5c-.638 0-1.178.213-1.564.434a3.484 3.484 0 0 0-.555.394l-.025.023-.003.003s-.204.146-.353.146-.352-.145-.352-.145l-.004-.004-.025-.023a3.484 3.484 0 0 0-.555-.394 3.3 3.3 0 0 0-1.064-.39V13.5H8h.5v.039l-.005.083a2.958 2.958 0 0 1-.298 1.102 2.257 2.257 0 0 1-.763.88C7.06 15.851 6.587 16 6 16s-1.061-.148-1.434-.396a2.255 2.255 0 0 1-.763-.88 2.958 2.958 0 0 1-.302-1.185v-.025l-.001-.009v-.003s0-.002.5-.002h-.5V13a.5.5 0 0 1 1 0v.506l.003.044a1.958 1.958 0 0 0 .195.726c.095.191.23.367.423.495.19.127.466.229.879.229s.689-.102.879-.229c.193-.128.328-.304.424-.495a1.958 1.958 0 0 0 .197-.77V7.544a3.3 3.3 0 0 0-1.064.39 3.482 3.482 0 0 0-.58.417l-.004.004S5.65 8.5 5.5 8.5c-.149 0-.352-.145-.352-.145l-.004-.004a3.482 3.482 0 0 0-.58-.417A3.166 3.166 0 0 0 3 7.5c-.638 0-1.177.213-1.564.434a3.482 3.482 0 0 0-.58.417l-.004.004S.65 8.5.5 8.5C0 8.5 0 8 0 8c0-3.78 3.375-6.762 7.5-6.986V.5A.5.5 0 0 1 8 0z"/>
                            </svg></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- HERO SECTION OF KOMODO DREGON -->
    <section class="h-100 bg-grblue">
        <div class="container h-100 py-5">
            <div class="row h-100 justify-content-between align-items-center">
                <div class="col-lg-5 h-50">
                    <div class="card bg-blueyellow d-flex flex-column h-100 justify-content-between text-center text-lg-left">
                        <!-- <h5>Semantic Web Pedia</h5> -->
                        <h1 class="font-weight-bold">
                            Komodo
                        </h1>
                        <h5 class=" font-weight-normal">
                            Satwa langka 
                        </h5>
                    </div>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0 ">
                    <?php
                        echo "<img src=" . $dbpedia['gambar'] . " class='h-100 w-100 opac-80 hover'>"; 
                    ?>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-black">
        <div class="container py-5">
            <div class="row">
                <div class="shadow text-white h-100">
                    <h5><?= $dbpedia['nama'] ?></h5>
                    <p class="card-text">&emsp;
                    <?php echo $dbpedia['deskripsi']; ?>
                    </p>
                </div>
            </div><br><br>
            <div class="row">
                <?php echo "<img src=" . $rdf['peta'] . ">"; //Memanggil gambar distribusi komodo
                echo "<br>";
                ?>
            </div>
        </div>
    </section>

    <section id="map">

    </section>

    <section>
    <div class="container py-3">
        <div class="row">
            <div class="col-lg-4 my-5" id="chart_div"></div>
        </div>
    </div>
    </section>

 
    <section class="bg-grblue">
        <div class="container py-5">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <div class="col">
                    <div class="card shadow text-light h-100 opac-80">
                        <img src="<?= $ogp1->image ?>" class="card-img h-100 w-100" alt="...">
                        <div class="card-img-overlay">
                            <h5 class="card-title"><?= $ogp1->site_name ?></h5>
                            <p class="card-text">
                            <p>
                                <?= $ogp1->title ?><p>
                                <p>Sumber: <a href="<?= $ogp1->url ?>" target="_blank"><?= $ogp1->site_name ?></a></p>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow text-light text-center h-100 opac-80">
                        <img src="<?= $ogp2->image ?>" class="card-img h-100 w-100" alt="...">
                        <div class="card-img-overlay d-flex flex-column justify-content-center">
                            <h5 class="card-title"><?= $ogp2->site_name ?></h5>
                            <p class="card-text">
                                <?= $ogp2->title ?>
                                <p>Sumber: <a href="<?= $ogp2->url ?>" target="_blank"><?= $ogp2->site_name ?></a></p>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow text-light text-right h-100 opac-80">
                        <img src="<?= $ogp3->image ?>" class="card-img h-100 w-100" alt="...">
                        <div class="card-img-overlay d-flex flex-column justify-content-end">
                            <h5 class="card-title"><?= $ogp3->site_name ?></h5>
                            <p class="card-text">
                            <?= $ogp3->title ?>
                            <p>Sumber: <a href="<?= $doc->get('geo:loc') ?>" target="_blank">Wikipedia</a></p>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
        


    <!------------------------------------Inisialisasi Mapbox------------------------------------>
    <script>
        var map = L.map('map').setView([<?= $rdf2['lat'] . "," . $rdf2['long'] ?>], 10);

        L.tileLayer('https://api.maptiler.com/maps/openstreetmap/{z}/{x}/{y}.jpg?key=R502YF4wURp0CyIf120D', {
            attribution: '<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
        }).addTo(map);

        var marker = L.marker([<?= $rdf2['lat'] . "," . $rdf2['long'] ?>]).addTo(map).bindPopup('<?= $rdf2['name'] ?>. <br> <p> <?= $doc->get('rdfs:comment') ?> </p>')
            .openPopup();
    </script>
    <!------------------------------------------------------------------------------------------->
</body>

</html>