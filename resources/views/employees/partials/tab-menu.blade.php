{{--
    File: resources/views/partials/tab-menu.blade.php
    Deskripsi:
    Versi "Clean Code" dari menu navigasi tab.
    - Menggunakan Flexbox untuk tata letak yang dinamis dan mudah dipelihara.
    - Menggunakan helper `request()->routeIs()` untuk menandai tab aktif.
    - Menggunakan `Route::has()` untuk secara dinamis membuat link hanya untuk route yang sudah ada.
    - Membutuhkan variabel `$employee` untuk membuat URL route yang benar saat mode edit.
    - Semua 10 tab didefinisikan dalam satu array untuk kemudahan pengelolaan.
--}}

@push('styles')
<style>
    /*
     * Menggunakan pendekatan BEM-like (Block, Element, Modifier) untuk CSS yang lebih terstruktur.
     * .tabs-nav (Block)
     * .tabs-nav__item (Element)
     * .tabs-nav__item--active (Modifier)
    */

    .tabs-nav {
        display: flex; /* Menggunakan Flexbox untuk alignment otomatis */
        width: 100%; /* Lebar penuh, contoh: 1175px */
        max-width: 1175px;
        height: 50px;
        background: #F7F7DA;
        border-bottom: 1px solid rgba(0, 0, 0, 0.20);
        margin: auto;
    }

    .tabs-nav__item {
        /*
         * `flex: 1;` adalah singkatan dari `flex-grow: 1; flex-shrink: 1; flex-basis: 0%;`
         * Ini membuat semua tab memiliki lebar yang sama dan mengisi ruang yang tersedia.
         * Ini jauh lebih baik daripada hardcoding `width: 119px;` dan `left: ...px;`.
        */
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        text-decoration: none;
        height: 100%;
        padding: 5px;
        border-right: 1px solid rgba(0, 0, 0, 0.20);
        box-sizing: border-box; /* Pastikan padding dan border termasuk dalam total lebar/tinggi */
        transition: background-color 0.2s ease-in-out;
        cursor: pointer;
    }

    .tabs-nav__item:last-child {
        border-right: none; /* Hapus border di item terakhir */
    }

    .tabs-nav__item-text {
        color: black;
        font-size: 12px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 400;
        line-height: 1.3;
    }

    /* --- MODIFIERS --- */

    /* Modifier untuk tab yang sedang aktif */
    .tabs-nav__item--active {
        background: #D8E6AD;
        font-weight: 600; /* Font menjadi tebal saat aktif */
    }

    /* Modifier untuk tab yang bisa di-hover (hanya untuk tag <a>) */
    a.tabs-nav__item:hover {
        background: #c9d893; /* Warna hover yang sedikit lebih gelap */
    }

    /* Modifier untuk tab yang belum memiliki route (tidak aktif) */
    .tabs-nav__item--inactive {
        cursor: not-allowed;
        background-color: #f7f7da !important; /* Paksa warna background agar tidak berubah saat hover */
        filter: grayscale(80%);
    }

    .tabs-nav__item--inactive .tabs-nav__item-text {
        color: #777;
    }
</style>
@endpush

@php
    // Variabel $employee dibutuhkan untuk route edit.
    $employeeId = $employee->id ?? null;

    // Definisikan semua tab dengan nama route yang diharapkan.
    // Ini akan menjadi acuan Anda untuk membuat route dan controller di masa depan.
    $allTabs = [
        'employees.edit'              => 'Personal',
        'employees.contact.edit'      => 'Contact',
        'employees.address.edit'      => 'Address',
        'employees.family.edit'       => 'Family &<br/>Dependent',
        'employees.education.edit'    => 'Education',
        'employees.training.edit'     => 'Training Record',
        'employees.health.edit'       => 'Health History',
        'employees.certification.edit'=> 'Certification',
        'employees.assurance.edit'    => 'Assurance',
        'employees.experience.edit'   => 'Work<br/>Experience',
    ];
@endphp

<nav class="tabs-nav">
    @foreach($allTabs as $route => $label)
        @php
            // Menentukan class modifier berdasarkan kondisi
            $isRouteActive = Route::has($route) && $employeeId;
            $isActivePage = request()->routeIs($route.'*') || (request()->routeIs('employees.create') && $route == 'employees.edit');
            $classes = 'tabs-nav__item';

            if ($isActivePage) {
                $classes .= ' tabs-nav__item--active';
            } elseif (!$isRouteActive && !request()->routeIs('employees.create')) {
                // Jangan set inactive jika di halaman create
                $classes .= ' tabs-nav__item--inactive';
            }
        @endphp

        @if ($isRouteActive)
            {{-- Jika route ada dan ini mode edit, buat link <a> --}}
            <a href="{{ route($route, $employeeId) }}" class="{{ $classes }}">
                <span class="tabs-nav__item-text">{!! $label !!}</span>
            </a>
        @else
            {{-- Jika route belum ada ATAU ini mode create, tampilkan sebagai <div> --}}
            <div class="{{ $classes }}" @if(!Route::has($route)) title="Fitur ini belum tersedia" @endif>
                <span class="tabs-nav__item-text">{!! $label !!}</span>
            </div>
        @endif
    @endforeach
</nav>
