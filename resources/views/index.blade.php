<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scream Scaler</title>
    <style>

        /* Popup Styles */
        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 300px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .hidden {
            display: none;
        }

    /* Style sederhana untuk progress bar dan countdown */
    progress {
        width: 100%;
        height: 30px;
    }

    #countdown, #screamCountdown {
        font-size: 48px;
        font-weight: bold;
        text-align: center;
        margin-top: 20px;
        color: #ff0000;
    }

    /* Style for the scream bar resembling a vertical scream canister */
    #screamBarContainer {
        width: 40px;  
        height: 300px; 
        border-radius: 20px;
        background: linear-gradient(180deg, #e0e0e0, #b0b0b0); 
        border: 3px solid #5c5c5c;
        box-shadow: inset 0px 2px 6px rgba(0, 0, 0, 0.3); 
        position: relative;
        overflow: hidden;
        margin: 20px auto; 
    }

    /* Hide the default progress element */
    #screamBar {
        display: none;
    }

    /* Custom vertical bar fill */
    #screamFill {
        width: 100%;
        height: 0;  
        background-color: #4CAF50; 
        transition: height 0.3s ease;
        position: absolute;
        bottom: 0;
    }

    /* Optional: Light effect moving across the bar */
    #screamBarContainer::before {
        content: '';
        position: absolute;
        top: 10px;
        bottom: 10px;
        left: 0;
        right: 0;
        background: radial-gradient(circle, rgba(255,255,255,0.5), transparent);
        animation: moveLightVertical 2s infinite;
    }

    @keyframes moveLightVertical {
        0% { top: -20px; }
        100% { top: 100%; }
    }

    /* Container and layout settings */
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background-image: url('https://imgs.search.brave.com/1gp4Noi81VZcEO8RIZKwWH5h8mKf1E_b7N1Zg8WqgbA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9jZG4u/d2FsbHBhcGVyc2Fm/YXJpLmNvbS80Ni8w/L0FlWTBiYy5qcGc');
        background-size: cover;
        background-position: center;
        font-family: Arial, sans-serif;
    }

    .container {
        width: 80%; 
        max-width: 1200px;
        background-color: rgba(255, 255, 255, 0.8);
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    /* Leaderboard table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;   
    }

    table, th, td {
        border: 1px solid #ddd;
        padding: 12px;
    }

    th {
        background-color: #f2f2f2;
        text-align: center;
    }

    td {
        text-align: center;
    }

    /* Style untuk garis penanda */
.marker {
    position: absolute;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #000;
    opacity: 0.6;
}

    </style>
</head>

<body>
    <div class="container">
        <h1>Scream Scaler</h1>
        
        <!-- Form input nama -->
        <input type="text" id="name" placeholder="Enter your name" required>
        <button id="startCountdown" disabled>Start Screaming</button>

<!-- Popup for Success Message -->
<div id="popupSuccess" class="popup hidden" onclick="hidePopup('popupSuccess')">
    <div class="popup-content">
        <p>Selamat kamu mencapai target dan mendapatkan hadiah!</p>
    </div>
</div>

<!-- Popup for Failure Message -->
<div id="popupFailure" class="popup hidden" onclick="hidePopup('popupFailure')">
    <div class="popup-content">
        <p>Yah poin kamu belum cukup, coba lagi ya!</p>
    </div>
</div>

        <!-- Countdown Visual Sebelum Mulai -->
        <div id="countdown" class="hidden">3</div>

        <!-- Countdown untuk berteriak -->
        <div id="screamCountdown" class="hidden">3</div>

        <!-- Indicator Scale Bar for Scream Intensity (Vertical) -->
        <h3>Your Scream Intensity:</h3>
        <div id="screamBarContainer">
            <div id="screamFill"></div> <!-- Progress bar -->
            <!-- Garis penanda setiap 50 poin -->
            <!-- Marker markers tetap sama -->
            <div class="marker" style="bottom: 10%;"></div>
            <div class="marker" style="bottom: 20%;"></div>
            <div class="marker" style="bottom: 30%;"></div>
            <div class="marker" style="bottom: 40%;"></div>
            <div class="marker" style="bottom: 50%;"></div>
            <div class="marker" style="bottom: 60%;"></div>
            <div class="marker" style="bottom: 70%;"></div>
            <div class="marker" style="bottom: 80%;"></div>
            <div class="marker" style="bottom: 90%;"></div>
            <div class="marker" style="bottom: 100%;"></div>
        </div>

        <!-- Tabel Leaderboard -->
        <h2>Leaderboard</h2>
        <table border="1">
            <!-- Isi tabel tetap sama -->
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Scream Scale</th>
                </tr>
            </thead>
            <tbody id="leaderboardBody">
                <!-- Data leaderboard akan di-inject di sini -->
            </tbody>
        </table>
    </div>

    <script>
        let audioContext;
        let meter;
        let screamScale = 0;

        const nameInput = document.getElementById('name');
        const startButton = document.getElementById('startCountdown');
        const countdownDiv = document.getElementById('countdown');
        const screamCountdownDiv = document.getElementById('screamCountdown');
        const screamFill = document.getElementById('screamFill');

        // Hanya aktifkan tombol "Start Screaming" jika nama telah diisi
        nameInput.addEventListener('input', () => {
            startButton.disabled = !nameInput.value;
        });

        // Proses Countdown sebelum mulai scream
        startButton.addEventListener('click', function() {
            startCountdown();
        });

        function startCountdown() {
            let countdown = 3;
            countdownDiv.classList.remove('hidden');
            const countdownInterval = setInterval(() => {
                countdownDiv.textContent = countdown;
                countdown--;
                if (countdown < 0) {
                    clearInterval(countdownInterval);
                    countdownDiv.classList.add('hidden');
                    startScreamProcess();
                }
            }, 1000);
        }

        let maxScreamScale = 0; // Variable to store the maximum scream scale

        function startScreamProcess() {
            let screamTime = 3;
            screamScale = 0; // Reset scream scale
            maxScreamScale = 0; // Reset the maximum scream scale
            screamFill.style.height = '0%'; // Reset bar fill
            screamCountdownDiv.classList.remove('hidden');

            // Activate the microphone
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(function (stream) {
                    audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    let mediaStreamSource = audioContext.createMediaStreamSource(stream);

                    meter = createAudioMeter(audioContext, 0.99, 0.9); // Adjust clipLevel and averaging
                    mediaStreamSource.connect(meter);

                    const screamInterval = setInterval(() => {
                        const minThreshold = 0.04; // Threshold minimum suara
                        screamScale = meter.volume > minThreshold ? Math.round(meter.volume * 1000) : 0;

                        // Store the maximum scream scale reached
                        if (screamScale > maxScreamScale) {
                            maxScreamScale = screamScale;
                        }

                        // Setiap 50 poin = 10%, maka kita bagi maxScreamScale dengan 5
                        let barFillPercentage = Math.min((maxScreamScale / 5), 100);
                        screamFill.style.height = barFillPercentage + '%';

                        // Logika perubahan warna bar berdasarkan nilai scream scale
                        if (maxScreamScale >= 350) {
                            screamFill.style.backgroundColor = 'red'; // Merah untuk 350-500
                        } else if (maxScreamScale >= 250) {
                            screamFill.style.backgroundColor = 'yellow'; // Kuning untuk 250-350
                        } else {
                            screamFill.style.backgroundColor = 'green'; // Hijau untuk 50-250
                        }

                    }, 100);

                    // Countdown for the scream duration
                    const screamCountdownInterval = setInterval(() => {
                        screamCountdownDiv.textContent = screamTime;
                        screamTime--;
                        if (screamTime < 0) {
                            clearInterval(screamCountdownInterval);
                            clearInterval(screamInterval);
                            screamCountdownDiv.classList.add('hidden');
                            submitScreamScore(); // Submit the score when the scream ends
                        }
                    }, 1000);
                })
                .catch(function (err) {
                    console.error("Error accessing microphone:", err);
                });
        }

        function hidePopup(popupId) {
    document.getElementById(popupId).classList.add('hidden');
}

function submitScreamScore() {
    const name = nameInput.value;

    // Hanya kirim data jika maxScreamScale >= 100
    if (!name || maxScreamScale < 100) {
        return;
    }

    // Tampilkan popupSuccess jika poin >= 350
    if (maxScreamScale >= 350) {
        document.getElementById('popupSuccess').classList.remove('hidden');
    } else {
        // Tampilkan popupFailure jika poin < 350
        document.getElementById('popupFailure').classList.remove('hidden');
    }

    fetch('/leaderboard', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: name, scream_scale: maxScreamScale })
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            return response.json().then(data => { throw new Error(data.error); });
        }
    })
    .then(data => {
        updateLeaderboard(data); // Perbarui leaderboard
    })
    .catch(error => {
        console.error(error.message);
    });

    // Reset bar scream setelah submit
    screamFill.style.height = '0%';
    screamFill.style.backgroundColor = 'green'; // Reset ke hijau
}



