@if (session('success'))
    <div class="mx-6 mt-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-sm flex justify-between items-center"
        x-data="{ show: true }" x-show="show" x-transition>
        <span>{{ session('success') }}</span>
        <button @click="show = false" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
    </div>
@endif

@if (session('error'))
    <div class="mx-6 mt-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm shadow-sm flex justify-between items-center"
        x-data="{ show: true }" x-show="show" x-transition>
        <span>{{ session('error') }}</span>
        <button @click="show = false" class="text-red-600 hover:text-red-900 font-bold">&times;</button>
    </div>
@endif
