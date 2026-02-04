<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
}

.app-footer {
    position: fixed;
    bottom: 0;
    left: 280px; /* sesuaikan dengan lebar sidebar */
    right: 0;

    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;

    background: var(--gradient);
    color: #ffffff;
    font-size: 13px;
    text-align: center;

    border-top: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    z-index: 999;
}

/* ===== TABLET ===== */
@media (max-width: 992px) {
    .app-footer {
        left: 0;
        font-size: 12px;
        padding: 0 15px;
    }
}

/* ===== TABLET KECIL ===== */
@media (max-width: 768px) {
    .app-footer {
        left: 0;
        height: 45px;
        font-size: 12px;
        padding: 0 12px;
    }
}

/* ===== MOBILE ===== */
@media (max-width: 576px) {
    .app-footer {
        left: 0;
        height: auto;
        min-height: 40px;
        padding: 10px 15px;
        font-size: 11px;
        line-height: 1.4;
    }
}

/* ===== MOBILE SANGAT KECIL ===== */
@media (max-width: 380px) {
    .app-footer {
        font-size: 10px;
        padding: 8px 10px;
    }
}
</style>

<footer class="app-footer">
    © 2026 Politeknik Nest - Magang UNS 2026
</footer>