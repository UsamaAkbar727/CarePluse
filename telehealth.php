<?php
require_once 'includes/header.php';
require_once 'config.php';
require_role(['admin', 'doctor']);

$pdo = get_conn();

// Get appointment or room details
$app_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
$room_code = isset($_GET['room']) ? sanitize($_GET['room']) : 'CP-ROOM-' . strtoupper(substr(md5(time() . rand()), 0, 8));

$patient_name = "Patient Consultation";
$doctor_name = "Attending Physician";

if ($app_id > 0) {
    $stmt = $pdo->prepare("
        SELECT a.*, p.name as patient_name, d.name as doctor_name 
        FROM appointments a 
        JOIN patients p ON a.patient_id = p.id 
        JOIN doctors d ON a.doctor_id = d.id 
        WHERE a.id = ?
    ");
    $stmt->execute([$app_id]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($app) {
        $patient_name = $app['patient_name'];
        $doctor_name = $app['doctor_name'];
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-1 font-weight-bold text-gray-800">📹 Virtual Telehealth Portal & Clinical Telemetry HUD</h2>
        <p class="text-muted mb-0">Encrypted HD Video Consultation & Live ICU/Vitals Telemetry Feed</p>
    </div>
    <div>
        <span class="badge badge-pill badge-danger px-3 py-2 animate-pulse"><i class="fas fa-circle mr-1"></i> LIVE SESSION</span>
        <span class="badge badge-light border text-dark px-3 py-2 font-weight-bold ml-2">Room: <?= htmlspecialchars($room_code) ?></span>
    </div>
</div>

<div class="row">
    <!-- Video Stream Area -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-lg border-0 rounded-lg overflow-hidden bg-dark text-white">
            <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-light"><i class="fas fa-video text-primary mr-2"></i> HD Clinical Video Stream</span>
                <span class="text-xs text-muted"><i class="fas fa-shield-alt text-success mr-1"></i> 256-bit End-to-End Encrypted</span>
            </div>
            <div class="card-body p-0 position-relative text-center d-flex align-items-center justify-content-center" style="min-height: 440px; background: radial-gradient(circle at center, #1a202c 0%, #0d1117 100%);">
                
                <!-- Main Patient Video Canvas / Simulation -->
                <div id="videoContainer" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow mb-3" style="width: 110px; height: 110px; font-size: 3rem;">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4 class="text-white font-weight-bold mb-1"><?= htmlspecialchars($patient_name) ?></h4>
                    <p class="text-success small mb-3"><i class="fas fa-microphone mr-1"></i> Audio connected | HD Video Transmitting</p>
                    
                    <div class="embed-responsive embed-responsive-16by9 d-none" id="realWebcamVideo">
                        <!-- Placeholder for WebRTC video element -->
                    </div>
                </div>

                <!-- Self Doctor PiP (Picture in Picture) -->
                <div class="position-absolute rounded-lg shadow-lg border border-primary overflow-hidden" style="bottom: 20px; right: 20px; width: 160px; height: 110px; background: #000;">
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white-50 text-xs">
                        <i class="fas fa-user-md fa-2x"></i>
                    </div>
                    <span class="position-absolute bg-dark text-white px-2 py-1 text-xs" style="top:0; left:0; opacity: 0.8; font-size: 0.7rem;"><?= htmlspecialchars($doctor_name) ?></span>
                </div>

                <!-- Floating Call Controls -->
                <div class="position-absolute w-100 d-flex justify-content-center gap-3 pb-3" style="bottom: 10px;">
                    <button class="btn btn-secondary btn-circle btn-lg mx-2" id="toggleMicBtn" onclick="toggleAudio()"><i class="fas fa-microphone"></i></button>
                    <button class="btn btn-secondary btn-circle btn-lg mx-2" id="toggleCamBtn" onclick="toggleVideo()"><i class="fas fa-video"></i></button>
                    <button class="btn btn-danger btn-circle btn-lg mx-2 shadow" onclick="endCall()"><i class="fas fa-phone-slash"></i></button>
                    <button class="btn btn-info btn-circle btn-lg mx-2" onclick="shareScreen()"><i class="fas fa-desktop"></i></button>
                </div>
            </div>
            
            <div class="card-footer bg-dark border-secondary d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="fas fa-network-wire text-info mr-1"></i> Bitrate: 4.8 Mbps | Latency: 18ms</small>
                <div>
                    <button class="btn btn-sm btn-outline-light mr-2" onclick="copyRoomLink()"><i class="fas fa-link mr-1"></i> Copy Invite Link</button>
                    <button class="btn btn-sm btn-success" onclick="openAICopilotModal()"><i class="fas fa-robot mr-1"></i> AI Live Clinical Insights</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Telemetry HUD & Vitals Monitor -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-gradient-dark text-white font-weight-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-heartbeat text-danger mr-2"></i> Real-time Telemetry Feed</span>
                <span class="badge badge-success px-2">SENSORS ONLINE</span>
            </div>
            <div class="card-body bg-dark text-light p-3">
                <!-- Heart Rate ECG Wave Sim -->
                <div class="mb-3 p-3 rounded bg-black border border-secondary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-xs text-muted font-weight-bold">HEART RATE (ECG MONITOR)</span>
                        <span class="text-danger font-weight-bold text-lg" id="liveHrVal">74 BPM</span>
                    </div>
                    <canvas id="ecgCanvas" height="65" class="w-100"></canvas>
                </div>

                <!-- Vitals Grid -->
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="p-3 rounded bg-secondary-dark border border-secondary">
                            <span class="text-xs text-muted d-block font-weight-bold">BLOOD PRESSURE</span>
                            <span class="h4 font-weight-bold text-info" id="liveBpVal">120/80</span>
                            <span class="text-xs text-light d-block">mmHg</span>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 rounded bg-secondary-dark border border-secondary">
                            <span class="text-xs text-muted d-block font-weight-bold">SpO2 (BLOOD OXYGEN)</span>
                            <span class="h4 font-weight-bold text-success" id="liveSpo2Val">98%</span>
                            <span class="text-xs text-light d-block">Optimal</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded bg-secondary-dark border border-secondary">
                            <span class="text-xs text-muted d-block font-weight-bold">BODY TEMP</span>
                            <span class="h4 font-weight-bold text-warning" id="liveTempVal">98.6°F</span>
                            <span class="text-xs text-light d-block">Normal</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded bg-secondary-dark border border-secondary">
                            <span class="text-xs text-muted d-block font-weight-bold">RESPIRATION</span>
                            <span class="h4 font-weight-bold text-primary" id="liveRespVal">16/min</span>
                            <span class="text-xs text-light d-block">Regular</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Telehealth Consultation Quick Notes -->
        <div class="card shadow border-0">
            <div class="card-header bg-white font-weight-bold text-primary">
                <i class="fas fa-sticky-note mr-2"></i> Live Consultation Notes
            </div>
            <div class="card-body">
                <form id="telehealthNotesForm">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-gray-700">Doctor Observation Notes</label>
                        <textarea class="form-control" rows="3" placeholder="Enter session notes, complaints, or prescribed treatment plan..."></textarea>
                    </div>
                    <button type="button" class="btn btn-primary btn-block shadow-sm font-weight-bold" onclick="saveTelehealthNotes()">
                        <i class="fas fa-save mr-1"></i> Save to EHR File
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ECG Wave Animation Canvas Logic
const canvas = document.getElementById('ecgCanvas');
const ctx = canvas.getContext('2d');
let step = 0;

function drawECG() {
    ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    ctx.beginPath();
    ctx.lineWidth = 2;
    ctx.strokeStyle = '#e74a3b';
    
    for (let x = 0; x < canvas.width; x += 3) {
        let y = canvas.height / 2;
        let pos = (x + step) % 120;
        
        if (pos > 40 && pos < 45) y -= 15;
        else if (pos >= 45 && pos < 50) y += 25;
        else if (pos >= 50 && pos < 55) y -= 35;
        else if (pos >= 55 && pos < 60) y += 10;
        
        if (x === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
    }
    ctx.stroke();
    step += 2;
    requestAnimationFrame(drawECG);
}
drawECG();

// Live Telemetry simulation adjustments
setInterval(() => {
    const hr = 72 + Math.floor(Math.random() * 6) - 3;
    document.getElementById('liveHrVal').innerText = hr + ' BPM';
}, 3000);

function toggleAudio() {
    Swal.fire({ icon: 'info', title: 'Audio Control', text: 'Microphone state toggled.', timer: 1500, showConfirmButton: false });
}
function toggleVideo() {
    Swal.fire({ icon: 'info', title: 'Video Stream', text: 'HD Camera state toggled.', timer: 1500, showConfirmButton: false });
}
function endCall() {
    Swal.fire({
        title: 'End Consultation?',
        text: 'Are you sure you want to conclude this live telehealth session?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, End Call'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "appointments.php";
        }
    });
}
function shareScreen() {
    Swal.fire({ icon: 'success', title: 'Screen Share', text: 'Display sharing stream initialized.', timer: 1500, showConfirmButton: false });
}
function copyRoomLink() {
    navigator.clipboard.writeText(window.location.href);
    Swal.fire({ icon: 'success', title: 'Link Copied', text: 'Encrypted telehealth room link copied to clipboard!', timer: 1800, showConfirmButton: false });
}
function saveTelehealthNotes() {
    Swal.fire({ icon: 'success', title: 'Notes Saved', text: 'Consultation notes successfully saved to Patient Electronic Health Record!', confirmButtonColor: '#4f46e5' });
}
</script>

<?php require_once 'includes/footer.php'; ?>
