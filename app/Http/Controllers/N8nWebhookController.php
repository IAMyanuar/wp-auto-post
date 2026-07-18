<?php

namespace App\Http\Controllers;

use App\Jobs\ProsesDanKirimKeWordPressJob;
use App\Models\Artikel;
use App\Services\ArtikelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class N8nWebhookController extends Controller
{
    public function __construct(protected ArtikelService $artikelService)
    {
    }

    public function receiveJudulResult(Request $request)
    {
        $validated = validator($request->all(), [
            'website_klien_id' => 'required|integer|exists:website_klien,id',
            'perintah_artikel_id' => 'required|integer|exists:perintah_artikel,id',
            'judul' => 'required|array|min:1',
            'judul.*' => 'required|string|max:500',
            'tanggal_jadwal' => 'nullable|date',
        ], [
            'website_klien_id.required' => 'ID website klien wajib disertakan.',
            'perintah_artikel_id.required' => 'ID perintah artikel wajib disertakan.',
            'website_klien_id.exists' => 'Website klien tidak ditemukan.',
            'perintah_artikel_id.exists' => 'Perintah artikel tidak ditemukan.',
            'judul.required' => 'Daftar judul wajib disertakan.',
            'judul.array' => 'Daftar judul harus berupa array.',
            'judul.min' => 'Minimal satu judul harus disertakan.',
        ])->validate();

        Log::info('Menerima daftar judul artikel dari n8n', [
            'website_klien_id' => $validated['website_klien_id'],
            'jumlah_judul' => count($validated['judul']),
        ]);

        $result = $this->artikelService->simpanJudulDariN8n(
            juduls: $validated['judul'],
            perintahArtikelId: (int) $validated['perintah_artikel_id'],
            websiteKlienId: (int) $validated['website_klien_id'],
            tanggalJadwal: $validated['tanggal_jadwal'] ?? null,
        );

        if (!$result['success']) {
            Log::warning('Tidak ada judul yang berhasil disimpan dari n8n', [
                'website_klien_id' => $validated['website_klien_id'],
            ]);

            return response()->json([
                'message' => 'Tidak ada judul yang berhasil disimpan.',
                'count' => 0,
                'ids' => [],
            ], 422);
        }

        return response()->json([
            'message' => 'Judul artikel berhasil disimpan.',
            'count' => $result['count'],
            'artikel_ids' => $result['ids'],
        ], 200);
    }

    public function receiveKonten(Request $request)
    {
        $data = $request->merge([
            'artikel_id' => $request->input('artikel_id'),
            'konten' => $request->input('konten'),
            'judul' => $request->input('judul'),
            'slug' => $request->input('slug'),
            'meta_deskripsi' => $request->input('meta_deskripsi'),
            'kata_kunci' => $request->input('kata_kunci'),
            'tags' => $request->input('tags'),
            'kategori' => $request->input('kategori'),
        ])->all();

        $validated = validator($data, [
            'artikel_id' => 'required|integer|exists:artikel,id',
            'konten' => 'required|string',
            'judul' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'meta_deskripsi' => 'nullable|string',
            'kata_kunci' => 'nullable|string',
            'tags' => 'nullable|string',
            'kategori' => 'nullable|string',
        ], [
            'artikel_id.required' => 'Kolom ID artikel wajib diisi.',
            'artikel_id.exists' => 'Artikel dengan ID tersebut tidak ditemukan.',
            'konten.required' => 'Konten artikel wajib diisi.',
        ])->validate();

        $artikel = Artikel::with('websiteKlien')->findOrFail($validated['artikel_id']);
        $websiteKlien = $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : 'Tidak ada';

        Log::info("Menerima data dari n8n (Konten Artikel)", [
            'artikel_id' => $artikel->id,
            'website' => $websiteKlien,
        ]);

        // ── Simpan konten dan metadata ke DB ─────────────────────────────────────
        $artikel->update([
            'status' => 'diproses',
            'konten' => $validated['konten'],
            'judul' => $validated['judul'] ?? $artikel->judul,
            'slug' => $validated['slug'],
            'meta_deskripsi' => $validated['meta_deskripsi'],
            'kata_kunci' => $validated['kata_kunci'],
            'tags' => $validated['tags'],
            'kategori' => $validated['kategori'],
        ]);

        // ── Dispatch Job background: cek plagiasi + upload gambar + kirim WP + broadcast ──
        ProsesDanKirimKeWordPressJob::dispatch($artikel->id);

        Log::info("ProsesDanKirimKeWordPressJob di-dispatch untuk Artikel ID {$artikel->id}.");

        return response()->json([
            'message' => 'Konten diterima dan sedang diproses di background.',
            'artikel_id' => $artikel->id,
        ], 200);
    }

    /**
     * Alias untuk receiveKonten — dipertahankan agar kompatibel dengan route lama.
     */
    public function receiveKontenResult(Request $request)
    {
        return $this->receiveKonten($request);
    }
}