/**
 * ShareLink Download Handler
 */
document.addEventListener('DOMContentLoaded', function () {
    const downloadBtn = document.getElementById('sharelink-download');
    
    if (!downloadBtn) {
        return;
    }

    downloadBtn.addEventListener('click', function () {
        // Get file URL from data attribute
        const fileUrl = downloadBtn.dataset.fileUrl;

        if (!fileUrl) {
            alert('Error: File URL not found.');
            return;
        }

        // Create a hidden anchor and trigger download
        const link = document.createElement('a');
        link.href = fileUrl;
        link.download = '';
        link.style.display = 'none';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
