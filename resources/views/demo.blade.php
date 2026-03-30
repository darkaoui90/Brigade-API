<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RestaurantAI-Brigade Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<div class="mx-auto max-w-6xl px-4 py-10">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-200">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                Demo UI (REST API + Sanctum + Queue)
            </div>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight">RestaurantAI-Brigade</h1>
            <p class="mt-1 text-sm text-slate-300">Login, set dietary profile, then analyze a plate to get a recommendation score.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/docs" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm hover:bg-white/10">API Docs</a>
            <button id="btnLogout" class="hidden rounded-xl bg-rose-500/90 px-4 py-2 text-sm font-medium text-white hover:bg-rose-500">Logout</button>
        </div>
    </div>

    <div id="toast" class="pointer-events-none fixed right-4 top-4 hidden max-w-sm rounded-xl border border-white/10 bg-black/70 px-4 py-3 text-sm"></div>

    <div class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 lg:col-span-1">
            <h2 class="text-lg font-semibold">Auth</h2>
            <p class="mt-1 text-sm text-slate-300">Token stored in localStorage for this demo.</p>

            <div id="authForms" class="mt-4 space-y-6">
                <form id="formLogin" class="space-y-3">
                    <div class="text-sm font-medium text-slate-200">Login</div>
                    <input class="w-full rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm outline-none focus:border-white/20"
                           type="email" name="email" placeholder="email" required>
                    <input class="w-full rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm outline-none focus:border-white/20"
                           type="password" name="password" placeholder="password" required>
                    <button class="w-full rounded-xl bg-indigo-500 px-4 py-2 text-sm font-medium hover:bg-indigo-400">Login</button>
                </form>

                <div class="h-px bg-white/10"></div>

                <form id="formRegister" class="space-y-3">
                    <div class="text-sm font-medium text-slate-200">Register</div>
                    <input class="w-full rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm outline-none focus:border-white/20"
                           type="text" name="name" placeholder="name" required>
                    <input class="w-full rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm outline-none focus:border-white/20"
                           type="email" name="email" placeholder="email" required>
                    <input class="w-full rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm outline-none focus:border-white/20"
                           type="password" name="password" placeholder="password (min 8)" minlength="8" required>
                    <button class="w-full rounded-xl bg-slate-100/90 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-white">Create account</button>
                </form>
            </div>

            <div id="authInfo" class="mt-4 hidden space-y-3">
                <div class="rounded-xl border border-white/10 bg-black/20 p-3 text-sm">
                    <div class="text-slate-300">Logged in as</div>
                    <div id="meLine" class="mt-1 font-medium"></div>
                    <div id="adminBadge" class="mt-2 hidden inline-flex items-center rounded-full bg-emerald-500/15 px-2 py-1 text-xs text-emerald-300 ring-1 ring-emerald-500/20">Admin</div>
                </div>
                <div class="rounded-xl border border-white/10 bg-black/20 p-3 text-xs text-slate-300">
                    <div class="font-medium text-slate-200">Token (preview)</div>
                    <div id="tokenPreview" class="mt-1 break-all"></div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 lg:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Dietary Profile</h2>
                    <p class="mt-1 text-sm text-slate-300">Restrictions used during recommendation scoring.</p>
                </div>
                <button id="btnRefresh" class="rounded-xl border border-white/10 bg-black/20 px-4 py-2 text-sm hover:bg-white/10">Refresh</button>
            </div>

            <form id="formProfile" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="flex items-center gap-2 rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm">
                    <input type="checkbox" name="dietary_tags" value="vegan" class="accent-indigo-400"> Vegan
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm">
                    <input type="checkbox" name="dietary_tags" value="no_sugar" class="accent-indigo-400"> No sugar
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm">
                    <input type="checkbox" name="dietary_tags" value="no_cholesterol" class="accent-indigo-400"> No cholesterol
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm">
                    <input type="checkbox" name="dietary_tags" value="gluten_free" class="accent-indigo-400"> Gluten free
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm sm:col-span-2">
                    <input type="checkbox" name="dietary_tags" value="no_lactose" class="accent-indigo-400"> No lactose
                </label>
                <button class="sm:col-span-2 rounded-xl bg-emerald-500/90 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Save profile</button>
            </form>

            <div class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4 lg:col-span-1">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium">Categories</div>
                        <button id="btnLoadCategories" class="text-xs text-slate-300 hover:text-white">Reload</button>
                    </div>
                    <div id="categoriesList" class="mt-3 space-y-2 text-sm text-slate-200"></div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-black/20 p-4 lg:col-span-2">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium">Plates</div>
                            <div class="text-xs text-slate-300">Press Analyze to enqueue a Job and compute a score.</div>
                        </div>
                        <button id="btnLoadPlates" class="rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-xs hover:bg-white/10">Reload</button>
                    </div>
                    <div id="platesGrid" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2"></div>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-white/10 bg-black/20 p-4 text-xs text-slate-300">
                Queue tip (for async): run <span class="font-mono text-slate-100">php artisan queue:work</span> in another terminal.
            </div>
        </div>
    </div>
