<?php

// TODO include_once('includes/authentication.php');

// TODO $configurationSession = new Zend_Session_Namespace('configuration');

parse_str(ltrim($_SERVER["QUERY_STRING"],'?'), $query); //recupere la requete envoyée partie (GET params)...
$query = array_change_key_case($query, CASE_UPPER); // force les clés en majuscule
$queryParamsAllow = array(//paramNom => requis
    'BBOX' ,
    'LAYERS' ,
    'EXCEPTIONS' ,
    'SRS' ,
    'CRS' ,
    'FORMAT' ,
    'WIDTH' ,
    'HEIGHT' ,
    'PROVIDER_ID' ,// TODO: use the query parameter
    'PLOT_CODE' ,// TODO: use the query parameter
    'TRANSPARENT' ,
    'VERSION' ,
    'STYLES' ,
	'QUERY_LAYERS' ,
	'X' ,
	'Y' ,
	'INFO_FORMAT' ,
	'HASSLD' ,
	'SERVICE' ,
	'REQUEST' ,
	'LAYER' ,
	'MAP.SCALEBAR',
    'OUTPUTFORMAT',
    'TYPENAME',
    'SRSNAME'
);

// Vérifie que les paramètres sont dans la liste des ceux autorisés
$queriesArg = array();
foreach($queryParamsAllow as $param) {
    if (isset($query[$param])){
        $queriesArg[$param] = $query[$param];
    }
}
// force la valeur de REQUEST
if (strcasecmp($queriesArg['REQUEST'] , "getlegendgraphic") == 0) {
	$queriesArg['REQUEST']  = 'GetLegendGraphic';
} else if (strcasecmp($queriesArg['REQUEST'] , "getmap") == 0) {
	$queriesArg['REQUEST']  = 'GetMap';
} else {
    $queriesArg['REQUEST']  = 'GetFeature';
}

// force la valeur de SERVICE
$geoJSONOFRequired = false;
if (strcasecmp($queriesArg['SERVICE'] , "WFS") !== 0) {
    header('Content-Type: image/png');
    $queriesArg['SERVICE']  = 'WMS';
} elseif (strcasecmp($queriesArg['OUTPUTFORMAT'] , "geojsonogr") === 0 || strcasecmp($queriesArg['OUTPUTFORMAT'] , "geojsontpl") === 0) {
    $geoJSONOFRequired = true;
    header('Content-Type: application/json,subtype=geojson,charset=utf-8');
}

// force la valeur de SESSION_ID
session_start();
$queriesArg['SESSION_ID'] = session_id();
session_abort();

header('Access-Control-Allow-Origin: *');

// TODO Set the url
//$url = $configurationSession->configuration['mapserver_private_url'];
$url = 'http://localhost/mapserv-ogam?';

if (empty($url)) {
	error_log("URL not set, check the configuration of the mapserver_private_url parameter and the bootstrap");
	echo "URL not set, check the configuration of the mapserver_private_url parameter and the bootstrap";
	exit;
}

// Set the uri (url + urn)
$uri = rtrim($url,'?').'?'.http_build_query($queriesArg);

//echo $uri;exit;
//error_log($uri);

$content = file_get_contents($uri);
if ($content !== FALSE) {
    if ($content === "" && $geoJSONOFRequired) { // BugFix: gdal-bin 1.10 OGR driver return nothing when there are no feature
        echo '{
            "type": "FeatureCollection",
            "features": []
        }';
    } else {
        echo $content;
    }
}