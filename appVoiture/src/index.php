<?php

namespace Gaetan\AppVoiture;

use DOMDocument;
use XSLTProcessor;

require_once __DIR__ . "/../vendor/autoload.php";

$xsltMeteo = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html" doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd" indent="yes" />

  <xsl:template match="/">
    <html>
      <head>
        <title>Prévisions Météo</title>
        <style>
          body { font-family: Arial, sans-serif; }
          table { width: 100%; border-collapse: collapse; margin: 20px 0; }
          th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
          th { background-color: #f4f4f4; }
        </style>
      </head>
      <body>
        <h1>Prévisions Météo</h1>
        <table>
          <thead>
            <tr>
              <th>Heure</th>
              <th>Température (°C)</th>
              <th>Humidité</th>
              <th>Vent Moyen (10m)</th>
              <th>Rafales (10m)</th>
              <th>Direction du Vent</th>
              <th>Risque de Pluie</th>
              <th>Risque de Neige</th>
            </tr>
          </thead>
          <tbody>
            <xsl:apply-templates select="previsions/echeance[position() &lt;= 8]"/>
          </tbody>
        </table>
      </body>
    </html>
  </xsl:template>

  <xsl:template match="echeance">
    <tr>
      <td>
        <xsl:value-of select="substring(@timestamp, 12, 5)"/>h, <xsl:value-of select="substring(@timestamp, 9, 2)"/> 
        <xsl:choose>
          <xsl:when test="substring(@timestamp, 6, 2) = '01'">Janvier</xsl:when>
          <xsl:when test="substring(@timestamp, 6, 2) = '02'">Février</xsl:when>
          <xsl:when test="substring(@timestamp, 6, 2) = '03'">Mars</xsl:when>
          <xsl:when test="substring(@timestamp, 6, 2) = '04'">Avril</xsl:when>
          <xsl:when test="substring(@timestamp, 6, 2) = '05'">Mai</xsl:when>
          <xsl:when test="substring(@timestamp, 6, 2) = '06'">Juin</xsl:when>
          <xsl:when test="substring(@timestamp, 6, 2) = '07'">Juillet</xsl:when>
          <xsl:when test="substring(@timestamp, 6, 2) = '08'">Août</xsl:when>
          <xsl:when test="substring(@timestamp, 6, 2) = '09'">Septembre</xsl:when>
          <xsl:when test="substring(@timestamp, 6, 2) = '10'">Octobre</xsl:when>
          <xsl:when test="substring(@timestamp, 6, 2) = '11'">Novembre</xsl:when>
          <xsl:otherwise>Décembre</xsl:otherwise>
        </xsl:choose>
        <xsl:value-of select="substring(@timestamp, 1, 4)"/>
      </td>
      <td>
        <xsl:value-of select="format-number(temperature/level[@val='2m'] - 273.15, '#.0')"/> °C
      </td>
      <td>
        <xsl:value-of select="humidite/level[@val='2m']"/> %
      </td>
      <td>
        <xsl:value-of select="vent_moyen/level[@val='10m']"/> km/h
      </td>
      <td>
        <xsl:value-of select="vent_rafales/level[@val='10m']"/> km/h
      </td>
      <td>
        <xsl:choose>
          <xsl:when test="vent_direction/level[@val='10m'] &lt; 22.5 or vent_direction/level[@val='10m'] &gt;= 337.5">Nord</xsl:when>
          <xsl:when test="vent_direction/level[@val='10m'] &gt;= 22.5 and vent_direction/level[@val='10m'] &lt; 67.5">Nord-Est</xsl:when>
          <xsl:when test="vent_direction/level[@val='10m'] &gt;= 67.5 and vent_direction/level[@val='10m'] &lt; 112.5">Est</xsl:when>
          <xsl:when test="vent_direction/level[@val='10m'] &gt;= 112.5 and vent_direction/level[@val='10m'] &lt; 157.5">Sud-Est</xsl:when>
          <xsl:when test="vent_direction/level[@val='10m'] &gt;= 157.5 and vent_direction/level[@val='10m'] &lt; 202.5">Sud</xsl:when>
          <xsl:when test="vent_direction/level[@val='10m'] &gt;= 202.5 and vent_direction/level[@val='10m'] &lt; 247.5">Sud-Ouest</xsl:when>
          <xsl:when test="vent_direction/level[@val='10m'] &gt;= 247.5 and vent_direction/level[@val='10m'] &lt; 292.5">Ouest</xsl:when>
          <xsl:otherwise>Nord-Ouest</xsl:otherwise>
        </xsl:choose>
      </td>
      <td>
        <xsl:value-of select="pluie"/> mm
      </td>
      <td>
        <xsl:value-of select="risque_neige"/>
      </td>
    </tr>
  </xsl:template>

</xsl:stylesheet>

END;

$urlAPILoc = "http://ip-api.com/xml/";
$urlApiInfoStation = "https://api.cyclocity.fr/contracts/nancy/gbfs/station_information.json";
if(isset($_SERVER['REMOTE_ADDR'])) {
    $ipClient = $_SERVER['REMOTE_ADDR'];
} else {
    $ipClient = "193.50.135.206"; //nancy
}

$ch = curl_init();

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
curl_setopt($ch, CURLOPT_HEADER, false); // Optional: Exclude the header in the output

curl_setopt($ch, CURLOPT_PROXY, 'www-cache'); // Proxy address
curl_setopt($ch, CURLOPT_PROXYPORT, 3128); // Proxy port
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); // Proxy type (HTTP)

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable peer verification
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable host verification


