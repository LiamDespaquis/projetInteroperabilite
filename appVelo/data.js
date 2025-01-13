import consts from './const.js'; 

const getGeolocation = async function() {
    return new Promise((resolve, reject) => {
      // Vérifier si la géolocalisation est disponible dans le navigateur
      if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            // Les coordonnées sont directement en WGS 84
            const { latitude, longitude } = position.coords;
            resolve({ latitude, longitude });
          },
          (error) => {
            // Gérer les erreurs
            reject(`Erreur de géolocalisation: ${error.message}`);
          }
        );
      } else {
        reject("La géolocalisation n'est pas supportée par ce navigateur.");
      }
    });
  }
  


const getMeteoData = async function(lat, lon) {
        const url = `${consts.baseMeteoUrl}&_ll=${lat},${lon}`;
          const response = await fetchData(url)
      
          const data = await response.json();
        const date = getPreviousAvailableTime()
        console.log(date)
        return data[date]
}

const getVeloStations = async function() {
    return fetchData(consts.stationsVeloUrl)
        .then(response => response.json()) 
        .then(data => {
            const stationDataMap = new Map();

            if (data && data.data && data.data.stations) {

                data.data.stations.forEach(station => {
                    const idStation = station.station_id;
                    const lat = station.lat;
                    const lon = station.lon;
                    const name = station.name;

                    stationDataMap.set(idStation, {
                        name: name,
                        lat: lat,
                        lon: lon
                    });
                });
            }

            return stationDataMap; 
        })
        .catch(error => {
            console.error("Erreur lors de la récupération des données :", error);
            return new Map(); // Retourne une Map vide en cas d'erreur
        });
}

const getStationInfo = async function() {

    return fetchData(consts.stationStatusUrl)
        .then(response => response.json()) 
        .then(data => {
            const stationDataMap = new Map();

            if (data && data.data && data.data.stations) {

                data.data.stations.forEach(station => {
                    const idStation = station.station_id;
                    const numBikesAvailable = station.num_bikes_available;
                    const numDocksAvailable = station.num_docks_available;

                    stationDataMap.set(idStation, {
                        bikesAvailable: numBikesAvailable,
                        docksAvailable: numDocksAvailable
                    });
                });
            }

            return stationDataMap; 
        })
        .catch(error => {
            console.error("Erreur lors de la récupération des données :", error);
            return new Map(); // Retourne une Map vide en cas d'erreur
        });
}

const getPollutionData = async function() {

}

const fetchData = async function(url) {
    return fetch(url)
}
function getPreviousAvailableTime() {
    const now = new Date();
    const availableHours = [1, 4, 7, 10, 13, 16, 19, 22];
    let currentHour = now.getHours();
  
    let prevHour = availableHours.reverse().find(hour => hour < currentHour);
  
    if (!prevHour) {
      prevHour = availableHours[availableHours.length - 1];
      now.setDate(now.getDate() - 1);
    } else {
      now.setHours(prevHour+1, 0, 0, 0);
    }
  
    const formattedDate = now.toISOString().slice(0, 19).replace("T", " ");
    return formattedDate;
  }
  
  
  
export default {
    getGeolocation,
    fetchData,
    getMeteoData,
    getVeloStations,
    getStationInfo,
    getPollutionData
  };