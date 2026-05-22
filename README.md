# Skripsi Assistant 📚

Skripsi Assistant adalah aplikasi web yang dirancang untuk membantu mahasiswa dalam proses penulisan skripsi. Aplikasi ini menyediakan fitur-fitur terintegrasi untuk mengelola dokumen, melacak progress, dan memanfaatkan AI untuk menghasilkan konten berkualitas.

## Fitur Utama ✨

### 📝 Manajemen Dokumen
- **Penyimpanan Dokumen** - Simpan berbagai jenis dokumen skripsi (Judul, BAB 1, BAB 2, BAB 3, UML, dll)
- **Versi Kontrol** - Lacak riwayat perubahan dokumen dengan timestamp
- **Edit & Update** - Mudah mengedit dan memperbarui konten dokumen

### 🤖 AI Generator
Fitur generasi konten berbasis AI untuk mempercepat proses penulisan:
- **Generator Judul** - Hasilkan judul skripsi yang menarik
- **Generator BAB 1** - Buat bab pendahuluan otomatis
- **Generator BAB 2** - Hasilkan tinjauan pustaka dan landasan teori
- **Generator BAB 3** - Buat metodologi penelitian
- **Generator UML** - Hasilkan diagram UML untuk aplikasi

### 📊 Dashboard & Progress Tracking
- **Statistik Ringkas** - Lihat jumlah dokumen dan progress penulisan
- **Progress Bar** - Visualisasi progress skripsi secara real-time
- **Riwayat AI** - Pantau semua aktivitas generate AI dengan detail token
- **Quick Menu** - Akses cepat ke fitur generator dari dashboard

### 👤 User Management
- **Autentikasi** - Sistem login aman dengan hashing password
- **Role-based Access** - Dukungan role Mahasiswa dan Admin
- **User Profil** - Kelola informasi pengguna

### 💾 AI History
- **Pencatatan AI Usage** - Setiap generate AI dicatat dengan:
  - Model AI yang digunakan
  - Prompt dan response
  - Token usage (input & output)
  - Timestamp

## Tech Stack 🛠️

| Teknologi | Versi |
|-----------|-------|
| **Backend** | PHP 8.1+ |
| **Frontend** | HTML, CSS, JavaScript |
| **Database** | MySQL 5.7+ |
| **Framework** | Custom MVC Framework |
| **UI Library** | Bootstrap 5 |
| **Icons** | Bootstrap Icons |

### Persentase Kode
- PHP: 94.1%
- JavaScript: 3.6%
- CSS: 2.3%

## Struktur Proyek 📁

```
skripsi_assistant/
├── app/                    # Application logic
│   ├── bootstrap.php      # Application initialization
│   ├── core/              # Core framework classes
│   ├── controllers/       # Controllers
│   ├─�� models/            # Models
│   └── views/             # Templates
├── config/                # Configuration files
├── database/              # Database files
├── assets/                # Static files (CSS, JS, images)
├── helpers/               # Helper functions
├── uploads/               # User uploads directory
├── index.php             # Entry point
└── app_pembantu_skripsi.sql  # Database schema
```

## Instalasi 🚀

