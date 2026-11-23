<style>
    :root {
        color-scheme: dark;
        --page: #101215;
        --surface: #171a1f;
        --surface-raised: #1d2128;
        --border: #2a3038;
        --text: #e7e9ed;
        --muted: #9aa2ad;
        --accent: #8981e8;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    body {
        background: var(--page) !important;
        color: var(--text) !important;
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
        line-height: 1.55;
    }

    h1,
    h2,
    h3 {
        color: #f1f2f4 !important;
        letter-spacing: -0.025em;
    }

    p {
        line-height: 1.6;
    }

    a:not(.btn):not(.btn-module):not(.btn-secondary):not(.btn-back) {
        color: var(--accent);
    }

    .panel,
    .card,
    .container,
    .login-card,
    .wrap {
        background-color: var(--surface) !important;
        border: 1px solid var(--border) !important;
        border-radius: 14px !important;
        box-shadow: none !important;
    }

    button,
    .btn,
    .btn-module:not(.btn-secondary),
    .btn-submit,
    .btn-sm,
    .btn-danger {
        border: 1px solid var(--border) !important;
        border-radius: 8px !important;
        background: var(--surface-raised) !important;
        color: var(--text) !important;
        font: inherit;
        font-weight: 600;
        box-shadow: none !important;
        transition: background-color 140ms ease, border-color 140ms ease;
    }

    .btn-module:not(.btn-secondary),
    .btn-submit,
    .btn:not(.btn-secondary):not(.btn-gray):not(.btn-red):not(.btn-green):not(.btn-danger):not(.btn-delete) {
        background: #38346b !important;
        border-color: #514c8b !important;
        color: #f4f3ff !important;
    }

    button:hover,
    .btn:hover,
    .btn-module:hover,
    .btn-submit:hover {
        background: #292d35 !important;
        border-color: #414854 !important;
        transform: none !important;
    }

    .btn-module:not(.btn-secondary):hover,
    .btn-submit:hover {
        background: #454080 !important;
        border-color: #625ca4 !important;
    }

    .btn-red,
    .btn-danger,
    .btn-delete,
    .btn-logout {
        background: #43252b !important;
        border-color: #684048 !important;
        color: #f2dfe1 !important;
    }

    .btn-green,
    .btn-restore {
        background: #223b33 !important;
        border-color: #36594b !important;
        color: #dcece4 !important;
    }

    input,
    select,
    textarea {
        border: 1px solid #343a44 !important;
        border-radius: 8px !important;
        background: #111419 !important;
        color: var(--text) !important;
        font: inherit;
    }

    input:focus,
    select:focus,
    textarea:focus {
        outline: 2px solid #625ca4 !important;
        outline-offset: 1px;
    }

    table {
        border-color: var(--border) !important;
        color: var(--text);
    }

    th,
    td {
        border-color: var(--border) !important;
    }

    th {
        background: var(--surface-raised) !important;
        color: #c8cbd1 !important;
    }

    .mode-badge,
    .badge,
    .sort-bar {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface-raised) !important;
        color: #c8cbd1 !important;
    }

    .alert,
    .alert-ok,
    .alert-status,
    .status {
        border: 1px solid #315c4d;
        border-radius: 8px;
        background: #1b2b25 !important;
        color: #c3e3d3 !important;
    }

    .alert-error,
    .alert-err,
    .error,
    .error-box {
        border: 1px solid #684048;
        border-radius: 8px;
        background: #302024 !important;
        color: #f0d0d4 !important;
    }

    [class~="bg-slate-950"],
    [class~="bg-slate-900"],
    [class~="bg-slate-800"] {
        background-color: var(--surface) !important;
    }

    [class~="border-slate-800"],
    [class~="border-slate-700"] {
        border-color: var(--border) !important;
    }

    [class~="text-slate-100"],
    [class~="text-slate-300"],
    [class~="text-white"] {
        color: var(--text) !important;
    }

    [class~="text-slate-400"] {
        color: var(--muted) !important;
    }

    [class~="text-purple-400"],
    [class~="text-purple-300"] {
        color: #b7b1f5 !important;
    }

    [class~="bg-purple-600"],
    [class~="bg-purple-500"] {
        background-color: #38346b !important;
    }

    [class~="bg-emerald-900/30"] {
        background-color: #1b2b25 !important;
    }

    [class~="bg-rose-900/30"] {
        background-color: #302024 !important;
    }

    @media (max-width: 640px) {
        body {
            padding: 16px !important;
        }

        table {
            display: block;
            max-width: 100%;
            overflow-x: auto;
        }
    }
</style>
