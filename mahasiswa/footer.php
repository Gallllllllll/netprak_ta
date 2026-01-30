<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
}

.app-footer {
    position: fixed;
    bottom: 0;
    left: 250px; /* default: desktop (ada sidebar) */
    right: 0;

    height: 40px;
    line-height: 40px;

    background: var(--gradient);
    color: #ffffff;
    font-size: 13px;
    text-align: center;

    border-top: 1px solid #d1d5db;
    z-index: 999;
}

/* ===== TABLET & MOBILE ===== */
@media (max-width: 992px) {
    .app-footer {
        left: 0;              /* sidebar biasanya collapse */
        font-size: 12px;
        padding: 0 10px;
    }
}

/* ===== MOBILE KECIL ===== */
@media (max-width: 576px) {
    .app-footer {
        height: auto;
        line-height: 1.4;
        padding: 8px 12px;
        font-size: 11px;
    }
}
</style>

<footer class="app-footer">
    © 2026 Politeknik Nest - Magang UNS 2026
</footer>
