<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'batik-wanita' => [
                ['name' => 'Batik Tulis Parang Klasik',       'price' => 250000, 'stock' => 24, 'img' => 'product-01.jpg', 'desc' => 'Batik tulis motif Parang, simbol keteguhan hati khas keraton Yogyakarta. Dibuat dengan teknik canting oleh pengrajin senior.', 'material' => 'Katun Primisima', 'weight' => '0,3 kg', 'colors' => ['Cokelat Sogan', 'Hitam'], 'sizes' => ['S','M','L','XL']],
                ['name' => 'Batik Cap Kawung Premium',        'price' => 350000, 'stock' => 15, 'img' => 'product-02.jpg', 'desc' => 'Batik cap motif Kawung dengan warna pekalongan cerah. Cocok untuk acara semi-formal maupun harian.', 'material' => 'Katun Dobby', 'weight' => '0,35 kg', 'colors' => ['Biru', 'Hijau'], 'sizes' => ['S','M','L','XL']],
                ['name' => 'Outer Batik Wanita Elegan',       'price' => 450000, 'stock' => 8,  'img' => 'product-04.jpg', 'desc' => 'Outer longgar dengan panel batik tulis di depan dan lengan. Dipadu dengan dress polos atau celana kerja.', 'material' => 'Katun Rayon', 'weight' => '0,5 kg', 'colors' => ['Hitam','Cokelat'], 'sizes' => ['S','M','L','XL']],
                ['name' => 'Blouse Batik Sogan',              'price' => 320000, 'stock' => 41, 'img' => 'product-05.jpg', 'desc' => 'Blouse motif Sogan klasik dengan pewarna alami tegeran dan soga. Nuansa hangat dan berkelas.', 'material' => 'Katun Primisima', 'weight' => '0,3 kg', 'colors' => ['Cokelat Sogan'], 'sizes' => ['S','M','L','XL']],
                ['name' => 'Dress Batik Cirebon',             'price' => 495000, 'stock' => 17, 'img' => 'product-07.jpg', 'desc' => 'Dress panjang motif pesisir Cirebon, detail lengan balon dan tali pinggang memperkuat siluet.', 'material' => 'Katun Rayon Premium', 'weight' => '0,55 kg', 'colors' => ['Biru','Merah'], 'sizes' => ['S','M','L','XL']],
                ['name' => 'Tunik Batik Lereng',              'price' => 285000, 'stock' => 22, 'img' => 'product-10.jpg', 'desc' => 'Tunik longgar motif Lereng khas Solo, nyaman dipakai untuk kerja maupun santai.', 'material' => 'Katun Rayon', 'weight' => '0,35 kg', 'colors' => ['Biru','Hijau'], 'sizes' => ['S','M','L','XL']],
                ['name' => 'Atasan Batik Remaja',             'price' => 245000, 'stock' => 9,  'img' => 'product-13.jpg', 'desc' => 'Atasan batik modern potongan crop dan bahan ringan, cocok untuk gaya anak muda.', 'material' => 'Katun Rayon', 'weight' => '0,25 kg', 'colors' => ['Pink','Hijau'], 'sizes' => ['S','M','L']],
                ['name' => 'Gamis Batik Modern',              'price' => 550000, 'stock' => 14, 'img' => 'product-14.jpg', 'desc' => 'Gamis panjang kombinasi batik di bawah dan polos di atas, dilengkapi tali pinggang.', 'material' => 'Katun Rayon Premium', 'weight' => '0,6 kg', 'colors' => ['Hitam','Navy'], 'sizes' => ['S','M','L','XL','XXL']],
                ['name' => 'Blus Batik Floral',               'price' => 275000, 'stock' => 26, 'img' => 'product-16.jpg', 'desc' => 'Blus batik motif floral dengan nuansa pesisir, ringan dan jatuh sempurna di badan.', 'material' => 'Katun Rayon', 'weight' => '0,3 kg', 'colors' => ['Merah','Pink'], 'sizes' => ['S','M','L','XL']],
                ['name' => 'Rok Batik Lilit Pekalongan',      'price' => 265000, 'stock' => 19, 'img' => 'product-01.jpg', 'desc' => 'Rok batik lilit model wrap dengan motif pesisir Pekalongan yang cerah dan ceria.', 'material' => 'Katun Primisima', 'weight' => '0,3 kg', 'colors' => ['Merah','Kuning'], 'sizes' => ['All Size']],
            ],

            'batik-pria' => [
                ['name' => 'Kemeja Batik Mega Mendung',       'price' => 275000, 'stock' => 32, 'img' => 'product-03.jpg', 'desc' => 'Kemeja pria motif Mega Mendung khas Cirebon dengan gradasi warna awan yang elegan. Regular fit.', 'material' => 'Katun Halus', 'weight' => '0,4 kg', 'colors' => ['Biru','Merah'], 'sizes' => ['M','L','XL','XXL']],
                ['name' => 'Kemeja Batik Sogan Jogja',        'price' => 395000, 'stock' => 30, 'img' => 'product-11.jpg', 'desc' => 'Kemeja pria motif Sogan klasik gaya Jogja. Slim fit, cocok untuk acara formal.', 'material' => 'Katun Primisima', 'weight' => '0,4 kg', 'colors' => ['Cokelat Sogan'], 'sizes' => ['M','L','XL','XXL']],
                ['name' => 'Kemeja Batik Truntum',            'price' => 385000, 'stock' => 28, 'img' => 'product-12.jpg', 'desc' => 'Motif Truntum melambangkan cinta yang tumbuh kembali. Pilihan untuk pernikahan dan lamaran.', 'material' => 'Katun Primisima', 'weight' => '0,4 kg', 'colors' => ['Hitam','Cokelat'], 'sizes' => ['M','L','XL','XXL']],
                ['name' => 'Kemeja Batik Sekar Jagad',        'price' => 420000, 'stock' => 18, 'img' => 'product-03.jpg', 'desc' => 'Motif Sekar Jagad mewakili keragaman. Lengan panjang dengan kerah klasik.', 'material' => 'Katun Primisima', 'weight' => '0,42 kg', 'colors' => ['Navy','Maroon'], 'sizes' => ['M','L','XL','XXL']],
                ['name' => 'Kemeja Batik Sidomukti',          'price' => 410000, 'stock' => 21, 'img' => 'product-11.jpg', 'desc' => 'Motif Sidomukti melambangkan kemakmuran. Bahan adem, cocok untuk iklim tropis.', 'material' => 'Katun Primisima', 'weight' => '0,4 kg', 'colors' => ['Cokelat Sogan','Hitam'], 'sizes' => ['M','L','XL']],
                ['name' => 'Kemeja Lengan Pendek Casual',     'price' => 245000, 'stock' => 35, 'img' => 'product-12.jpg', 'desc' => 'Kemeja batik lengan pendek untuk tampilan santai namun tetap rapi.', 'material' => 'Katun Dobby', 'weight' => '0,3 kg', 'colors' => ['Biru','Hijau','Cokelat'], 'sizes' => ['M','L','XL','XXL']],
                ['name' => 'Jaket Bomber Motif Batik',        'price' => 565000, 'stock' => 12, 'img' => 'product-03.jpg', 'desc' => 'Jaket bomber modern dengan panel batik pada bagian dada dan lengan. Streetwear nusantara.', 'material' => 'Katun + Poliester', 'weight' => '0,7 kg', 'colors' => ['Hitam','Navy'], 'sizes' => ['M','L','XL']],
                ['name' => 'Setelan Batik Safari',            'price' => 675000, 'stock' => 10, 'img' => 'product-11.jpg', 'desc' => 'Setelan kemeja safari batik lengkap dengan celana kain. Untuk tampilan resmi modern.', 'material' => 'Katun Twill', 'weight' => '0,9 kg', 'colors' => ['Cokelat','Hitam'], 'sizes' => ['M','L','XL','XXL']],
                ['name' => 'Kemeja Batik Anak Muda',          'price' => 225000, 'stock' => 40, 'img' => 'product-12.jpg', 'desc' => 'Kemeja batik potongan slim modern dengan motif kontemporer untuk remaja pria.', 'material' => 'Katun Rayon', 'weight' => '0,35 kg', 'colors' => ['Biru','Hijau','Abu-abu'], 'sizes' => ['S','M','L']],
                ['name' => 'Kemeja Batik Lengan Panjang Formal','price' => 450000, 'stock' => 16, 'img' => 'product-03.jpg', 'desc' => 'Kemeja batik lengan panjang untuk acara kantor dan formal. Potongan regular fit.', 'material' => 'Katun Primisima', 'weight' => '0,45 kg', 'colors' => ['Hitam','Navy','Maroon'], 'sizes' => ['M','L','XL','XXL']],
            ],

            'aksesoris' => [
                ['name' => 'Scarf Batik Sutra Lukis',         'price' => 180000, 'stock' => 5,  'img' => 'product-06.jpg', 'desc' => 'Scarf batik lukis dari kain sutra asli dengan sentuhan lembut di kulit. Ukuran 50 x 150 cm.', 'material' => 'Sutra ATBM', 'weight' => '0,15 kg', 'colors' => ['Ungu','Merah','Biru'], 'sizes' => ['All Size']],
                ['name' => 'Selendang Batik Pesisir',         'price' => 195000, 'stock' => 18, 'img' => 'product-08.jpg', 'desc' => 'Selendang batik pesisiran warna cerah, cocok untuk padanan kebaya atau gaun malam.', 'material' => 'Katun Tipis', 'weight' => '0,2 kg', 'colors' => ['Merah','Kuning','Pink'], 'sizes' => ['All Size']],
                ['name' => 'Sepatu Motif Batik',              'price' => 425000, 'stock' => 14, 'img' => 'product-09.jpg', 'desc' => 'Sneakers dengan aksen batik pada upper. Nyaman dipakai harian.', 'material' => 'Canvas + Kulit Sintetis', 'weight' => '0,9 kg', 'colors' => ['Hitam','Putih'], 'sizes' => ['38','39','40','41','42','43']],
                ['name' => 'Tas Batik Handmade',              'price' => 325000, 'stock' => 20, 'img' => 'product-15.jpg', 'desc' => 'Tas jinjing dengan panel batik tulis di bagian luar dan lapisan kulit sintetis tahan air.', 'material' => 'Katun + Kulit Sintetis', 'weight' => '0,7 kg', 'colors' => ['Cokelat','Hitam'], 'sizes' => ['30 x 25 x 10 cm']],
                ['name' => 'Dompet Wanita Motif Batik',       'price' => 165000, 'stock' => 32, 'img' => 'product-15.jpg', 'desc' => 'Dompet panjang wanita dengan motif batik dan slot kartu lengkap.', 'material' => 'Kulit Sintetis + Katun', 'weight' => '0,2 kg', 'colors' => ['Merah','Hitam','Cokelat'], 'sizes' => ['All Size']],
                ['name' => 'Ikat Kepala Udeng Batik',         'price' => 85000,  'stock' => 50, 'img' => 'product-06.jpg', 'desc' => 'Udeng khas Jawa dengan motif batik klasik, siap pakai untuk acara adat.', 'material' => 'Katun Primisima', 'weight' => '0,1 kg', 'colors' => ['Cokelat Sogan','Hitam'], 'sizes' => ['All Size']],
                ['name' => 'Sarung Batik Tulis Halus',        'price' => 550000, 'stock' => 8,  'img' => 'product-06.jpg', 'desc' => 'Sarung batik tulis halus untuk ibadah dan acara resmi. Motif Truntum.', 'material' => 'Katun Primisima', 'weight' => '0,5 kg', 'colors' => ['Hijau','Biru','Cokelat'], 'sizes' => ['All Size']],
                ['name' => 'Tas Selempang Motif Parang',      'price' => 295000, 'stock' => 15, 'img' => 'product-15.jpg', 'desc' => 'Tas selempang dengan panel batik Parang, cocok untuk aktivitas harian.', 'material' => 'Katun + Kulit Sintetis', 'weight' => '0,5 kg', 'colors' => ['Cokelat','Hitam'], 'sizes' => ['25 x 20 x 8 cm']],
                ['name' => 'Bros Batik Kuningan',             'price' => 75000,  'stock' => 60, 'img' => 'product-08.jpg', 'desc' => 'Bros kuningan ukir dengan sisipan kain batik. Pelengkap kebaya atau scarf.', 'material' => 'Kuningan + Katun', 'weight' => '0,05 kg', 'colors' => ['Emas','Perak'], 'sizes' => ['All Size']],
                ['name' => 'Pashmina Batik Premium',          'price' => 220000, 'stock' => 25, 'img' => 'product-08.jpg', 'desc' => 'Pashmina dengan motif batik kontemporer, pilihan tepat untuk gaya hijab modern.', 'material' => 'Katun Sutra', 'weight' => '0,2 kg', 'colors' => ['Hitam','Navy','Maroon','Hijau'], 'sizes' => ['175 x 75 cm']],
            ],
        ];

        $sku = 1;
        foreach ($data as $categorySlug => $products) {
            $category = Category::where('slug', $categorySlug)->first();
            if (! $category) {
                continue;
            }

            foreach ($products as $p) {
                $skuCode = 'BP-' . str_pad((string) $sku, 3, '0', STR_PAD_LEFT);
                $slug = Str::slug($p['name']);

                if (Product::where('slug', $slug)->where('sku', '!=', $skuCode)->exists()) {
                    $slug .= '-' . strtolower($skuCode);
                }

                Product::updateOrCreate(
                    ['sku' => $skuCode],
                    [
                        'category_id' => $category->id,
                        'name'        => $p['name'],
                        'slug'        => $slug,
                        'description' => $p['desc'],
                        'price'       => $p['price'],
                        'stock'       => $p['stock'],
                        'stock_min'   => 10,
                        'image'       => $p['img'],
                        'weight'      => $p['weight'],
                        'material'    => $p['material'],
                        'colors'      => $p['colors'],
                        'sizes'       => $p['sizes'],
                        'status'      => $p['stock'] <= 0 ? 'habis' : 'aktif',
                    ]
                );
                $sku++;
            }
        }
    }
}
