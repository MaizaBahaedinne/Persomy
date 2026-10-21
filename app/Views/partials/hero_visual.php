<?php
/** Illustration décorative de l'accueil : réseau orbital (SVG inline, sans image externe). */
?>
<div class="hero-visual" aria-hidden="true">
    <svg viewBox="0 0 480 480" fill="none" focusable="false">
        <defs>
            <radialGradient id="hv-glow" cx="50%" cy="50%" r="50%">
                <stop offset="0" stop-color="#402371" stop-opacity=".22"/>
                <stop offset="1" stop-color="#402371" stop-opacity="0"/>
            </radialGradient>
            <linearGradient id="hv-core" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#6a45a8"/>
                <stop offset="1" stop-color="#2b1850"/>
            </linearGradient>
        </defs>
        <circle cx="240" cy="240" r="236" fill="url(#hv-glow)"/>
        <circle cx="240" cy="240" r="210" stroke="#402371" stroke-opacity=".14"/>
        <circle cx="240" cy="240" r="105" stroke="#402371" stroke-opacity=".22"/>

        <g class="orbit">
            <circle cx="240" cy="240" r="170" stroke="#402371" stroke-opacity=".4" stroke-dasharray="3 7"/>
            <g stroke="#402371" stroke-opacity=".28">
                <line x1="240" y1="240" x2="240" y2="70"/>
                <line x1="240" y1="240" x2="401.7" y2="187.5"/>
                <line x1="240" y1="240" x2="339.9" y2="377.5"/>
                <line x1="240" y1="240" x2="140.1" y2="377.5"/>
                <line x1="240" y1="240" x2="78.3" y2="187.5"/>
            </g>
            <g class="node"><circle cx="240" cy="70" r="14" fill="#402371" fill-opacity=".16"/><circle cx="240" cy="70" r="6" fill="#402371"/></g>
            <g class="node"><circle cx="401.7" cy="187.5" r="14" fill="#402371" fill-opacity=".16"/><circle cx="401.7" cy="187.5" r="6" fill="#402371"/></g>
            <g class="node"><circle cx="339.9" cy="377.5" r="14" fill="#402371" fill-opacity=".16"/><circle cx="339.9" cy="377.5" r="6" fill="#402371"/></g>
            <g class="node"><circle cx="140.1" cy="377.5" r="14" fill="#402371" fill-opacity=".16"/><circle cx="140.1" cy="377.5" r="6" fill="#402371"/></g>
            <g class="node"><circle cx="78.3" cy="187.5" r="14" fill="#402371" fill-opacity=".16"/><circle cx="78.3" cy="187.5" r="6" fill="#402371"/></g>
        </g>

        <g class="orbit orbit-rev">
            <g class="node"><circle cx="240" cy="135" r="4" fill="#402371"/></g>
            <g class="node"><circle cx="330.9" cy="292.5" r="4" fill="#402371"/></g>
            <g class="node"><circle cx="149.1" cy="292.5" r="4" fill="#402371"/></g>
        </g>

        <circle cx="240" cy="240" r="62" fill="#402371" fill-opacity=".12"/>
        <circle cx="240" cy="240" r="46" fill="url(#hv-core)"/>
        <circle cx="240" cy="240" r="46" stroke="#fff" stroke-opacity=".35"/>
        <path d="M226 262V218h18a14 14 0 0 1 0 28h-18" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</div>
