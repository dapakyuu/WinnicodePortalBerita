<?php

/**
 * Profil perusahaan pengelola portal. Dipakai di footer dan halaman About Us.
 * Ubah teks di sini (tidak perlu mengedit view).
 */
return [
    'name' => 'Winnicode Garuda Teknologi',
    'email' => 'Admin@Winnicode.com',
    'phone' => '+6285159932501',
    'field' => 'Jurnalistik/Berita',
    'address' => 'Jl. Asia Afrika No.158, Kb. Pisang, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40261',
    'legal' => 'AHU-032658.AH.01.30.Tahun 2023',
    'map_query' => 'Jl. Asia Afrika No.158, Bandung',
    'instagram' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/'),

    // Ringkasan singkat untuk footer
    'summary' => 'PT Winnicode Garuda Teknologi adalah perusahaan media publikasi dan layanan digital yang berdiri di Bandung sejak 2020. '
        . 'Melalui portal berita ini, Winnicode menyajikan informasi terkini seputar olahraga, teknologi, budaya, gaya hidup, dan berbagai topik lainnya '
        . 'yang ditulis oleh tim redaksi dan para kontributor. Kami percaya informasi yang akurat dan mudah diakses adalah bagian penting dari transformasi digital di Indonesia.',

    // Profil lengkap untuk halaman About Us (satu elemen = satu paragraf)
    'profile' => [
        'PT Winnicode Garuda Teknologi didirikan pada tahun 2020 di Bandung oleh sekelompok profesional muda dengan latar belakang kuat di bidang teknologi informasi dan komunikasi. '
            . 'Berkantor pusat di Bandung, Winnicode juga memiliki cabang di kota besar lain seperti Yogyakarta dan Jakarta.',
        'Winnicode bergerak di bidang media publikasi dan layanan digital. Kami membangun dan mengelola platform digital, mulai dari portal berita, website perusahaan, hingga media sosial, '
            . 'serta memberikan layanan konsultasi teknologi informasi bagi berbagai institusi sebagai mitra transformasi digital.',
        'Portal Berita Winnicode hadir untuk menyajikan berita yang cepat, akurat, dan mudah diakses. Selain tulisan tim redaksi, pembaca yang terdaftar dapat mengirimkan berita dan video, '
            . 'memberi komentar dan rating, serta menyimpan berita favorit. Visi kami adalah menjadi pemimpin di industri media publikasi dan layanan digital di Indonesia.',
    ],
];
