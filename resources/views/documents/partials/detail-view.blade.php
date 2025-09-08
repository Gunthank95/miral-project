<div class="bg-gray-50 p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Kolom Kiri: Detail Gambar & Pekerjaan --}}
    <div class="space-y-4">
        <div>
            <h4 class="font-semibold text-sm text-gray-700 mb-2">Daftar Gambar</h4>
            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1 bg-white p-3 rounded-md border">
                @forelse ($document->drawingDetails as $drawing)
                    <li>
                        <span class="font-mono bg-gray-200 px-1 rounded text-xs">{{ $drawing->drawing_number }}</span> - {{ $drawing->drawing_title }}
                    </li>
                @empty
                    <li>Tidak ada detail gambar.</li>
                @endforelse
            </ul>
        </div>
        <div>
            <h4 class="font-semibold text-sm text-gray-700 mb-2">Pekerjaan RAB Terkait</h4>
            <div class="space-y-2 text-sm">
                @forelse ($document->rabItems as $item)
                    <div class="flex justify-between items-center p-2 bg-white rounded-md border">
                        <span class="text-gray-800">{{ $item->item_name }}</span>
                        @if(isset($item->pivot->completion_status))
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full 
                                {{ $item->pivot->completion_status === 'lengkap' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $item->pivot->completion_status)) }}
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 p-2">- Tidak ada pekerjaan terkait -</p>
                @endforelse
            </div>
        </div>
    </div>
    {{-- Kolom Kanan: Riwayat Persetujuan --}}
    <div>
        <h4 class="font-semibold text-sm text-gray-700 mb-2">Riwayat Persetujuan</h4>
        @if ($document->approvals->isNotEmpty())
            <table class="min-w-full text-sm bg-white rounded-md border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Tanggal</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Oleh</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($document->approvals->sortByDesc('created_at') as $approval)
                        <tr>
                            <td class="px-3 py-2 text-gray-500">{{ $approval->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-3 py-2 font-medium text-gray-800">{{ $approval->user->name ?? 'User' }}</td>
                            <td class="px-3 py-2">
                                @php
                                    $statusConfig = [ 'pending' => ['text' => 'Diajukan', 'color' => 'blue'], 'revision' => ['text' => 'Revisi', 'color' => 'yellow'], 'approved' => ['text' => 'Disetujui', 'color' => 'green'], 'rejected' => ['text' => 'Ditolak', 'color' => 'red'], ];
                                    $appConfig = $statusConfig[strtolower($approval->status)] ?? ['text' => ucfirst($approval->status), 'color' => 'gray'];
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $appConfig['color'] }}-100 text-{{ $appConfig['color'] }}-800">
                                    {{ $appConfig['text'] }}
                                </span>
                            </td>
                        </tr>
                        @if($approval->notes)
                        <tr class="bg-gray-50"><td colspan="3" class="px-3 py-1 text-xs text-gray-600 italic border-t">Catatan: "{{ $approval->notes }}"</td></tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-sm text-gray-500 p-3 bg-white rounded-md border">Belum ada riwayat persetujuan.</p>
        @endif
    </div>
</div>