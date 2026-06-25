<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ---- Tentang ----
            'about_title'    => 'Tentang Batik Penawuo',
            'about_subtitle' => 'Warisan Budaya, Karya Tangan',
            'about_story'    => 'Batik Penawuo hadir sebagai tempat belanja batik berkualitas tinggi yang memadukan motif tradisional dengan sentuhan modern. Setiap kain yang kami hadirkan adalah hasil karya pengrajin lokal berpengalaman yang menjaga keaslian teknik membatik turun-temurun.',
            'about_mission'  => 'Melestarikan seni batik nusantara sekaligus memberdayakan pengrajin lokal agar karya mereka dikenal luas.',
            'about_quote'    => 'Setiap helai kain batik menyimpan cerita — tentang tanah, tentang manusia, tentang waktu.',

            // ---- Kontak & Toko ----
            'store_name'           => 'Batik Penawuo',
            'contact_email'        => 'halo@batikpenawuo.id',
            'contact_phone'        => '(+62) 812-3456-7890',
            'contact_address'      => 'Jl. Malioboro No. 123, Kerinci 55213, Indonesia',
            'contact_hours'        => 'Senin – Sabtu, 08.00 – 17.00 WIB',
            'contact_maps_embed'   => '',

            // ---- Sosial media ----
            'social_facebook'  => '',
            'social_instagram' => '',
            'social_tiktok'    => '',
            'social_youtube'   => '',

            // ---- Footer ----
            'footer_topbar_promo' => 'Gratis ongkir untuk pembelian di atas Rp 300.000!',
            'footer_col1_title'   => 'Batik Penawuo',
            'footer_col1_links'   => json_encode([
                ['label' => 'Tentang Kami',  'url' => '/tentang'],
                ['label' => 'Hubungi Kami',  'url' => '/kontak'],
                ['label' => 'Katalog Produk','url' => '/produk'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'footer_col2_title'   => 'Layanan',
            'footer_col2_links'   => json_encode([
                ['label' => 'Cara Belanja',      'url' => '/kontak'],
                ['label' => 'Info Pengiriman',   'url' => '/kontak'],
                ['label' => 'Kebijakan Pengembalian', 'url' => '/kontak'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'footer_col3_title'   => 'Hubungi Kami',
            'footer_col3_text'    => 'Pertanyaan tentang produk atau pesanan? Kami siap membantu Anda.',
            'footer_show_payments'=> '1',
            'footer_payment_icons'=> '[]',
            'footer_copyright'    => '© ' . date('Y') . ' Batik Penawuo. Hak cipta dilindungi.',
        ];

        SiteSetting::setMany($defaults);
    }
}
