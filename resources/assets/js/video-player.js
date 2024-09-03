const airPlaySettings = window.twwVideo?.airplaySettings ?? {};

const statsState = {
    played: 0,
}

const setStatsState = (key, value) => {
    statsState[key] = value;
}

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
        }
    });

    player.airplay();

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

    player.bigPlayButton.on('click', function() {
        setStatsState('played', statsState.played + 1);

        if (statsState.played < 1) {
            
        }
    });
});

var player = videojs('my-video');

player.on('fullscreenchange', function() {
    if (player.isFullscreen()) {
        let api = document.documentElement.requestFullscreen(); // Fullscreen API request
    } else {
        if (document.fullscreenElement) {
            document.exitFullscreen(); // Fullscreen API exit
        }
    }
});


