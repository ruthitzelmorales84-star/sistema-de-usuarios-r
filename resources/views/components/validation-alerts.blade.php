@if (session('success'))
    <div class="rounded-lg bg-emerald-950/70 border border-emerald-500/50 px-4 py-3 text-sm text-emerald-200">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="rounded-lg bg-rose-950/70 border border-rose-500/50 px-4 py-3 text-sm text-rose-200">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="rounded-lg bg-rose-950/70 border border-rose-500/50 px-4 py-3 text-sm text-rose-200 space-y-1">
        <p class="font-semibold">Revisa los siguientes campos:</p>
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif