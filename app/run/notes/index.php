<?php
error_reporting(0);
ini_set('display_errors', 0);

$dir    = __DIR__ . '/notes_data/';
$imgDir = __DIR__ . '/notes_imgs/';
if (!is_dir($dir))    @mkdir($dir,    0777, true);
if (!is_dir($imgDir)) @mkdir($imgDir, 0777, true);

// ── MIME ────────────────────────────────────────
function getMime($path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $map = array('jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp');
    return isset($map[$ext]) ? $map[$ext] : 'image/jpeg';
}

// ── INPUT: HopWeb bazen $_POST doldurmaz ─────────
// JSON body, FormData veya GET parametresi - hepsini destekle
$rawInput = file_get_contents('php://input');
$jdata = array();
if ($rawInput) {
    $trimmed = trim($rawInput);
    if (strlen($trimmed) > 0 && $trimmed[0] === '{') {
        $decoded = json_decode($rawInput, true);
        if (is_array($decoded)) $jdata = $decoded;
    }
}

function inp($key, $default = '') {
    global $jdata;
    if (array_key_exists($key, $jdata))  return $jdata[$key];
    if (isset($_POST[$key]))             return $_POST[$key];
    if (isset($_GET[$key]))              return $_GET[$key];
    return $default;
}

// ── RESİM SERVE ─────────────────────────────────
if (isset($_GET['img'])) {
    $fn   = basename($_GET['img']);
    $path = $imgDir . $fn;
    if ($fn && file_exists($path)) {
        header('Content-Type: ' . getMime($path));
        header('Cache-Control: max-age=86400');
        readfile($path);
    }
    exit;
}

// ── AJAX ────────────────────────────────────────
$act = inp('act');
$isAjax = ($act !== '' && $act !== null);

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');

    // NOT LİSTESİ
    if ($act === 'list') {
        $notes = array();
        $files = glob($dir . '*.json');
        if ($files) {
            foreach ($files as $f) {
                $d = json_decode(file_get_contents($f), true);
                if ($d) $notes[] = $d;
            }
        }
        usort($notes, function($a, $b) {
            $pa = (!empty($a['pinned'])) ? 1 : 0;
            $pb = (!empty($b['pinned'])) ? 1 : 0;
            if ($pa !== $pb) return $pb - $pa;
            $ma = isset($a['modified']) ? $a['modified'] : 0;
            $mb = isset($b['modified']) ? $b['modified'] : 0;
            return $mb - $ma;
        });
        echo json_encode($notes, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // KAYDET
    if ($act === 'save') {
        $id = inp('id');
        $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $id);
        if (!$id) $id = 'note_' . uniqid();
        $file = $dir . $id . '.json';
        $old  = array();
        if (file_exists($file)) {
            $tmp = json_decode(file_get_contents($file), true);
            if (is_array($tmp)) $old = $tmp;
        }

        $images = (isset($old['images']) && is_array($old['images'])) ? $old['images'] : array();

        // Resim yükle
        if (!empty($_FILES['img']['tmp_name']) && is_uploaded_file($_FILES['img']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('jpg','jpeg','png','gif','webp'))) {
                $fn = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['img']['tmp_name'], $imgDir . $fn)) {
                    $images[] = $fn;
                }
            }
        }

        $title = trim(inp('title'));
        if (!$title) $title = date('d.m.Y H:i');

        $pinned = inp('pinned');
        $isPinned = ($pinned === '1' || $pinned === 'true' || $pinned === true);

        $data = array(
            'id'       => $id,
            'title'    => $title,
            'body'     => inp('body'),
            'images'   => $images,
            'pinned'   => $isPinned,
            'created'  => isset($old['created']) ? $old['created'] : time(),
            'modified' => time(),
        );
        file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        echo json_encode(array('ok' => true, 'id' => $id, 'data' => $data), JSON_UNESCAPED_UNICODE);
        exit;
    }

    // SİL
    if ($act === 'delete') {
        $id   = preg_replace('/[^a-zA-Z0-9_\-]/', '', inp('id'));
        $file = $dir . $id . '.json';
        if ($id && file_exists($file)) {
            $d = json_decode(file_get_contents($file), true);
            if (isset($d['images']) && is_array($d['images'])) {
                foreach ($d['images'] as $img) {
                    @unlink($imgDir . basename($img));
                }
            }
            unlink($file);
            echo json_encode(array('ok' => true));
        } else {
            echo json_encode(array('ok' => false, 'err' => 'not bulunamadi'));
        }
        exit;
    }

    // RESİM SİL
    if ($act === 'del_img') {
        $id  = preg_replace('/[^a-zA-Z0-9_\-]/', '', inp('id'));
        $img = basename(inp('img'));
        $file = $dir . $id . '.json';
        if ($id && $img && file_exists($file)) {
            $d = json_decode(file_get_contents($file), true);
            $newImgs = array();
            foreach ($d['images'] as $i) { if ($i !== $img) $newImgs[] = $i; }
            $d['images'] = $newImgs;
            file_put_contents($file, json_encode($d, JSON_UNESCAPED_UNICODE));
            @unlink($imgDir . $img);
        }
        echo json_encode(array('ok' => true));
        exit;
    }

    echo json_encode(array('ok' => false, 'err' => 'unknown: ' . $act));
    exit;
}
// HTML SAYFASI ───────────────────────────────────
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
<title>Notlar</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
html,body{height:100%;overflow:hidden;background:#000;color:#fff;
  font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue",Arial,sans-serif;
  -webkit-font-smoothing:antialiased}
