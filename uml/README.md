# AuraBeauty BI - Dokumentasi UML

Folder ini berisi seluruh diagram UML untuk sistem **AuraBeauty BI Console**: *Analisis Keranjang Belanja & Manajemen Overstock*.

Diagram dibuat menggunakan [PlantUML](https://plantuml.com/) dan mencakup 9 (sembilan) jenis diagram yang merepresentasikan arsitektur, alur data, dan interaksi komponen pada aplikasi Laravel + Python.

---

## Daftar File

| No | File | Jenis Diagram | Deskripsi Singkat |
|---|---|---|---|
| 1 | `01-use-case-diagram.puml` | **Diagram Kasus Penggunaan** | Aktor (Analis Data, Admin) dan fitur yang dapat diakses. |
| 2 | `02-class-diagram.puml` | **Diagram Kelas** | Struktur kelas Laravel (`AnalyticsController`, `Rule`, `OverstockBundle`) dan Python (`AprioriAnalysis`). |
| 3 | `03-entity-relationship-diagram.puml` | **ERD** | Relasi tabel `dim_products`, `fact_sales_transactions`, `dim_customers`, `dim_date`, `association_rules`, `overstock_items`. |
| 4 | `04-sequence-run-analysis.puml` | **Diagram Sekuen** | Alur eksekusi saat pengguna menekan tombol **Run Analysis**. |
| 5 | `05-sequence-view-dashboard.puml` | **Diagram Sekuen** | Alur pembacaan dan render dashboard `/analytics/apriori`. |
| 6 | `06-activity-apriori-analysis.puml` | **Diagram Aktivitas** | Langkah-langkah pipeline Apriori dari memuat data hingga mengekspor CSV. |
| 7 | `07-component-diagram.puml` | **Diagram Komponen** | Komponen-komponen sistem: Browser, Laravel, Python, CSV, SQLite, Chart.js. |
| 8 | `08-deployment-diagram.puml` | **Diagram Deployment** | Penempatan node: Stasiun Kerja Pengguna, Server Web, SQLite, Runtime Python. |
| 9 | `09-state-apriori-job.puml` | **Diagram State** | State lifecycle pekerjaan analisis Apriori (Idle → Running → Success / Error). |

---

## Cara Render Diagram

### Opsi 1: PlantUML Online Server
Salin isi file `.puml` ke:
- [www.plantuml.com/plantuml/uml](http://www.plantuml.com/plantuml/uml)

### Opsi 2: Ekstensi VS Code
Instal ekstensi **PlantUML** (jebbs.plantuml), lalu tekan `Alt + D` pada file `.puml`.

### Opsi 3: PlantUML CLI (membutuhkan Java)
```bash
# Unduh plantuml.jar dari https://plantuml.com/download
java -jar plantuml.jar *.puml
```

---

## Konteks Sistem

- **Backend**: Laravel 11.x + PHP 8.x
- **Mesin Machine Learning**: Python 3.x (`apriori_analysis.py`)
- **Algoritma**: Apriori untuk *Market Basket Analysis*
- **Database**: SQLite (default Laravel)
- **Sumber Data**: `product_code - Sheet1.csv`, `transactions - Sheet1.csv`
- **Output**: `association_rules.csv`
- **Frontend**: Blade + Bootstrap 5 + Chart.js

---

## Catatan

- `association_rules` dan `overstock_items` pada ERD adalah hasil/generate; saat ini disimpan sebagai CSV dan array hard-coded di controller.
- Semua diagram mengacu pada implementasi aktual di folder `beauty-dashboard/` dan skrip `apriori_analysis.py`.
