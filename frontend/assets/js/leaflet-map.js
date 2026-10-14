/**
 * Govi Paura — leaflet-map.js
 * Loaded only on pages that set $needsMap = true.
 * Exposes window.gpMap helpers for initialising a delivery route map
 * using the data returned by GET /deliveries/{id}/map.
 */
(function () {
  /**
   * Initialise or re-draw a Leaflet map inside `containerEl`.
   *
   * @param {HTMLElement} containerEl  – the div that Leaflet mounts into
   * @param {object}      mapData      – the object from GET /deliveries/{id}/map
   * @returns {L.Map} the Leaflet map instance
   */
  function initDeliveryMap(containerEl, mapData) {
    // Remove any previously created map (Leaflet complains about double-init)
    if (containerEl._gpLeafletMap) {
      containerEl._gpLeafletMap.remove();
    }

    const tiles = mapData.tiles ?? {};
    const tileUrl = tiles.url ?? 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const attribution = tiles.attribution ?? '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';

    let map;

    if (!mapData.ready) {
      // No coordinates saved yet — show a placeholder centred on Sri Lanka
      map = L.map(containerEl).setView([7.8731, 80.7718], 7);
      L.tileLayer(tileUrl, { attribution }).addTo(map);

      const msg = L.control({ position: 'topright' });
      msg.onAdd = () => {
        const div = L.DomUtil.create('div');
        div.style.cssText = 'background:#fff;padding:6px 10px;border-radius:6px;font-size:12px;color:#555;';
        div.textContent = 'Location coordinates not yet saved.';
        return div;
      };
      msg.addTo(map);
      containerEl._gpLeafletMap = map;
      return map;
    }

    // Fit the map to both markers
    map = L.map(containerEl);
    L.tileLayer(tileUrl, { attribution }).addTo(map);

    const pickupIcon = L.divIcon({
      className: '',
      html: '<div style="background:var(--sage,#4a7c59);width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>',
      iconSize: [14, 14],
      iconAnchor: [7, 7],
    });
    const dropoffIcon = L.divIcon({
      className: '',
      html: '<div style="background:var(--rust,#b5451b);width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>',
      iconSize: [14, 14],
      iconAnchor: [7, 7],
    });

    (mapData.markers ?? []).forEach((m) => {
      if (m.latitude == null || m.longitude == null) return;
      const icon = m.type === 'pickup' ? pickupIcon : dropoffIcon;
      const label = m.approximate
        ? `${m.label} <em style="font-size:11px;">(approximate)</em>`
        : `${m.label}<br><span style="font-size:12px;">${m.address ?? ''}</span>`;
      L.marker([m.latitude, m.longitude], { icon }).addTo(map).bindPopup(label);
    });

    if (mapData.route && mapData.route.length === 2) {
      L.polyline(mapData.route, { color: '#4a7c59', weight: 3, dashArray: '6 4' }).addTo(map);
    }

    if (mapData.bounds) {
      map.fitBounds(mapData.bounds, { padding: [24, 24] });
    } else if (mapData.center) {
      map.setView(mapData.center, 12);
    }

    containerEl._gpLeafletMap = map;
    return map;
  }

  window.gpMap = { init: initDeliveryMap };
})();

