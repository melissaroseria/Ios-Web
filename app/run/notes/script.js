function showActions(note) {
    document.getElementById('deleteNote').value = note;
    document.getElementById('newName').value = ''; // Temizle
    document.getElementById('popup').style.display = 'flex';
}

function closePopup() {
    document.getElementById('popup').style.display = 'none';
}

function deleteNote() {
    const deleteField = document.getElementById('deleteNote').value;
    if (deleteField) {
        document.getElementById('actionForm').submit();
    }
}