:root{
  --bg:#000;--card:#1c1c1e;--card2:#2c2c2e;
  --sep:rgba(84,84,88,.6);--gold:#ffd60a;
  --sub:#8e8e93;--red:#ff453a;--green:#30d158;
  --safe-top:env(safe-area-inset-top,44px);
  --safe-bot:env(safe-area-inset-bottom,20px);
}
#app{width:100%;height:100%;overflow:hidden;position:relative}
.screen{position:absolute;inset:0;display:flex;flex-direction:column;background:var(--bg);overflow:hidden;transform:translateX(100%);transition:transform .35s cubic-bezier(.32,.72,0,1);will-change:transform}
.screen.active{transform:translateX(0)}
.screen.behind{transform:translateX(-28%)}
.sb{flex-shrink:0;height:var(--safe-top)}
.nav{display:flex;justify-content:space-between;align-items:center;padding:0 16px 8px;flex-shrink:0}
.nbtn{background:none;border:none;color:var(--gold);font-size:17px;cursor:pointer;padding:6px 2px;display:flex;align-items:center;gap:4px;font-family:inherit}
.nbtn i{font-size:15px}
.nbtn:active{opacity:.4}
.nbtn.bold{font-weight:700}
.list-scroll{flex:1;overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;padding-bottom:calc(72px + var(--safe-bot))}
.list-scroll::-webkit-scrollbar{display:none}
.page-h{padding:0 20px 8px}
.page-h h1{font-size:34px;font-weight:700;letter-spacing:-.5px}
.search-wrap{padding:0 16px 14px;flex-shrink:0}
.sbox{background:var(--card2);border-radius:12px;display:flex;align-items:center;gap:8px;padding:9px 12px}
.sbox i{color:var(--sub);font-size:15px}
.sbox input{background:none;border:none;color:#fff;font-size:17px;width:100%;outline:none;font-family:inherit}
.sbox input::placeholder{color:var(--sub)}
.sec-lbl{font-size:13px;font-weight:600;color:var(--sub);text-transform:uppercase;letter-spacing:.3px;padding:0 20px 8px}
.notes-ul{background:var(--card);border-radius:12px;margin:0 16px 20px;overflow:hidden}
.nrow{position:relative;overflow:hidden;border-bottom:.5px solid var(--sep)}
.nrow:last-child{border-bottom:none}
.nrow-in{display:flex;align-items:center;gap:12px;padding:13px 16px;background:var(--card);transition:transform .28s cubic-bezier(.25,.46,.45,.94);cursor:pointer}
.nrow-in:active{background:var(--card2)}
.nthumb{width:46px;height:46px;border-radius:10px;object-fit:cover;flex-shrink:0}
.ntxt{flex:1;min-width:0}
.ntitle{font-size:17px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.nmeta{display:flex;align-items:center;gap:8px;margin-top:3px}
.ndate{font-size:14px;color:var(--sub);flex-shrink:0}
.npreview{font-size:14px;color:var(--sub);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.narrow{color:var(--sep);font-size:12px;flex-shrink:0}
.pin-ic{color:var(--gold);font-size:11px;margin-left:4px}
.ndel{position:absolute;right:0;top:0;bottom:0;width:80px;background:var(--red);display:flex;align-items:center;justify-content:center;transform:translateX(100%);transition:transform .28s cubic-bezier(.25,.46,.45,.94);cursor:pointer}
.ndel i{color:#fff;font-size:20px}
.nrow.open .nrow-in{transform:translateX(-80px)}
.nrow.open .ndel{transform:translateX(0)}
.empty-state{padding:52px 20px;text-align:center;color:var(--sub)}
.empty-state i{font-size:48px;opacity:.2;margin-bottom:14px}
.empty-state p{font-size:16px}
.bot-bar{position:fixed;bottom:0;left:0;right:0;background:rgba(18,18,18,.94);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-top:.5px solid var(--sep);display:flex;justify-content:space-between;align-items:center;padding:10px 20px calc(10px + var(--safe-bot));z-index:40}
.note-cnt{font-size:13px;color:var(--sub);flex:1;text-align:center}
.compose{background:none;border:none;color:var(--gold);font-size:27px;cursor:pointer;padding:2px}
.compose:active{opacity:.4}
.bot-side{flex:1}
.edit-scroll{flex:1;overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;padding-bottom:calc(80px + var(--safe-bot))}
.edit-scroll::-webkit-scrollbar{display:none}
.cover-zone{position:relative;width:100%;background:var(--card);overflow:hidden;flex-shrink:0}
.cover-zone.no-cover{height:0}
.cover-zone.has-cover{height:220px}
.cover-zone img{width:100%;height:100%;object-fit:cover}
.cover-grad{position:absolute;bottom:0;left:0;right:0;height:80px;background:linear-gradient(transparent,rgba(0,0,0,.65))}
.cover-acts{position:absolute;bottom:12px;right:12px;display:flex;gap:8px}
.cbtn{background:rgba(0,0,0,.6);backdrop-filter:blur(8px);border:none;border-radius:20px;color:#fff;font-size:13px;font-weight:600;padding:7px 14px;cursor:pointer;display:flex;align-items:center;gap:6px;font-family:inherit}
.cbtn:active{opacity:.6}
.cbtn.cadd{background:rgba(255,214,10,.88);color:#000}
.edit-body{padding:16px 20px 0}
.title-f{width:100%;background:none;border:none;color:#fff;font-size:26px;font-weight:700;letter-spacing:-.3px;outline:none;font-family:inherit;resize:none;overflow:hidden;line-height:1.3;caret-color:var(--gold)}
.title-f::placeholder{color:var(--sub)}
.meta-ln{font-size:13px;color:var(--sub);padding:6px 0 10px;border-bottom:.5px solid var(--sep);margin-bottom:10px}
.body-f{width:100%;background:none;border:none;color:#fff;font-size:17px;line-height:1.65;outline:none;font-family:inherit;resize:none;min-height:45vh;caret-color:var(--gold)}
.body-f::placeholder{color:var(--sub)}
.img-strip{display:flex;gap:10px;overflow-x:auto;padding:12px 20px;scrollbar-width:none}
.img-strip::-webkit-scrollbar{display:none}
.img-chip{position:relative;flex-shrink:0}
.img-chip img{width:110px;height:110px;object-fit:cover;border-radius:12px;display:block}
.chip-del{position:absolute;top:5px;right:5px;background:rgba(0,0,0,.7);border:none;border-radius:50%;width:22px;height:22px;color:#fff;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.fmt-bar{position:fixed;bottom:0;left:0;right:0;background:rgba(28,28,30,.96);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-top:.5px solid var(--sep);display:flex;align-items:center;gap:2px;padding:8px 6px calc(8px + var(--safe-bot));overflow-x:auto;scrollbar-width:none;z-index:40}
.fmt-bar::-webkit-scrollbar{display:none}
.fbtn{background:none;border:none;color:#fff;font-size:18px;padding:8px 11px;border-radius:8px;cursor:pointer;flex-shrink:0}
.fbtn:active{background:var(--card2)}
.fbtn.gold{color:var(--gold)}
.fsep{width:.5px;height:22px;background:var(--sep);margin:0 4px;flex-shrink:0}
.fsp{flex:1}
.sdot{width:8px;height:8px;border-radius:50%;background:var(--sep);display:inline-block;transition:background .25s}
.sdot.saving{background:var(--gold)}
.sdot.saved{background:var(--green)}
.sheet-bg{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:200;display:flex;align-items:flex-end;opacity:0;pointer-events:none;transition:opacity .22s}
.sheet-bg.show{opacity:1;pointer-events:all}
.sheet{width:100%;background:#1c1c1e;border-radius:14px 14px 0 0;padding:20px 20px calc(20px + var(--safe-bot));transform:translateY(100%);transition:transform .28s cubic-bezier(.32,.72,0,1)}
.sheet-bg.show .sheet{transform:translateY(0)}
.sheet h3{font-size:17px;font-weight:600;text-align:center;margin-bottom:4px}
.sheet p{font-size:14px;color:var(--sub);text-align:center;margin-bottom:18px}
.sbtn{display:block;width:100%;background:var(--card2);border:none;border-radius:12px;padding:15px;font-size:17px;cursor:pointer;font-family:inherit;color:#fff;margin-bottom:10px}
.sbtn.danger{background:var(--red);font-weight:700}
.sbtn:active{opacity:.7}
#coverInput,#bodyImgInput{display:none}
@keyframes rowIn{from{opacity:0;transform:translateX(16px)}to{opacity:1;transform:none}}
.nrow{animation:rowIn .18s ease both}
</style>
</head>
<body>
<div id="app">

<div class="screen active" id="listScreen">
  <div class="sb"></div>
  <div class="nav">
    <button class="nbtn"><i class="fas fa-chevron-left"></i> iCloud</button>
    <button class="nbtn">Düzenle</button>
  </div>
  <div class="page-h"><h1>Notlar</h1></div>
  <div class="search-wrap">
    <div class="sbox">
      <i class="fas fa-magnifying-glass"></i>
      <input type="text" id="searchInput" placeholder="Ara" autocomplete="off">
    </div>
  </div>
  <div class="list-scroll">
    <div id="pinnedSec"></div>
    <div id="mainSec"></div>
  </div>
  <div class="bot-bar">
    <div class="bot-side"></div>
    <span class="note-cnt" id="noteCnt">0 Not</span>
    <div class="bot-side" style="display:flex;justify-content:flex-end">
      <button class="compose" id="newBtn"><i class="fas fa-square-pen"></i></button>
    </div>
  </div>
</div>

<div class="screen" id="editScreen">
  <div class="sb"></div>
  <div class="nav">
    <button class="nbtn" id="backBtn"><i class="fas fa-chevron-left"></i> Notlar</button>
    <div style="display:flex;align-items:center;gap:10px">
      <span class="sdot" id="sdot"></span>
      <button class="nbtn bold" id="doneBtn">Bitti</button>
    </div>
  </div>
  <div class="edit-scroll">
    <div class="cover-zone no-cover" id="coverZone">
      <img id="coverImg" src="" alt="" style="display:none">
      <div class="cover-grad" id="coverGrad" style="display:none"></div>
      <div class="cover-acts">
        <button class="cbtn" id="rmCoverBtn" style="display:none" onclick="removeCover()"><i class="fas fa-trash"></i></button>
        <button class="cbtn cadd" onclick="document.getElementById('coverInput').click()"><i class="fas fa-camera"></i> Kapak</button>
      </div>
    </div>
    <div class="edit-body">
      <textarea class="title-f" id="titleF" rows="1" placeholder="Başlık"></textarea>
      <div class="meta-ln" id="metaLn"></div>
      <div class="img-strip" id="imgStrip"></div>
      <textarea class="body-f" id="bodyF" placeholder="Not..."></textarea>
    </div>
  </div>
  <div class="fmt-bar">
    <button class="fbtn" onclick="fmt('**','**')"><i class="fas fa-bold"></i></button>
    <button class="fbtn" onclick="fmt('_','_')"><i class="fas fa-italic"></i></button>
    <button class="fbtn" onclick="fmt('~~','~~')"><i class="fas fa-strikethrough"></i></button>
    <div class="fsep"></div>
    <button class="fbtn" onclick="insLine('- ')"><i class="fas fa-list-ul"></i></button>
    <button class="fbtn" onclick="insLine('1. ')"><i class="fas fa-list-ol"></i></button>
    <button class="fbtn" onclick="insLine('[ ] ')"><i class="far fa-square-check"></i></button>
    <div class="fsep"></div>
    <button class="fbtn gold" onclick="document.getElementById('bodyImgInput').click()"><i class="fas fa-image"></i></button>
    <div class="fsp"></div>
    <button class="fbtn" onclick="document.getElementById('bodyF').focus()"><i class="fas fa-keyboard"></i></button>
  </div>
</div>

<input type="file" id="coverInput" accept="image/*">
<input type="file" id="bodyImgInput" accept="image/*" multiple>

<div class="sheet-bg" id="delSheet">
  <div class="sheet">
    <h3>Notu Sil</h3>
    <p>Bu not kalıcı olarak silinecek.</p>
    <button class="sbtn danger" id="confirmDel"><i class="fas fa-trash"></i> Notu Sil</button>
    <button class="sbtn" onclick="closeSheet()">İptal</button>
  </div>
</div>
</div>

<script>
var notes = [], currentId = null, saveTimer = null, deleteId = null;
var BASE = location.href.split('?')[0];

// ── API ─────────────────────────────────────────
// HopWeb için: JSON body ile POST, act parametresi URL'de
function apiPost(act, data) {
  data.act = act;
  return fetch(BASE + '?act=' + encodeURIComponent(act), {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data)
  }).then(function(r){ return r.json(); });
}

function apiGet(act) {
  return fetch(BASE + '?act=' + encodeURIComponent(act))
    .then(function(r){ return r.json(); });
}

// Resim yükleme için ayrı FormData POST
function apiUpload(act, formData) {
  formData.append('act', act);
  return fetch(BASE + '?act=' + encodeURIComponent(act), {
    method: 'POST',
    body: formData
  }).then(function(r){ return r.json(); });
}

// ── NOTLARI YÜKLE ───────────────────────────────
function loadNotes() {
  apiGet('list').then(function(data) {
    notes = Array.isArray(data) ? data : [];
    renderList('');
  }).catch(function(e){ console.error(e); });
}

loadNotes();

// ── RENDER ──────────────────────────────────────
function renderList(q) {
  q = (q || '').toLowerCase();
  var visible = notes.filter(function(n) {
    if (!q) return true;
    return (n.title||'').toLowerCase().indexOf(q) >= 0 ||
           (n.body||'').toLowerCase().indexOf(q) >= 0;
  });
  var pinned   = visible.filter(function(n){ return n.pinned; });
  var unpinned = visible.filter(function(n){ return !n.pinned; });
  var pSec = document.getElementById('pinnedSec');
  var mSec = document.getElementById('mainSec');
  pSec.innerHTML = ''; mSec.innerHTML = '';

  if (pinned.length) {
    pSec.innerHTML = '<div class="sec-lbl">Sabitlenmiş</div>';
    var ul = document.createElement('div'); ul.className = 'notes-ul';
    pinned.forEach(function(n){ ul.appendChild(makeRow(n)); });
    pSec.appendChild(ul);
  }
  if (unpinned.length) {
    if (pinned.length) mSec.innerHTML = '<div class="sec-lbl">Notlar</div>';
    var ul2 = document.createElement('div'); ul2.className = 'notes-ul';
    unpinned.forEach(function(n){ ul2.appendChild(makeRow(n)); });
    mSec.appendChild(ul2);
  }
  if (!visible.length) {
    mSec.innerHTML = '<div class="notes-ul"><div class="empty-state"><div><i class="far fa-sticky-note"></i></div><p>Henüz not yok 😊</p></div></div>';
  }
  document.getElementById('noteCnt').textContent = notes.length + ' Not';
}

// ── SATIR ────────────────────────────────────────
function makeRow(note) {
  var wrap = document.createElement('div');
  wrap.className = 'nrow'; wrap.dataset.id = note.id;
  var dateStr = fmtDate(note.modified);
  var preview = (note.body||'').replace(/\n/g,' ').substring(0,55) || ' ';
  var pinHtml = note.pinned ? '<i class="fas fa-thumbtack pin-ic"></i>' : '';
  var thumbHtml = '';
  if (note.images && note.images.length > 0) {
    thumbHtml = '<img class="nthumb" src="' + BASE + '?img=' + encodeURIComponent(note.images[0]) + '" alt="">';
  }
  wrap.innerHTML =
    '<div class="nrow-in">' + thumbHtml +
      '<div class="ntxt">' +
        '<div class="ntitle">' + esc(note.title) + pinHtml + '</div>' +
        '<div class="nmeta"><span class="ndate">' + dateStr + '</span>' +
        '<span class="npreview">' + esc(preview) + '</span></div>' +
      '</div><i class="fas fa-chevron-right narrow"></i></div>' +
    '<div class="ndel"><i class="fas fa-trash"></i></div>';
  wrap.querySelector('.nrow-in').addEventListener('click', function(){ openNote(note.id); });
  wrap.querySelector('.ndel').addEventListener('click', function(){ askDelete(note.id); });
  attachSwipe(wrap);
  return wrap;
}

// ── SWIPE ────────────────────────────────────────
function attachSwipe(row) {
  var sx=0,sy=0,moved=false,dir=null;
  row.addEventListener('touchstart',function(e){sx=e.touches[0].clientX;sy=e.touches[0].clientY;moved=false;dir=null;},{passive:true});
  row.addEventListener('touchmove',function(e){
    var dx=e.touches[0].clientX-sx,dy=e.touches[0].clientY-sy;
    if(!dir) dir=Math.abs(dx)>Math.abs(dy)?'h':'v';
    if(dir==='v') return;
    moved=true;
    var inner=row.querySelector('.nrow-in'),del=row.querySelector('.ndel');
    var move=Math.max(0,Math.min(-dx,110));
    inner.style.transition='none'; del.style.transition='none';
    inner.style.transform='translateX(-'+move+'px)';
    del.style.transform=move>=80?'translateX(0)':'translateX('+(100-(move/80*100))+'%)';
  },{passive:true});
  row.addEventListener('touchend',function(e){
    var dx=e.changedTouches[0].clientX-sx;
    var inner=row.querySelector('.nrow-in'),del=row.querySelector('.ndel');
    inner.style.transition=''; del.style.transition='';
    document.querySelectorAll('.nrow.open').forEach(function(r){
      if(r!==row){r.classList.remove('open');r.querySelector('.nrow-in').style.transform='';r.querySelector('.ndel').style.transform='';}
    });
    if(moved && dx<-55){row.classList.add('open');}
    else{row.classList.remove('open');inner.style.transform='';del.style.transform='';}
  },{passive:true});
}
document.querySelector('.list-scroll').addEventListener('touchstart',function(e){
  if(!e.target.closest('.nrow'))document.querySelectorAll('.nrow.open').forEach(function(r){r.classList.remove('open');});
},{passive:true});

// ── SİL ─────────────────────────────────────────
function askDelete(id){ deleteId=id; document.getElementById('delSheet').classList.add('show'); }
function closeSheet(){ document.getElementById('delSheet').classList.remove('show'); deleteId=null; }

document.getElementById('confirmDel').addEventListener('click', function(){
  if(!deleteId) return;
  var id=deleteId; closeSheet();
  var row=document.querySelector('.nrow[data-id="'+id+'"]');
  if(row){ row.style.transition='opacity .2s'; row.style.opacity='0'; setTimeout(function(){row.remove();},220); }
  apiPost('delete',{id:id}).then(function(){
    notes=notes.filter(function(n){return n.id!==id;});
    document.getElementById('noteCnt').textContent=notes.length+' Not';
    if(currentId===id) currentId=null;
  });
});

// ── NOT AÇ ───────────────────────────────────────
function openNote(id) {
  var note=null;
  for(var i=0;i<notes.length;i++){if(notes[i].id===id){note=notes[i];break;}}
  if(!note) return;
  currentId=id;
  document.getElementById('titleF').value=note.title||'';
  document.getElementById('bodyF').value=note.body||'';
  document.getElementById('metaLn').textContent=fmtDateFull(note.modified);
  autoH(document.getElementById('titleF')); autoH(document.getElementById('bodyF'));
  setCover(note.cover_url||null);
  buildStrip(note.images||[]);
  showScreen('editScreen');
  setTimeout(function(){document.getElementById('bodyF').focus();},400);
}

document.getElementById('newBtn').addEventListener('click',function(){
  currentId=null;
  document.getElementById('titleF').value=''; document.getElementById('bodyF').value='';
  document.getElementById('metaLn').textContent=fmtDateFull(Math.floor(Date.now()/1000));
  setCover(null); buildStrip([]);
  showScreen('editScreen');
  setTimeout(function(){document.getElementById('titleF').focus();},400);
});

// ── KAYDET ───────────────────────────────────────
function scheduleSave(){ document.getElementById('sdot').className='sdot saving'; clearTimeout(saveTimer); saveTimer=setTimeout(doSave,800); }

function doSave(imgFile) {
  var title=document.getElementById('titleF').value.trim()||'Yeni Not';
  var body=document.getElementById('bodyF').value;

  if(imgFile) {
    var fd=new FormData(); fd.append('id',currentId||''); fd.append('title',title); fd.append('body',body); fd.append('img',imgFile);
    return apiUpload('save',fd).then(handleSaveRes);
  }

  var payload={title:title,body:body};
  if(currentId) payload.id=currentId;
  var note=null; for(var i=0;i<notes.length;i++){if(notes[i].id===currentId){note=notes[i];break;}}
  if(note&&note.pinned) payload.pinned='1';

  return apiPost('save',payload).then(handleSaveRes);
}

function handleSaveRes(res) {
  if(res&&res.ok){
    var dot=document.getElementById('sdot'); dot.className='sdot saved'; setTimeout(function(){dot.className='sdot';},1400);
    currentId=res.id;
    var idx=-1; for(var i=0;i<notes.length;i++){if(notes[i].id===res.id){idx=i;break;}}
    if(idx>=0) notes[idx]=res.data; else notes.unshift(res.data);
  }
  return res;
}

document.getElementById('titleF').addEventListener('input',function(){autoH(this);scheduleSave();});
document.getElementById('bodyF').addEventListener('input',function(){autoH(this);scheduleSave();});

function goBack(){clearTimeout(saveTimer);doSave().then(function(){renderList(document.getElementById('searchInput').value);showScreen('listScreen');});}
document.getElementById('backBtn').addEventListener('click',goBack);
document.getElementById('doneBtn').addEventListener('click',goBack);

// ── KAPAK ────────────────────────────────────────
document.getElementById('coverInput').addEventListener('change',function(){
  var file=this.files[0]; if(!file) return;
  setCover(URL.createObjectURL(file));
  doSave(file).then(function(res){if(res&&res.data)buildStrip(res.data.images||[]);});
  this.value='';
});
function setCover(src){
  var zone=document.getElementById('coverZone'),img=document.getElementById('coverImg'),grad=document.getElementById('coverGrad'),rm=document.getElementById('rmCoverBtn');
  if(src){img.src=src;img.style.display='block';grad.style.display='block';rm.style.display='flex';zone.className='cover-zone has-cover';}
  else{img.src='';img.style.display='none';grad.style.display='none';rm.style.display='none';zone.className='cover-zone no-cover';}
}
function removeCover(){setCover(null);scheduleSave();}

// ── FOTOĞRAF ŞERİDİ ─────────────────────────────
document.getElementById('bodyImgInput').addEventListener('change',function(){
  var files=this.files;
  for(var i=0;i<files.length;i++){(function(f){doSave(f).then(function(res){if(res&&res.data)buildStrip(res.data.images||[]);});})(files[i]);}
  this.value='';
});
function buildStrip(images){
  var strip=document.getElementById('imgStrip'); strip.innerHTML='';
  if(!images||!images.length) return;
  images.forEach(function(img){addChip(img,BASE+'?img='+encodeURIComponent(img));});
}
function addChip(filename,src){
  var strip=document.getElementById('imgStrip');
  var chip=document.createElement('div'); chip.className='img-chip'; chip.dataset.img=filename;
  chip.innerHTML='<img src="'+src+'" alt=""><button class="chip-del" onclick="delImg(\''+filename+'\',this.parentElement)"><i class="fas fa-times"></i></button>';
  strip.appendChild(chip);
}
function delImg(filename,el){
  el.remove();
  apiPost('del_img',{id:currentId,img:filename});
  for(var i=0;i<notes.length;i++){
    if(notes[i].id===currentId&&notes[i].images){
      notes[i].images=notes[i].images.filter(function(x){return x!==filename;}); break;
    }
  }
}

// ── ARAMA ────────────────────────────────────────
document.getElementById('searchInput').addEventListener('input',function(){renderList(this.value);});

// ── FORMAT ───────────────────────────────────────
function fmt(b,a){var ta=document.getElementById('bodyF'),s=ta.selectionStart,e=ta.selectionEnd,sel=ta.value.slice(s,e);ta.value=ta.value.slice(0,s)+b+sel+a+ta.value.slice(e);ta.selectionStart=ta.selectionEnd=s+b.length+sel.length+a.length;ta.focus();scheduleSave();}
function insLine(pfx){var ta=document.getElementById('bodyF'),s=ta.selectionStart,ls=ta.value.lastIndexOf('\n',s-1)+1;ta.value=ta.value.slice(0,ls)+pfx+ta.value.slice(ls);ta.selectionStart=ta.selectionEnd=ls+pfx.length+(s-ls);ta.focus();scheduleSave();}

// ── NAV ──────────────────────────────────────────
function showScreen(id){
  document.querySelectorAll('.screen').forEach(function(s){s.classList.remove('active','behind');});
  document.getElementById(id).classList.add('active');
  if(id==='editScreen') document.getElementById('listScreen').classList.add('behind');
}

// ── UTILS ────────────────────────────────────────
function autoH(el){el.style.height='auto';el.style.height=el.scrollHeight+'px';}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function fmtDate(ts){if(!ts)return'';var d=new Date(ts*1000),now=new Date();if(d.toDateString()===now.toDateString())return d.toLocaleTimeString('tr-TR',{hour:'2-digit',minute:'2-digit'});if(d.getFullYear()===now.getFullYear())return d.toLocaleDateString('tr-TR',{day:'numeric',month:'short'});return d.toLocaleDateString('tr-TR');}
function fmtDateFull(ts){var d=new Date(ts*1000);return d.toLocaleDateString('tr-TR',{weekday:'long',day:'numeric',month:'long',year:'numeric'})+' '+d.toLocaleTimeString('tr-TR',{hour:'2-digit',minute:'2-digit'});}
document.addEventListener('touchmove',function(e){if(!e.target.closest('.list-scroll')&&!e.target.closest('.edit-scroll'))e.preventDefault();},{passive:false});
</script>
</body>
</html>