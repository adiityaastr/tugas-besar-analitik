# AuraBeauty BI Console
**Market Basket Analysis & Overstock Management System**

![AuraBeauty](https://img.shields.io/badge/AuraBeauty-Business_Intelligence-3B82F6?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)
![Python](https://img.shields.io/badge/Python-3.x-3776AB?style=for-the-badge&logo=python)

A powerful Business Intelligence dashboard built to solve inventory stagnation and discover natural product purchasing patterns. This system applies the **Apriori Machine Learning Algorithm** to thousands of transactional records to generate highly accurate product bundling recommendations.

---

## 🚀 Key Features

*   **Algorithmic Discovery**: Uses Apriori to extract frequent itemsets (2, 3, or 4 products) from massive transaction datasets.
*   **Overstock Cure**: Automatically cross-references stagnant inventory (overstock) with high-affinity product associations to suggest profitable, data-backed clearance bundles.
*   **Flat Design UI**: Built with a strict, highly-legible **Flat Design System**. No shadows, no gradients. Pure geometric shapes, high-contrast typography (Outfit), and bold color blocking for maximum analytical clarity.
*   **Interactive Visualizations**: Integrated Chart.js for real-time Support vs. Confidence scatter plots and Lift Score horizontal bar charts.
*   **Dynamic Data Grid**: Filter bundles by size, sort by Support/Confidence/Lift, and Export directly to CSV.

## 🛠 Tech Stack

*   **Backend**: Laravel 11.x (PHP 8.5)
*   **Machine Learning Engine**: Python (Native Apriori Fallback via `itertools` & `pandas`)
*   **Database**: SQLite
*   **Frontend**: Tailwind CSS (via CDN) & Chart.js

## 🎨 Design System: Flat UI

This dashboard rejects the artificial depth of modern web design. 
- **Typography**: Uses **Outfit** (extrabold for headings, medium for data).
- **Color Palette**: Pure White backgrounds against Gray 900 text, punctuated by solid primary blocks of Blue 500 (`#3B82F6`), Emerald 500 (`#10B981`), and Amber 500 (`#F59E0B`).
- **Hierarchy**: Defined entirely by scale, color contrast, and rigid grids. Absolute zero drop shadows (`shadow-none`).

## ⚙️ Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/adiityaastr/tugas-besar-analitik.git
   cd tugas_besar_analitik/beauty-dashboard
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Ensure `.env` points to a valid SQLite database:
   ```env
   DB_CONNECTION=sqlite
   ```

4. **Python Configuration**
   Ensure Python 3.x is installed on your system. The Laravel controller (`AnalyticsController.php`) triggers the analysis script located at `scripts/apriori_analysis.py`.
   *Note: Ensure your `python` command is globally accessible, or update the path in the controller.*

5. **Run the Application**
   ```bash
   php artisan serve
   ```
   Navigate to `http://127.0.0.1:8000/analytics/apriori`

## 📊 Data Processing

The Python backend parses `transactions.csv`, groups items by `customer_id`, and runs a frequent itemset generation algorithm based on a strict `min_support` threshold (e.g., `0.5%`). The output is serialized into `association_rules.csv` and seamlessly ingested by the Laravel frontend.

---
*Crafted with precision for data-driven decisions.*
