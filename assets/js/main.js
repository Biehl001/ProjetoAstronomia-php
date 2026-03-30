// ============================================
// COSMOS NEWS - JavaScript Principal
// ============================================

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Mobile Menu Toggle ----------
    const mobileToggle = document.getElementById('mobileToggle');
    const navbarMenu = document.getElementById('navbarMenu');

    if (mobileToggle && navbarMenu) {
        mobileToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    }

    // ---------- Mobile Dropdown Toggle ----------
    const dropdowns = document.querySelectorAll('.nav-dropdown');
    dropdowns.forEach(function(dropdown) {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (toggle && window.innerWidth <= 768) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            });
        }
    });

    // ---------- Close alerts after 5 seconds ----------
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });

    // ---------- Image Upload Preview ----------
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const preview = this.closest('.image-upload').querySelector('.image-preview');
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (preview) {
                        preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // ---------- Delete Confirmation Modal ----------
    const deleteButtons = document.querySelectorAll('[data-delete]');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-delete');
            const name = this.getAttribute('data-name') || 'este item';
            showDeleteModal(url, name);
        });
    });

    // ---------- Smooth scroll for anchor links ----------
    document.querySelectorAll('a[href^="#"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ---------- News card click ----------
    document.querySelectorAll('.news-card[data-href]').forEach(function(card) {
        card.addEventListener('click', function() {
            window.location.href = this.getAttribute('data-href');
        });
    });

    // ---------- Navbar scroll effect ----------
    let lastScroll = 0;
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        const currentScroll = window.pageYOffset;
        if (currentScroll > 100) {
            navbar.style.boxShadow = '0 4px 30px rgba(0,0,0,0.3)';
        } else {
            navbar.style.boxShadow = 'none';
        }
        lastScroll = currentScroll;
    });
});

// ---------- Delete Modal ----------
function showDeleteModal(url, name) {
    // Remove existing modal
    const existing = document.getElementById('deleteModal');
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.id = 'deleteModal';
    modal.className = 'modal-overlay active';
    modal.innerHTML = `
        <div class="modal-box">
            <i class="fas fa-exclamation-triangle modal-icon"></i>
            <h3>Confirmar exclusão</h3>
            <p>Tem certeza que deseja excluir <strong>${name}</strong>? Esta ação não pode ser desfeita.</p>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
                <a href="${url}" class="btn btn-danger"><i class="fas fa-trash"></i> Excluir</a>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeDeleteModal();
    });
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.remove();
}
