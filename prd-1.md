# USER JOURNEY

## Aktor
- User
- Admin

## Kebutuhan Antarmuka
- Website harus responsif dan dapat digunakan dengan baik pada desktop maupun mobile.
- Antarmuka website dibangun menggunakan Tailwind CSS.
- Tampilan harus menyesuaikan ukuran layar agar halaman login, dashboard, log data, peta, audio, dan halaman pengaturan tetap mudah digunakan di berbagai perangkat.
- Pada desktop, sistem dapat menampilkan layout yang lebih luas seperti sidebar, tabel penuh, dan beberapa panel dalam satu layar.
- Pada mobile, sistem harus menyesuaikan layout menjadi lebih ringkas seperti card, stack layout, tombol yang mudah ditekan, dan navigasi yang tetap nyaman digunakan.
- Komponen tabel pada halaman log dan manajemen data harus tetap dapat diakses di mobile, misalnya dengan scroll horizontal atau layout alternatif.
- Komponen peta harus tetap tampil proporsional pada desktop dan mobile.
- Semua form input, termasuk pengaturan koordinat alat, durasi deep sleep, dan form login harus tetap mudah diisi pada layar kecil.
- Desain antarmuka harus konsisten, sederhana, dan memudahkan pengguna dalam memantau data alat.
- Sistem tidak menyediakan fitur registrasi mandiri untuk user.
- Akun pertama pada sistem hanya akun admin.
- Akun user hanya dapat dibuat oleh admin melalui dashboard admin.

---

## 1. Login dan Autentikasi

Aktor:
- User
- Admin

Alur:
- User/Admin membuka halaman login
- User/Admin memasukkan email atau username dan password
- User/Admin dapat memilih opsi remember me
- Jika lupa password, user/admin dapat menggunakan fitur forgot password
- Sistem memverifikasi data login
- Jika login berhasil, sistem mengarahkan pengguna ke dashboard sesuai perannya

Aturan:
- Tidak ada fitur register mandiri pada halaman login
- Akun pertama yang tersedia pada sistem adalah akun admin
- Akun user hanya dapat dibuat oleh admin melalui dashboard admin
- Hak akses ditentukan berdasarkan role masing-masing akun

Pain point:
- Pengguna membutuhkan akses masuk yang aman dan mudah
- Sistem harus membatasi pembuatan akun agar tidak sembarang orang dapat mendaftar

Respons sistem:
- Menyediakan halaman login yang aman dan responsif
- Menyediakan fitur remember me
- Menyediakan fitur forgot password
- Menolak akses registrasi mandiri
- Mengarahkan pengguna ke dashboard sesuai role

---

## 2. Monitoring Dashboard dan Maps

Aktor:
- User
- Admin

Alur:
- User/Admin login ke sistem
- Membuka halaman dashboard
- Sistem menampilkan data sensor secara real-time
- Sistem menampilkan peta dengan marker alat 1 dan alat 2
- User/Admin dapat melihat posisi alat pada peta

Pengelolaan lokasi:
- Admin dapat mengatur lokasi alat secara manual
- Input koordinat (latitude dan longitude)
- Atau menggeser pin pada peta
- Lokasi hanya berubah jika diubah oleh admin

Pain point:
- Tidak mengetahui lokasi alat secara pasti

Respons sistem:
- Menampilkan marker alat secara akurat berdasarkan input admin

---

## 3. Monitoring Sensor dan Status Hujan

Aktor:
- User
- Admin

Alur:
- User/Admin melihat data curah hujan
- User/Admin melihat data ketinggian air
- Sistem menampilkan status hujan (Rain / No Rain)

Catatan:
- Status hujan sepenuhnya ditentukan oleh alat 1 (hardware)

Pain point:
- Sulit mengetahui kondisi hujan secara real-time

Respons sistem:
- Menampilkan status hujan secara jelas di dashboard

---

## 4. Trigger Rekaman Audio (Alat 2)

Aktor:
- Admin

Alur:
- Alat 1 mendeteksi hujan
- Data dikirim ke server
- Server memproses kondisi hujan
- Jika hujan terdeteksi:
  - Server mengirim perintah melalui MQTT
  - Alat 2 mulai merekam suara hujan
- Jika hujan berhenti:
  - Alat 2 berhenti merekam

Pain point:
- Rekaman tidak sinkron dengan kondisi hujan

Respons sistem:
- Mengirim trigger otomatis ke alat 2

Aturan:
- Alat 2 hanya aktif jika menerima perintah dari sistem

---

## 5. Akses Rekaman Audio

Aktor:
- User
- Admin

Alur:
- Membuka halaman audio
- Sistem menampilkan daftar rekaman
- User/Admin memilih rekaman
- User/Admin dapat:
  - Memutar audio
  - Mengunduh audio

Pain point:
- Sulit mengakses rekaman suara hujan

Respons sistem:
- Menyediakan audio player dan fitur download

---

## 6. Logging Data Sensor

Aktor:
- User
- Admin

Alur:
- Membuka halaman log data
- Sistem menampilkan data sensor dalam bentuk tabel
- User/Admin melakukan filter berdasarkan waktu atau alat

Pain point:
- Sulit melihat data historis

Respons sistem:
- Menyediakan data lengkap dengan timestamp

---

## 7. Download Data

Aktor:
- User
- Admin

Alur:
- Memilih rentang waktu
- Memilih data yang ingin diunduh
- Menekan tombol download
- Sistem menghasilkan file (CSV/Excel)

Pain point:
- Sulit mengambil data untuk analisis

Respons sistem:
- Menyediakan fitur export data

---

## 8. Settings (Pengaturan Deep Sleep)

Aktor:
- Admin

Alur:
- Admin membuka halaman settings
- Admin mengatur durasi deep sleep
- Admin menyimpan pengaturan
- Sistem mengirim konfigurasi ke alat melalui MQTT

Pain point:
- Tidak bisa mengontrol konsumsi daya alat

Respons sistem:
- Mengirim konfigurasi secara real-time ke perangkat

---

## 9. Manage Database

Aktor:
- Admin

Alur:
- Admin membuka halaman manajemen data
- Admin memilih data tertentu
- Admin menghapus data

Pain point:
- Data menumpuk di database

Respons sistem:
- Menghapus data dari sistem

Aturan:
- Hanya admin yang memiliki akses untuk menghapus data

---

## 10. Manajemen Akun User

Aktor:
- Admin

Alur:
- Admin membuka halaman manajemen akun
- Admin menambahkan akun user baru
- Admin mengisi data user seperti nama, email/username, password, dan role
- Sistem menyimpan akun user baru
- Admin dapat mengubah atau menghapus akun user jika diperlukan

Aturan:
- User tidak dapat membuat akun sendiri
- Hanya admin yang dapat menambahkan akun user
- Role akun harus ditentukan saat pembuatan akun
- Akun yang dibuat oleh admin dapat digunakan user untuk login ke sistem

Pain point:
- Sistem harus memastikan pengelolaan akun tetap terpusat dan aman

Respons sistem:
- Menyediakan halaman khusus untuk admin dalam mengelola akun user
- Membatasi akses manajemen akun hanya untuk admin