$latIut = 48.68285708780425;
$longIut = 6.161036265989825;

curl_setopt($ch, CURLOPT_URL, $urlAPILoc. $ipClient); // Set the URL

$res = curl_exec($ch);

if ($res === false) {
    $lat = $latIut;
    $lon = $longIut;
} else {
    $xml = simplexml_load_string($res);
    /*var_dump($xml);*/

    $lat = $xml->lat;
    $lon = $xml->lon;
    $ville = $xml->city;
    if ($ville != "Nancy") {
        $lat = $latIut;
        $lon = $longIut;
    }
}

$loc = "$lat,$lon";
$urlInfoClimat = "https://www.infoclimat.fr/public-api/gfs/xml?_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2&_ll=";

curl_setopt($ch, CURLOPT_URL, $urlInfoClimat.$loc);
$resInfoClimat = curl_exec($ch);
if($resInfoClimat === false) {
    $htmlMeteo = "<p>Erreur lors de la récupération des données météo</p>";
} else {
    $xmlMeteo = simplexml_load_string($resInfoClimat);
    /*var_dump($xmlMeteo->echeance[2]);*/
    $xsltProcessor = new XSLTProcessor();
    $styleDomDocument = new DOMDocument();
    $styleDomDocument->loadXML($xsltMeteo);
    $xsltProcessor->importStylesheet($styleDomDocument);
    $htmlMeteo = $xsltProcessor->transformToDoc($xmlMeteo)->saveHTML();
}

$urlPollution = "https://services3.arcgis.com/Is0UwT37raQYl9Jj/arcgis/rest/services/ind_grandest/FeatureServer/0/query?where=lib_zone%3D%27Nancy%27&objectIds=&time=&geometry=&geometryType=esriGeometryEnvelope&inSR=&spatialRel=esriSpatialRelIntersects&resultType=none&distance=0.0&units=esriSRUnit_Meter&returnGeodetic=false&outFields=*&returnGeometry=true&featureEncoding=esriDefault&multipatchOption=xyFootprint&maxAllowableOffset=&geometryPrecision=&outSR=&datumTransformation=&applyVCSProjection=false&returnIdsOnly=false&returnUniqueIdsOnly=false&returnCountOnly=false&returnExtentOnly=false&returnQueryGeometry=false&returnDistinctValues=false&cacheHint=false&orderByFields=&groupByFieldsForStatistics=&outStatistics=&having=&resultOffset=&resultRecordCount=&returnZ=false&returnM=false&returnExceededLimitFeatures=true&quantizationParameters=&sqlFormat=none&f=pjson&token=";
curl_setopt($ch, CURLOPT_URL, $urlPollution);
$resPollution = curl_exec($ch);
if($resPollution === false) {
    $qualiteAirHtml = "<p>Erreur lors de la récupération des données de pollution</p>";
} else {
    $jsonPollution = json_decode($resPollution, true);
    /*var_dump($jsonPollution);*/
    $long = count($jsonPollution["features"]);
    $attribut = $jsonPollution["features"][$long - 1]["attributes"];
    $qualite = $attribut["lib_qual"];
    $coul = $attribut["coul_qual"];

    $qualiteAirHtml = <<<END

  <h2>Qualité de l'air à Nancy</h2>
  <p style="color:$coul"><b>$qualite</b></p>


END;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
crossorigin=""/>
 <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
</head>

<body>
    <h1>Map des points de difficultés dans Nancy</h1>
<div id="map" style="height:37em"></div>
    <?php echo $htmlMeteo ?>
    <?php echo $qualiteAirHtml ?>
</body>
<script>
<?php
echo "let lat = $lat;\n";
echo "let lon = $lon;\n";
?>
let map = L.map('map').setView([lat, lon], 13);
let marker = L.marker([lat, lon]).addTo(map);
marker.bindPopup("Vous êtes ici").openPopup();

  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);
let markerIncident = null;
<?php
$urlIncident = "https://carto.g-ny.org/data/cifs/cifs_waze_v2.json";
curl_setopt($ch, CURLOPT_URL, $urlIncident);
$jsonIncident = curl_exec($ch);
if($jsonIncident !== false) {
    $incidents = json_decode($jsonIncident, true);
    //var_dump($incidents);
    foreach($incidents["incidents"] as $incident) {
        $latLong = explode(" ", $incident["location"]["polyline"]);
        $latIncidents = $latLong[0];
        $longIncidents = $latLong[1];
        $date = new \DateTime($incident["creationtime"]);
        $date = $date->format('d/m/Y');
        $desc = $incident["short_description"];
        $rue = $incident["location"]["street"];
        echo "markerIncident = L.marker([$latIncidents, $longIncidents]).addTo(map);\n";
        echo "markerIncident.bindPopup(`$desc\\n$rue\\n $date`);\n";
    }
}
?>

</script>
<?php
curl_close($ch);
?>
