// ============================================
// ADMIN PANEL - ZARZĄDZANIE UŻYTKOWNIKAMI
// Plik: admin/assets/js/users.js
// ============================================

// MODAL DODAWANIA UŻYTKOWNIKA
function openAddModal() {
    const modal = document.getElementById('addModal');
    if (modal) {
        modal.classList.add('active');
        // Zablokuj scroll na body
        document.body.style.overflow = 'hidden';
    }
}

function closeAddModal() {
    const modal = document.getElementById('addModal');
    if (modal) {
        modal.classList.remove('active');
        // Przywróć scroll na body
        document.body.style.overflow = '';
    }
}

// INICJALIZACJA PO ZAŁADOWANIU STRONY
document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // 1. OBSŁUGA MODALU
    // ========================================
    const modal = document.getElementById('addModal');
    
    if (modal) {
        // Zamknij modal po kliknięciu w tło (poza content)
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeAddModal();
            }
        });
    }
    
    // Zamknij modal klawiszem ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            closeAddModal();
        }
    });
    
    // ========================================
    // 2. POTWIERDZENIE USUWANIA
    // ========================================
    const deleteLinks = document.querySelectorAll('a[href*="usun="]');
    
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const userName = this.closest('tr').querySelector('.user-name')?.textContent || 'tego użytkownika';
            
            if (confirm(`Czy na pewno chcesz usunąć ${userName}?\n\nTa operacja jest nieodwracalna!`)) {
                window.location.href = this.href;
            }
        });
    });
    
    // ========================================
    // 3. WALIDACJA FORMULARZA DODAWANIA
    // ========================================
    const addForm = document.querySelector('#addModal form');
    
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const haslo = this.querySelector('input[name="haslo"]').value;
            const login = this.querySelector('input[name="login"]').value;
            
            // Sprawdź długość hasła
            if (haslo.length < 6) {
                e.preventDefault();
                alert('Hasło musi mieć co najmniej 6 znaków!');
                return false;
            }
            
            // Sprawdź długość loginu
            if (login.length < 3) {
                e.preventDefault();
                alert('Login musi mieć co najmniej 3 znaki!');
                return false;
            }
        });
    }
    
    // ========================================
    // 4. WALIDACJA FORMULARZA EDYCJI
    // ========================================
    const editForm = document.querySelector('form[name="edytuj_uzytkownika"]');
    
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const noweHaslo = this.querySelector('input[name="nowe_haslo"]').value;
            
            // Jeśli wpisano nowe hasło, sprawdź jego długość
            if (noweHaslo !== '' && noweHaslo.length < 6) {
                e.preventDefault();
                alert('Nowe hasło musi mieć co najmniej 6 znaków!');
                return false;
            }
        });
    }
    
    // ========================================
    // 5. POTWIERDZENIE ZMIANY ROLI
    // ========================================
    const roleButtons = document.querySelectorAll('.role-select-form button[name="zmien_role"]');
    
    roleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const form = this.closest('form');
            const select = form.querySelector('select[name="rola"]');
            const nowaRola = select.value;
            const userName = this.closest('tr').querySelector('.user-name')?.textContent || 'użytkownika';
            
            const rolaText = nowaRola === 'admin' ? 'Administratora' : 'Użytkownika';
            
            if (confirm(`Czy na pewno chcesz zmienić rolę ${userName} na: ${rolaText}?`)) {
                form.submit();
            }
        });
    });
    
    // ========================================
    // 6. PODŚWIETLENIE WYBRANEJ OPCJI ROLI W DROPDOWN
    // ========================================
    const roleSelects = document.querySelectorAll('.role-select-form select');
    
    roleSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Podświetl wybraną opcję
            this.style.borderColor = '#00d9ff';
            setTimeout(() => {
                this.style.borderColor = '';
            }, 1000);
        });
    });
    
    // ========================================
    // 7. ANIMACJA STATYSTYK (LICZNIKI)
    // ========================================
    const statValues = document.querySelectorAll('.quick-stat-value');
    
    statValues.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = finalValue / 30; // 30 kroków animacji
        
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                stat.textContent = finalValue;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(currentValue);
            }
        }, 30);
    });
    
    // ========================================
    // 8. FILTROWANIE TABELI W CZASIE RZECZYWISTYM (OPCJONALNE)
    // ========================================
    const searchInput = document.querySelector('input[name="search"]');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            // Opóźnienie 500ms przed wyszukaniem
            searchTimeout = setTimeout(() => {
                const searchValue = this.value.toLowerCase();
                const rows = document.querySelectorAll('.admin-table tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    
                    if (text.includes(searchValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }, 500);
        });
    }
    
    // ========================================
    // 9. TOOLTIP DLA SKRÓCONYCH INFORMACJI
    // ========================================
    const userEmails = document.querySelectorAll('.admin-table td:nth-child(2)');
    
    userEmails.forEach(email => {
        email.title = email.textContent; // Pokaż pełny email po najechaniu
    });
    
    // ========================================
    // 10. AUTO-UKRYWANIE ALERTÓW PO 5 SEKUNDACH
    // ========================================
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    console.log('✅ Panel użytkowników załadowany pomyślnie!');
});

// ============================================
// EKSPORT FUNKCJI (opcjonalnie)
// ============================================
window.openAddModal = openAddModal;
window.closeAddModal = closeAddModal;