<?php
/**
 * CarePulse - Global Footer
 * ob_end_flush() sends the buffered output started in header.php
 */
?>
</div><!-- /page-content -->
</div><!-- /main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    /* ---- Mobile sidebar ---- */
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebar-overlay').classList.add('show');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('show');
    }

    /* ---- Auto-dismiss flash alerts after 5s ---- */
    setTimeout(() => {
        document.querySelectorAll('.alert:not(.alert-permanent)').forEach(el => {
            try { new bootstrap.Alert(el).close(); } catch(e) {}
        });
    }, 5000);

    /* ---- Global SweetAlert delete confirm ---- */
    function confirmDelete(url, msg = 'This action cannot be undone!') {
        Swal.fire({
            title: 'Delete this record?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            borderRadius: '14px',
            customClass: { popup: 'rounded-4', confirmButton: 'rounded-3', cancelButton: 'rounded-3' }
        }).then(r => { if (r.isConfirmed) window.location.href = url; });
    }

    /* ---- Password Visibility Toggle ---- */
    document.addEventListener('click', function(e) {
        if (e.target.closest('.password-toggle-icon')) {
            const btn = e.target.closest('.password-toggle-icon');
            const container = btn.closest('.password-field-container');
            const input = container.querySelector('input');
            const icon = btn.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    });

    // Theme Management
    const themeBtn = document.getElementById('darkModeToggle');
    const themeIcon = document.getElementById('themeIcon');
    
    // Check local storage for preference
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        if (themeIcon) {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        }
    }
    
    if (themeBtn) {
        themeBtn.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            
            if (themeIcon) {
                if (isDark) {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                } else {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                }
            }
        });
    }
</script>

</body>
</html>
<?php ob_end_flush(); ?>