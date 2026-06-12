<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    private const BANNER_IMAGE_MESSAGES = [
        'image.uploaded' => 'Upload gambar gagal. File mungkin lebih besar dari batas server (cek upload_max_filesize di php.ini).',
        'image.max'      => 'Ukuran gambar tidak boleh lebih dari 3 MB.',
        'image.image'    => 'File harus berupa gambar (JPG, PNG, atau WebP).',
        'image.mimes'    => 'Format gambar harus JPG, PNG, atau WebP.',
    ];

    public function index()
    {
        return view('admin.cms', [
            'banners' => Banner::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    // ---- CMS: Site Settings (Tentang / Kontak / Footer) ----
    public function saveSettings(Request $request, string $group)
    {
        $allowedKeys = [
            'tentang' => [
                'about_title', 'about_subtitle', 'about_story', 'about_mission', 'about_quote',
            ],
            'kontak'  => [
                'store_name', 'contact_email', 'contact_phone', 'contact_address',
                'contact_hours', 'contact_maps_embed',
                'social_facebook', 'social_instagram', 'social_tiktok', 'social_youtube',
            ],
            // Alamat toko terstruktur — dipakai sebagai titik asal RajaOngkir.
            // Tarif ongkir TIDAK lagi diatur di sini (sebelumnya zona-based);
            // sekarang dihitung otomatis lewat RajaOngkirService.
            'pengiriman' => [
                'store_province_id', 'store_province_name',
                'store_city_id', 'store_city_name',
                'store_district_id', 'store_district_name',
                'store_full_address',
            ],
            'footer'  => [
                // Topbar
                'footer_topbar_promo',
                // 3 kolom (judul + isi)
                'footer_col1_title', 'footer_col1_links',
                'footer_col2_title', 'footer_col2_links',
                'footer_col3_title', 'footer_col3_text',
                // Ikon pembayaran
                'footer_show_payments', 'footer_payment_icons',
                // Hak cipta
                'footer_copyright',
            ],
        ];
        if (! isset($allowedKeys[$group])) {
            abort(404);
        }

        // Field array di group footer — disimpan sebagai JSON string di site_settings.
        $arrayKeys = [
            'footer_col1_links'    => ['label', 'url'],
            'footer_col2_links'    => ['label', 'url'],
            'footer_payment_icons' => ['image_url', 'alt'],
        ];
        // Field checkbox di group footer — defaultkan ke '0' kalau tidak dikirim.
        $checkboxKeys = ['footer_show_payments'];

        $pairs = [];
        foreach ($allowedKeys[$group] as $key) {
            if (isset($arrayKeys[$key])) {
                $rows  = (array) $request->input($key, []);
                $clean = [];
                foreach ($rows as $row) {
                    if (! is_array($row)) continue;
                    $entry = [];
                    foreach ($arrayKeys[$key] as $sub) {
                        $entry[$sub] = trim((string) ($row[$sub] ?? ''));
                    }
                    // Buang baris kosong total (semua sub-field kosong).
                    if (count(array_filter($entry, fn ($v) => $v !== '')) > 0) {
                        $clean[] = $entry;
                    }
                }
                $pairs[$key] = json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                continue;
            }
            if (in_array($key, $checkboxKeys, true)) {
                $pairs[$key] = $request->input($key) ? '1' : '0';
                continue;
            }
            $pairs[$key] = $request->input($key);
        }
        SiteSetting::setMany($pairs);

        return redirect()->route('admin.cms', ['#tab-' . $group])
            ->with('status', 'Pengaturan ' . ucfirst($group) . ' berhasil disimpan.');
    }

    // ---- CMS: Banner CRUD ----
    private function moveBannerImage(Request $request, string $field = 'image'): ?string
    {
        $file = $request->file($field);
        if (! $file) {
            return null;
        }
        $destination = public_path('uploads/banners');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($destination, $filename);
        return 'uploads/banners/' . $filename;
    }

    public function storeBanner(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:120',
            'subtitle'   => 'nullable|string|max:160',
            'image'            => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
            'image_max_height' => 'nullable|integer|min:120|max:1200',
            'link'             => 'nullable|string|max:200',
            'cta_text'         => 'nullable|string|max:60',
            'sort_order'       => 'nullable|integer|min:0|max:9999',
            'is_active'        => 'nullable|in:0,1',
        ], self::BANNER_IMAGE_MESSAGES);

        $data['image']      = $this->moveBannerImage($request);
        $data['cta_text']   = $data['cta_text']   ?? 'Belanja Sekarang';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = (bool) ($data['is_active'] ?? 0);

        Banner::create($data);
        return redirect()->route('admin.cms', ['#tab-banner'])->with('status', 'Banner ditambahkan.');
    }

    public function updateBanner(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:120',
            'subtitle'         => 'nullable|string|max:160',
            'image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'image_max_height' => 'nullable|integer|min:120|max:1200',
            'link'             => 'nullable|string|max:200',
            'cta_text'         => 'nullable|string|max:60',
            'sort_order'       => 'nullable|integer|min:0|max:9999',
            'is_active'        => 'nullable|in:0,1',
        ], self::BANNER_IMAGE_MESSAGES);

        if ($request->hasFile('image')) {
            // hapus file lama
            if ($banner->image && str_starts_with($banner->image, 'uploads/')) {
                @unlink(public_path($banner->image));
            }
            $data['image'] = $this->moveBannerImage($request);
        } else {
            unset($data['image']);
        }
        $data['cta_text']   = $data['cta_text']   ?? 'Belanja Sekarang';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = (bool) ($data['is_active'] ?? 0);

        $banner->update($data);
        return redirect()->route('admin.cms', ['#tab-banner'])->with('status', 'Banner diperbarui.');
    }

    public function destroyBanner(Banner $banner)
    {
        if ($banner->image && str_starts_with($banner->image, 'uploads/')) {
            @unlink(public_path($banner->image));
        }
        $banner->delete();
        return redirect()->route('admin.cms', ['#tab-banner'])->with('status', 'Banner dihapus.');
    }

}
