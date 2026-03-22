<?php
$notesDir = 'notes/';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_POST['filename'] ?? '');
    if ($filename && file_exists($notesDir . $filename . '.json')) {
        // Also remove images if this note has a cover
        $data = json_decode(file_get_contents($notesDir . $filename . '.json'), true);
        if (!empty($data['images'])) {
            foreach ($data['images'] as $img) {
                @unlink('uploads/' . $img);
            }
        }
        unlink($notesDir . $filename . '.json');
        echo json_encode(['success' => true]);
        exit;
    }
}
echo json_encode(['success' => false]);
?>
