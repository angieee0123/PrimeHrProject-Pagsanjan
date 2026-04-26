<div id="successModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 450px; text-align: center; padding: 32px;">
        <div style="width: 64px; height: 64px; background: #e8f9ef; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <h3 style="margin: 0 0 12px; font-size: 20px; font-weight: 700; color: #0b044d;">Success!</h3>
        <p style="margin: 0 0 24px; font-size: 14px; color: #6b6a8a; line-height: 1.6;">Attendance corrected successfully!</p>
        <button onclick="closeSuccessModal()" style="padding: 12px 32px; background: #15803d; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif;">Done</button>
    </div>
</div>

<style>
#successModal .modal-box {
    animation: successSlideIn 0.3s ease-out;
}

@keyframes successSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
