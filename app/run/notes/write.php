<?php
$notesDir = 'notes/';
$uploadsDir = 'uploads/';
if (!is_dir($notesDir)) mkdir($notesDir, 0777, true);
if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0777, true);

// Load existing note
$id = $_GET['id'] ?? null;
$note = [
    'title' => '',
    'content' => '',
    'tags' => [],
    'pinned' => false,
    'is_journal' => false,
    'cover_image' => null,
    'images' => [],
    'created' => date('c'),
    'modified' => date('c'),
];
if ($id && file_exists($notesDir . $id . '.json')) {
    $saved = json_decode(file_get_contents($notesDir . $id . '.json'), true);
    if ($saved) $note = array_merge($note, $saved);
}

// SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    
    if ($action === 'save') {
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) $title = 'Not ' . date('d-m-Y H:i');
        
        $content = $_POST['content'] ?? '';
        $tags = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
        $isPinned = !empty($_POST['pinned']);
        $isJournal = !empty($_POST['is_journal']);
        
        // Handle image upload
        $coverImage = $note['cover_image'];
        $allImages = $note['images'] ?? [];

        if (!empty($_FILES['cover']['name'])) {
            $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp','heic'])) {
                $imgName = uniqid('img_') . '.' . $ext;
                if (move_uploaded_file($_FILES['cover']['tmp_name'], $uploadsDir . $imgName)) {
                    $coverImage = $imgName;
                    if (!in_array($imgName, $allImages)) $allImages[] = $imgName;
                }
            }
        }
        
        // Multiple images
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
                if (!empty($tmp)) {
                    $ext = strtolower(pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','gif','webp','heic'])) {
                        $imgName = uniqid('img_') . '.' . $ext;
                        if (move_uploaded_file($tmp, $uploadsDir . $imgName)) {
                            $allImages[] = $imgName;
                            if (!$coverImage) $coverImage = $imgName;
                        }
                    }
                }
            }
        }

        $filename = $id ?? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $title) . '_' . time();
        
        $data = [
            'title' => $title,
            'content' => $content,
            'tags' => array_values($tags),
            'pinned' => $isPinned,
            'is_journal' => $isJournal,
            'cover_image' => $coverImage,
            'images' => $allImages,
            'created' => $note['created'],
            'modified' => date('c'),
        ];
        
        file_put_contents($notesDir . $filename . '.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $id ? 'Notu Düzenle' : 'Yeni Not' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
        
        :root {
            --bg: #000000;
            --surface: #1C1C1E;
            --surface2: #2C2C2E;
            --border: rgba(84,84,88,0.65);
            --accent: #FFB340;
            --text: #FFFFFF;
            --subtext: #8E8E93;
            --safe-bottom: env(safe-area-inset-bottom, 20px);
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
        }

        .status-bar { height: 44px; }

        /* TOP NAV */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 16px 8px;
            position: sticky;
            top: 0;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 100;
        }
        .nav-btn {
            color: var(--accent);
            font-size: 17px;
            cursor: pointer;
            background: none;
            border: none;
            padding: 8px 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            font-family: inherit;
        }
        .nav-btn.done { font-weight: 600; }
        .nav-btn:active { opacity: 0.6; }

        /* COVER IMAGE */
        .cover-section {
            position: relative;
            width: 100%;
            height: 220px;
            background: var(--surface);
            overflow: hidden;
        }
        .cover-section.has-image { height: 260px; }
        .cover-img {
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .cover-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            height: 80px;
        }
        .cover-upload-btn {
            position: absolute;
            bottom: 16px; right: 16px;
            background: rgba(255,179,64,0.9);
            color: #000;
            border: none;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: inherit;
        }
        .cover-upload-btn:active { opacity: 0.8; }

        /* JOURNAL DATE HEADER */
        .journal-header {
            padding: 20px 20px 4px;
            display: none;
        }
        .journal-header.show { display: block; }
        .journal-day {
            font-size: 48px;
            font-weight: 700;
            line-height: 1;
            color: var(--accent);
        }
        .journal-month {
            font-size: 17px;
            color: var(--subtext);
            margin-top: 4px;
        }

        /* EDITOR */
        .editor-wrap { padding: 16px 20px 8px; }

        .title-input {
            width: 100%;
            background: transparent;
            border: none;
            color: var(--text);
            font-size: 28px;
            font-weight: 700;
            outline: none;
            font-family: inherit;
            letter-spacing: -0.3px;
            padding: 0;
            margin-bottom: 12px;
        }
        .title-input::placeholder { color: var(--subtext); }

        .content-area {
            width: 100%;
            background: transparent;
            border: none;
            color: var(--text);
            font-size: 17px;
            line-height: 1.6;
            outline: none;
            resize: none;
            font-family: inherit;
            min-height: 40vh;
            caret-color: var(--accent);
            padding: 0;
        }
        .content-area::placeholder { color: var(--subtext); }

        /* IMAGE GALLERY */
        .img-gallery {
            padding: 12px 20px;
            display: flex;
            gap: 10px;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .img-gallery::-webkit-scrollbar { display: none; }
        .gallery-img-wrap {
            position: relative;
            flex-shrink: 0;
        }
        .gallery-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
        }
        .gallery-del {
            position: absolute;
            top: 4px; right: 4px;
            background: rgba(0,0,0,0.6);
            border: none;
            border-radius: 50%;
            width: 22px; height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #fff;
            font-size: 11px;
        }

        /* DIVIDER */
        .divider {
            height: 0.5px;
            background: var(--border);
            margin: 8px 20px;
        }

        /* OPTIONS ROW */
        .options-row {
            padding: 12px 20px;
            display: flex;
            flex-direction: column;
            gap: 0;
            background: var(--surface);
            border-radius: 12px;
            margin: 12px 16px;
        }
        .opt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 13px 0;
            border-bottom: 0.5px solid var(--border);
        }
        .opt-row:last-child { border-bottom: none; }
        .opt-label {
            font-size: 17px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .opt-label i { width: 20px; text-align: center; color: var(--accent); }

        /* TOGGLE SWITCH */
        .toggle {
            width: 51px; height: 31px;
            background: var(--surface2);
            border-radius: 16px;
            position: relative;
            cursor: pointer;
            transition: background 0.3s;
            flex-shrink: 0;
        }
        .toggle.on { background: var(--green); }
        .toggle::after {
            content: '';
            position: absolute;
            width: 27px; height: 27px;
            background: #fff;
            border-radius: 50%;
            top: 2px; left: 2px;
            transition: transform 0.3s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
        .toggle.on::after { transform: translateX(20px); }

        /* TAGS */
        .tags-input-wrap {
            padding: 12px 20px;
        }
        .tags-label {
            font-size: 13px;
            color: var(--subtext);
            margin-bottom: 8px;
        }
        .tags-input {
            background: var(--surface);
            border: none;
            color: var(--text);
            font-size: 16px;
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            outline: none;
            font-family: inherit;
        }
        .tags-input::placeholder { color: var(--subtext); }

        /* TOOLBAR */
        .format-bar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: rgba(28,28,30,0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 0.5px solid var(--border);
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 10px 12px calc(10px + var(--safe-bottom));
            overflow-x: auto;
            scrollbar-width: none;
        }
        .format-bar::-webkit-scrollbar { display: none; }
        .fmt-btn {
            background: none;
            border: none;
            color: var(--text);
            font-size: 18px;
            padding: 8px 10px;
            border-radius: 8px;
            cursor: pointer;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fmt-btn:active { background: var(--surface2); }
        .fmt-btn.accent { color: var(--accent); }
        .fmt-sep { width: 0.5px; height: 24px; background: var(--border); margin: 0 4px; flex-shrink: 0; }
        .fmt-spacer { flex: 1; }

        .spacer { height: 100px; }

        /* CHECKLIST item */
        .check-line { display: flex; align-items: flex-start; gap: 8px; margin: 4px 0; }
        .check-line input[type=checkbox] { margin-top: 3px; accent-color: var(--accent); }

        /* hidden file inputs */
        #coverInput, #imagesInput { display: none; }
    </style>
</head>
<body>

<form id="mainForm" method="POST" enctype="multipart/form-data" action="write.php<?= $id ? '?id='.$id : '' ?>">
<input type="hidden" name="action" value="save">
<input type="hidden" name="pinned" id="pinnedField" value="<?= $note['pinned'] ? '1' : '' ?>">
<input type="hidden" name="is_journal" id="journalField" value="<?= $note['is_journal'] ? '1' : '' ?>">

<input type="file" id="coverInput" name="cover" accept="image/*" capture="environment">
<input type="file" id="imagesInput" name="images[]" accept="image/*" capture="environment" multiple>

<div class="status-bar"></div>

<div class="top-nav">
    <button type="button" class="nav-btn" onclick="location.href='index.php'">
        <i class="fas fa-chevron-left"></i> Notlar
    </button>
    <div style="display:flex;gap:12px;align-items:center">
        <button type="button" class="nav-btn" onclick="toggleOptions()" id="optBtn">
            <i class="fas fa-ellipsis"></i>
        </button>
        <button type="submit" class="nav-btn done">Bitti</button>
    </div>
</div>

<!-- COVER IMAGE -->
<div class="cover-section <?= $note['cover_image'] ? 'has-image' : '' ?>" id="coverSection">
    <?php if ($note['cover_image'] && file_exists($uploadsDir . $note['cover_image'])): ?>
        <img class="cover-img" id="coverPreview" src="uploads/<?= htmlspecialchars($note['cover_image']) ?>" alt="">
        <div class="cover-overlay"></div>
    <?php else: ?>
        <img class="cover-img" id="coverPreview" src="" alt="" style="display:none">
        <div class="cover-overlay" id="coverOverlay" style="display:none"></div>
    <?php endif; ?>
    <button type="button" class="cover-upload-btn" onclick="document.getElementById('coverInput').click()">
        <i class="fas fa-camera"></i>
        <?= $note['cover_image'] ? 'Değiştir' : 'Kapak Ekle' ?>
    </button>
</div>

<!-- JOURNAL HEADER -->
<div class="journal-header <?= $note['is_journal'] ? 'show' : '' ?>" id="journalHeader">
    <div class="journal-day"><?= date('d') ?></div>
    <div class="journal-month"><?= strftime('%B %Y') ?: date('F Y') ?></div>
</div>

<!-- EDITOR -->
<div class="editor-wrap">
    <input type="text" name="title" class="title-input" 
           placeholder="Başlık" 
           value="<?= htmlspecialchars($note['title']) ?>"
           id="titleInput">
    <textarea name="content" class="content-area" id="contentArea"
              placeholder="Yazmaya başla..."><?= htmlspecialchars($note['content']) ?></textarea>
</div>

<!-- IMAGE GALLERY -->
<?php if (!empty($note['images'])): ?>
<div class="img-gallery" id="imgGallery">
    <?php foreach ($note['images'] as $img): ?>
        <?php if (file_exists($uploadsDir . $img)): ?>
        <div class="gallery-img-wrap">
            <img class="gallery-img" src="uploads/<?= htmlspecialchars($img) ?>" alt="">
            <button type="button" class="gallery-del" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="img-gallery" id="imgGallery" style="display:none"></div>
<?php endif; ?>

<!-- OPTIONS PANEL -->
<div id="optPanel" style="display:none">
    <div class="divider"></div>
    <div class="options-row">
        <div class="opt-row">
            <span class="opt-label"><i class="fas fa-thumbtack"></i> Sabitle</span>
            <div class="toggle <?= $note['pinned'] ? 'on' : '' ?>" id="pinToggle" onclick="togglePin(this)"></div>
        </div>
        <div class="opt-row">
            <span class="opt-label"><i class="fas fa-book-open"></i> Günlük Modu</span>
            <div class="toggle <?= $note['is_journal'] ? 'on' : '' ?>" id="journalToggle" onclick="toggleJournal(this)"></div>
        </div>
    </div>

    <div class="tags-input-wrap">
        <div class="tags-label">Etiketler (virgülle ayır)</div>
        <input type="text" name="tags" class="tags-input" 
               placeholder="iş, okul, kişisel..."
               value="<?= htmlspecialchars(implode(', ', $note['tags'])) ?>">
    </div>
</div>

<div class="spacer"></div>

<!-- FORMAT BAR -->
<div class="format-bar">
    <button type="button" class="fmt-btn" onclick="insertFormat('**','**')"><i class="fas fa-bold"></i></button>
    <button type="button" class="fmt-btn" onclick="insertFormat('_','_')"><i class="fas fa-italic"></i></button>
    <button type="button" class="fmt-btn" onclick="insertFormat('\n- ','')"><i class="fas fa-list-ul"></i></button>
    <button type="button" class="fmt-btn" onclick="insertFormat('\n1. ','')"><i class="fas fa-list-ol"></i></button>
    <button type="button" class="fmt-btn" onclick="insertFormat('\n[ ] ','')"><i class="fas fa-check-square"></i></button>
    <div class="fmt-sep"></div>
    <button type="button" class="fmt-btn accent" onclick="document.getElementById('imagesInput').click()">
        <i class="fas fa-image"></i>
    </button>
    <button type="button" class="fmt-btn accent" onclick="document.getElementById('coverInput').click()">
        <i class="fas fa-camera"></i>
    </button>
    <div class="fmt-spacer"></div>
    <button type="button" class="fmt-btn" onclick="document.getElementById('contentArea').focus()">
        <i class="fas fa-keyboard"></i>
    </button>
</div>

</form>

<script>
// Cover image preview
document.getElementById('coverInput').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const url = URL.createObjectURL(file);
    const preview = document.getElementById('coverPreview');
    const overlay = document.getElementById('coverOverlay');
    const section = document.getElementById('coverSection');
    preview.src = url;
    preview.style.display = 'block';
    if (overlay) overlay.style.display = 'block';
    section.classList.add('has-image');
});

// Multiple images preview
document.getElementById('imagesInput').addEventListener('change', function() {
    const gallery = document.getElementById('imgGallery');
    gallery.style.display = 'flex';
    Array.from(this.files).forEach(file => {
        const url = URL.createObjectURL(file);
        const wrap = document.createElement('div');
        wrap.className = 'gallery-img-wrap';
        wrap.innerHTML = `<img class="gallery-img" src="${url}" alt="">
        <button type="button" class="gallery-del" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
        gallery.appendChild(wrap);
    });
});

// Options panel
function toggleOptions() {
    const panel = document.getElementById('optPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    document.getElementById('optBtn').style.color = panel.style.display === 'block' ? 'var(--accent)' : 'var(--text)';
}

// Pin toggle
function togglePin(el) {
    el.classList.toggle('on');
    document.getElementById('pinnedField').value = el.classList.contains('on') ? '1' : '';
}

// Journal toggle
function toggleJournal(el) {
    el.classList.toggle('on');
    const isOn = el.classList.contains('on');
    document.getElementById('journalField').value = isOn ? '1' : '';
    const header = document.getElementById('journalHeader');
    if (isOn) header.classList.add('show');
    else header.classList.remove('show');
}

// Format insert
function insertFormat(before, after) {
    const ta = document.getElementById('contentArea');
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const sel = ta.value.substring(start, end);
    ta.value = ta.value.substring(0, start) + before + sel + after + ta.value.substring(end);
    ta.focus();
    ta.selectionStart = ta.selectionEnd = start + before.length + sel.length + after.length;
}

// Auto-title from date if journal and empty
document.getElementById('journalToggle')?.addEventListener('click', function() {
    const titleInput = document.getElementById('titleInput');
    if (!titleInput.value && document.getElementById('journalField').value === '1') {
        titleInput.value = new Date().toLocaleDateString('tr-TR', {day:'numeric', month:'long', year:'numeric'});
    }
});

// Auto resize textarea
const ta = document.getElementById('contentArea');
function resize() { ta.style.height = 'auto'; ta.style.height = ta.scrollHeight + 'px'; }
ta.addEventListener('input', resize);
resize();
</script>
</body>
</html>
