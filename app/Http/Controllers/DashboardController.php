<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\WebsiteKlien;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalArtikel    = Artikel::count();
        $terjadwal       = Artikel::where('status', 'terjadwal')->count();
        $terpublish      = Artikel::where('status', 'terpublish')->count();
        $gagal           = Artikel::where('status', 'gagal')->count();
        $totalWebsite    = WebsiteKlien::count();
        $avgSeo          = Artikel::whereNotNull('skor_seo')->avg('skor_seo');

        // Artikel terbaru (5 terakhir)
        $artikelTerbaru = Artikel::with('websiteKlien')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Data kalender: artikel dengan tanggal jadwal
        $calendarEvents = Artikel::whereNotNull('tanggal_jadwal')
            ->with('websiteKlien')
            ->select('id', 'judul', 'tanggal_jadwal', 'status', 'wp_url', 'website_klien_id')
            ->orderBy('tanggal_jadwal')
            ->get()
            ->map(function ($a) {
                $colorMap = [
                    'terjadwal'   => ['#3b82f6', '#bfdbfe'],
                    'terpublish'  => ['#10b981', '#d1fae5'],
                    'gagal'       => ['#ef4444', '#fee2e2'],
                    'diproses'    => ['#f59e0b', '#fef3c7'],
                ];
                [$bg, $border] = $colorMap[$a->status] ?? ['#6b7280', '#e5e7eb'];

                return [
                    'id'              => $a->id,
                    'title'           => $a->judul,
                    'start'           => $a->tanggal_jadwal->toIso8601String(),
                    'backgroundColor' => $bg,
                    'borderColor'     => $bg,
                    'textColor'       => '#ffffff',
                    'extendedProps'   => [
                        'status'       => $a->status,
                        'url'          => $a->wp_url,
                        'editUrl'      => route('penjadwalan.edit', $a->id),
                        'website'      => $a->websiteKlien->nama_website ?? '-',
                        'jamPublish'   => $a->tanggal_jadwal->format('H:i'),
                        'tanggalLengkap' => $a->tanggal_jadwal->translatedFormat('d M Y, H:i'),
                    ],
                ];
            });

        return view('pages.dashboard.index', compact(
            'totalArtikel', 'terjadwal', 'terpublish', 'gagal',
            'totalWebsite', 'avgSeo', 'artikelTerbaru', 'calendarEvents'
        ));
    }
}
