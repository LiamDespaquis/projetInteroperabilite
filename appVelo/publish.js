function updateWeatherData(temperature, wind, rain) {
    document.getElementById('temperature').textContent = temperature;
    document.getElementById('wind').textContent = wind;
    document.getElementById('rain').textContent = rain;
}

// Fonction pour mettre à jour les informations de pollution
function updatePollutionData(pollutionLevel) {
    document.getElementById('pollutionLevel').textContent = pollutionLevel;
}

let setUpMap = async function( startingPoint, stationValues) {
    var map = L.map('map').setView([startingPoint.latitude, startingPoint.longitude], 14); // Position initiale : Nancy (latitude, longitude)

        // Ajouter une couche de tuiles (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        stationValues.push({ lat: startingPoint.latitude, lon: startingPoint.longitude, title: 'Votre Position'})
        // Ajouter des marqueurs pour chaque point
        stationValues.forEach(function(point) {
            L.marker([point.lat, point.lon])
                .addTo(map)
                .bindPopup(point.title) // Affiche un titre dans une popup
                .openPopup();
        });
        window.addEventListener('resize', function() {
            map.invalidateSize();
          });
}

export default {
    updatePollutionData, 
    updateWeatherData, 
    setUpMap
}