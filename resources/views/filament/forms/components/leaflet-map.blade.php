<div wire:ignore x-data x-init="setTimeout(() => {

    const latInput = document.getElementById('sucursal_lat');
    const lngInput = document.getElementById('sucursal_long');
    const addressInput = document.getElementById('ubicacion');

    if (!window.L) {
        console.warn('Leaflet no encontrado');
        return;
    }

    // =========================
    // MAP INIT
    // =========================
    const defaultLat =
        parseFloat(latInput?.value) ||
        parseFloat('{{ $getRecord()?->lat ?? -16.5 }}');

    const defaultLng =
        parseFloat(lngInput?.value) ||
        parseFloat('{{ $getRecord()?->long ?? -68.15 }}');

    const map = L.map($refs.map).setView(
        [defaultLat, defaultLng],
        13
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }
    ).addTo(map);

    const marker = L.marker(
        [defaultLat, defaultLng], {
            draggable: true
        }
    ).addTo(map);

    // =========================
    // ERROR MESSAGE
    // =========================
    const errorMessage = document.createElement('p');

    errorMessage.style.color = '#dc2626';
    errorMessage.style.fontSize = '13px';
    errorMessage.style.marginTop = '6px';

    addressInput.parentNode.appendChild(errorMessage);

    // =========================
    // CONTROL FLAGS
    // =========================
    let internalUpdate = false;

    // =========================
    // SET COORDS
    // =========================
    function setCoordinates(lat, lng) {

        internalUpdate = true;

        latInput.value = lat.toFixed(8);
        lngInput.value = lng.toFixed(8);

        latInput.dispatchEvent(
            new Event('input', { bubbles: true })
        );

        lngInput.dispatchEvent(
            new Event('input', { bubbles: true })
        );

        setTimeout(() => {
            internalUpdate = false;
        }, 300);
    }

    // =========================
    // MAP -> ADDRESS
    // =========================
    async function reverseGeocode(lat, lng) {

        try {

            const res = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
            );

            const data = await res.json();

            if (data?.display_name) {

                internalUpdate = true;

                addressInput.value = data.display_name;

                addressInput.dispatchEvent(
                    new Event('input', { bubbles: true })
                );

                errorMessage.textContent = '';

                setTimeout(() => {
                    internalUpdate = false;
                }, 300);
            }

        } catch (e) {
            console.error(e);
        }
    }

    // =========================
    // ADDRESS -> MAP
    // =========================
    let debounce;

    async function searchAddress(address) {

        if (!address || address.length < 3) return;

        try {

            let query = address;

            const lowerAddress = address.toLowerCase();

            if (
                !lowerAddress.includes('bolivia') &&
                !lowerAddress.includes('la paz')
            ) {
                query += ', La Paz, Bolivia';
            }

            const res = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`
            );

            const data = await res.json();

            if (data && data.length > 0) {

                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);

                map.setView([lat, lng], 16);

                marker.setLatLng([lat, lng]);

                setCoordinates(lat, lng);

                errorMessage.textContent = '';

            } else {

                errorMessage.textContent =
                    'No se encontró una dirección válida.';
            }

        } catch (e) {

            console.error(e);

            errorMessage.textContent =
                'Error al buscar la dirección.';
        }
    }

    // =========================
    // LAT/LONG -> MAP
    // =========================
    function updateMapFromCoords() {

        if (internalUpdate) return;

        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (!isNaN(lat) && !isNaN(lng)) {

            map.setView([lat, lng], 16);

            marker.setLatLng([lat, lng]);

            reverseGeocode(lat, lng);
        }
    }

    // =========================
    // MAP CLICK
    // =========================
    map.on('click', function(event) {

        marker.setLatLng(event.latlng);

        setCoordinates(
            event.latlng.lat,
            event.latlng.lng
        );

        reverseGeocode(
            event.latlng.lat,
            event.latlng.lng
        );
    });

    // =========================
    // DRAG MARKER
    // =========================
    marker.on('dragend', function() {

        const pos = marker.getLatLng();

        setCoordinates(pos.lat, pos.lng);

        reverseGeocode(pos.lat, pos.lng);
    });

    // =========================
    // INPUT ADDRESS
    // =========================
    addressInput.addEventListener('input', function() {

        if (internalUpdate) return;

        clearTimeout(debounce);

        debounce = setTimeout(() => {

            searchAddress(this.value);

        }, 1000);
    });

    // =========================
    // LAT/LONG LISTENERS
    // =========================
    latInput.addEventListener('input', updateMapFromCoords);

    lngInput.addEventListener('input', updateMapFromCoords);

    // =========================
    // FIX MAP RENDER
    // =========================
    setTimeout(() => {
        map.invalidateSize();
    }, 500);

}, 300);">
    <div style="
        display:flex;
        flex-direction:column;
        gap:12px;
    ">

        <div x-ref="map"
            style="
            height: 420px;
            width: 100%;
            border-radius: 14px;
            overflow: hidden;
        ">
        </div>

        <div style="display:flex; justify-content:flex-end;">

            <a href="https://www.google.com/maps?q={{ $getRecord()?->lat }},{{ $getRecord()?->long }}" target="_blank"
                style="
                display:inline-flex;
                align-items:center;
                gap:8px;
                background:#2563eb;
                color:white;
                padding:10px 16px;
                border-radius:10px;
                text-decoration:none;
                font-weight:600;
            ">
                Ver en Google Maps
            </a>

        </div>

    </div>

    <p style="margin-top: 8px; font-size: 13px; color: #6b7280;">
        Puedes escribir una dirección, mover el marcador o editar las coordenadas.
    </p>
</div>
