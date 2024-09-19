<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scream Scaler</title>
    <style>
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

    #screamBar {
        margin-top: 20px;
    }

    /* Sembunyikan elemen saat tidak diperlukan */
    .hidden {
        display: none;
    }

    /* Style untuk notifikasi */
    #notification {
        display: none;
        padding: 10px;
        margin: 10px;
        color: white;
        background-color: red;
        border-radius: 5px;
        text-align: center;
    }

    /* Tambahkan background image dan buat konten di tengah */
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background-image: url('https://img.freepik.com/free-vector/halloween-background-with-old-cemetery-gravestones-spooky-leafless-trees-full-moon-night-sky-realistic-illustration_1284-65419.jpg?w=1480&t=st=1726729371~exp=1726729971~hmac=517f2fd9ee52a9753ba7ca7e6a865fd77e1fe1fa5e8e8850e5b87830c24748bf'); /* Ganti dengan path ke gambar Anda */
        background-size: cover;
        background-position: center;
        font-family: Arial, sans-serif;
    }

    /* Perbesar container dan beri padding */
    .container {
        width: 80%; /* Lebar container diatur menjadi 80% dari lebar layar */
        max-width: 1200px; /* Maksimal lebar container agar tidak terlalu lebar di layar besar */
        background-color: rgba(255, 255, 255, 0.8);
        padding: 30px; /* Tambahkan padding agar konten lebih longgar */
        border-radius: 10px;
        text-align: center;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    /* Tabel leaderboard lebih lebar dan estetis */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table, th, td {
        border: 1px solid #ddd;
        padding: 12px; /* Lebih banyak padding agar terlihat lebih lega */
    }

    th {
        background-color: #f2f2f2;
        text-align: center;
    }

    td {
        text-align: center;
    }

</style>

<body>
<div class="container">
        <h1>Scream Scaler</h1>
        
        <!-- Form input nama -->
        <input type="text" id="name" placeholder="Enter your name" required>
        <button id="startCountdown" disabled>Start Screaming</button>

        <!-- Countdown Visual Sebelum Mulai -->
        <div id="countdown" class="hidden">3</div>

        <!-- Countdown untuk berteriak -->
        <div id="screamCountdown" class="hidden">3</div>

        <!-- Indicator Scale Bar untuk teriakan -->
        <h3>Your Scream Intensity:</h3>
        <progress id="screamBar" value="0" max="100"></progress>

        <!-- Tabel Leaderboard -->
        <h2>Leaderboard</h2>
        <table border="1">
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

 <!-- Notifikasi Pop-up -->
 <div id="notification">Name already exists in the leaderboard!</div>

    <script>
        let audioContext;
        let meter;
        let screamScale = 0;
    
        const nameInput = document.getElementById('name');
        const startButton = document.getElementById('startCountdown');
        const countdownDiv = document.getElementById('countdown');
        const screamCountdownDiv = document.getElementById('screamCountdown');
        const screamBar = document.getElementById('screamBar');
    
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
    
        function startScreamProcess() {
            let screamTime = 3;
            screamScale = 0;
            screamBar.value = 0; // Reset progress bar
            screamCountdownDiv.classList.remove('hidden');
    
            // Aktifkan mikrofon
            navigator.mediaDevices.getUserMedia({ audio: true })
            .then(function(stream) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                let mediaStreamSource = audioContext.createMediaStreamSource(stream);
    
                meter = createAudioMeter(audioContext);
                mediaStreamSource.connect(meter);
    
                // Update deteksi suara selama 3 detik
                const screamInterval = setInterval(() => {
                    screamScale = Math.round(meter.volume * 1000);
                    screamBar.value = Math.min(screamScale, 100); // Update bar volume
                }, 100);
    
                // Countdown untuk durasi teriakan
                const screamCountdownInterval = setInterval(() => {
                    screamCountdownDiv.textContent = screamTime;
                    screamTime--;
                    if (screamTime < 0) {
                        clearInterval(screamCountdownInterval);
                        clearInterval(screamInterval);
                        screamCountdownDiv.classList.add('hidden');
                        submitScreamScore(); // Kirim score setelah waktu habis
                    }
                }, 1000);
            });
        }
    
        // Buat deteksi audio (audio meter)
        function createAudioMeter(audioContext) {
            let processor = audioContext.createScriptProcessor(512);
            processor.onaudioprocess = function(event) {
                let input = event.inputBuffer.getChannelData(0);
                let sum = 0.0;
                for (let i = 0; i < input.length; ++i) {
                    sum += input[i] * input[i];
                }
                meter.volume = Math.sqrt(sum / input.length);
            };
            processor.connect(audioContext.destination);
            return processor;
        }
    
        // Kirim hasil score secara otomatis ke server
        function submitScreamScore() {
            const name = nameInput.value;
            if (!name || screamScale === 0) return;
    
            fetch('/leaderboard', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: name, scream_scale: screamScale })
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    return response.json().then(data => { throw new Error(data.error); });
                }
            })
            .then(data => {
                alert('Score successfully submitted!');
                updateLeaderboard(data); // Update leaderboard di client
            })
            .catch(error => {
                alert(error.message); // Menampilkan notifikasi jika skor tidak masuk leaderboard
            });
    
            // Reset progress bar setelah submit
            screamBar.value = 0;
        }
    
        // Update leaderboard di halaman tanpa reload
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
    </script>
    

</body>
</html>
