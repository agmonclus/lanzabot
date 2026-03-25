/* Lanzabot — App JS */

// File upload UX
document.addEventListener('DOMContentLoaded', () => {

    const fileInput = document.getElementById('codeFile');
    const uploadBtn = document.getElementById('uploadBtn');
    const selectedFile = document.getElementById('selectedFile');
    const uploadZone = document.getElementById('uploadZone');

    if (fileInput) {
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                const name = fileInput.files[0].name;
                const size = (fileInput.files[0].size / 1024 / 1024).toFixed(2);
                selectedFile.textContent = `${name} (${size} MB)`;
                selectedFile.style.display = 'block';
                if (uploadBtn) uploadBtn.style.display = 'inline-flex';
            }
        });
    }

    if (uploadZone) {
        uploadZone.addEventListener('dragover', e => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
        uploadZone.addEventListener('drop', e => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            if (fileInput && e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    }

    // Auto-hide flash messages
    document.querySelectorAll('.flash').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 4000);
    });

    // Confirm delete forms
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('submit', e => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });
});
