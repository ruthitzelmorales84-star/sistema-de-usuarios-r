<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('components.minimal-theme')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-8 px-4">
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Sistema de Gestión de Productos</h1>
                <p class="text-sm text-slate-400">
                    Rol actual: <span class="font-semibold text-purple-400 uppercase">{{ auth()->user()->role ?? 'invitado' }}</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-sm">
                    Inicio
                </a>
                @if ($isAdmin)
                    <button type="button" onclick="openCreateModal()" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold">
                        + Nuevo producto
                    </button>
                @endif
            </div>
        </div>

        {{-- Resumen de saldo, ventas acumuladas y dinero ganado --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-xs uppercase text-slate-400">Saldo disponible (Compra)</p>
                <p class="text-xl font-bold text-sky-300 mt-1">${{ number_format((float) ($buyerBalance ?? session('buyer_balance', 1000)), 2) }}</p>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-xs uppercase text-slate-400">Ventas acumuladas</p>
                <p class="text-xl font-bold text-purple-300 mt-1">{{ $totalSold ?? 0 }} unidades</p>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-xs uppercase text-slate-400">Dinero ganado acumulado</p>
                <p class="text-xl font-bold text-emerald-300 mt-1">${{ number_format((float) ($totalEarned ?? 0), 2) }}</p>
            </div>
        </div>

        <x-validation-alerts />

        {{-- Filtros, búsqueda y ordenamiento --}}
        <form method="GET" action="{{ route('products.index') }}" class="bg-slate-900 border border-slate-800 rounded-xl p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="lg:col-span-2">
                    <label class="block text-xs text-slate-400 mb-1">Buscar por nombre</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Ej. Cuaderno, Mouse..."
                        class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500">
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Categoría</label>
                    <select name="category_id" class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white">
                        <option value="">Todas</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Estado</label>
                    <select name="status" class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white">
                        <option value="">Todos</option>
                        <option value="disponible" @selected($filters['status'] === 'disponible')>Disponible</option>
                        <option value="agotado" @selected($filters['status'] === 'agotado')>Agotado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Precio mín.</label>
                    <input type="number" step="0.01" min="0" name="min_price" value="{{ $filters['min_price'] }}" placeholder="0.00"
                        class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Precio máx.</label>
                    <input type="number" step="0.01" min="0" name="max_price" value="{{ $filters['max_price'] }}" placeholder="9999.00"
                        class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white">
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-800">
                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="text-xs text-slate-400 mr-1">Ordenar por:</label>
                        <select name="sort" class="rounded-lg bg-slate-800 border border-slate-700 px-3 py-1.5 text-sm text-white">
                            <option value="created_at_desc" @selected($sort === 'created_at_desc')>Fecha: Más recientes</option>
                            <option value="created_at_asc" @selected($sort === 'created_at_asc')>Fecha: Más antiguos</option>
                            <option value="name_asc" @selected($sort === 'name_asc')>Nombre: A - Z</option>
                            <option value="name_desc" @selected($sort === 'name_desc')>Nombre: Z - A</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>Precio: Mayor a menor</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>Precio: Menor a mayor</option>
                            <option value="stock_desc" @selected($sort === 'stock_desc')>Existencias: Mayor a menor</option>
                            <option value="stock_asc" @selected($sort === 'stock_asc')>Existencias: Menor a mayor</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-slate-400 mr-1">Mostrar:</label>
                        <select name="per_page" class="rounded-lg bg-slate-800 border border-slate-700 px-3 py-1.5 text-sm text-white">
                            @foreach ($allowedPerPage as $size)
                                <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('products.index') }}" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-sm text-slate-300">
                        Limpiar filtros
                    </a>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold">
                        Aplicar
                    </button>
                </div>
            </div>
        </form>

        {{-- Resumen de paginación --}}
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-400">
            <div>
                Total de registros encontrados: <span class="font-semibold text-white">{{ $products->total() }}</span>
            </div>
            <div>
                Página <span class="font-semibold text-white">{{ $products->currentPage() }}</span>
                de <span class="font-semibold text-white">{{ max(1, $products->lastPage()) }}</span>
            </div>
        </div>

        {{-- Tabla de productos --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="bg-slate-800/80 text-slate-300 uppercase text-xs">
                    <tr>
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Nombre</th>
                        <th class="py-3 px-4">Descripción</th>
                        <th class="py-3 px-4">Categoría</th>
                        <th class="py-3 px-4">Precio</th>
                        <th class="py-3 px-4">Existencias</th>
                        <th class="py-3 px-4">Vendidos</th>
                        <th class="py-3 px-4">Ganado</th>
                        <th class="py-3 px-4">Estado</th>
                        <th class="py-3 px-4">Fecha de registro</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $product->id }}</td>
                            <td class="py-3 px-4 font-semibold text-white">{{ $product->name }}</td>
                            <td class="py-3 px-4 text-slate-300 max-w-xs">{{ $product->description }}</td>
                            <td class="py-3 px-4 text-slate-300">{{ $product->category?->name ?? 'Sin categoría' }}</td>
                            <td class="py-3 px-4 text-emerald-300 font-medium">${{ number_format((float) $product->price, 2) }}</td>
                            <td class="py-3 px-4 text-slate-200">{{ $product->stock }}</td>
                            <td class="py-3 px-4 text-purple-300 font-medium">{{ $product->sold_count ?? 0 }}</td>
                            <td class="py-3 px-4 text-emerald-300 font-medium">${{ number_format((float) ($product->total_earned ?? 0), 2) }}</td>
                            <td class="py-3 px-4">
                                <x-product-status-badge :status="$product->status" />
                            </td>
                            <td class="py-3 px-4 text-slate-400 whitespace-nowrap">
                                {{ $product->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-2">
                                <form action="{{ route('products.buy', $product) }}" method="POST" class="inline-flex items-center gap-1">
                                    @csrf
                                    <input type="number" name="quantity" value="1" min="1" max="{{ max(1, (int) $product->stock) }}"
                                        @disabled((int) $product->stock < 1 || $product->status !== 'disponible')
                                        class="w-14 rounded bg-slate-800 border border-slate-700 px-2 py-1 text-xs text-white disabled:opacity-40">
                                    <button type="submit"
                                        @disabled((int) $product->stock < 1 || $product->status !== 'disponible')
                                        class="px-2.5 py-1 rounded bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white text-xs font-semibold">
                                        Comprar
                                    </button>
                                </form>

                                @if ($isAdmin)
                                    <button type="button"
                                        onclick='openEditModal(@json($product))'
                                        class="px-2.5 py-1 rounded bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold">
                                        Editar
                                    </button>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                                        onsubmit="return confirm('¿Eliminar este producto?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-8 px-4 text-center text-slate-400">
                                No se encontraron productos con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Enlaces de paginación --}}
        <div>
            {{ $products->links() }}
        </div>
    </div>

    @if ($isAdmin)
        {{-- Modal Crear / Editar --}}
        <div id="productModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center p-4 z-50">
            <div class="bg-slate-900 border border-slate-700 rounded-xl w-full max-w-lg p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h2 id="modalTitle" class="text-lg font-bold text-white">Nuevo producto</h2>
                    <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-white text-xl">&times;</button>
                </div>

                <form id="productForm" method="POST" action="{{ route('products.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Nombre *</label>
                        <input type="text" name="name" id="fieldName" required
                            class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Descripción *</label>
                        <textarea name="description" id="fieldDescription" rows="3" required
                            class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-300 mb-1">Categoría *</label>
                            <select name="category_id" id="fieldCategory" required
                                class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white">
                                <option value="">Selecciona...</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-300 mb-1">Estado *</label>
                            <select name="status" id="fieldStatus" required
                                class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white">
                                <option value="disponible">Disponible</option>
                                <option value="agotado">Agotado</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-300 mb-1">Precio *</label>
                            <input type="number" step="0.01" min="0.01" name="price" id="fieldPrice" required
                                class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white">
                        </div>

                        <div>
                            <label class="block text-xs text-slate-300 mb-1">Existencias *</label>
                            <input type="number" step="1" min="0" name="stock" id="fieldStock" required
                                class="w-full rounded-lg bg-slate-800 border border-slate-700 px-3 py-2 text-sm text-white">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-sm">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const modal = document.getElementById('productModal');
            const form = document.getElementById('productForm');
            const methodInput = document.getElementById('formMethod');
            const modalTitle = document.getElementById('modalTitle');

            function openCreateModal() {
                modalTitle.textContent = 'Nuevo producto';
                form.action = "{{ route('products.store') }}";
                methodInput.value = 'POST';
                document.getElementById('fieldName').value = '';
                document.getElementById('fieldDescription').value = '';
                document.getElementById('fieldCategory').value = '';
                document.getElementById('fieldStatus').value = 'disponible';
                document.getElementById('fieldPrice').value = '';
                document.getElementById('fieldStock').value = '0';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function openEditModal(product) {
                modalTitle.textContent = 'Editar producto #' + product.id;
                form.action = "{{ url('/productos') }}/" + product.id;
                methodInput.value = 'PUT';
                document.getElementById('fieldName').value = product.name ?? '';
                document.getElementById('fieldDescription').value = product.description ?? '';
                document.getElementById('fieldCategory').value = product.category_id ?? '';
                document.getElementById('fieldStatus').value = product.status ?? 'disponible';
                document.getElementById('fieldPrice').value = product.price ?? '';
                document.getElementById('fieldStock').value = product.stock ?? 0;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        </script>
    @endif
</body>
</html>