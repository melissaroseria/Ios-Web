// Modal'ı açma fonksiyonu
function openModal(fileName) {
    const modal = document.getElementById("logModal");
    const logContent = document.getElementById("logContent");

    // Log dosyasını yükleme
    fetch('logs/' + fileName)
        .then(response => response.text())
        .then(data => {
            logContent.textContent = data;
        });

    // Modal'ı açma
    modal.style.display = "flex";
    setTimeout(() => modal.querySelector('.modal-content').classList.add('fadeIn'), 50);
}

// Modal'ı kapama fonksiyonu
function closeModal() {
    const modalContent = document.querySelector('.modal-content');
    
    // Modal'ı kaydırarak kapatma
    modalContent.classList.add('swiped');
    setTimeout(() => {
        modalContent.classList.remove('swiped');
        document.getElementById("logModal").style.display = "none";
    }, 300);
}

// Dışarı tıklayarak modal'ı kapatma
window.addEventListener('click', function(e) {
    const modal = document.getElementById("logModal");
    if (e.target === modal) {
        closeModal();
    }
});

// Modal'ın kaydırılabilir olması için kullanıcı dokunuşları veya fare hareketi
let startX;
let isDragging = false;

document.querySelector('.modal-content').addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
    isDragging = true;
});

document.querySelector('.modal-content').addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    const moveX = e.touches[0].clientX - startX;
    if (moveX > 100) {  // Sağ kaydırma hareketi
        closeModal();
        isDragging = false;
    }
});

document.querySelector('.modal-content').addEventListener('touchend', () => {
    isDragging = false;
});

// Sayfa yüklendiğinde otomatik modal açılmasını engellemek için
window.addEventListener('load', function() {
    const modal = document.getElementById("logModal");
    modal.style.display = "none"; // Modal'ı varsayılan olarak gizli tut
});