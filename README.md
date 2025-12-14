# command yang sering digunakan untuk membuat api

-   php artisan install:api
-   php artisan make:Model nama_model -m -c --api
-   composer require laravel/breeze --dev (download package breeze)
-   php artisan breeze:install api

# folder penting yang harus diperhatikan!!

-   route/api.php
-   controller/http/controllers/Auth/AuthenticatedSessionController.php
-   controller/http/controllers/Auth/RegisteredUserController.php
-   controller/http/controllers/ProductController

# debugging tanggal 09-12-2025

-   api mengembalikan erorr status 419:page expired saat di postman. hal ini terjadi karena laravel mengira request dari browser.
    Ini disebabkan karena pada postman di bagian header tidak ditambahakan "Key : Accept dan value : application/json"
-   pada AuthenticatedSessionController terjadi erorr return value of store. Hal ini disebabkan karena response typenya adalah
    "Response" yang mana seharusnya adalah "JsonResponse".

# debugging tanggal 09-12-2025

-   ErrorException Undefined array key 0
-   vendor\laravel\framework\src\Illuminate\Support\Facades\Facade.php:363, Illuminate\Routing\Router::\_\_call("middleware", ["role:pelanggan"], "role:pelanggan")
-   Kode Pemicu -> Route::middleware(middleware: ['role:pelanggan'])->group(function() {});
    Solusi -> Route::middleware(['role:pelanggan'])->group(function() {});

# Integrasi layanan midtrans

-   route /transaction/{product} akan di panggil dan akan mengkases ProductController.php dengan method transaction
-   pada dir ProductController.php dengan method transaction akan memanggil helper Transaction.php yang akan mengembalikan response berupa link yang digunakan untuk melakukan pembayaran
-   pada dir app/helper/MidtransTransaction.php akan melakukan post request ke midtrans.

# note:

-   untuk file auth.php untuk semua middleware yang hanya menggunakan "auth" ganti menjadi "auth:sanctum"
-   Hindari menggunakan Named Arguments (contoh: nama_param: value) pada method Laravel
-   untuk mengambil status pembayaran dari midtrans dibutuhkan yang namanya webhooks. untuk endpoint webhooks adalah POST api/notification yang harus di daftarkan di dashboard midtrans. endpoint webhooks tersebut wajib online terlebih dahulu menggunakna vps atau menggunakan ngrox untuk testing

<!-- Semua catatan diatas bisa dijadikan pedoman untuk project selanjutnya -->
