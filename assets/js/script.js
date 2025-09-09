// Double click pada card ruangan
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.ruangan-card');
    
    cards.forEach(card => {
        card.addEventListener('dblclick', function() {
            const ruanganId = this.getAttribute('data-id');
            const modal = new bootstrap.Modal(document.getElementById(`detailModal${ruanganId}`));
            modal.show();
        });
    });
    
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('#msg-flash');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        }, 5000);
    });
    
    // Form validation for time inputs
    const timeInputs = document.querySelectorAll('input[type="time"]');
    timeInputs.forEach(input => {
        input.addEventListener('change', function() {
            const startTime = document.getElementById('jam_mulai');
            const endTime = document.getElementById('jam_selesai');
            
            if (startTime && endTime && startTime.value && endTime.value) {
                if (startTime.value >= endTime.value) {
                    alert('Jam selesai harus setelah jam mulai');
                    endTime.value = '';
                    endTime.focus();
                }
            }
        });
    });
    
    // Form validation for date inputs
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert('Tanggal tidak boleh di masa lalu');
                this.value = '';
                this.focus();
            }
        });
    });
});