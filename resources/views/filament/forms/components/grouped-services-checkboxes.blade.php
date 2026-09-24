@php
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();

    $categories = \App\Models\ServiceCategory::with(['services' => fn ($q) => $q->orderBy('id')])->orderBy('id')->get();

    $categoryIcons = [
        'Connectivity' => 'heroicon-m-signal',
        'Platform' => 'heroicon-m-server-stack',
        'Service' => 'heroicon-m-cpu-chip',
    ];
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @foreach ($categories as $category)
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-900/40 p-3 flex flex-col justify-start">
                <!-- Header Kategori Ringkas -->
                <div class="flex items-center gap-2 pb-2 mb-1.5 border-b border-gray-200/70 dark:border-gray-800 text-xs font-bold text-gray-900 dark:text-gray-100">
                    <x-filament::icon
                        :icon="$categoryIcons[$category->name] ?? 'heroicon-m-folder'"
                        class="w-4 h-4 text-primary-600 dark:text-primary-400 shrink-0"
                    />
                    <span>{{ $category->name }}</span>
                    <span class="text-[10px] font-normal text-gray-400 dark:text-gray-500 ml-auto">
                        ({{ $category->services->count() }})
                    </span>
                </div>

                <!-- Daftar Checkbox Simpel & Kompak -->
                <div class="space-y-1">
                    @forelse ($category->services as $service)
                        @php
                            $val = (string) $service->id;
                        @endphp

                        <label
                            wire:key="{{ $field->getId() }}.{{ $statePath }}.service.{{ $val }}"
                            class="fi-fo-checkbox-list-option-label flex items-center gap-x-3 py-1.5 px-2 rounded-lg hover:bg-white dark:hover:bg-gray-800/80 cursor-pointer select-none transition-colors"
                            style="display: flex; align-items: center; gap: 10px;"
                        >
                            <x-filament::input.checkbox
                                :valid="! ($errors?->has($statePath) ?? false)"
                                :attributes="
                                    \Filament\Support\prepare_inherited_attributes($getExtraInputAttributeBag())
                                        ->merge([
                                            'disabled' => $isDisabled,
                                            'value' => $val,
                                            'wire:loading.attr' => 'disabled',
                                            $applyStateBindingModifiers('wire:model') => $statePath,
                                        ], escape: false)
                                        ->class(['rounded text-primary-600 focus:ring-primary-500 cursor-pointer'])
                                "
                            />
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-200 select-none">
                                {{ $service->name }}
                            </span>
                        </label>
                    @empty
                        <span class="text-xs text-gray-400 italic">Kosong</span>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-dynamic-component>
