<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use App\Models\DocumentApproval;
use Illuminate\Support\Facades\Storage;

class ApprovalController extends Controller
{
	public function index()
	{
		// Mengambil ID Paket Pekerjaan yang sedang aktif dari session
		$currentPackageId = session('current_package_id');

		// Menyiapkan variabel kosong sebagai default
		$pendingDocuments = collect(); 

		if ($currentPackageId) {
			// HANYA JIKA ADA PAKET YANG DIPILIH, baru kita cari dokumennya
			$pendingDocuments = \App\Models\Document::where('package_id', $currentPackageId)
				->where('requires_approval', true)
				->where('status', 'pending')
				->with('user') // Mengambil info pengunggah
				->latest() // Mengurutkan dari yang terbaru
				->get();
		}

		return view('approvals.index', [
			'pendingDocuments' => $pendingDocuments
		]);
	}

	public function storeReview(Request $request, Document $document)
	{
		$request->validate([
			'status' => 'required|in:Disetujui,Revisi Diperlukan,Ditolak',
			'notes' => 'nullable|string',
			'reviewed_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // Maksimal 10MB
		]);

		// 1. Simpan file yang di-review oleh MK
		$filePath = $request->file('reviewed_file')->store('document_reviews', 'public');

		// 2. Cari data pengajuan awal dari kontraktor
		$originalSubmission = $document->approvals()->whereNull('parent_id')->first();

		// 3. Buat entri baru di database untuk hasil review ini
		$review = DocumentApproval::create([
			'document_id' => $document->id,
			'parent_id' => $originalSubmission->id, // Menjadikannya "anak" dari pengajuan awal
			'user_id' => Auth::id(),
			'role' => 'mk', // Asumsi yang mereview adalah MK
			'status' => $request->status,
			'notes' => $request->notes,
			'reviewed_file_path' => $filePath,
		]);

		// 4. Update status dokumen utama jika sudah final (Disetujui atau Ditolak)
		if (in_array($request->status, ['Disetujui', 'Ditolak'])) {
			$document->update(['status' => strtolower($request->status)]);
		} else {
			// Jika butuh revisi, kembalikan status ke 'pending' agar kontraktor bisa upload lagi
			$document->update(['status' => 'pending']);
		}

		return back()->with('success', 'Hasil review berhasil disimpan.');
	}
}