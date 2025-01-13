const stationStatusUrl = "https://api.cyclocity.fr/contracts/nancy/gbfs/station_status.json";
const stationsVeloUrl = "https://api.cyclocity.fr/contracts/nancy/gbfs/station_information.json";
const basePollutionUrl = "https://services3.arcgis.com/Is0UwT37raQYl9Jj/arcgis/rest/services/ind_grandest/FeatureServer/0/query?where=1%3D1&4:x_wgs84&49:y_wgs84&geometryType=esriGeometryPoint&inSR=4326&spatialRel=esriSpatialRelIntersects&distance=1000&units=esriSRUnit_Meter&returnGeometry=true&outFields=*&f=pjson";
const baseMeteoUrl = "https://www.infoclimat.fr/public-api/gfs/json?_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";

export default {
  stationStatusUrl,
  stationsVeloUrl,
  basePollutionUrl,
  baseMeteoUrl
};
