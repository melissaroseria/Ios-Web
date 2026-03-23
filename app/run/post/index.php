<?php
// ─── API Proxy Handler ────────────────────────────────────────────────────────
if (isset($_POST['__action'])) {
    header('Content-Type: application/json');

    if ($_POST['__action'] === 'send_request') {
        $url     = trim($_POST['url'] ?? '');
        $method  = strtoupper($_POST['method'] ?? 'GET');
        $body    = $_POST['body'] ?? '';
        $headers = json_decode($_POST['headers'] ?? '[]', true);

        if (!$url) { echo json_encode(['error' => 'URL boş olamaz']); exit; }
        if (!preg_match('/^https?:\/\//i', $url)) { echo json_encode(['error' => 'Geçersiz URL']); exit; }

        $ch = curl_init();
        $curlHeaders = [];
        foreach ((array)$headers as $h) {
            if (!empty($h['key'])) $curlHeaders[] = $h['key'] . ': ' . $h['value'];
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => $curlHeaders,
            CURLOPT_HEADER         => true,
        ]);

        if (in_array($method, ['POST','PUT','PATCH']) && $body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $start    = microtime(true);
        $raw      = curl_exec($ch);
        $elapsed  = round((microtime(true) - $start) * 1000);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) { echo json_encode(['error' => $error]); exit; }

        $rawHeaders = substr($raw, 0, $headerSize);
        $respBody   = substr($raw, $headerSize);
        $parsedHeaders = [];
        foreach (explode("\r\n", $rawHeaders) as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $parsedHeaders[trim($k)] = trim($v);
            }
        }

        echo json_encode([
            'status'  => $httpCode,
            'time'    => $elapsed,
            'size'    => strlen($respBody),
            'headers' => $parsedHeaders,
            'body'    => $respBody,
        ]);
        exit;
    }

    if ($_POST['__action'] === 'save_collection') {
        $file = __DIR__ . '/collections.json';
        $cols = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $cols[] = [
            'id'      => uniqid(),
            'name'    => htmlspecialchars($_POST['name'] ?? 'Yeni İstek'),
            'method'  => $_POST['method'] ?? 'GET',
            'url'     => $_POST['url'] ?? '',
            'headers' => $_POST['headers'] ?? '[]',
            'body'    => $_POST['body'] ?? '',
        ];
        file_put_contents($file, json_encode($cols));
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($_POST['__action'] === 'delete_collection') {
        $file = __DIR__ . '/collections.json';
        $cols = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $cols = array_values(array_filter($cols, fn($c) => $c['id'] !== $_POST['id']));
        file_put_contents($file, json_encode($cols));
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['error' => 'Bilinmeyen aksiyon']);
    exit;
}

// ─── Load saved collections ───────────────────────────────────────────────────
$collectionsFile = __DIR__ . '/collections.json';
$savedCollections = file_exists($collectionsFile)
    ? json_decode(file_get_contents($collectionsFile), true) ?? []
    : [];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>API Tester</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<style>
/* ── Reset & Base ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --bg:        #0a0a0f;
    --bg2:       #111118;
    --surface:   rgba(255,255,255,0.04);
    --surface2:  rgba(255,255,255,0.07);
    --border:    rgba(255,255,255,0.08);
    --text:      #f2f2f7;
    --muted:     #8e8e93;
    --dim:       #48484a;
    --blue:      #0a84ff;
    --green:     #30d158;
    --orange:    #ff9f0a;
    --red:       #ff453a;
    --purple:    #bf5af2;
    --yellow:    #ffd60a;
    --radius:    14px;
    --radius-sm: 9px;
    --mono:      'JetBrains Mono', monospace;
}
html { scroll-behavior: smooth; }
body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Inter', -apple-system, sans-serif;
    font-size: 14px;
    line-height: 1.5;
    min-height: 100vh;
    overflow-x: hidden;
}
body::before {
    content: '';
    position: fixed; inset: 0; z-index: 0;
    background:
        radial-gradient(ellipse 800px 500px at 20% 0%, rgba(10,132,255,.07) 0%, transparent 70%),
        radial-gradient(ellipse 600px 400px at 80% 100%, rgba(191,90,242,.05) 0%, transparent 70%);
    pointer-events: none;
}
::-webkit-scrollbar { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 4px; }

/* ── Layout ── */
.app { position: relative; z-index: 1; display: flex; height: 100vh; overflow: hidden; }

