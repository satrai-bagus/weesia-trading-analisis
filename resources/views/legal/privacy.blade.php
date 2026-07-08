<x-legal-page title="Kebijakan Privasi" updated="7 Juli 2026">
    <p>Kebijakan ini menjelaskan data apa yang dikumpulkan Weesia, bagaimana data digunakan, dan hak kamu sebagai
        pengguna.</p>

    <h2>1. Data yang Kami Kumpulkan</h2>
    <ul>
        <li><strong>Data akun</strong> &mdash; nama, alamat email, dan kata sandi (tersimpan dalam bentuk hash).</li>
        <li><strong>Login Google</strong> &mdash; jika kamu masuk dengan Google, kami menerima nama dan email dari akun
            Google kamu. Kami tidak menerima kata sandi Google.</li>
        <li><strong>Data transaksi</strong> &mdash; riwayat pembelian token/langganan, nominal, dan bukti pembayaran
            yang kamu unggah untuk verifikasi.</li>
        <li><strong>Nomor WhatsApp</strong> &mdash; opsional, hanya jika kamu mengisinya untuk menerima notifikasi
            pesanan atau alert analisa.</li>
        <li><strong>Data penggunaan</strong> &mdash; analisa yang kamu buka dan posisi pantauan yang kamu simpan di
            dashboard.</li>
    </ul>

    <h2>2. Bagaimana Data Digunakan</h2>
    <ul>
        <li>Menyediakan layanan: akses analisa, arsip, notifikasi, dan riwayat pesanan.</li>
        <li>Memverifikasi pembayaran manual dan memproses saldo token/langganan.</li>
        <li>Mengirim notifikasi yang kamu aktifkan (misalnya alert saat level analisa tersentuh).</li>
        <li>Menjaga keamanan akun dan mencegah penyalahgunaan.</li>
    </ul>

    <h2>3. Yang Tidak Kami Lakukan</h2>
    <ul>
        <li>Kami tidak menjual atau menyewakan data pribadi kamu kepada pihak ketiga.</li>
        <li>Kami tidak meminta kata sandi, kode OTP, atau akses ke akun exchange kamu.</li>
        <li>Kami tidak menggunakan data kamu untuk iklan pihak ketiga.</li>
    </ul>

    <h2>4. Penyimpanan dan Keamanan</h2>
    <p>Data disimpan di server Weesia dan hanya dapat diakses oleh admin untuk keperluan operasional (misalnya
        verifikasi pembayaran). Kata sandi disimpan dalam bentuk hash dan tidak dapat dibaca oleh siapa pun.</p>

    <h2>5. Hak Kamu</h2>
    <p>Kamu berhak meminta salinan data pribadimu, memperbaikinya, atau meminta penghapusan akun beserta datanya.
        Ajukan permintaan melalui email resmi Weesia di
        <a href="mailto:{{ config('app.contact_email') }}" class="text-gold-200 underline decoration-gold-500/50 underline-offset-4 hover:text-gold-100">{{ config('app.contact_email') }}</a>.</p>

    <h2>6. Perubahan Kebijakan</h2>
    <p>Kebijakan ini dapat diperbarui sewaktu-waktu. Perubahan signifikan akan diumumkan melalui situs. Tanggal
        pembaruan terakhir selalu tercantum di bagian atas halaman ini.</p>
</x-legal-page>