### Prasyarat
- PHP 8.1 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/rakhafauzi/skripsi_assistant.git
   cd skripsi_assistant
   ```

2. **Setup Database**
   ```bash
   # Buat database baru
   mysql -u root -p -e "CREATE DATABASE app_pembantu_skripsi;"
   
   # Import schema
   mysql -u root -p app_pembantu_skripsi < app_pembantu_skripsi.sql
   ```

3. **Konfigurasi Aplikasi**
   - Salin file konfigurasi dari `config/` 
   - Sesuaikan dengan pengaturan server lokal Anda

4. **Upload ke Web Server**
   ```bash
   # Pindahkan folder ke root directory web server
   # Contoh: /var/www/html/skripsi_assistant
   ```

5. **Set Permissions**
   ```bash
   chmod 755 uploads/
   chmod 644 index.php
   ```

6. **Akses Aplikasi**
   ```
   http://localhost/skripsi_assistant/
   ```

## Database Schema 🗄️

### Tabel Users
- `id` - Primary Key
- `name` - Nama pengguna
- `email` - Email (unique)
- `password_hash` - Hash password
- `role` - Role (mahasiswa/admin)
- `created_at`, `updated_at` - Timestamps

### Tabel Documents
- `id` - Primary Key
- `user_id` - Foreign Key ke users
- `type` - Tipe dokumen (judul, bab1, bab2, dll)
- `title` - Judul dokumen
- `content` - Konten dokumen
- `content_format` - Format (text/json)
- `created_at`, `updated_at` - Timestamps

### Tabel AI History
- `id` - Primary Key
- `user_id` - Foreign Key ke users
- `feature` - Fitur yang digunakan
- `prompt` - Prompt yang dikirim ke AI
- `response` - Response dari AI
- `model` - Model AI yang digunakan
- `input_tokens`, `output_tokens`, `total_tokens` - Token usage
- `created_at` - Timestamp

### Tabel Settings
- `id` - Primary Key
- `key` - Nama setting
- `value` - Nilai setting
- `updated_at` - Timestamp

## Penggunaan 📖

### Untuk Mahasiswa

1. **Daftar & Login**
   - Buat akun baru dengan email dan password
   - Login dengan kredensial Anda

2. **Lihat Dashboard**
   - Pantau progress skripsi
   - Lihat dokumentasi terbaru
   - Akses riwayat generate AI

3. **Generate Konten**
   - Pilih fitur generator (Judul, BAB 1, BAB 2, BAB 3, UML)
   - Input prompt/topik
   - Dapatkan konten yang dihasilkan AI
   - Simpan ke dokumen

4. **Kelola Dokumen**
   - Lihat semua dokumen yang tersimpan
   - Edit dan perbarui konten
   - Hapus dokumen jika diperlukan

### Untuk Admin

- Kelola user accounts
- Monitor penggunaan AI
- Manage settings aplikasi
- Lihat statistik penggunaan

## API Endpoints 🔌

Beberapa endpoint utama:

```
POST   /auth/login              - User login
POST   /auth/logout             - User logout
POST   /auth/register           - User registration

GET    /dashboard               - Dashboard dashboard
GET    /documents/index         - List dokumen
POST   /documents/store         - Simpan dokumen
GET    /documents/detail/:id    - Detail dokumen
GET    /documents/edit/:id      - Edit dokumen
DELETE /documents/delete/:id    - Hapus dokumen

POST   /generator/title         - Generate judul
POST   /generator/bab1          - Generate BAB 1
POST   /generator/bab2          - Generate BAB 2
POST   /generator/bab3          - Generate BAB 3
POST   /generator/uml           - Generate UML

GET    /history/index           - Riwayat AI
GET    /history/detail/:id      - Detail riwayat
```

## Kontribusi 🤝

Kontribusi sangat diterima! Berikut cara berkontribusi:

1. Fork repository
2. Buat branch untuk fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

## Roadmap 🗓️

- [ ] Integrasi dengan multiple AI providers
- [ ] Export dokumen ke PDF/Word
- [ ] Kolaborasi real-time
- [ ] Template skripsi yang dapat dikustomisasi
- [ ] Analisis plagiarisme
- [ ] Mobile app

## Troubleshooting 🔧

### Database Connection Error
- Pastikan MySQL service berjalan
- Verifikasi kredensial database di config
- Periksa permissions folder

### Upload Error
- Pastikan folder `uploads/` writable
- Check file size limits
- Verify disk space

### AI Generation Error
- Periksa koneksi internet
- Verifikasi API keys
- Check rate limits

## Lisensi 📄

Project ini belum memiliki lisensi resmi. Hubungi pemilik untuk informasi lebih lanjut.

## Kontak & Support 📧

- **Author**: Rakha Fauzi
- **GitHub**: [@rakhafauzi](https://github.com/rakhafauzi)
- **Repository**: [skripsi_assistant](https://github.com/rakhafauzi/skripsi_assistant)

## Changelog 📝

### Version 1.0.0 (Current)
- Initial release
- Dashboard dengan statistik
- AI Generator untuk berbagai tipe dokumen
- Manajemen dokumen lengkap
- AI History tracking
- User authentication & authorization

---

**Dibuat dengan ❤️ untuk membantu mahasiswa dalam menulis skripsi**
