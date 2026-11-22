<?php
require __DIR__.'/config.php';
$pdo = db();

$bodies = $pdo->query("SELECT id, name, state, latitude, longitude FROM water_bodies ORDER BY name")->fetchAll();
$bodyId = filter_input(INPUT_GET, 'body', FILTER_VALIDATE_INT) ?: 0;
$parameter = $_GET['parameter'] ?? 'wqi';
$allowed = ['wqi','ph','dissolved_oxygen','turbidity','temperature','tds'];
if (!in_array($parameter, $allowed, true)) $parameter = 'wqi';

$where = '';
$params = [];
if ($bodyId > 0) { $where = 'WHERE m.water_body_id = :body'; $params['body'] = $bodyId; }

$sql = "SELECT m.*, w.name AS body_name, w.state
        FROM measurements m JOIN water_bodies w ON w.id=m.water_body_id
        $where ORDER BY m.measured_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$measurements = $stmt->fetchAll();

$latestSql = "SELECT w.id,w.name,w.state,m.wqi,m.measured_at
              FROM water_bodies w
              JOIN measurements m ON m.water_body_id=w.id
              JOIN (SELECT water_body_id,MAX(measured_at) latest
                    FROM measurements GROUP BY water_body_id) x
                ON x.water_body_id=m.water_body_id AND x.latest=m.measured_at
              ORDER BY w.name";
$latest = $pdo->query($latestSql)->fetchAll();

