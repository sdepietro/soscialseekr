<style>
    /* ===========================
    City Map Overlay + Spots (sin degradé, SVG externo)
    =========================== */
    :root{
        --overlay-height: 180px;
        --overlay-radius: 12px;

        /* URL del SVG externo (puede ser cualquier SVG de calles) */
        --map-url: url("https://ocad.ch/wp-content/uploads/2018/11/Berlin.png");

        --spot-color: #ffffff;
        --spot-glow: rgb(126, 126, 126);
        --anim-time: 2800ms;
    }

    /* el overlay no empuja el layout */
    .page-content{ position: relative; }

    /* contenedor del overlay */
    .city-map-overlay{
        position: absolute;
        top: 0; left: 0; right: 0;
        height: var(--overlay-height);
        z-index: 0;
        pointer-events: none;
        border-radius: var(--overlay-radius);
        overflow: hidden;
    }

    /* fondo = SVG externo directamente, SIN gradiente */
    .city-map-bg{
        position: absolute;
        inset: 0;
        z-index: 0;
        background-image: var(--map-url);
        background-size: cover;           /* ajusta como “hero” */
        background-position: center;
        background-repeat: no-repeat;
        /* convertir el mapa a “líneas blancas sobre fondo oscuro” */
        filter: grayscale(1) invert(1) contrast(1.15) brightness(0.9);
        opacity: .30;                     /* sutileza */
    }

    /* capa de spots */
    .city-map-spots{
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
    }

    /* spot */
    .map-spot{
        position: absolute;
        display: block;
        border-radius: 999px;
        background: radial-gradient(circle at 50% 50%, #bcbcbc 0%, var(--spot-color) 55%, rgba(255,255,255,0) 65%);
        box-shadow:
            0 0 10px 2px var(--spot-glow),
            0 0 26px 8px rgba(94, 94, 94, 0.18);
        animation:
            spot-pulse var(--anim-time) ease-in-out infinite,
            slight-drift calc(var(--anim-time) * 4) ease-in-out infinite;
        opacity: .95;
    }
    .map-spot::after{
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 999px;
        border: 2px solid var(--spot-color);
        opacity: .6;
        transform: scale(1);
        animation: spot-ripple calc(var(--anim-time) * 1.15) ease-out infinite;
    }

    /* posiciones/tamaños (20 spots) */
    /*.map-spot:nth-child(1){  top:14%; left:4%;  width:14px; height:14px; animation-delay:120ms; }*/
    /*.map-spot:nth-child(2){  top:18%; left:18%; width:10px; height:10px; animation-delay:260ms; }*/
    /*.map-spot:nth-child(3){  top:10%; left:32%; width:12px; height:12px; animation-delay:420ms; }*/
    /*.map-spot:nth-child(4){  top:12%; left:48%; width:11px; height:11px; animation-delay:580ms; }*/
    /*.map-spot:nth-child(5){  top:16%; left:63%; width:9px;  height:9px;  animation-delay:740ms; }*/
    /*.map-spot:nth-child(6){  top:12%; left:78%; width:12px; height:12px; animation-delay:900ms; }*/

    .map-spot:nth-child(7){  top:45%; left:10%; width:9px;  height:9px;  animation-delay:180ms; }
    .map-spot:nth-child(8){  top:36%; left:28%; width:10px; height:10px; animation-delay:340ms; }
    .map-spot:nth-child(9){  top:30%; left:46%; width:12px; height:12px; animation-delay:500ms; }
    .map-spot:nth-child(10){ top:34%; left:62%; width:8px;  height:8px;  animation-delay:660ms; }
    .map-spot:nth-child(11){ top:32%; left:80%; width:10px; height:10px; animation-delay:820ms; }

    /*.map-spot:nth-child(12){ top:56%; left:6%;  width:8px;  height:8px;  animation-delay:220ms; }*/
    /*.map-spot:nth-child(13){ top:58%; left:24%; width:13px; height:13px; animation-delay:380ms; }*/
    /*.map-spot:nth-child(14){ top:52%; left:42%; width:9px;  height:9px;  animation-delay:540ms; }*/
    /*.map-spot:nth-child(15){ top:58%; left:60%; width:12px; height:12px; animation-delay:700ms; }*/
    /*.map-spot:nth-child(16){ top:54%; left:78%; width:9px;  height:9px;  animation-delay:860ms; }*/

    .map-spot:nth-child(16){ top:55%; left:70%; width:10px; height:10px; animation-delay:260ms; }

    .map-spot:nth-child(17){ top:76%; left:14%; width:10px; height:10px; animation-delay:260ms; }
    .map-spot:nth-child(18){ top:72%; left:36%; width:12px; height:12px; animation-delay:420ms; }
    .map-spot:nth-child(19){ top:94%; left:58%; width:9px;  height:9px;  animation-delay:580ms; }
    .map-spot:nth-child(20){ top:78%; left:82%; width:11px; height:11px; animation-delay:740ms; }

    /* animaciones */
    @keyframes spot-pulse{
        0%   { transform: scale(0.86); opacity: .9; }
        50%  { transform: scale(1.10); opacity: 1; }
        100% { transform: scale(0.86); opacity: .9; }
    }
    @keyframes spot-ripple{
        0%   { opacity: .55; transform: scale(1); }
        70%  { opacity: 0;   transform: scale(2.4); }
        100% { opacity: 0;   transform: scale(2.6); }
    }
    @keyframes slight-drift{
        0%   { transform: translate3d(0,0,0); }
        50%  { transform: translate3d(1px,-1px,0); }
        100% { transform: translate3d(0,0,0); }
    }
    /* ===========================
       Gradientes de sombra para texto
       =========================== */

    /* De izquierda a derecha: detrás del título "Nuevo Usuario" */
    .city-map-gradient-left {
        position: absolute;
        top: 0;
        left: 0;
        height: var(--overlay-height);
        width: 50%;
        background: linear-gradient(to right, rgb(25, 30, 35) 0%, rgba(0, 0, 0, 0) 100%);
        z-index: 2;
        pointer-events: none;
    }

    /* De derecha a izquierda: detrás del breadcrumb */
    .city-map-gradient-right {
        position: absolute;
        top: 0;
        right: 0;
        height: var(--overlay-height);
        width: 25%;
        background: linear-gradient(to left, rgb(25, 30, 35) 0%, rgba(0, 0, 0, 0) 100%);
        z-index: 2;
        pointer-events: none;
    }



</style>

<div class="city-map-overlay" aria-hidden="true">
    <div class="city-map-bg"></div>
    <div class="city-map-gradient-left"></div>
    <div class="city-map-gradient-right"></div>
    <div class="city-map-spots">
        <span class="map-spot"></span><span class="map-spot"></span><span class="map-spot"></span>
        <span class="map-spot"></span><span class="map-spot"></span><span class="map-spot"></span>
        <span class="map-spot"></span><span class="map-spot"></span><span class="map-spot"></span>
        <span class="map-spot"></span><span class="map-spot"></span><span class="map-spot"></span>
        <span class="map-spot"></span><span class="map-spot"></span><span class="map-spot"></span>
        <span class="map-spot"></span><span class="map-spot"></span><span class="map-spot"></span>
        <span class="map-spot"></span><span class="map-spot"></span>
    </div>
</div>
