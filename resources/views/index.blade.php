<html>
<head>
    <title>Scream Scaler</title>
</head>
<body>
    <h1>Scream Scaler</h1>
    
    <!-- Form untuk input dan teriakan -->
    <form method="POST" action="{{ route('leaderboard.store') }}">
        @csrf
        <input type="text" name="name" placeholder="Your Name" required>
        <button type="button" id="start">Start Screaming</button>
        <input type="hidden" id="scream_scale" name="scream_scale">
        <button type="submit">Submit</button>
    </form>

    <!-- Tabel Leaderboard -->
    <h2>Leaderboard</h2>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Name</th>
                <th>Scream Scale</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leaderboards as $key => $leaderboard)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $leaderboard->name }}</td>
                <td>{{ $leaderboard->scream_scale }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        let audioContext;
        let meter;

        document.getElementById('start').addEventListener('click', function() {
            navigator.mediaDevices.getUserMedia({ audio: true })
            .then(function(stream) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                let mediaStreamSource = audioContext.createMediaStreamSource(stream);

                meter = createAudioMeter(audioContext);
                mediaStreamSource.connect(meter);

                requestAnimationFrame(drawLoop);
            });
        });

        function drawLoop() {
            let screamScale = Math.round(meter.volume * 1000);
            document.getElementById('scream_scale').value = screamScale;
            requestAnimationFrame(drawLoop);
        }

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
    </script>
</body>
</html>