/* ── Sidebar ── */
.sidebar {
    width: 280px; flex-shrink: 0;
    background: rgba(17,17,24,0.95);
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column;
    backdrop-filter: blur(20px);
    transition: transform .3s cubic-bezier(.4,0,.2,1);
}
.sidebar-header {
    padding: 20px 18px 14px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.sidebar-header h2 { font-size: 13px; font-weight: 700; color: var(--muted); letter-spacing: .8px; text-transform: uppercase; }
.btn-icon {
    width: 30px; height: 30px; border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--muted); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; transition: all .15s;
}
.btn-icon:hover { background: var(--surface2); color: var(--text); }

.collections { flex: 1; overflow-y: auto; padding: 10px; }
.collection-item {
    display: flex; align-items: center; gap: 9px;
    padding: 10px 11px; border-radius: var(--radius-sm);
    cursor: pointer; margin-bottom: 3px;
    transition: background .15s; position: relative;
    border: 1px solid transparent;
}
.collection-item:hover { background: var(--surface); border-color: var(--border); }
.collection-item.active { background: rgba(10,132,255,.1); border-color: rgba(10,132,255,.2); }
.col-badge {
    font-size: 9px; font-weight: 800; font-family: var(--mono);
    padding: 2px 6px; border-radius: 5px; flex-shrink: 0;
    letter-spacing: .3px;
}
.col-name { font-size: 13px; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.col-delete { opacity: 0; font-size: 14px; color: var(--red); cursor: pointer; padding: 2px 5px; border-radius: 5px; border: none; background: transparent; transition: opacity .15s; }
.collection-item:hover .col-delete { opacity: 1; }
.empty-collections { text-align: center; padding: 30px 16px; color: var(--dim); font-size: 13px; }
.empty-collections .icon { font-size: 32px; margin-bottom: 8px; }

/* ── Main ── */
.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

/* ── Top bar ── */
.topbar {
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px;
    background: rgba(17,17,24,0.6);
    backdrop-filter: blur(20px);
    flex-shrink: 0;
}
.app-logo { font-size: 15px; font-weight: 800; letter-spacing: -.3px; }
.app-logo span { color: var(--blue); }
.topbar-spacer { flex: 1; }

/* ── URL bar ── */
.urlbar {
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    display: flex; gap: 8px; align-items: center;
    flex-shrink: 0;
}
.method-wrap { position: relative; }
.method-btn {
    padding: 10px 14px; border-radius: var(--radius-sm);
    border: 1px solid rgba(255,255,255,.1);
    background: var(--surface2);
    font-size: 12px; font-weight: 800; font-family: var(--mono);
    cursor: pointer; display: flex; align-items: center; gap: 5px;
    transition: all .15s; white-space: nowrap;
    min-width: 90px; justify-content: space-between;
}
.method-btn svg { flex-shrink: 0; opacity: .5; }
.method-dropdown {
    position: absolute; top: calc(100% + 6px); left: 0; z-index: 100;
    background: #1c1c28; border: 1px solid rgba(255,255,255,.12);
    border-radius: var(--radius); overflow: hidden;
    box-shadow: 0 16px 48px rgba(0,0,0,.5);
    min-width: 110px;
    display: none;
}
.method-dropdown.open { display: block; animation: fadeIn .15s ease; }
.method-opt {
    padding: 10px 14px; cursor: pointer;
    font-size: 12px; font-weight: 800; font-family: var(--mono);
    transition: background .1s;
}
.method-opt:hover { background: rgba(255,255,255,.06); }

.url-input {
    flex: 1; padding: 10px 14px; border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: rgba(255,255,255,.04);
    color: var(--text); font-size: 14px; outline: none;
    transition: border-color .2s;
    font-family: var(--mono);
}
.url-input:focus { border-color: var(--blue); background: rgba(10,132,255,.04); }
.url-input::placeholder { color: var(--dim); font-family: 'Inter', sans-serif; }

.btn-send {
    padding: 10px 22px; border-radius: var(--radius-sm);
    border: none; background: var(--blue);
    color: #fff; font-size: 14px; font-weight: 700;
    cursor: pointer; transition: all .2s;
    box-shadow: 0 4px 16px rgba(10,132,255,.3);
    white-space: nowrap;
}
.btn-send:hover { background: #2196ff; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(10,132,255,.4); }
.btn-send:active { transform: translateY(0); }
.btn-send:disabled { background: rgba(10,132,255,.3); box-shadow: none; transform: none; cursor: not-allowed; }

/* ── Panels ── */
.panels { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.panel-half { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }

.tabs {
    display: flex; border-bottom: 1px solid var(--border);
    flex-shrink: 0; padding: 0 16px;
}
.tab {
    padding: 11px 14px; cursor: pointer;
    font-size: 12px; font-weight: 600;
    color: var(--muted); border: none; background: transparent;
    border-bottom: 2px solid transparent; margin-bottom: -1px;
    transition: all .15s; white-space: nowrap;
}
.tab:hover { color: var(--text); }
.tab.active { color: var(--blue); border-bottom-color: var(--blue); }

.tab-body { flex: 1; overflow-y: auto; padding: 16px; }

/* ── Headers table ── */
.header-row { display: flex; gap: 8px; margin-bottom: 7px; align-items: center; }
.header-row input {
    flex: 1; padding: 9px 11px; border-radius: var(--radius-sm);
    border: 1px solid var(--border); background: var(--surface);
    color: var(--text); font-size: 13px; outline: none; transition: border-color .2s;
    font-family: var(--mono);
}
.header-row input:focus { border-color: var(--blue); }
.btn-remove {
    width: 28px; height: 28px; border-radius: 7px; flex-shrink: 0;
    border: 1px solid rgba(255,59,48,.2); background: rgba(255,59,48,.08);
    color: var(--red); cursor: pointer; font-size: 15px;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
}
.btn-remove:hover { background: rgba(255,59,48,.18); }
.btn-add {
    padding: 8px 14px; border-radius: var(--radius-sm);
    border: 1px solid rgba(10,132,255,.25); background: rgba(10,132,255,.08);
    color: var(--blue); font-size: 12px; font-weight: 600; cursor: pointer;
    transition: all .15s; margin-top: 4px;
}
.btn-add:hover { background: rgba(10,132,255,.15); }

/* ── Body textarea ── */
.body-textarea {
    width: 100%; min-height: 130px;
    padding: 12px 14px; border-radius: var(--radius);
    border: 1px solid var(--border); background: var(--surface);
    color: var(--text); font-size: 13px; font-family: var(--mono);
    line-height: 1.65; outline: none; resize: vertical;
    transition: border-color .2s;
}
.body-textarea:focus { border-color: var(--blue); }

/* ── Divider ── */
.divider { height: 1px; background: var(--border); flex-shrink: 0; }

/* ── Response ── */
.response-header {
    padding: 10px 16px; display: flex; align-items: center;
    justify-content: space-between; flex-shrink: 0;
}
.response-meta { display: flex; align-items: center; gap: 10px; }
.status-badge {
    padding: 3px 10px; border-radius: 7px;
    font-size: 12px; font-weight: 800; font-family: var(--mono);
}
.meta-chip {
    font-size: 11px; color: var(--muted);
    background: var(--surface2); padding: 3px 9px;
    border-radius: 6px; font-family: var(--mono);
}

/* ── JSON viewer ── */
.json-viewer { font-family: var(--mono); font-size: 12.5px; line-height: 1.75; }
.j-key    { color: #64d2ff; }
.j-str    { color: #ffd60a; }
.j-num    { color: #30d158; }
.j-bool   { color: #ff6b6b; }
.j-null   { color: #ff453a; }
.j-punct  { color: #636366; }

/* ── Loading spinner ── */
.spinner {
    width: 20px; height: 20px;
    border: 2.5px solid rgba(10,132,255,.2);
    border-top-color: var(--blue);
    border-radius: 50%;
    animation: spin .7s linear infinite;
    display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Buttons ── */
.btn {
    padding: 9px 16px; border-radius: var(--radius-sm);
    border: 1px solid var(--border); background: var(--surface);
    color: var(--muted); font-size: 13px; font-weight: 500;
    cursor: pointer; transition: all .15s;
}
.btn:hover { background: var(--surface2); color: var(--text); }
.btn-primary {
    border-color: rgba(10,132,255,.3); background: rgba(10,132,255,.1);
    color: var(--blue);
}
.btn-primary:hover { background: rgba(10,132,255,.18); color: var(--blue); }

/* ── Modal ── */
.modal-overlay {
    position: fixed; inset: 0; z-index: 200;
    background: rgba(0,0,0,.65); backdrop-filter: blur(8px);
    display: none; align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; animation: fadeIn .2s ease; }
.modal {
    background: #1c1c28; border: 1px solid rgba(255,255,255,.12);
    border-radius: 20px; padding: 24px;
    width: 340px; box-shadow: 0 24px 64px rgba(0,0,0,.6);
    animation: slideUp .25s cubic-bezier(.4,0,.2,1);
}
.modal h3 { font-size: 17px; font-weight: 700; margin-bottom: 16px; }
.modal input {
    width: 100%; padding: 11px 13px; border-radius: var(--radius-sm);
    border: 1px solid var(--border); background: var(--surface);
    color: var(--text); font-size: 14px; outline: none;
    margin-bottom: 16px; transition: border-color .2s;
}
.modal input:focus { border-color: var(--blue); }
.modal-btns { display: flex; gap: 10px; }
.modal-btns .btn { flex: 1; text-align: center; }
.btn-confirm { flex: 1; padding: 9px; border-radius: var(--radius-sm); border: none; background: var(--blue); color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; }

/* ── Dot indicator ── */
.live-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); box-shadow: 0 0 8px var(--green); animation: pulse 2s infinite; }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }

/* ── Animations ── */
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* ── Empty state ── */
.empty-state { text-align: center; padding: 50px 20px; color: var(--dim); }
.empty-state .icon { font-size: 44px; margin-bottom: 12px; }
.empty-state p { font-size: 15px; font-weight: 600; color: var(--muted); margin-bottom: 6px; }
.empty-state small { font-size: 13px; }

/* ── Format btn ── */
.format-btn {
    margin-top: 8px; padding: 6px 12px; border-radius: 7px;
    border: 1px solid rgba(48,209,88,.2); background: rgba(48,209,88,.08);
    color: var(--green); font-size: 11px; font-weight: 600; cursor: pointer;
    transition: all .15s;
}
.format-btn:hover { background: rgba(48,209,88,.15); }

/* ── Responsive ── */
@media (max-width: 640px) {
    .sidebar { position: fixed; top: 0; left: 0; height: 100%; z-index: 50; transform: translateX(-100%); }
    .sidebar.open { transform: translateX(0); box-shadow: 8px 0 32px rgba(0,0,0,.5); }
    .sidebar-overlay { position: fixed; inset: 0; z-index: 49; background: rgba(0,0,0,.5); display: none; }
    .sidebar-overlay.open { display: block; }
    .app { display: block; }
    .main { height: 100vh; }
    .urlbar { flex-wrap: wrap; }
    .url-input { order: 3; flex-basis: 100%; }
    .btn-send { order: 4; flex: 1; }
}
</style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="app">
<!-- ─── Sidebar ─────────────────────────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Koleksiyonlar</h2>
        <button class="btn-icon" onclick="openSaveModal()" title="Yeni kaydet">+</button>
    </div>
    <div class="collections" id="collectionList">
        <?php if (empty($savedCollections)): ?>
        <div class="empty-collections">
            <div class="icon">📂</div>
            Henüz kayıtlı istek yok.<br>
            <small>Gönderdikten sonra + ile kaydet</small>
        </div>
        <?php else: ?>
        <?php foreach ($savedCollections as $col): ?>
        <div class="collection-item" onclick="loadCollection(<?= htmlspecialchars(json_encode($col)) ?>)">
            <span class="col-badge" style="background:<?= getMethodBg($col['method']) ?>;color:<?= getMethodColor($col['method']) ?>;">
                <?= htmlspecialchars($col['method']) ?>
            </span>
            <span class="col-name"><?= htmlspecialchars($col['name']) ?></span>
            <button class="col-delete" onclick="deleteCollection(event,'<?= htmlspecialchars($col['id']) ?>')">✕</button>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</aside>

<!-- ─── Main ────────────────────────────────────────────────────────────────── -->
<div class="main">

    <!-- Topbar -->
    <div class="topbar">
        <button class="btn-icon" id="menuBtn" onclick="toggleSidebar()">☰</button>
        <span class="app-logo">API<span>Tester</span></span>
        <div style="display:flex;align-items:center;gap:6px;margin-left:8px;">
            <div class="live-dot"></div>
            <span style="font-size:11px;color:var(--muted);">PHP Proxy</span>
        </div>
        <div class="topbar-spacer"></div>
        <button class="btn btn-primary" onclick="openSaveModal()">+ Kaydet</button>
    </div>

    <!-- URL Bar -->
    <div class="urlbar">
        <div class="method-wrap">
            <button class="method-btn" id="methodBtn" onclick="toggleMethodMenu()" style="color:var(--blue);border-color:rgba(10,132,255,.25);">
                <span id="methodLabel">GET</span>
                <svg width="10" height="6" viewBox="0 0 10 6"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg>
            </button>
            <div class="method-dropdown" id="methodDropdown">
                <?php
                $methods = ['GET','POST','PUT','DELETE','PATCH'];
                foreach ($methods as $m) {
                    echo '<div class="method-opt" style="color:'.getMethodColor($m).'" onclick="setMethod(\''.$m.'\')">'.$m.'</div>';
                }
                ?>
            </div>
        </div>
        <input type="text" class="url-input" id="urlInput" placeholder="https://api.example.com/endpoint" value="https://jsonplaceholder.typicode.com/posts/1">
        <button class="btn-send" id="sendBtn" onclick="sendRequest()">Gönder ↑</button>
    </div>

    <!-- Panels -->
    <div class="panels">

        <!-- Request panel -->
        <div class="panel-half">
            <div class="tabs">
                <button class="tab active" data-tab="headers" onclick="switchReqTab(this,'headers')">Headers</button>
                <button class="tab" data-tab="body" onclick="switchReqTab(this,'body')">Body</button>
                <button class="tab" data-tab="auth" onclick="switchReqTab(this,'auth')">Auth</button>
            </div>

            <!-- Headers tab -->
            <div class="tab-body" id="req-headers">
                <div id="headerRows"></div>
                <button class="btn-add" onclick="addHeaderRow()">+ Header Ekle</button>
            </div>

            <!-- Body tab -->
            <div class="tab-body" id="req-body" style="display:none">
                <div style="display:flex;gap:6px;margin-bottom:10px;">
                    <button class="btn" style="font-size:11px;padding:5px 12px;" onclick="setBodyType('json')">JSON</button>
                    <button class="btn" style="font-size:11px;padding:5px 12px;" onclick="setBodyType('form')">Form</button>
                    <button class="btn" style="font-size:11px;padding:5px 12px;" onclick="setBodyType('raw')">Raw</button>
                </div>
                <textarea class="body-textarea" id="bodyInput" placeholder='{"key": "value"}'></textarea>
                <button class="format-btn" onclick="formatBody()">✦ JSON Formatla</button>
            </div>

            <!-- Auth tab -->
            <div class="tab-body" id="req-auth" style="display:none">
                <div style="margin-bottom:12px;color:var(--muted);font-size:13px;">Authorization türü:</div>
                <div style="display:flex;gap:6px;margin-bottom:14px;">
                    <button class="btn" style="font-size:12px;" onclick="setAuth('bearer')">Bearer</button>
                    <button class="btn" style="font-size:12px;" onclick="setAuth('apikey')">API Key</button>
                    <button class="btn" style="font-size:12px;" onclick="setAuth('basic')">Basic</button>
                </div>
                <input type="text" id="authInput" placeholder="Token veya anahtar girin..."
                    style="width:100%;padding:10px 13px;border-radius:9px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:13px;outline:none;font-family:var(--mono);"
                    oninput="applyAuth(this.value)">
            </div>
        </div>

        <div class="divider"></div>

        <!-- Response panel -->
        <div class="panel-half">
            <div class="response-header">
                <div class="tabs" style="padding:0;border:none;flex:1;">
                    <button class="tab active" data-rtab="body" onclick="switchResTab(this,'body')">Body</button>
                    <button class="tab" data-rtab="headers" onclick="switchResTab(this,'headers')">Headers</button>
                    <button class="tab" data-rtab="raw" onclick="switchResTab(this,'raw')">Raw</button>
                </div>
                <div class="response-meta" id="responseMeta" style="display:none">
                    <span class="status-badge" id="statusBadge"></span>
                    <span class="meta-chip" id="timeBadge"></span>
                    <span class="meta-chip" id="sizeBadge"></span>
                </div>
            </div>

            <!-- Response body -->
            <div class="tab-body" id="res-body">
                <div class="empty-state" id="emptyState">
                    <div class="icon">⚡</div>
                    <p>Hazır</p>
                    <small>URL girin ve Gönder'e basın</small>
                </div>
                <div id="loadingState" style="display:none;text-align:center;padding:40px;">
                    <div class="spinner"></div>
                    <div style="margin-top:12px;color:var(--muted);font-size:13px;">İstek gönderiliyor...</div>
                </div>
                <div id="jsonView" class="json-viewer" style="display:none;background:rgba(0,0,0,.25);border-radius:10px;padding:14px;overflow-x:auto;"></div>
                <div id="errorView" style="display:none;background:rgba(255,69,58,.07);border:1px solid rgba(255,69,58,.2);border-radius:10px;padding:14px;color:var(--red);font-family:var(--mono);font-size:13px;"></div>
            </div>

            <!-- Response headers -->
            <div class="tab-body" id="res-headers" style="display:none">
                <div id="resHeaderList"></div>
            </div>

            <!-- Raw -->
            <div class="tab-body" id="res-raw" style="display:none">
                <pre id="rawView" style="color:var(--text);font-family:var(--mono);font-size:12px;white-space:pre-wrap;word-break:break-all;"></pre>
            </div>
        </div>
    </div>
</div>
</div>

<!-- ─── Save Modal ── -->
<div class="modal-overlay" id="saveModal">
    <div class="modal">
        <h3>Koleksiyona Kaydet</h3>
        <input type="text" id="saveNameInput" placeholder="İstek adı..." onkeydown="if(event.key==='Enter')saveCollection()">
        <div class="modal-btns">
            <button class="btn" onclick="closeSaveModal()">İptal</button>
            <button class="btn-confirm" onclick="saveCollection()">Kaydet</button>
        </div>
    </div>
</div>

<script>
// ── State ──────────────────────────────────────────────────────────────────────
let currentMethod = 'GET';
let authType = 'bearer';
let lastResponse = null;

const METHODS = {
    GET:    { color: '#30d158', bg: 'rgba(48,209,88,.15)' },
    POST:   { color: '#0a84ff', bg: 'rgba(10,132,255,.15)' },
    PUT:    { color: '#ff9f0a', bg: 'rgba(255,159,10,.15)' },
    DELETE: { color: '#ff453a', bg: 'rgba(255,69,58,.15)'  },
    PATCH:  { color: '#bf5af2', bg: 'rgba(191,90,242,.15)' },
};

// ── Method selector ────────────────────────────────────────────────────────────
function toggleMethodMenu() {
    document.getElementById('methodDropdown').classList.toggle('open');
}
function setMethod(m) {
    currentMethod = m;
    const { color, bg } = METHODS[m] || METHODS.GET;
    const btn = document.getElementById('methodBtn');
    btn.style.color = color;
    btn.style.borderColor = color + '40';
    document.getElementById('methodLabel').textContent = m;
    document.getElementById('methodDropdown').classList.remove('open');

    // Show/hide body tab
    const bodyTab = document.querySelector('[data-tab="body"]');
    if (['GET','DELETE'].includes(m)) bodyTab.style.opacity = '.4';
    else bodyTab.style.opacity = '1';
}
document.addEventListener('click', e => {
    if (!e.target.closest('.method-wrap')) {
        document.getElementById('methodDropdown').classList.remove('open');
    }
});

// ── Tabs ───────────────────────────────────────────────────────────────────────
function switchReqTab(btn, tab) {
    document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ['headers','body','auth'].forEach(t => {
        document.getElementById('req-' + t).style.display = t === tab ? 'block' : 'none';
    });
}
function switchResTab(btn, tab) {
    document.querySelectorAll('[data-rtab]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ['body','headers','raw'].forEach(t => {
        document.getElementById('res-' + t).style.display = t === tab ? 'block' : 'none';
    });
}

// ── Headers ────────────────────────────────────────────────────────────────────
function addHeaderRow(k = '', v = '') {
    const row = document.createElement('div');
    row.className = 'header-row';
    row.innerHTML = `
        <input placeholder="Header adı" value="${escHtml(k)}" class="h-key">
        <input placeholder="Değer" value="${escHtml(v)}" class="h-val">
        <button class="btn-remove" onclick="this.closest('.header-row').remove()">×</button>`;
    document.getElementById('headerRows').appendChild(row);
}
addHeaderRow('Content-Type', 'application/json');
addHeaderRow();

function getHeaders() {
    const rows = document.querySelectorAll('.header-row');
    return Array.from(rows)
        .map(r => ({ key: r.querySelector('.h-key').value.trim(), value: r.querySelector('.h-val').value.trim() }))
        .filter(h => h.key);
}

// ── Auth ───────────────────────────────────────────────────────────────────────
function setAuth(type) { authType = type; }
function applyAuth(val) {
    // remove existing Authorization row
    document.querySelectorAll('.header-row').forEach(r => {
        if (r.querySelector('.h-key').value === 'Authorization') r.remove();
    });
    if (!val) return;
    let header = '';
    if (authType === 'bearer') header = 'Bearer ' + val;
    else if (authType === 'basic') header = 'Basic ' + btoa(val);
    else header = val;
    addHeaderRow('Authorization', header);
}

// ── Body ───────────────────────────────────────────────────────────────────────
function setBodyType(type) {}
function formatBody() {
    const ta = document.getElementById('bodyInput');
    try { ta.value = JSON.stringify(JSON.parse(ta.value), null, 2); } catch {}
}

// ── Send request ───────────────────────────────────────────────────────────────
async function sendRequest() {
    const url = document.getElementById('urlInput').value.trim();
    if (!url) return shake(document.getElementById('urlInput'));

    setLoading(true);

    const fd = new FormData();
    fd.append('__action', 'send_request');
    fd.append('url', url);
    fd.append('method', currentMethod);
    fd.append('headers', JSON.stringify(getHeaders()));
    fd.append('body', document.getElementById('bodyInput').value);

    try {
        const res = await fetch(window.location.href, { method: 'POST', body: fd });
        const data = await res.json();
        setLoading(false);
        renderResponse(data);
    } catch (e) {
        setLoading(false);
        renderResponse({ error: e.message });
    }
}

// ── Render response ────────────────────────────────────────────────────────────
function renderResponse(data) {
    lastResponse = data;

    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('jsonView').style.display = 'none';
    document.getElementById('errorView').style.display = 'none';

    if (data.error) {
        const ev = document.getElementById('errorView');
        ev.style.display = 'block';
        ev.textContent = '⚠ ' + data.error;
        document.getElementById('responseMeta').style.display = 'none';
        return;
    }

    // Meta badges
    const meta = document.getElementById('responseMeta');
    meta.style.display = 'flex';
    const sb = document.getElementById('statusBadge');
    const statusColor = data.status >= 200 && data.status < 300 ? '#30d158' : data.status >= 400 ? '#ff453a' : '#ff9f0a';
    sb.textContent = data.status;
    sb.style.background = statusColor + '18';
    sb.style.color = statusColor;
    sb.style.border = '1px solid ' + statusColor + '44';
    document.getElementById('timeBadge').textContent = '⏱ ' + data.time + 'ms';
    document.getElementById('sizeBadge').textContent = '📦 ' + formatBytes(data.size);

    // JSON body
    const jv = document.getElementById('jsonView');
    jv.style.display = 'block';
    try {
        const parsed = JSON.parse(data.body);
        jv.innerHTML = syntaxHighlight(JSON.stringify(parsed, null, 2));
    } catch {
        jv.style.fontFamily = 'var(--mono)';
        jv.style.fontSize = '13px';
        jv.style.color = 'var(--text)';
        jv.textContent = data.body;
    }

    // Response headers
    const rhl = document.getElementById('resHeaderList');
    rhl.innerHTML = '';
    Object.entries(data.headers || {}).forEach(([k, v]) => {
        const row = document.createElement('div');
        row.style = 'display:flex;gap:12px;padding:8px 11px;background:var(--surface);border-radius:8px;margin-bottom:5px;';
        row.innerHTML = `<span style="color:#64d2ff;font-family:var(--mono);font-size:12px;min-width:160px;flex-shrink:0;">${escHtml(k)}</span><span style="color:var(--text);font-family:var(--mono);font-size:12px;word-break:break-all;">${escHtml(v)}</span>`;
        rhl.appendChild(row);
    });

    // Raw
    document.getElementById('rawView').textContent = data.body;
}

function setLoading(on) {
    document.getElementById('sendBtn').disabled = on;
    document.getElementById('loadingState').style.display = on ? 'block' : 'none';
    document.getElementById('emptyState').style.display = 'none';
    if (on) {
        document.getElementById('jsonView').style.display = 'none';
        document.getElementById('errorView').style.display = 'none';
        document.getElementById('responseMeta').style.display = 'none';
    }
}

// ── Syntax highlight ───────────────────────────────────────────────────────────
function syntaxHighlight(json) {
    return json
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, match => {
            if (/^"/.test(match)) {
                if (/:$/.test(match)) return `<span class="j-key">${match}</span>`;
                return `<span class="j-str">${match}</span>`;
            }
            if (/true|false/.test(match)) return `<span class="j-bool">${match}</span>`;
            if (/null/.test(match)) return `<span class="j-null">${match}</span>`;
            return `<span class="j-num">${match}</span>`;
        });
}

// ── Collection ─────────────────────────────────────────────────────────────────
function openSaveModal() { document.getElementById('saveModal').classList.add('open'); document.getElementById('saveNameInput').focus(); }
function closeSaveModal() { document.getElementById('saveModal').classList.remove('open'); }
document.getElementById('saveModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeSaveModal(); });

async function saveCollection() {
    const name = document.getElementById('saveNameInput').value.trim();
    if (!name) return shake(document.getElementById('saveNameInput'));
    const fd = new FormData();
    fd.append('__action', 'save_collection');
    fd.append('name', name);
    fd.append('method', currentMethod);
    fd.append('url', document.getElementById('urlInput').value);
    fd.append('headers', JSON.stringify(getHeaders()));
    fd.append('body', document.getElementById('bodyInput').value);
    await fetch(window.location.href, { method: 'POST', body: fd });
    closeSaveModal();
    location.reload();
}

async function deleteCollection(e, id) {
    e.stopPropagation();
    const fd = new FormData();
    fd.append('__action', 'delete_collection');
    fd.append('id', id);
    await fetch(window.location.href, { method: 'POST', body: fd });
    location.reload();
}

function loadCollection(col) {
    setMethod(col.method);
    document.getElementById('urlInput').value = col.url;
    document.getElementById('bodyInput').value = col.body || '';
    document.getElementById('headerRows').innerHTML = '';
    try {
        const hdrs = JSON.parse(col.headers);
        hdrs.forEach(h => addHeaderRow(h.key, h.value));
    } catch {}
    addHeaderRow();
    closeSidebar();
}

// ── Sidebar ────────────────────────────────────────────────────────────────────
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}

// ── Utils ──────────────────────────────────────────────────────────────────────
function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function formatBytes(n) { if (n < 1024) return n + ' B'; return (n/1024).toFixed(1) + ' KB'; }
function shake(el) {
    el.style.animation = 'none';
    el.offsetHeight;
    el.style.animation = 'shake .3s ease';
    el.addEventListener('animationend', () => el.style.animation = '', { once: true });
}

// Enter to send
document.getElementById('urlInput').addEventListener('keydown', e => { if (e.key === 'Enter') sendRequest(); });
</script>

<style>
@keyframes shake {
    0%,100% { transform: translateX(0); }
    20%,60%  { transform: translateX(-6px); }
    40%,80%  { transform: translateX(6px); }
}
</style>

</body>
</html>
<?php
function getMethodColor($m) {
    return ['GET'=>'#30d158','POST'=>'#0a84ff','PUT'=>'#ff9f0a','DELETE'=>'#ff453a','PATCH'=>'#bf5af2'][$m] ?? '#8e8e93';
}
function getMethodBg($m) {
    return ['GET'=>'rgba(48,209,88,.15)','POST'=>'rgba(10,132,255,.15)','PUT'=>'rgba(255,159,10,.15)','DELETE'=>'rgba(255,69,58,.15)','PATCH'=>'rgba(191,90,242,.15)'][$m] ?? 'rgba(142,142,147,.15)';
}
?>