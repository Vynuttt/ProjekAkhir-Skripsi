<x-filament-panels::page>
    <div class="space-y-6">

        {{-- === SECTION: Backup Database === --}}
        <x-filament::section>
            <h2 class="text-lg font-bold mb-4">Backup Database</h2>
            <x-filament::button wire:click="createBackup" color="success" icon="heroicon-o-cloud-arrow-up">
                Buat Backup Sekarang
            </x-filament::button>
        </x-filament::section>

        {{-- === SECTION: Restore & Download Backup === --}}
        <x-filament::section>
            <h2 class="text-lg font-bold mb-4">Daftar File Backup</h2>

            @php
                $files = $this->getBackupFiles();
            @endphp

            @if (empty($files))
                <p class="text-gray-500">Belum ada file backup ditemukan.</p>
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-700">
                    <table class="min-w-full text-sm text-left border-collapse">
                        <thead class="bg-gray-900 text-gray-200">
                            <tr>
                                <th class="p-3 border-b border-gray-700">Nama File</th>
                                <th class="p-3 border-b border-gray-700 w-1/3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-800 text-gray-100 divide-y divide-gray-700">
                            @foreach ($files as $file)
                                <tr>
                                    <td class="p-3">{{ $file }}</td>
                                    <td class="p-3 text-center flex flex-wrap gap-2 justify-center">
                                        {{-- Tombol Download --}}
                                        <x-filament::button
                                            wire:click.prevent="downloadBackup('{{ $file }}')"
                                            color="primary"
                                            icon="heroicon-o-arrow-down-tray"
                                            size="sm">
                                            Download
                                        </x-filament::button>

                                        {{-- Tombol Restore (buka modal konfirmasi) --}}
                                        <x-filament::button
                                            x-data
                                            x-on:click="$dispatch('open-modal', { id: 'confirm-restore-{{ Str::slug($file) }}' })"
                                            color="warning"
                                            icon="heroicon-o-arrow-path"
                                            size="sm">
                                            Restore
                                        </x-filament::button>

                                        {{-- Modal Konfirmasi Restore --}}
                                        <x-filament::modal id="confirm-restore-{{ Str::slug($file) }}" width="md">
                                            <x-slot name="heading">
                                                Konfirmasi Restore Database
                                            </x-slot>

                                            <x-slot name="description">
                                                Apakah Anda yakin ingin me-restore database dari file <strong>{{ $file }}</strong>?<br>
                                                <span class="text-red-400 text-sm">Tindakan ini tidak dapat dibatalkan dan akan menimpa seluruh data saat ini.</span>
                                            </x-slot>

                                            <x-slot name="footer">
                                                <x-filament::button
                                                    color="gray"
                                                    x-on:click="$dispatch('close-modal', { id: 'confirm-restore-{{ Str::slug($file) }}' })">
                                                    Batal
                                                </x-filament::button>

                                                <x-filament::button
                                                    wire:click="restoreBackup('{{ $file }}')"
                                                    color="warning"
                                                    x-on:click="$dispatch('close-modal', { id: 'confirm-restore-{{ Str::slug($file) }}' })">
                                                    Ya, Lanjutkan
                                                </x-filament::button>
                                            </x-slot>
                                        </x-filament::modal>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- === SECTION: Informasi === --}}
        <x-filament::section>
            <div class="text-sm text-gray-400">
                <p>📦 File backup disimpan di folder: <code>storage/app/backups</code></p>
                <p>🧠 Pastikan Anda memiliki izin <strong>owner</strong> atau <strong>admin</strong> untuk melakukan restore.</p>
                <p>⚠️ Saat melakukan restore, seluruh data pada database akan diganti dengan data dari file backup yang dipilih.</p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
