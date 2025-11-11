{{-- resources/views/admin_panel/layouts/partials/travel_map_core.blade.php --}}
@push('scripts')
    <script>
        (function(){
            const apiKey = "{{ config('constants.google_maps_api_key') }}";
            if (!apiKey) {
                console.warn('Falta GOOGLE_MAPS_API_KEY en .env para mostrar mapas.');
                return;
            }

            // Carga única de Google Maps
            function ensureGoogleLoaded(cb){
                if (window.google && google.maps && google.maps.Map) { cb(); return; }
                const existing = Array.from(document.scripts).find(s => s.src && s.src.includes('maps.googleapis.com/maps/api/js'));
                if (existing) { existing.addEventListener('load', cb, { once: true }); return; }
                const script = document.createElement('script');
                script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=geometry`;
                script.async = true;
                script.defer = true;
                script.onload = cb;
                document.head.appendChild(script);
            }

            // ===== Helpers comunes =====
            function safeParse(s){ try { return JSON.parse(s || '[]'); } catch(e){ return []; } }
            function isFiniteNum(n){ const v = parseFloat(n); return Number.isFinite(v); }

            // 0->A, 25->Z, 26->AA, etc.
            function labelFromIndex(i){
                let label = '';
                i = Number(i);
                while (i >= 0) {
                    label = String.fromCharCode((i % 26) + 65) + label;
                    i = Math.floor(i / 26) - 1;
                }
                return label;
            }

            function latLngFromObj(obj){
                if (!obj || !isFiniteNum(obj.lat) || !isFiniteNum(obj.lng)) return null;
                return new google.maps.LatLng(parseFloat(obj.lat), parseFloat(obj.lng));
            }

            function firstPointFromPolylineOr(obj, polyline){
                if (polyline) {
                    try {
                        const path = google.maps.geometry.encoding.decodePath(polyline);
                        if (path && path.length) return path[0];
                    } catch(e){}
                }
                return latLngFromObj(obj);
            }
            function lastPointFromPolylineOr(obj, polyline){
                if (polyline) {
                    try {
                        const path = google.maps.geometry.encoding.decodePath(polyline);
                        if (path && path.length) return path[path.length - 1];
                    } catch(e){}
                }
                return latLngFromObj(obj);
            }

            // Paleta default para líneas
            const defaultColors = [
                '#A5D6A7', '#90CAF9', '#FFCC80', '#F48FB1', '#CE93D8',
                '#B39DDB', '#80CBC4', '#E6EE9C', '#FFAB91', '#B0BEC5'
            ];

            // API Pública reutilizable
            function renderInEl(el, segments, opts = {}){
                if (!el) return;

                const style = opts.style || (window.darkNoLabelsStyle || []);
                const lineColors = opts.colors || defaultColors;
                const withLetters = opts.withLetters !== false; // por defecto true
                const zoomControl = opts.zoomControl ?? false;   // listado: false, detalle: true
                const gesture = opts.gestureHandling || 'cooperative';

                const map = new google.maps.Map(el, {
                    center: { lat: -34.603722, lng: -58.381592 },
                    zoom: 12,
                    styles: style,
                    backgroundColor: '#0b0f14',
                    disableDefaultUI: true,
                    fullscreenControl: false,
                    streetViewControl: false,
                    mapTypeControl: false,
                    zoomControl: zoomControl,
                    gestureHandling: gesture
                });

                const bounds = new google.maps.LatLngBounds();

                // 1) Dibujar líneas de tramos
                (segments || []).forEach(function(seg, idx){
                    try {
                        const color = lineColors[idx % lineColors.length];
                        let path = [];

                        if (seg.polyline) {
                            path = google.maps.geometry.encoding.decodePath(seg.polyline);
                        } else if (
                            seg.origin && isFiniteNum(seg.origin.lat) && isFiniteNum(seg.origin.lng) &&
                            seg.destination && isFiniteNum(seg.destination.lat) && isFiniteNum(seg.destination.lng)
                        ) {
                            path = [
                                new google.maps.LatLng(parseFloat(seg.origin.lat), parseFloat(seg.origin.lng)),
                                new google.maps.LatLng(parseFloat(seg.destination.lat), parseFloat(seg.destination.lng))
                            ];
                        }

                        if (path && path.length) {
                            new google.maps.Polyline({
                                path: path,
                                strokeColor: color,
                                strokeOpacity: 0.95,
                                strokeWeight: 4,
                                map: map
                            });
                            path.forEach(p => bounds.extend(p));
                        }
                    } catch (e) {
                        console.warn('No se pudo renderizar un tramo:', e);
                    }
                });

                // 2) Marcadores A, B, C... (orígenes + destino final)
                if (withLetters) {
                    const waypoints = [];
                    if (segments && segments.length > 0) {
                        const firstOrigin = firstPointFromPolylineOr(segments[0].origin, segments[0].polyline);
                        if (firstOrigin) waypoints.push(firstOrigin);
                        for (let i = 1; i < segments.length; i++) {
                            const o = firstPointFromPolylineOr(segments[i].origin, segments[i].polyline);
                            if (o) waypoints.push(o);
                        }
                        const lastSeg = segments[segments.length - 1];
                        const finalDest = lastPointFromPolylineOr(lastSeg.destination, lastSeg.polyline);
                        if (finalDest) waypoints.push(finalDest);
                    }

                    // Dedup por coord
                    const seen = new Set();
                    const uniquePoints = [];
                    waypoints.forEach(pt => {
                        const k = pt.lat().toFixed(6) + ',' + pt.lng().toFixed(6);
                        if (!seen.has(k)) { seen.add(k); uniquePoints.push(pt); }
                    });

                    uniquePoints.forEach((pt, idx) => {
                        const label = labelFromIndex(idx);
                        new google.maps.Marker({
                            position: pt,
                            map: map,
                            label: {
                                text: label,
                                color: '#ffffff',
                                fontSize: '12px',
                                fontWeight: '700'
                            },
                            title: `Punto ${label}`
                        });
                        bounds.extend(pt);
                    });
                }

                // 3) Fit bounds
                try {
                    if (typeof bounds.isEmpty === 'function') {
                        if (!bounds.isEmpty()) map.fitBounds(bounds);
                    } else {
                        const ne = bounds.getNorthEast();
                        const sw = bounds.getSouthWest();
                        if (ne && sw && ne.toUrlValue() !== sw.toUrlValue()) map.fitBounds(bounds);
                    }
                } catch (e) {}

                return map;
            }

            // Busca todos los contenedores y renderiza
            function renderAll(selector = '.travel-map', opts = {}){
                const els = document.querySelectorAll(selector);
                els.forEach(el => {
                    const segments = safeParse(el.dataset.segments) || [];
                    renderInEl(el, segments, opts);
                });
            }

            // Exponer una API global minimal
            window.TravelMap = window.TravelMap || {
                renderInEl,
                renderAll,
                ensureLoaded: ensureGoogleLoaded
            };

            // Si querés autoinicializar siempre que esté presente el selector, descomentá:
            // ensureGoogleLoaded(() => renderAll());
        })();
    </script>
@endpush