$labels = array_reverse(array_map(fn($r)=>date('d/m/Y H:i', strtotime($r['measured_at'])), $measurements));
$values = array_reverse(array_map(fn($r)=>(float)$r[$parameter], $measurements));
$parameterNames = [
 'wqi'=>'Índice de calidad (WQI-NSF)','ph'=>'pH',
 'dissolved_oxygen'=>'Oxígeno disuelto (mg/L)','turbidity'=>'Turbidez (NTU)',
 'temperature'=>'Temperatura (°C)','tds'=>'Sólidos disueltos (mg/L)'
];
function qualityLabel($v): string {
    if ($v >= 90) return 'Excelente';
    if ($v >= 70) return 'Buena';
    if ($v >= 50) return 'Media';
    if ($v >= 25) return 'Mala';
    return 'Muy mala';
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Observatorio de calidad del agua</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{background:#f3f6f8;color:#183044}.navbar{background:#063b55}.hero{background:linear-gradient(120deg,#064b68,#168a9b);color:white;border-radius:18px}
.card{border:0;border-radius:16px;box-shadow:0 3px 16px #15384a12}.metric{font-size:1.8rem;font-weight:700}.muted{color:#6c7d89}
.badge-quality{background:#e0f2f1;color:#00695c}.table thead th{color:#547080;font-size:.85rem}
</style>
</head>
<body>
<nav class="navbar navbar-dark"><div class="container"><span class="navbar-brand fw-semibold">AGUA<span class="text-info">DATA</span> · Observatorio nacional</span><span class="text-white-50 small">Prototipo académico</span></div></nav>
<main class="container py-4">
<section class="hero p-4 p-md-5 mb-4">
 <div class="row align-items-center"><div class="col-lg-8">
 <div class="text-uppercase small fw-semibold mb-2">Monitoreo de cuerpos continentales lénticos</div>
 <h1 class="display-6 fw-bold">Calidad del agua en lagos y presas de México</h1>
 <p class="mb-0">Consulta indicadores fisicoquímicos, compara sitios y explora su evolución temporal.</p>
 </div><div class="col-lg-4 text-lg-end mt-3 mt-lg-0"><span class="badge text-bg-light p-2">Datos demostrativos</span></div></div>
</section>

<form class="card p-3 mb-4" method="get">
 <div class="row g-3 align-items-end">
  <div class="col-md-6"><label class="form-label">Cuerpo de agua</label><select class="form-select" name="body"><option value="0">Todos los cuerpos de agua</option>
  <?php foreach($bodies as $b): ?><option value="<?=$b['id']?>" <?=$bodyId===(int)$b['id']?'selected':''?>><?=htmlspecialchars($b['name'].' — '.$b['state'])?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Variable a visualizar</label><select class="form-select" name="parameter">
  <?php foreach($parameterNames as $key=>$name): ?><option value="<?=$key?>" <?=$parameter===$key?'selected':''?>><?=htmlspecialchars($name)?></option><?php endforeach; ?></select></div>
  <div class="col-md-2 d-grid"><button class="btn btn-primary">Actualizar</button></div>
 </div>
</form>

<div class="row g-3 mb-4">
<?php
$count=count($latest);
$avg=$count ? array_sum(array_map(fn($r)=>(float)$r['wqi'],$latest))/$count : 0;
$best=$count ? max(array_map(fn($r)=>(float)$r['wqi'],$latest)) : 0;
?>
 <div class="col-md-4"><div class="card p-3"><div class="muted">Cuerpos con registros</div><div class="metric"><?=$count?></div><small class="muted">En la base de datos</small></div></div>
 <div class="col-md-4"><div class="card p-3"><div class="muted">WQI promedio más reciente</div><div class="metric"><?=number_format($avg,1)?><small class="fs-6"> / 100</small></div><small class="muted">Promedio entre cuerpos registrados</small></div></div>
 <div class="col-md-4"><div class="card p-3"><div class="muted">Mayor WQI registrado recientemente</div><div class="metric"><?=number_format($best,1)?></div><small class="muted">Escala demostrativa 0–100</small></div></div>
</div>

<div class="row g-3">
 <div class="col-lg-8"><div class="card p-3 h-100">
  <div class="d-flex justify-content-between flex-wrap gap-2"><div><h2 class="h5 mb-1">Evolución de la variable</h2><div class="muted small"><?=htmlspecialchars($parameterNames[$parameter])?></div></div></div>
  <canvas id="trend" height="115"></canvas>
 </div></div>
 <div class="col-lg-4"><div class="card p-3 h-100"><h2 class="h5">Último registro por cuerpo</h2>
  <?php foreach($latest as $r): ?><div class="border-bottom py-2"><div class="d-flex justify-content-between"><strong><?=htmlspecialchars($r['name'])?></strong><span class="badge badge-quality"><?=htmlspecialchars(qualityLabel((float)$r['wqi']))?></span></div><div class="d-flex justify-content-between small muted"><span><?=htmlspecialchars($r['state'])?></span><span>WQI <?=number_format((float)$r['wqi'],1)?></span></div></div><?php endforeach; ?>
 </div></div>
</div>

<div class="card p-3 mt-4">
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><h2 class="h5 mb-0">Registros de monitoreo</h2><span class="muted small"><?=count($measurements)?> observaciones</span></div>
 <div class="table-responsive mt-3"><table class="table table-hover align-middle">
 <thead><tr><th>Cuerpo de agua</th><th>Fecha</th><th>WQI</th><th>pH</th><th>OD (mg/L)</th><th>Turbidez (NTU)</th><th>Temp. (°C)</th><th>TDS (mg/L)</th></tr></thead><tbody>
 <?php foreach($measurements as $m): ?><tr>
 <td><?=htmlspecialchars($m['body_name'])?></td><td><?=date('d/m/Y H:i',strtotime($m['measured_at']))?></td>
 <td><strong><?=number_format((float)$m['wqi'],1)?></strong></td><td><?=number_format((float)$m['ph'],2)?></td>
 <td><?=number_format((float)$m['dissolved_oxygen'],2)?></td><td><?=number_format((float)$m['turbidity'],2)?></td>
 <td><?=number_format((float)$m['temperature'],2)?></td><td><?=number_format((float)$m['tds'],1)?></td>
 </tr><?php endforeach; ?>
 </tbody></table></div>
 <p class="small muted mb-0">La clasificación WQI mostrada es orientativa y utiliza umbrales simplificados para esta demostración. No sustituye el cálculo oficial del índice ni una evaluación normativa.</p>
</div>
<footer class="text-center muted small py-4">AGUADATA · Ejemplo de aplicación PHP + Apache + MySQL</footer>
</main>
<script>
const labels = <?=json_encode($labels, JSON_UNESCAPED_UNICODE)?>;
const values = <?=json_encode($values)?>;
const parameter = <?=json_encode($parameterNames[$parameter], JSON_UNESCAPED_UNICODE)?>;
new Chart(document.getElementById('trend'), {
 type:'line',
 data:{labels,datasets:[{label:parameter,data:values,borderWidth:2,tension:.25,fill:true,pointRadius:3}]},
 options:{responsive:true,plugins:{legend:{display:true}},scales:{y:{beginAtZero:false}}}
});
</script>
</body></html>
