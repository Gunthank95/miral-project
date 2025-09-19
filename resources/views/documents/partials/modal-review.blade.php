<div x-show="reviewModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div @click.away="closeReviewModal()" class="bg-gray-50 rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] flex flex-col">
        <div class="bg-white p-4 border-b rounded-t-lg">
            <h2 id="modal-title" class="text-xl font-bold">Formulir Review Shop Drawing</h2>
            <p class="text-sm text-gray-500">No. Surat: <strong x-text="reviewModal.documentTitle"></strong></p>
        </div>
        <div class="p-6 overflow-y-auto space-y-6">
            <div x-show="reviewModal.loading" class="text-center py-10"><p class="text-gray-500">Memuat detail dokumen...</p></div>
            
            {{-- Tampilkan form HANYA jika loading selesai DAN ada 'details' --}}
            <div x-show="!reviewModal.loading && reviewModal.details" class="space-y-6">
                <form :action="reviewModal.actionUrl" method="POST">
                    @csrf
                    
                    {{-- 1. Review Per Gambar --}}
                    <div>
                        <h3 class="font-semibold text-gray-800 border-b pb-2 mb-2">1. Hasil Pemeriksaan Gambar</h3>
                        <div class="space-y-4">
                            {{-- PERBAIKAN: Hapus satu template x-for yang berulang --}}
                            <template x-for="drawing in reviewModal.details.drawings" :key="drawing.id">
                                <div class="p-3 bg-white rounded-md border grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-1">
                                        <p class="text-sm font-medium" x-text="drawing.drawing_title"></p>
                                        <p class="text-xs text-gray-500 font-mono" x-text="drawing.drawing_number"></p>
                                    </div>
                                    <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label :for="'status_' + drawing.id" class="text-xs font-medium text-gray-600">Status</label>
                                            <select :name="'drawings[' + drawing.id + '][status]'" :id="'status_' + drawing.id" class="mt-1 block w-full py-1 px-2 border border-gray-300 rounded-md text-sm" required>
                                                <option value="approved">Disetujui</option>
                                                <option value="revision">Revisi</option>
                                                <option value="rejected">Ditolak</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label :for="'notes_' + drawing.id" class="text-xs font-medium text-gray-600">Catatan</label>
                                            <input type="text" :name="'drawings[' + drawing.id + '][notes]'" :id="'notes_' + drawing.id" class="mt-1 block w-full border border-gray-300 rounded-md text-sm py-1 px-2">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- 2. Verifikasi Kelengkapan Pekerjaan --}}
                    <div>
                        <h3 class="font-semibold text-gray-800 border-b pb-2 mb-2">2. Verifikasi Kelengkapan Pekerjaan</h3>
                        <div class="space-y-2 text-sm">
                            {{-- PERBAIKAN: Hapus satu template x-for yang berulang --}}
                            <template x-for="item in reviewModal.details.rab_items" :key="item.id">
                                <div class="flex justify-between items-center p-2 bg-white rounded-md border">
                                    <span x-text="item.item_name"></span>
                                    <select :name="'rab_items[' + item.id + '][completion_status]'" class="py-1 px-2 border border-gray-300 rounded-md text-xs">
                                        <option value="belum_lengkap" :selected="item.pivot.completion_status == 'belum_lengkap'">Belum Lengkap</option>
                                        <option value="lengkap" :selected="item.pivot.completion_status == 'lengkap'">Lengkap</option>
                                    </select>
                                </div>
                            </template>
                            <div x-show="!reviewModal.details.rab_items || reviewModal.details.rab_items.length === 0" class="text-gray-500 text-sm p-2">
                                Tidak ada item RAB yang terkait.
                            </div>
                        </div>
                    </div>
                    
                    {{-- 3. Keputusan & Disposisi Akhir --}}
					{{-- BUNGKUS DENGAN TEMPLATE X-IF INI --}}
					<template x-if="reviewModal.details.current_user_level == 60">
						<div class="bg-white p-4 rounded-md border">
							<h3 class="font-semibold text-gray-800 mb-2">3. Keputusan Akhir & Disposisi (PM)</h3>
							<div class="space-y-4">
								<div>
									<label for="overall_notes_pm" class="block text-sm font-medium text-gray-700">Catatan Keseluruhan</label>
									<textarea x-model="reviewModal.form.overall_notes" id="overall_notes_pm" name="overall_notes" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
								</div>
								<div>
									<label for="disposition" class="block text-sm font-medium text-gray-700">Disposisi Final</label>
									{{-- SEDERHANAKAN PILIHANNYA --}}
									<select x-model="reviewModal.form.disposition" id="disposition" name="disposition" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md" required>
										<option value="">-- Pilih Keputusan --</option>
										<option value="to_owner">Teruskan ke Owner</option>
										<option value="to_revision">Kembalikan ke Kontraktor (Revisi)</option>
									</select>                            
								</div>
							</div>
						</div>
					</template>

                    {{-- Footer Tombol Aksi Form --}}
                    <div class="mt-6 flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" @click="closeReviewModal()" class="bg-white py-2 px-4 border border-gray-300 rounded-md">Batal</button>
                        <button type="submit" class="bg-indigo-600 py-2 px-4 text-white rounded-md">Simpan & Lanjutkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>