import data from './data.js';
import publish from './publish.js'

let init = async function () {
    const geolocation = await data.getGeolocation();

    data.getMeteoData(geolocation.latitude, geolocation.longitude).then((values) => {
        publish.updateWeatherData((values.temperature.sol-273.15).toFixed(1), values.vent_moyen['10m'], values.pluie)
    })


    Promise.all([data.getVeloStations(), data.getStationInfo()]).then(([veloStation, veloStationInfo]) => {
        const stationValues = []
    veloStation.forEach((value, index) => {
        stationValues.push({ lat: value.lat, lon: value.lon, title: `${value.name}, Vélos disponibles : ${veloStationInfo.get(index).bikesAvailable}, place de parking disponibles : ${veloStationInfo.get(index).docksAvailable}` })
    })

    publish.setUpMap(geolocation, stationValues)
    })
    
    
}




init();