1. buat kode dengan arsitek clean, DDD, dan sesuai standar laravel, dan PSR-4
2. mencipatakan package sesuai slogan "lite modular", itu package ringan, efisien, efektif dan tidak terlalu banyak load yang dapat memberatkan proses booting

3. struktur untuk permodul akan menciptakan 1 entity berisi :
    - http berisi controller, dan request
    - models
    - services
    - providers
    - routes
    - migrations
    - views
    - livewire (jika ada)
4. ketentuan :

    - jika pakai flag --api, cek apakah package sudah terinstal (sanctum/passport/jwt) jika belum skip, jika sudah file controller api masuk di folder api pada http
    - untuk folder view livewire, masuknya view kemudian livewire
    - untuk route hanya indexsaya dengan name modulname.index
    - untuk service itu kosong hanya konten panduan
    - untuk controller menggunakan custructor ke service entity function index kemudian mengarah ke view index entity tersebut (view->modulname/entityname->index)
    - untuk view hanya 1 file yaitu index, dengan konten minimal (view module ... )
    - untuk model mungkin protected table dengan nama table sesuai entiti migration
    - untuk migration menggunakan penamaan standar migration
    - untuk provider hanya ada 1 provider sesuai nama modul, meskipun nanti 1 modul bisa banyak entity
    - provider hanya untuk mendaftarkan service/bindings spesifik modul, bukan untuk load routes/views/migrations
    - penamaan route (web atau api) mengguunakan pendekatan - (misal web-blog.php, api-blog.php dll)
    - pemeberian middleawara (baik web atau api) di buat pada route service buakn di route

5. Entity :

    - entity merupakan penambahan file di modul yang sudah ada, benambahan berupa controller, request, model, migration, view dan livewire (jika ada)
    - jika modul sudah menggunaakn --api, berarti entity mengikuti, jika tidak maka skip

6. Command :

    - mod:make -> pembauatan modul
    - mod:make-entity -> pembuatan entity
    - mod:delete-module -> delete modul
    - mod:delete-entity -> delete entity dari modul
    - mod:cache -> cache informasi modul (daftar modul, view namespace, route path)

7. Penulisan harap dibuat konsisten, sesuai standar laravel

8. Implementasi saat ini :

    - Command mod:make sudah diimplementasikan
    - Command mod:make-entity sudah diimplementasikan
    - Command mod:delete-module sudah diimplementasikan
    - Command mod:delete-entity sudah diimplementasikan
    - Command mod:cache sudah diimplementasikan (cache modul list, view namespace, route path)
    - Route hanya index dengan nama modulname.index
    - Controller hanya memiliki function index yang mengarah ke view
    - Model memiliki property $table sesuai entity
    - Service hanya berisi komentar panduan
    - View hanya 1 file index dengan path modulename/entityname/index
    - Middleware diterapkan di service provider
    - Struktur folder sesuai aturan
