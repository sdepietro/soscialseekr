@once
@push('scripts')
<script>
    // Centralized Google Maps dark theme without labels.
    // Exposes: window.darkNoLabelsStyle
    (function(){
        if (window.darkNoLabelsStyle) return;
        window.darkNoLabelsStyle = [
            { elementType: "geometry", stylers: [{ color: "#0b0f14" }] },
            { elementType: "labels", stylers: [{ visibility: "off" }] },

            { featureType: "administrative", elementType: "geometry", stylers: [{ visibility: "off" }] },
            { featureType: "poi", elementType: "all", stylers: [{ visibility: "off" }] },

            { featureType: "road", elementType: "geometry", stylers: [{ color: "#1a2330" }] },
            { featureType: "road", elementType: "geometry.stroke", stylers: [{ color: "#2a3648" }, { weight: 0.6 }] },
            { featureType: "road", elementType: "labels", stylers: [{ visibility: "off" }] },

            { featureType: "transit", elementType: "all", stylers: [{ visibility: "off" }] },

            { featureType: "water", elementType: "geometry", stylers: [{ color: "#0f1722" }] },
            { featureType: "landscape", elementType: "geometry", stylers: [{ color: "#0e141c" }] },
            { featureType: "landscape.natural", elementType: "geometry", stylers: [{ color: "#0c1118" }] }
        ];
    })();
</script>
@endpush
@endonce
