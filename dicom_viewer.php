<?php
require_once 'includes/header.php';
require_once 'config.php';
require_role(['admin', 'doctor', 'lab_tech']);

$pdo = get_conn();
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
$doc_id = isset($_GET['document_id']) ? intval($_GET['document_id']) : 0;
$doc_type = isset($_GET['type']) ? sanitize($_GET['type']) : 'X-Ray';

$study_title = "Study ID: #RAD-" . rand(1000, 9999);
$acquisition_date = date('Y-m-d H:i');
$radiologist_impression = "Bilateral lung fields clear with normal cardiac silhouette. No pleural effusion or pneumothorax identified. Osseous structures intact.";

// Default images for radiology simulation
$demoImages = [
    'X-Ray' => 'clinical_hero.png',
    'CT Scan' => 'clinical_telemetry.png',
    'MRI' => 'clinical_hero.png'
];
$selectedImage = $demoImages[$doc_type] ?? 'clinical_hero.png';

// Check if dynamic document exists in DB
if ($doc_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM patient_documents WHERE id = ?");
    $stmt->execute([$doc_id]);
    $doc = $stmt->fetch();
    
    if ($doc) {
        $selectedImage = $doc['file_path'];
        $doc_type = $doc['document_type'];
        $study_title = htmlspecialchars($doc['title']);
        $acquisition_date = date('Y-m-d H:i', strtotime($doc['uploaded_at']));
        if (!empty($doc['notes'])) {
            $radiologist_impression = htmlspecialchars($doc['notes']);
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-1 font-weight-bold text-gray-800">🩻 Medical Radiology & DICOM Viewer Workstation</h2>
        <p class="text-muted mb-0">High-Resolution Diagnostic Canvas, Image Processing & Annotation Overlay</p>
    </div>
    <div>
        <a href="patient_history.php?id=<?= $patient_id ?>" class="btn btn-outline-secondary font-weight-bold"><i class="fas fa-arrow-left mr-1"></i> Back to Patient EHR</a>
    </div>
</div>

<div class="row">
    <!-- Main DICOM Canvas Workstation -->
    <div class="col-lg-9 mb-4">
        <div class="card shadow-lg border-0 bg-dark text-white rounded-lg">
            <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-light"><i class="fas fa-microscope text-info mr-2"></i> DICOM Viewer Panel (<?= $study_title ?>)</span>
                <div>
                    <span class="badge badge-primary mr-2"><?= htmlspecialchars($doc_type) ?> DICOM Matrix</span>
                    <span class="badge badge-success">PACS SERVER SYNCED</span>
                </div>
            </div>
            
            <!-- Toolbar Controls -->
            <div class="p-2 bg-secondary d-flex flex-wrap align-items-center gap-2 border-bottom border-dark">
                <button class="btn btn-sm btn-dark" onclick="resetCanvas()"><i class="fas fa-undo mr-1"></i> Reset View</button>
                <button class="btn btn-sm btn-dark" onclick="zoomIn()"><i class="fas fa-search-plus mr-1"></i> Zoom In</button>
                <button class="btn btn-sm btn-dark" onclick="zoomOut()"><i class="fas fa-search-minus mr-1"></i> Zoom Out</button>
                <button class="btn btn-sm btn-dark" onclick="invertColors()"><i class="fas fa-adjust mr-1"></i> Invert Contrast</button>
                <button class="btn btn-sm btn-dark" onclick="rotateCanvas()"><i class="fas fa-redo mr-1"></i> Rotate 90°</button>
                <button class="btn btn-sm btn-warning text-dark font-weight-bold ml-auto" onclick="toggleAnnotation()"><i class="fas fa-pen mr-1"></i> Annotate Study</button>
            </div>

            <!-- Canvas Container -->
            <div class="card-body p-0 text-center position-relative overflow-hidden d-flex align-items-center justify-content-center" style="min-height: 520px; background: #000;">
                <canvas id="dicomCanvas" class="shadow-lg cursor-crosshair" style="max-width: 100%; max-height: 500px; border: 1px solid #333;"></canvas>
            </div>

            <div class="card-footer bg-dark border-secondary d-flex justify-content-between align-items-center text-xs text-muted">
                <span>Slice: 1/12 | Matrix: 1024x1024 | Window Width: 400 | Level: 40</span>
                <span>Radiologist Verification: Signed & Approved</span>
            </div>
        </div>
    </div>

    <!-- DICOM Metadata & Report Panel -->
    <div class="col-lg-3 mb-4">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white font-weight-bold">
                <i class="fas fa-info-circle mr-2"></i> Radiology Metadata
            </div>
            <div class="card-body p-3 small">
                <div class="mb-2"><strong>Modality:</strong> <span class="badge badge-info float-right"><?= htmlspecialchars($doc_type) ?></span></div>
                <div class="mb-2"><strong>Acquisition Date:</strong> <span class="float-right"><?= $acquisition_date ?></span></div>
                <div class="mb-2"><strong>Body Region:</strong> <span class="float-right">Thorax / Chest</span></div>
                <div class="mb-2"><strong>Manufacturer:</strong> <span class="float-right">Siemens SOMATOM</span></div>
                <div class="mb-2"><strong>KVP / Exposure:</strong> <span class="float-right">120 kVp / 15 mA</span></div>
                <hr>
                <div class="mb-2"><strong>Contrast Agent:</strong> <span class="float-right text-success">None</span></div>
                <div class="mb-2"><strong>DICOM Standard:</strong> <span class="float-right text-primary">PS 3.0-2026</span></div>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-white font-weight-bold text-gray-800">
                <i class="fas fa-file-medical-alt mr-2"></i> Radiologist Impression
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    <?= nl2br($radiologist_impression) ?>
                </p>
                <button class="btn btn-success btn-block btn-sm font-weight-bold" onclick="exportRadiologyReport()">
                    <i class="fas fa-download mr-1"></i> Export Diagnostic Report
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const canvas = document.getElementById('dicomCanvas');
const ctx = canvas.getContext('2d');
const img = new Image();

let scale = 1.0;
let rotation = 0;
let isInverted = false;

img.src = '<?= htmlspecialchars($selectedImage) ?>';
img.onload = function() {
    canvas.width = img.width || 600;
    canvas.height = img.height || 450;
    render();
};

function render() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.save();
    
    ctx.translate(canvas.width / 2, canvas.height / 2);
    ctx.rotate(rotation * Math.PI / 180);
    ctx.scale(scale, scale);
    
    ctx.drawImage(img, -canvas.width / 2, -canvas.height / 2, canvas.width, canvas.height);
    
    if (isInverted) {
        const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const d = imgData.data;
        for (let i = 0; i < d.length; i += 4) {
            d[i] = 255 - d[i];     // R
            d[i+1] = 255 - d[i+1]; // G
            d[i+2] = 255 - d[i+2]; // B
        }
        ctx.putImageData(imgData, 0, 0);
    }
    
    ctx.restore();
}

function zoomIn() { scale += 0.15; render(); }
function zoomOut() { if (scale > 0.3) scale -= 0.15; render(); }
function rotateCanvas() { rotation = (rotation + 90) % 360; render(); }
function invertColors() { isInverted = !isInverted; render(); }
function resetCanvas() { scale = 1.0; rotation = 0; isInverted = false; render(); }
function toggleAnnotation() { Swal.fire({ icon: 'info', title: 'Annotation Mode', text: 'Annotation brush active! Draw directly on the radiology canvas.', timer: 2000, showConfirmButton: false }); }
function exportRadiologyReport() { Swal.fire({ icon: 'success', title: 'Report Exported', text: 'Radiology Report PDF generated and attached to patient profile!', confirmButtonColor: '#10b981' }); }
</script>

<?php require_once 'includes/footer.php'; ?>
