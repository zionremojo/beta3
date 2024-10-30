// Remove preloader only after a country is rendered on the map
function hidePreloader() {
    if ($("#preloader").length) {
        $("#preloader").fadeOut("slow", function () {
            $(this).remove();
        });
    }
}

// Update Country by Coordinates
function updateCountryByCoords(lat, lng) {
    $.ajax({
        url: "php/geocodeCountry.php",
        type: 'POST',
        dataType: 'JSON',
        data: {
            lat: lat,
            lng: lng
        },
        success: function (result) {
            if (result.status.name == "ok") {
                $('#countrySelect').val(result['data']['countryCode']).change();
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            hidePreloader(); // Ensure preloader hides even if there's an error
        }
    });
}

// Tile Layers
const streets = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 19,
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012'
});

const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
});

// Base layers for toggling between Streets and Satellite
const basemaps = {
    "Streets": streets,
    "Satellite": satellite
};

// Marker cluster groups
const cityMarkers = L.markerClusterGroup();
const airportMarkers = L.markerClusterGroup();
const earthquakeMarkers = L.markerClusterGroup();

// Initialize the map with Streets as the default layer
let map = L.map('map', {
    layers: [streets] // Set default to Streets
}).fitWorld();

// Layer Control for basemaps and overlays (City Markers)
const overlayMaps = {
    "Cities": cityMarkers, // Overlay for city markers
    "Airports": airportMarkers,
    "Earthquakes": earthquakeMarkers
};

// Add layer control to allow users to switch between Street/Satellite and toggle City Markers
L.control.layers(basemaps, overlayMaps).addTo(map);

// Fetch and populate the country dropdown
$.ajax({
    type: "GET",
    url: "php/getCountries.php",
    data: "",
    dataType: "json",
    success: (response) => {
        let countryInfo = response;
        countryInfo = Object.values(countryInfo).sort((a, b) =>
            a.name.localeCompare(b.name)
        );

        let str = "";
        for (let i = 0; i < countryInfo.length; i++) {
            const country = countryInfo[i];
            str += `<option value="${country.code}">${country.name}</option>`;
        }

        $("#countrySelect").append(str);
        getUserLocation(); // Start geolocation after countries are loaded
    },
    error: () => {
        Toastify({
            text: "Error loading countries",
            duration: 2000,
            newWindow: true,
            close: true,
            gravity: "top",
            position: "center",
            backgroundColor: "red",
        }).showToast();
        hidePreloader(); // Ensure preloader hides if there's an error fetching countries
    },
});

// Event listener for country selection from dropdown
$('#countrySelect').on('change', function () {
    const selectedCountryCode = $(this).val();
    if (!selectedCountryCode) return; // Exit if no country is selected

    map.eachLayer(function (layer) {
        if (layer instanceof L.GeoJSON || layer instanceof L.MarkerClusterGroup) {
            map.removeLayer(layer); // Remove previously highlighted country and markers
        }
    });

    // Fetch country border and related data
    $.ajax({
        url: 'php/getCountryBorder.php',
        type: 'POST',
        dataType: 'json',
        data: { countryCode: selectedCountryCode },
        success: function(response) {
            if (response.status.name === 'ok') {
                // Adding the country border to the map
                const countryFeature = response.data;
                const bounds = countryFeature.bounds; // Assuming the bounds are sent in the response
                const geoJsonLayer = L.geoJSON(countryFeature, {
                    style: { color: 'blue', weight: 2 }
                }).addTo(map);
                map.fitBounds(geoJsonLayer.getBounds());

                // Load markers within the bounds
                loadCityMarkers(bounds);
                loadAirportMarkers(bounds);
                loadEarthquakeMarkers(bounds);

                hidePreloader();  // Hide preloader after rendering
            } else {
                hidePreloader();  // Hide preloader if an error occurs
            }
        },
        error: function() {
            hidePreloader();  // Ensure preloader hides if there is an error
        }
    });
});

function loadCityMarkers(bounds) {
    $.ajax({
        url: 'php/getCities.php',
        type: 'POST',
        dataType: 'json',
        data: {
            north: bounds.north,
            south: bounds.south,
            east: bounds.east,
            west: bounds.west
        },
        success: function (response) {
            if (response.status.name === 'ok') {
                const markers = L.markerClusterGroup();
                response.data.forEach(city => {
                    const marker = L.marker([city.lat, city.lng], {
                        icon: L.ExtraMarkers.icon({
                            prefix: 'fa',
                            icon: 'fa-city',
                            markerColor: 'green',
                            svg: true,
                            shape: 'square',
                            iconColor: '#fff'
                        })
                    }).bindPopup(`<strong>${city.name}</strong><br>Population: ${city.population}`);
                    markers.addLayer(marker);
                });
                map.addLayer(markers);
            }
        },
        error: function () {
            console.error("Error fetching city data");
        }
    });
}

