<style>
:root {
    --pink: #FF74C7;
    --orange: #FF983D;
    --gradient: linear-gradient(135deg, #FF74C7, #FF983D);
}

.app-footer {
    position: fixed;
    bottom: 0;
    left: 250px; /* sesuai lebar sidebar */
    right: 0;

    height: 40px;
    line-height: 40px;

    background: var(--gradient); /* 🔥 GRADIENT */
    color: #ffffff;
    font-size: 13px;
    text-align: center;

    border-top: none;
    z-index: 999;
}
</style>

<footer class="app-footer">
    © 2026 Politeknik Nest – Magang UNS 2026
</footer>
