import 'video.js';
import videojs from 'video.js';
import '../../videojs/plugins/es/nuevo.js';
import { airplay } from '../../videojs/plugins/es/videojs.airplay.js'; 

const airPlaySettings = window.twwVideo?.airplaySettings ?? {};

const nuevo_plugin_options = {
    contextMenu: false,
    buttonBackward: true,
    buttonForward: true,
    airPlaySettings: airPlaySettings  
};

let videoElements = document.querySelectorAll('.video-js');

videoElements.forEach(function(videoElement) {
    let player = videojs(videoElement, {
        plugins: {
            nuevo: nuevo_plugin_options,
            airplay: {} // Explicitly mention airplay plugin to ensure usage
        }
    });

    // Initialize airplay plugin explicitly
    if (typeof player.airplay === 'function') {
        player.airplay();
    }

    // Function to handle fullscreen change
    function handleFullscreenChange() {
        if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
            player.addClass('vjs-fullscreen');
        } else {
            player.removeClass('vjs-fullscreen');
        }
    }

    // Event listeners for fullscreen change
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('mozfullscreenchange', handleFullscreenChange);
    document.addEventListener('MSFullscreenChange', handleFullscreenChange);

    if (airPlaySettings.metadata) {
        function updateAirplayMetadata(metadata) {
            if (player.tech_ && player.tech_.featuresAirPlay) {
                console.log('Update AirPlay metadata:', metadata); 
            }
        }

        player.ready(function() {
            updateAirplayMetadata(airPlaySettings.metadata);
        });
    }
});