function loadAirportMarkers(bounds) {
    $.ajax({
        url: 'php/getAirports.php',
        type: 'POST',
        dataType: 'json',
        data: {
            north: bounds.north,
            south: bounds.south,
            east: bounds.east,
            west: bounds.west
        },
        success: function(response) {
            if (response.status.name === 'ok') {
                const markers = L.markerClusterGroup();
                response.data.forEach(airport => {
                    const marker = L.marker([airport.lat, airport.lng], {
                        icon: L.ExtraMarkers.icon({
                            prefix: 'fa',
                            icon: 'fa-plane',
                            markerColor: 'orange',
                            svg: true,
                            shape: 'square',
                            iconColor: '#fff'
                        })
                    }).bindPopup(`<strong>${airport.name}</strong>`);
                    markers.addLayer(marker);
                });
                map.addLayer(markers);
            } else {
                console.error("Error retrieving airport data");
            }
        },
        error: function() {
            console.error("Error fetching airport data");
        }
    });
}

function loadEarthquakeMarkers(bounds) {
    $.ajax({
        url: 'php/getEarthquakes.php',
        type: 'POST',
        dataType: 'json',
        data: {
            north: bounds.north,
            south: bounds.south,
            east: bounds.east,
            west: bounds.west
        },
        success: function (response) {
            if (response.earthquakes) {
                const earthquakeIcon = L.ExtraMarkers.icon({
                    icon: 'fa-house-crack',
                    markerColor: 'red',
                    prefix: 'fa',
                    svg: true,
                    shape: 'square',
                    iconColor: '#fff'
                });
                const markers = L.markerClusterGroup();
                response.earthquakes.forEach(eq => {
                    const marker = L.marker([eq.lat, eq.lng], { icon: earthquakeIcon })
                        .bindPopup(`
                            <strong>Date and Time:</strong> ${new Date(eq.datetime).toLocaleString('en-GB')}<br>
                            <strong>Depth:</strong> ${eq.depth} km<br>
                            <strong>Magnitude:</strong> ${eq.magnitude}
                        `);
                    markers.addLayer(marker);
                });
                map.addLayer(markers);
            }
        },
        error: function() {
            console.error("Error fetching earthquake data");
        }
    });
}

// Geolocation prompt and default handling
function getUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            updateCountryByCoords(lat, lng);
        }, function () {
            // If geolocation is denied, set default to United Kingdom
            $('#countrySelect').val('GB').change(); // Default to United Kingdom
            highlightCountry('GB'); // Render UK after geolocation denied
        });
    } else {
        // Default to United Kingdom if geolocation is not available
        $('#countrySelect').val('GB').change();
        highlightCountry('GB'); // Render UK if geolocation is unavailable
    }
}


// AJAX to fetch country information for the "Country Overview" modal
let countryOverviewButton = L.easyButton('fa-info-circle', function() {
    const countryCode = $('#countrySelect').val(); // Get selected country code
    if (countryCode) {
        // AJAX request to fetch country info
        $.ajax({
            url: 'php/getCountryOverview.php',
            type: 'POST',
            dataType: 'json',
            data: { countryCode: countryCode },
            success: function (response) {
                if (response.status.name === 'ok') {
                    const countryData = response.data;

                    let modalContent = `
                      <div class="container-fluid">
                        <!-- Row for each data point -->
                        <div class="row row-striped">
                          <div class="col-1 text-center"><i class="fa fa-globe"></i></div>
                          <div class="col-4 fw-bold">Country Name:</div>
                          <div class="col-7">${countryData.name}</div>
                        </div>
                        <div class="row row-striped">
                          <div class="col-1 text-center"><i class="fa fa-star"></i></div>
                          <div class="col-4 fw-bold">Capital:</div>
                          <div class="col-7">${countryData.capital}</div>
                        </div>
                        <div class="row row-striped">
                          <div class="col-1 text-center"><i class="fa fa-users"></i></div>
                          <div class="col-4 fw-bold">Population:</div>
                          <div class="col-7">${countryData.population.toLocaleString()}</div>
                        </div>
                        <div class="row row-striped">
                          <div class="col-1 text-center"><i class="fa fa-money-bill"></i></div>
                          <div class="col-4 fw-bold">Currency:</div>
                          <div class="col-7">${countryData.currency}</div>
                        </div>
                        <div class="row row-striped">
                          <div class="col-1 text-center"><i class="fa fa-map"></i></div>
                          <div class="col-4 fw-bold">Continent:</div>
                          <div class="col-7">${countryData.continent}</div>
                        </div>
                        <div class="row row-striped">
                          <div class="col-1 text-center"><i class="fa fa-car"></i></div>
                          <div class="col-4 fw-bold">Driving Side:</div>
                          <div class="col-7">${countryData.drivingSide}</div>
                        </div>
                        <div class="row row-striped">
                          <div class="col-1 text-center"><i class="fa fa-flag"></i></div>
                          <div class="col-4 fw-bold">Flag:</div>
                          <div class="col-7"><img src="${countryData.flag}" alt="Flag of ${countryData.name}" class="img-fluid" width="100"></div>
                        </div>
                      </div>
                    `;

                    $('#countryOverviewBody').html(modalContent); // Populate the modal body with country info
                    $('#countryOverviewModal').modal('show'); // Show the modal
                } else {
                    $('#countryOverviewBody').html('<p>Error loading country information.</p>');
                }
            },
            error: function () {
                $('#countryOverviewBody').html('<p>Error fetching country information.</p>');
            }
        });
    } else {
        $('#countryOverviewBody').html('<p>Please select a country first.</p>');
        $('#countryOverviewModal').modal('show');
    }
}).addTo(map); // Adds the button directly to the map

