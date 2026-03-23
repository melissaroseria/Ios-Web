document.addEventListener('DOMContentLoaded', () => {
    const apiUrlForm = document.getElementById('apiUrlForm');
    const resultBox = document.getElementById('resultBox');
    const responseOutput = document.getElementById('responseOutput');
    const apiLink = document.getElementById('apiLink');
    const popupModal = document.getElementById('popupModal');
    const apiIframe = document.getElementById('apiIframe');
    const closeModal = document.getElementById('closeModal');

    // API URL formunun gönderilmesi
    apiUrlForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const endpoint = document.getElementById('apiUrlInput').value;
        if (!endpoint) {
            alert("Lütfen geçerli bir API URL'si girin.");
            return;
        }

        // Sonuç kutusunu göster
        resultBox.classList.add('visible');
        responseOutput.textContent = `API Sonucu: ${endpoint}`;
        
        // API'yi Görüntüle butonuna tıklandığında pop-up açılacak
        apiLink.onclick = () => {
            popupModal.style.display = 'flex';  // Pop-up'ı göster
            apiIframe.src = endpoint;  // API linkini iframe'e yerleştir
        };
    });

    // Pop-up'ı kapatma
    closeModal.onclick = () => {
        popupModal.style.display = 'none';  // Pop-up'ı gizle
        apiIframe.src = '';  // iframe'deki içeriği temizle
    };

    // Pop-up dışında bir yere tıklanırsa da pop-up'ı kapat
    window.onclick = (event) => {
        if (event.target == popupModal) {
            popupModal.style.display = 'none';
            apiIframe.src = '';  // iframe'i temizle
        }
    };
});