function updateLeaderboard(data) {
    const leaderboardBody = document.getElementById('leaderboardBody');
    leaderboardBody.innerHTML = '';
    data.forEach((item, index) => {
        const row = `<tr>
            <td>${index + 1}</td>
            <td>${item.name}</td>
            <td>${item.scream_scale}</td>
        </tr>`;
        leaderboardBody.innerHTML += row;
    });
}


        // Audio meter function dari Chris Wilson
        function createAudioMeter(audioContext, clipLevel = 0.98, averaging = 0.95, clipLag = 750) {
            const processor = audioContext.createScriptProcessor(512);
            processor.onaudioprocess = volumeAudioProcess;
            processor.clipping = false;
            processor.lastClip = 0;
            processor.volume = 0;
            processor.clipLevel = clipLevel;
            processor.averaging = averaging;
            processor.clipLag = clipLag;

            processor.connect(audioContext.destination);

            processor.checkClipping = function () {
                if (!this.clipping) return false;
                if ((this.lastClip + this.clipLag) < window.performance.now()) this.clipping = false;
                return this.clipping;
            };

            processor.shutdown = function () {
                this.disconnect();
                this.onaudioprocess = null;
            };

            return processor;
        }

        

        function volumeAudioProcess(event) {
        const buf = event.inputBuffer.getChannelData(0);
        let sum = 0;
        let x;

        for (let i = 0; i < buf.length; i++) {
            x = buf[i];
            sum += x * x;
        }

        const rms = Math.sqrt(sum / buf.length);

        // Kurangi sensitivitas dengan mengurangi skala volume
        const sensitivityFactor = 0.9; // Sesuaikan nilai untuk mengurangi sensitivitas
        this.volume = Math.max(rms * sensitivityFactor, this.volume * this.averaging);

        if (this.volume > this.clipLevel) {
            this.clipping = true;
            this.lastClip = window.performance.now();
        }
    }
    </script>
</body>
</html>