// Apply custom styles for the "Country Overview" button
countryOverviewButton.button.style.backgroundColor = '#50c878'; // Emerald green shade
countryOverviewButton.button.style.color = '#ffffff'; // White text
countryOverviewButton.button.style.border = 'none'; // Remove border

// Adding a weather button on the map using Leaflet EasyButton
let weatherButton = L.easyButton('fa-cloud', function() {
    const countryCode = $('#countrySelect').val(); // Get selected country code

    if (countryCode) {
        // Fetch country information to get the capital city
        $.ajax({
            url: 'php/getCountryOverview.php', // Reuse the country info API
            type: 'POST',
            dataType: 'json',
            data: { countryCode: countryCode },
            success: function (response) {
                if (response.status.name === 'ok') {
                    const capitalCity = response.data.capital;
                    const countryName = response.data.name;

                    // Update modal title to include "Capital City, Country"
                    $('#weatherModalLabel').html(`${capitalCity}, ${countryName}`);
                    
                    // Fetch weather for the capital city
                    $.ajax({
                        url: 'php/getWeather.php', // PHP cURL file to get weather
                        type: 'POST',
                        dataType: 'json',
                        data: { city: capitalCity },
                        beforeSend: function() {
                            // Show preloader while data is loading
                            $('#pre-load').removeClass('fadeOut').show();
                        },
                        success: function (weatherResponse) {
                            if (weatherResponse.status.name === 'ok') {
                                const weatherData = weatherResponse.data;
                                
                                // Populate today's weather
                                $('#todayConditions').html(weatherData.today.condition);
                                $('#todayIcon').attr('src', weatherData.today.icon);
                                $('#todayMaxTemp').html(weatherData.today.maxTemp);
                                $('#todayMinTemp').html(weatherData.today.minTemp);
                                
                                // Populate Day 1 weather
                                $('#day1Date').html(weatherData.day1.date);
                                $('#day1Icon').attr('src', weatherData.day1.icon);
                                $('#day1MaxTemp').html(weatherData.day1.maxTemp);
                                $('#day1MinTemp').html(weatherData.day1.minTemp);
                                
                                // Populate Day 2 weather
                                $('#day2Date').html(weatherData.day2.date);
                                $('#day2Icon').attr('src', weatherData.day2.icon);
                                $('#day2MaxTemp').html(weatherData.day2.maxTemp);
                                $('#day2MinTemp').html(weatherData.day2.minTemp);

                                // Set last updated time in UK format (DD-MMM-YY HH:mm)
                                const lastUpdatedRaw = new Date(weatherData.lastUpdated);
                                const day = lastUpdatedRaw.getDate();
                                const month = lastUpdatedRaw.toLocaleString('en-GB', { month: 'short' });
                                const year = lastUpdatedRaw.getFullYear().toString().slice(-2);
                                const time = lastUpdatedRaw.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
                                const lastUpdatedFormatted = `${day}-${month}-${year} ${time}`;

                                $('#lastUpdated').html(lastUpdatedFormatted);

                                // Delay hiding the preloader for a short while
                                setTimeout(function() {
                                    $('#pre-load').addClass('fadeOut');
                                }, 800); // Delay of 800 milliseconds for the preloader
                                
                                // Show the modal
                                $('#weatherModal').modal('show');
                            } else {
                                $('#weatherModalLabel').html("Error retrieving weather data");
                            }
                        },
                        error: function () {
                            $('#weatherModalLabel').html("Error fetching weather data");
                        }
                    });
                } else {
                    $('#weatherModalLabel').html("Error retrieving capital city");
                }
            },
            error: function () {
                $('#weatherModalLabel').html("Error fetching country data");
            }
        });
    } else {
        $('#weatherModalLabel').html("Please select a country first");
        $('#weatherModal').modal('show');
    }
}).addTo(map); // Add to Leaflet map


// Apply custom styles for the "Weather" button
weatherButton.button.style.backgroundColor = '#4a90e2'; // Light blue shade
weatherButton.button.style.color = '#ffffff'; // White text
weatherButton.button.style.border = 'none'; // Remove border