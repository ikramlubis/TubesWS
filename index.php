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

    echo $detail['deskripsi'];
    echo "<br>";
    echo "<img src=".$detail['gambar'].">"

?>