</div>

<script>
    const tokenKey = 'restaurantai_token';
    const toastEl = document.getElementById('toast');

    function toast(message, variant = 'info') {
        toastEl.textContent = message;
        toastEl.classList.remove('hidden');
        toastEl.className = 'pointer-events-none fixed right-4 top-4 max-w-sm rounded-xl border px-4 py-3 text-sm ' + (
            variant === 'error'
                ? 'border-rose-500/30 bg-rose-500/10 text-rose-100'
                : 'border-white/10 bg-black/70 text-slate-100'
        );
        setTimeout(() => toastEl.classList.add('hidden'), 2800);
    }

    function getToken() { return localStorage.getItem(tokenKey); }
    function setToken(token) { token ? localStorage.setItem(tokenKey, token) : localStorage.removeItem(tokenKey); }

    async function api(path, options = {}) {
        const headers = Object.assign({ 'Accept': 'application/json' }, options.headers || {});
        const token = getToken();
        if (token) headers['Authorization'] = `Bearer ${token}`;
        if (options.body && !(options.body instanceof FormData)) headers['Content-Type'] = 'application/json';

        const res = await fetch(path.startsWith('/api') ? path : ('/api' + path), {
            ...options,
            headers,
            body: options.body instanceof FormData ? options.body : (options.body ? JSON.stringify(options.body) : undefined),
        });

        const text = await res.text();
        let json = null;
        try { json = text ? JSON.parse(text) : null; } catch (e) {}

        if (!res.ok) throw new Error(json?.message || `Request failed (${res.status})`);
        return json;
    }

    function setAuthedUI(user) {
        document.getElementById('authForms').classList.toggle('hidden', !!user);
        document.getElementById('authInfo').classList.toggle('hidden', !user);
        document.getElementById('btnLogout').classList.toggle('hidden', !user);
        document.getElementById('adminBadge').classList.toggle('hidden', !(user?.is_admin));

        if (user) {
            document.getElementById('meLine').textContent = `${user.name} — ${user.email}`;
            const t = getToken() || '';
            document.getElementById('tokenPreview').textContent = t ? (t.slice(0, 32) + '…') : '';
        }
    }

    function checkedValues(form, name) {
        return Array.from(form.querySelectorAll(`input[name="${name}"]:checked`)).map(i => i.value);
    }

    async function loadProfile() {
        const profile = await api('/profile');
        const tags = profile?.dietary_tags || [];
        document.querySelectorAll('#formProfile input[name="dietary_tags"]').forEach((cb) => cb.checked = tags.includes(cb.value));
    }

    async function loadCategories() {
        const categories = await api('/categories');
        const list = document.getElementById('categoriesList');
        list.innerHTML = '';
        const items = categories?.data || [];
        if (items.length === 0) {
            list.innerHTML = '<div class="text-xs text-slate-400">No categories yet.</div>';
            return;
        }
        for (const c of items) {
            const el = document.createElement('div');
            el.className = 'flex items-center justify-between rounded-xl border border-white/10 bg-black/30 px-3 py-2';
            el.innerHTML = `<div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background:${c.color || '#94a3b8'}"></span><span>${c.name}</span></div><span class="text-xs text-slate-400">${c.is_active ? 'active' : 'inactive'}</span>`;
            list.appendChild(el);
        }
    }

    function badgeForRecommendation(rec) {
        if (!rec) return '<span class="rounded-full border border-white/10 bg-white/5 px-2 py-1 text-xs text-slate-300">No analysis</span>';
        if (rec.status === 'processing') return '<span class="rounded-full border border-indigo-500/30 bg-indigo-500/10 px-2 py-1 text-xs text-indigo-200">processing</span>';
        const score = Number(rec.score ?? 0);
        const cls = score >= 80 ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200'
            : score >= 50 ? 'border-amber-500/30 bg-amber-500/10 text-amber-200'
                : 'border-rose-500/30 bg-rose-500/10 text-rose-200';
        return `<span class="rounded-full border px-2 py-1 text-xs ${cls}">${score}%</span>`;
    }

    async function loadPlates() {
        const plates = await api('/plates');
        const grid = document.getElementById('platesGrid');
        grid.innerHTML = '';
        const items = plates?.data || [];
        if (items.length === 0) {
            grid.innerHTML = '<div class="text-xs text-slate-400">No plates yet.</div>';
            return;
        }
        for (const p of items) {
            const ingredients = (p.ingredients || []).map(i => i.name).join(', ') || '—';
            const categoryName = p.category?.name || '—';
            const rec = p.recommendation;
            const warn = rec?.warning_message ? `<div class="mt-2 text-xs text-slate-300">${rec.warning_message}</div>` : '';

            const el = document.createElement('div');
            el.className = 'rounded-2xl border border-white/10 bg-black/30 p-4';
            el.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold">${p.name}</div>
                        <div class="mt-1 text-xs text-slate-300">${categoryName} • $${Number(p.price).toFixed(2)}</div>
                    </div>
                    <div class="shrink-0">${badgeForRecommendation(rec)}</div>
                </div>
                <div class="mt-3 text-xs text-slate-300"><span class="text-slate-400">Ingredients:</span> ${ingredients}</div>
                ${warn}
                <button class="btnAnalyze mt-4 w-full rounded-xl bg-indigo-500 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-400">
                    Analyze compatibility
                </button>
            `;
            el.querySelector('.btnAnalyze').addEventListener('click', async () => {
                try {
                    toast('Queued analysis…');
                    await api(`/recommendations/analyze/${p.id}`, { method: 'POST' });
                    await loadPlates();
                } catch (e) {
                    toast(e.message, 'error');
                }
            });
            grid.appendChild(el);
        }
    }

    async function refreshAll() {
        const me = await api('/me');
        setAuthedUI(me);
        await loadProfile();
        await loadCategories();
        await loadPlates();
    }

    document.getElementById('formLogin').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        try {
            const res = await api('/login', { method: 'POST', body: { email: fd.get('email'), password: fd.get('password') } });
            setToken(res.token);
            toast('Logged in!');
            await refreshAll();
        } catch (err) { toast(err.message, 'error'); }
    });

    document.getElementById('formRegister').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        try {
            const res = await api('/register', { method: 'POST', body: { name: fd.get('name'), email: fd.get('email'), password: fd.get('password') } });
            setToken(res.token);
            toast('Account created!');
            await refreshAll();
        } catch (err) { toast(err.message, 'error'); }
    });

    document.getElementById('btnLogout').addEventListener('click', async () => {
        try { await api('/logout', { method: 'POST' }); } catch (e) {}
        setToken(null);
        toast('Logged out');
        setAuthedUI(null);
    });

    document.getElementById('btnRefresh').addEventListener('click', async () => {
        try { await refreshAll(); } catch (e) { toast(e.message, 'error'); }
    });
    document.getElementById('btnLoadCategories').addEventListener('click', async () => {
        try { await loadCategories(); } catch (e) { toast(e.message, 'error'); }
    });
    document.getElementById('btnLoadPlates').addEventListener('click', async () => {
        try { await loadPlates(); } catch (e) { toast(e.message, 'error'); }
    });

    document.getElementById('formProfile').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const tags = checkedValues(e.target, 'dietary_tags');
            await api('/profile', { method: 'PUT', body: { dietary_tags: tags } });
            toast('Profile saved');
            await loadPlates();
        } catch (err) { toast(err.message, 'error'); }
    });

    (async () => {
        if (!getToken()) return;
        try { await refreshAll(); } catch (e) { setToken(null); }
    })();
</script>
</body>
</html>

