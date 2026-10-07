/**
 * Ubah <dialog open data-dialog-otomatis> yang dirender server menjadi modal.
 * Tanpa JavaScript dialog tetap tampil sebagai kotak biasa dan tombol
 * form method="dialog" tetap bisa menutupnya.
 */
export function initDialogOtomatis() {
    document.querySelectorAll('dialog[data-dialog-otomatis][open]').forEach((dialog) => {
        if (typeof dialog.showModal !== 'function') {
            return;
        }

        dialog.close();
        dialog.showModal();

        // Klik pada latar redup di luar isi popup menutup dialog.
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
}
