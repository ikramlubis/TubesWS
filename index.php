<?php
//-------------------------------Inisialisasi Setting rdf/rdf--------------------------------
require 'vendor/autoload.php';
//-------------------------------Inisialisasi arah sparql untuk dbpedia akan dijalankan--------------------
$sparql_endpoint = 'https://dbpedia.org/sparql';
$sparql_dbpedia = new \EasyRdf\Sparql\Client($sparql_endpoint);
//-------------------------------Inisialisasi arah sparql untuk rdf (jena fuseki) akan dijalankan----------
$sparql_jena = new \EasyRdf\Sparql\Client('http://localhost:3030/komodo/query');
//-------------------------------Setting namespace--------------------------------------------------------
\EasyRdf\RdfNamespace::set('dbp', 'http://dbpedia.org/property/');
\EasyRdf\RdfNamespace::set('dbo', 'http://dbpedia.org/ontology/');
\EasyRdf\RdfNamespace::set('dbr', 'http://dbpedia.org/resource/');
\EasyRdf\RdfNamespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
\EasyRdf\RdfNamespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
\EasyRdf\RdfNamespace::set('xsd', 'http://www.w3.org/2001/XMLSchema#');
\EasyRdf\RdfNamespace::set('geo', 'http://www.opengis.net/ont/geosparql#');
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
foreach ($result_dbpedia as $row)
{
    $dbpedia = [
        'deskripsi' => $row->deskripsi, //Deskripsi Komodo
        'gambar' => $row->gambar, //Gambar Komodo
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
                bars: 'vertical'
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
            width: 300px;
            height: 200px;
        }

        #chart_div {
            width: 500px;
        }
    </style>
</head>

<body>

    <!-- HERO SECTION OF KOMODO DREGON -->
    <section class="h-100">
        <div class="container h-100 py-5">
            <div class="row h-100 justify-content-between align-items-center">
                <div class="col-lg-5 h-50">
                    <div class="d-flex flex-column h-100 justify-content-between text-center text-lg-left">
                        <h5>Semantic Web Pedia</h5>
                        <h1 class="font-weight-bold">
                            Komodo
                        </h1>
                        <h5 class="text-muted font-weight-normal">
                            Satwa langka 
                        </h5>
                        <div class="">
                            <div class="btn btn btn-primary">
                                Subscribe now
                            </div>
                            <div class="btn btn ml-3 btn-outline-primary">
                                Learn more
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <?php
                        echo "<img src=" . $dbpedia['gambar'] . " class='h-100 w-100'>"; 
                    ?>
                </div>
            </div>
        </div>
    </section>



    <section class="bg-white">
        <div class="container py-5">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <div class="col">
                    <div class="card shadow text-light h-100">
                        <img src="../TubesWS/src/images/sports.jpg" class="card-img h-100 w-100" alt="...">
                        <div class="card-img-overlay">
                            <h5 class="card-title">The Bookworm</h5>
                            <p class="card-text">
                                Delve into the ocean of knowledge
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow text-light text-center h-100">
                        <img src="../TubesWS/src/images/windmill_nightscape.png" class="card-img h-100 w-100" alt="...">
                        <div class="card-img-overlay d-flex flex-column justify-content-center">
                            <h5 class="card-title">The NightSky</h5>
                            <p class="card-text">
                                Explore the billion star hotel
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow text-light text-right h-100">
                        <img src="../TubesWS/src/images/sports.jpg" class="card-img h-100 w-100" alt="...">
                        <div class="card-img-overlay d-flex flex-column justify-content-end">
                            <h5 class="card-title">The Speed Run</h5>
                            <p class="card-text">Keep dem legs movin'</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <div id="map"></div>
    <div id="chart_div"></div>

<?php echo $dbpedia['deskripsi']; 
echo "<img src=" . $rdf['peta'] . ">"; //Memanggil gambar distribusi komodo
echo "<br>";
?>





    <!------------------------------------Inisialisasi Mapbox------------------------------------>
    <script>
        var map = L.map('map').setView([<?= $rdf2['lat'] . "," . $rdf2['long'] ?>], 10);

        L.tileLayer('https://api.maptiler.com/maps/streets-v2/{z}/{x}/{y}.png?key=R502YF4wURp0CyIf120D', {
            attribution: '<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
        }).addTo(map);

        var marker = L.marker([<?= $rdf2['lat'] . "," . $rdf2['long'] ?>]).addTo(map).bindPopup('<?= $rdf2['name'] ?>.')
            .openPopup();
    </script>
    <!------------------------------------------------------------------------------------------->
</body>

</html>