<?php

namespace App\Http\Controllers;

use App\Models\WebsiteKlien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebClientController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $search = $request->get('search');

        $query = WebsiteKlien::latest();

        if ($search) {
            $query->where('nama_website', 'like', "%{$search}%")
                ->orWhere('url_website', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
        }

        $clients = $query->paginate($limit)->withQueryString();

        return view('pages.web-client.index', compact('clients', 'limit', 'search'));
    }

    public function create()
    {
        return view('pages.web-client.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_website' => 'required|string|max:255',
            'url_website' => 'required|url|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'no_telpon' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ]);

        $validatedData['publikasi_otomatis'] = $request->has('publikasi_otomatis');

        // Cek koneksi ke WordPress REST API
        $isConnected = $this->checkWordPressConnection(
            $validatedData['url_website'],
            $validatedData['username'],
            $validatedData['password']
        );

        if (!$isConnected) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi gagal! Pastikan URL, Username, dan Application Password WordPress sudah benar.'
                ], 422);
            }
            return back()->withInput()->with('error', 'Koneksi gagal! Pastikan URL, Username, dan Application Password WordPress sudah benar.');
        }

        WebsiteKlien::create($validatedData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Website klien berhasil ditambahkan dan terhubung!'
            ]);
        }

        return redirect()->route('web-client.index')->with('success', 'Website klien berhasil ditambahkan dan terhubung!');
    }

    public function edit(WebsiteKlien $web_client)
    {
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json($web_client);
        }
        return view('pages.web-client.edit', compact('web_client'));
    }

    public function update(Request $request, WebsiteKlien $web_client)
    {
        $validatedData = $request->validate([
            'nama_website' => 'required|string|max:255',
            'url_website' => 'required|url|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
            'no_telpon' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ]);

        $passwordToCheck = $request->filled('password') ? $validatedData['password'] : $web_client->password;

        if (!$request->filled('password')) {
            unset($validatedData['password']);
        }

        $validatedData['publikasi_otomatis'] = $request->has('publikasi_otomatis') || $request->input('publikasi_otomatis') == 1;

        // Cek koneksi ke WordPress REST API
        $isConnected = $this->checkWordPressConnection(
            $validatedData['url_website'],
            $validatedData['username'],
            $passwordToCheck
        );

        if (!$isConnected) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi gagal! Pastikan URL, Username, dan Application Password WordPress sudah benar.'
                ], 422);
            }
            return back()->withInput()->with('error', 'Koneksi gagal! Pastikan URL, Username, dan Application Password WordPress sudah benar.');
        }

        $web_client->update($validatedData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data website klien berhasil diperbarui dan terhubung!'
            ]);
        }

        return redirect()->route('web-client.index')->with('success', 'Data website klien berhasil diperbarui dan terhubung!');
    }

    public function destroy(WebsiteKlien $web_client)
    {
        $web_client->delete();

        return redirect()->route('web-client.index')->with('success', 'Website klien berhasil dihapus!');
    }

    private function checkWordPressConnection($url, $username, $password)
    {
        $baseUrl = \App\Models\WebsiteKlien::extractBaseUrl($url);
        try {
            $response = Http::withBasicAuth($username, $password)
                ->timeout(10)
                ->get($baseUrl . '/wp-json/wp/v2/users/me');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
