<?php
    require_once( "sparqllib.php" );

    $dbpedia_endpoint = sparql_connect('https://dbpedia.org/sparql');

    sparql_ns( "dbp","http://dbpedia.org/property/" );
    sparql_ns( "dbo","http://dbpedia.org/ontology/" );
    sparql_ns( "dbr","http://dbpedia.org/resource/" );

    $query = '
        select distinct ?film ?runtime ?director where {
        ?film rdf:type dbo:Film.
        ?film dbo:runtime ?runtime.
        ?film dbo:director ?director.
        ?film dbo:director dbr:Robert_Zemeckis.
        Filter(?runtime > 5280).
    }';

    $result = sparql_query($query);
    $fields = sparql_field_array( $result );

    echo "<table class='example_table'>";
    echo "<tr>";
    foreach( $fields as $field)
    {
        echo "<th>$field</th>";
    }
    echo "</tr>";

    while($row = sparql_fetch_array( $result ) )
    {
        echo "<tr>";
        foreach( $fields as $field )
        {
            echo "<td>$row[$field]</td>";
        }

        echo "</tr>";
    }
    echo "</table>";
?>
