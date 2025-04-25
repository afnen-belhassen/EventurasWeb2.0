document.addEventListener('DOMContentLoaded', function () {
    // SweetAlert2 test button
    const testBtn = document.getElementById('test-btn');
    if (testBtn) {
        testBtn.addEventListener('click', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, test it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/test/reclamation/123';
                }
            });
        });
    }

    // File input behavior
    const fileInput = document.querySelector('.file-area input[type="file"]');
    const defaultText = document.querySelector('.file-dummy .default');
    const successText = document.querySelector('.file-dummy .success');

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files.length > 0) {
                defaultText?.classList.add('hide');
                successText?.classList.add('show');
            } else {
                defaultText?.classList.remove('hide');
                successText?.classList.remove('show');
            }
        });
    }
});

// Global functions for popup
function openCategoryPopup() {
    document.getElementById('categoryPopup')?.classList.add('open');
}

function closeCategoryPopup() {
    document.getElementById('categoryPopup')?.classList.remove('open');
}
