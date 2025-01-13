import data from './data.js';
import publish from './publish.js'

let init = async function () {
    const geolocation = await data.getGeolocation();

    data.getMeteoData(geolocation.latitude, geolocation.longitude).then((values) => {
        publish.updateWeatherData((values.temperature.sol-273.15).toFixed(1), values.vent_moyen['10m'], values.pluie)
    })

    data.getPollutionData().then((values) => {
        data.getMostRecentNearbyRecord(values.features, geolocation.latitude, geolocation.longitude, 5).then((pollutionData) => {
            publish.updatePollutionData(`${pollutionData.lib_qual} - prélèvement fait le ${unixToDate(pollutionData.date_ech)} à ${pollutionData.lib_zone}`)
        })
    })


    Promise.all([data.getVeloStations(), data.getStationInfo()]).then(([veloStation, veloStationInfo]) => {
        const stationValues = []
        veloStation.forEach((value, index) => {
            stationValues.push({ lat: value.lat, lon: value.lon, title: `${value.name}, Vélos disponibles : ${veloStationInfo.get(index).bikesAvailable}, place de parking disponibles : ${veloStationInfo.get(index).docksAvailable}` })
        })

        publish.setUpMap(geolocation, stationValues)
    })
    
    
}

function unixToDate(unixTimestamp) {
    // Créer un objet Date à partir du timestamp Unix
    const date = new Date(unixTimestamp);

    // Extraire le jour, le mois et l'année
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Les mois sont indexés de 0 à 11
    const year = date.getFullYear();

    // Retourner la date au format "dd-mm-aaaa"
    return `${day}-${month}-${year}`;
}




init();