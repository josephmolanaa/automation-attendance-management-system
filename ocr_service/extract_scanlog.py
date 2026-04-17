#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
extract_scanlog.py — Ekstrak data absensi dari PDF Kartu Scanlog (CamScanner).

Strategi:
  1. pdfplumber.extract_text()  — hanya efektif untuk PDF dengan teks digital
  2. pdftotext (poppler)         — fallback untuk PDF digital
  3. Tesseract OCR               — UTAMA untuk PDF hasil scan gambar (CamScanner)
     - Per halaman dikonversi ke gambar PNG DPI tinggi
     - Diproses dengan preprocess_image.py (deskew, denoise, binarize)
     - Tesseract dengan konfigurasi yang optimal untuk dokumen tabel
"""

import sys
import re
import json
import os
import subprocess
import difflib
from datetime import datetime

try:
    import pdfplumber
    HAS_PDFPLUMBER = True
except ImportError:
    HAS_PDFPLUMBER = False

try:
    from pdf2image import convert_from_path
    import pytesseract

    # Auto-detect Tesseract binary di Windows jika tidak ada di PATH
    import platform
    if platform.system() == 'Windows':
        import shutil
        if not shutil.which('tesseract'):
            win_candidates = [
                r'C:\Program Files\Tesseract-OCR\tesseract.exe',
                r'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe',
            ]
            for p in win_candidates:
                if os.path.exists(p):
                    pytesseract.pytesseract.tesseract_cmd = p
                    break

    # Detect available Tesseract languages
    try:
        _langs = pytesseract.get_languages(config='')
        TESSERACT_LANGS = 'ind+eng' if 'ind' in _langs else 'eng'
    except Exception:
        TESSERACT_LANGS = 'ind+eng'  # default, Railway pasti punya ind

    # Auto-detect poppler path di Windows
    POPPLER_PATH = None
    if platform.system() == 'Windows':
        import shutil as _sh
        if not _sh.which('pdftoppm'):
            winget_poppler = os.path.expanduser(
                r'~\AppData\Local\Microsoft\WinGet\Packages'
            )
            if os.path.exists(winget_poppler):
                for d in os.listdir(winget_poppler):
                    if 'poppler' in d.lower():
                        candidate = os.path.join(winget_poppler, d)
                        # Cari Library/bin di dalamnya
                        for root, dirs, files in os.walk(candidate):
                            if 'pdftoppm.exe' in files:
                                POPPLER_PATH = root
                                break
                        if POPPLER_PATH:
                            break

    HAS_TESSERACT = True
except ImportError:
    HAS_TESSERACT = False
    TESSERACT_LANGS = 'ind+eng'
    POPPLER_PATH = None


# Pre-processing module (opsional — akan fallback jika tidak tersedia)
try:
    _ocr_dir = os.path.dirname(os.path.abspath(__file__))
    sys.path.insert(0, _ocr_dir)
    from preprocess_image import preprocess, backend_info
    HAS_PREPROCESS = True
except ImportError:
    HAS_PREPROCESS = False
    def preprocess(img): return img
    def backend_info(): return "none"

# Optional: rapidfuzz untuk fuzzy matching yang lebih akurat
try:
    from rapidfuzz import fuzz, process as rfprocess
    HAS_RAPIDFUZZ = True
except ImportError:
    HAS_RAPIDFUZZ = False

HARI_IDX = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU']

SKIP_WORDS = {
    'NIP', 'NAMA', 'JABATAN', 'TANGGAL', 'SCAN', 'PEGAWAI', 'DATA',
    'SCANLOG', 'KARTU', 'KANTOR', 'DEPARTEMEN', 'SCAN 1', 'SCAN 2',
    'TOTAL', 'NO', 'DIPINDAI', 'CAMSCANNER', 'DATASCANLOG',
    'HALAMAN', 'PAGE', 'DATE', 'TIME', 'NAME',
}

# Tahun valid untuk attendance (hindari OCR salah baca "2" jadi "3")
VALID_YEAR_RANGE = range(2020, 2035)


def get_hari(date_obj):
    return HARI_IDX[date_obj.weekday()]


def normalize_time(s):
    if not s:
        return None
    s = str(s).strip()
    # Ganti separator yang salah (titik/koma → titik dua)
    s = re.sub(r'[.,]', ':', s)
    # Hapus semua karakter selain angka dan titik dua
    s = re.sub(r'[^0-9:]', '', s)
    if not s or len(s) < 4:
        return None
    parts = s.split(':')
    try:
        if len(parts) == 1 and len(s) in (4, 6):
            h = int(s[:2]); m = int(s[2:4])
            sec = int(s[4:6]) if len(s) == 6 else 0
        elif len(parts) == 2:
            h, m, sec = int(parts[0]), int(parts[1][:2]), 0
        elif len(parts) >= 3:
            h, m, sec = int(parts[0]), int(parts[1][:2]), int(parts[2][:2])
        else:
            return None
        if h > 23 or m > 59 or sec > 59:
            return None
        return f"{h:02d}:{m:02d}:{sec:02d}"
    except (ValueError, IndexError):
        return None


def normalize_date(s):
    if not s:
        return None
    s = str(s).strip()

    # Fix OCR salah baca tahun: 3026 → 2026, 1026 → 2026, dsb.
    def fix_year(y_str):
        y = int(y_str)
        if y not in VALID_YEAR_RANGE:
            # Coba koreksi: ambil 2 digit terakhir dan prefix 20
            last2 = y % 100
            candidate = 2000 + last2
            if candidate in VALID_YEAR_RANGE:
                return str(candidate)
            return None
        return str(y)

    m = re.match(r'(\d{1,2})[-/.](\d{1,2})[-/.](\d{2,4})', s)
    if m:
        d, mo = int(m.group(1)), int(m.group(2))
        raw_y = m.group(3)
        y_str = fix_year('20' + raw_y if len(raw_y) == 2 else raw_y)
        if not y_str:
            return None
        try:
            return datetime(int(y_str), mo, d).strftime('%Y-%m-%d')
        except ValueError:
            return None
    return None


def is_valid_name(text):
    if not text or len(text.strip()) < 3:
        return False
    t = text.strip().upper()
    if t in SKIP_WORDS:
        return False
    # Bukan baris yang hanya angka/simbol/spasi
    if re.match(r'^[\d\s\-|_]+$', t):
        return False
    # Minimal 3 huruf alfabet
    return sum(1 for c in t if c.isalpha()) >= 3


def clean_ocr_name(raw):
    """
    Bersihkan artefak OCR yang umum pada nama:
    - Hapus angka yang menempel di nama
    - Hapus karakter non-alfabet selain spasi dan apostrof
    - Hapus leading/trailing noise (contoh: "I" di depan nama karena border tabel terbaca)
    - Normalisasi multiple whitespace
    """
    if not raw:
        return ''

    # Hapus karakter yang bukan huruf, spasi, atau apostrof
    cleaned = re.sub(r"[^A-Za-z\s'\-]", ' ', raw)

    # Hapus kata-kata yang terlalu pendek (1-2 huruf) yang tidak masuk akal sebagai bagian nama
    # KECUALI inisial standar seperti "A", "B" yang umum dalam nama Indonesia
    words = cleaned.split()
    # Filter kata noise (bukan huruf bermakna)
    words = [w for w in words if len(w) >= 2 or (len(w) == 1 and w.isalpha())]

    result = ' '.join(words).strip()

    # Strip leading I/l yang merupakan artefak border tabel vertikal OCR.
    # Contoh: "IKUSTORO" → "KUSTORO", "ISMAN SURANDARU" → BIARKAN (ISMAN = nama valid)
    # Hanya strip jika:
    #   - Kata pertama diawali 'I' dan sisanya consonant-heavy (tanpa vokal setelah I)
    #   - Atau jika kata pertama SATU KATA (tanpa spasi) dan kurang masuk sebagai nama
    words_r = result.split()
    if words_r:
        first = words_r[0]
        rest_words = words_r[1:]
        # Strip leading I hanya jika kata pertama dimulai I + consonant langsung
        # contoh: IKUSTORO (I + K), INARSA (I + N) — bukan ISMAN (I + S + vowel lagi)
        if len(first) >= 4:
            m2 = re.match(r'^I([BCDFGHJKLMNPQRSTVWXYZ]{2})', first.upper())
            if m2:
                # Cek kata kedua dst — jika ada, ini kemungkinan nama "ISMAN SURANDARU" (valid)
                # Hanya strip jika tidak ada kata sesudahnya (single-word OCR artifact)
                if not rest_words:
                    result = first[1:].strip()  # Hapus leading I
        # Kasus lain: satu kata saja "IKUSTORO" tanpa spasi
        elif len(first) >= 5 and not rest_words:
            m3 = re.match(r'^I([A-Z][a-zA-Z]{3,})$', first)
            if m3:
                result = first[1:]

    return ' '.join(result.split())



def load_template_names():
    """Load daftar nama karyawan dari file Excel template."""
    try:
        import openpyxl
        base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        tpl_dir = os.path.join(base_dir, 'TEMPLATE')
        names = set()
        for fn in os.listdir(tpl_dir):
            if fn.endswith('.xlsx'):
                wb = openpyxl.load_workbook(
                    os.path.join(tpl_dir, fn), data_only=True, read_only=True)
                for sheet in wb.worksheets:
                    for row in sheet.iter_rows(min_col=3, max_col=5):
                        for cell in row:
                            v = cell.value
                            if v and isinstance(v, str):
                                v = v.strip().upper()
                                if is_valid_name(v) and v not in SKIP_WORDS and len(v) >= 3:
                                    names.add(v)
                wb.close()
        return sorted(names)
    except Exception:
        return []


def fuzzy_match_name(raw, valid_names, threshold=70):
    """
    Cocokkan nama OCR ke daftar nama valid dari template.
    Pakai rapidfuzz bila tersedia (lebih akurat), fallback ke difflib.
    """
    if not valid_names:
        return clean_ocr_name(raw).upper()

    raw_clean = clean_ocr_name(raw).upper().strip()
    if not raw_clean:
        return raw.upper().strip()

    # Exact match
    if raw_clean in valid_names:
        return raw_clean

    # Substring match (nama raw ada di dalam nama valid atau sebaliknya)
    for vn in valid_names:
        if raw_clean == vn:
            return vn
        if len(raw_clean) >= 4 and raw_clean in vn:
            return vn
        if len(vn) >= 4 and vn in raw_clean:
            return vn

    # Word overlap matching
    raw_words = {w for w in raw_clean.split() if len(w) > 2}
    best_overlap, best_name = 0, None
    for vn in valid_names:
        vn_words = {w for w in vn.split() if len(w) > 2}
        overlap = len(raw_words & vn_words)
        if overlap > best_overlap:
            best_overlap = overlap
            best_name = vn
    if best_overlap >= 1:
        return best_name

    # Fuzzy string matching
    if HAS_RAPIDFUZZ:
        result = rfprocess.extractOne(
            raw_clean, valid_names,
            scorer=fuzz.token_sort_ratio,
            score_cutoff=threshold
        )
        if result:
            return result[0]
    else:
        matches = difflib.get_close_matches(raw_clean, valid_names, n=1, cutoff=threshold / 100)
        if matches:
            return matches[0]

    return raw_clean


# ── Extraction functions ──────────────────────────────────────────────────────

def extract_via_pdfplumber(pdf_path):
    """Coba baca teks langsung dari PDF (hanya efektif untuk PDF digital, bukan scan)."""
    if not HAS_PDFPLUMBER:
        return ''
    try:
        texts = []
        with pdfplumber.open(pdf_path) as pdf:
            for page in pdf.pages:
                t = page.extract_text(x_tolerance=3, y_tolerance=3)
                if t:
                    texts.append(t)
        return '\n'.join(texts)
    except Exception:
        return ''


def extract_via_pdftotext(pdf_path):
    """Fallback: gunakan pdftotext (poppler) untuk PDF digital."""
    for args in [
        ['pdftotext', '-layout', pdf_path, '-'],
        ['pdftotext', pdf_path, '-'],
    ]:
        try:
            r = subprocess.run(args, capture_output=True, text=True, timeout=30)
            if r.stdout and len(r.stdout.strip()) > 50:
                return r.stdout
        except Exception:
            continue
    return ''


def extract_via_tesseract(pdf_path, debug=False):
    """
    Ekstrak teks dari PDF gambar menggunakan Tesseract OCR.

    Peningkatan dibanding versi sebelumnya:
    - DPI 300 (lebih tinggi dari sebelumnya 250)
    - Auto-detect Tesseract binary dan poppler di Windows
    - Pre-processing gambar (grayscale, deskew, denoise, binarize)
    - PSM 4 (single-column) dan PSM 6 (block) dicoba keduanya, ambil yang lebih panjang
    """
    if not HAS_TESSERACT:
        return ''
    try:
        kwargs = {'dpi': 300}
        if POPPLER_PATH:
            kwargs['poppler_path'] = POPPLER_PATH
        pages = convert_from_path(pdf_path, **kwargs)
    except Exception as e:
        if debug:
            print(f"[DEBUG] convert_from_path gagal: {e}", file=sys.stderr)
        return ''

    if debug:
        print(f"[DEBUG] Pre-process backend: {backend_info()}", file=sys.stderr)
        print(f"[DEBUG] Tesseract langs: {TESSERACT_LANGS}", file=sys.stderr)
        print(f"[DEBUG] Halaman PDF: {len(pages)}", file=sys.stderr)

    full_parts = []

    # Konfigurasi Tesseract
    # PSM 4: Assume single column — bagus untuk laporan dengan satu kolom tabel
    # PSM 6: Assume block of text — bisa lebih baik untuk tabel lebar
    tess_configs = [
        '--psm 4 --oem 3',
        '--psm 6 --oem 3',
    ]

    for page_num, page_img in enumerate(pages):
        # Pre-processing gambar
        try:
            processed = preprocess(page_img)
        except Exception:
            processed = page_img

        best_text = ''
        # Coba dengan bahasa yang tersedia, fallback ke eng
        for lang in [TESSERACT_LANGS, 'eng']:
            for cfg in tess_configs:
                try:
                    text = pytesseract.image_to_string(processed, lang=lang, config=cfg)
                    if len(text.strip()) > len(best_text.strip()):
                        best_text = text
                except Exception:
                    continue
            if best_text:
                break  # Berhasil, tidak perlu fallback

        if debug:
            print(f"[DEBUG] Page {page_num+1}: {len(best_text)} chars extracted", file=sys.stderr)

        if best_text:
            full_parts.append(best_text)

    return '\n'.join(full_parts)


# ── Parsing ───────────────────────────────────────────────────────────────────

def parse_from_text(full_text, valid_names):
    """
    Parse teks OCR mentah menjadi struktur data karyawan+absensi.

    Mendukung format output Tesseract:
      A) Nama dan tanggal pada baris yang SAMA:
         "NASAR SUPRIANTO 31-03-2026 07:50:37 20:04:29"

      B) Nama pada baris sendiri, tanggal di baris berikutnya:
         "NASAR SUPRIANTO"
         "31-03-2026  07:50:37  20:04:29"

      C) Spasi lebar (wide-table output):
         "     NASAR SUPRIANTO          31-03-2026  07:50:37  20:04:29"
    """
    emp_map = {}
    emp_order = []

    def add_record(name, tanggal, scan1, scan2):
        if name not in emp_map:
            emp_map[name] = []
            emp_order.append(name)
        # Update record yang sudah ada untuk tanggal yang sama
        for rec in emp_map[name]:
            if rec['tanggal'] == tanggal:
                if scan1 and not rec['scan1']:
                    rec['scan1'] = scan1
                if scan2 and not rec['scan2']:
                    rec['scan2'] = scan2
                return
        try:
            d_obj = datetime.strptime(tanggal, '%Y-%m-%d')
            hari = get_hari(d_obj)
        except ValueError:
            hari = '-'
        emp_map[name].append({
            'tanggal': tanggal,
            'hari': hari,
            'scan1': scan1,
            'scan2': scan2,
        })

    last_name = None

    for line in full_text.splitlines():
        line = line.strip()
        if not line or len(line) < 4:
            continue

        # Abaikan baris header/footer
        line_upper = line.upper()
        if any(kw in line_upper for kw in [
            'KARTU SCANLOG', 'NIP NAMA', 'HALAMAN', 'CAMSCANNER',
            'SCAN 1 SCAN 2', 'DIPINDAI', 'DATASCANLOG'
        ]):
            continue

        # Cari pola tanggal di baris ini
        dm = re.search(r'(\d{1,2}[-/.]\d{1,2}[-/.]\d{2,4})', line)

        # ── Baris tanpa tanggal: kemungkinan nama karyawan ────────────────
        if not dm:
            # Bersihkan noise OCR dan cek apakah bisa jadi nama
            candidate_raw = re.sub(r'^[^A-Za-z]+', '', line)
            candidate = clean_ocr_name(candidate_raw)
            if is_valid_name(candidate) and len(candidate) >= 4:
                matched = fuzzy_match_name(candidate, valid_names)
                # Terima jika cocok dengan template ATAU tidak ada template
                if not valid_names or matched in valid_names:
                    last_name = matched
            continue

        # ── Baris dengan tanggal ──────────────────────────────────────────
        tanggal = normalize_date(dm.group(1))
        if not tanggal:
            continue

        date_start = dm.start()
        date_end   = dm.end()

        # Ambil bagian kiri tanggal → kandidat nama
        name_raw = line[:date_start].strip()
        name_raw_clean = clean_ocr_name(name_raw)

        if is_valid_name(name_raw_clean) and len(name_raw_clean) >= 3:
            name = fuzzy_match_name(name_raw_clean, valid_names)
            last_name = name
        elif last_name:
            name = last_name
        else:
            continue  # Tidak ada nama → skip

        # Ambil jam dari bagian kanan tanggal
        after = line[date_end:].strip()
        times = []
        for tk in re.findall(r'\b(\d{1,2}:\d{2}(?::\d{2})?)\b', after):
            t = normalize_time(tk)
            if t:
                times.append(t)

        # Jika tidak ada waktu kanan, coba di kiri tanggal (setelah nama)
        if not times:
            before_date = line[len(name_raw):date_start]
            for tk in re.findall(r'\b(\d{1,2}:\d{2}(?::\d{2})?)\b', before_date):
                t = normalize_time(tk)
                if t:
                    times.append(t)

        # Urutkan jam: scan1 = lebih kecil (masuk), scan2 = lebih besar (pulang)
        try:
            times.sort(key=lambda t: datetime.strptime(t, '%H:%M:%S'))
        except Exception:
            pass

        scan1 = times[0] if len(times) >= 1 else None
        scan2 = times[1] if len(times) >= 2 else None

        # Jika hanya ada satu jam dan itu jam sore/malam (>= 14:00) → itu scan2 (pulang)
        if scan1 and not scan2:
            try:
                h = int(scan1.split(':')[0])
                if h >= 14:
                    scan2, scan1 = scan1, None
            except (ValueError, IndexError):
                pass

        add_record(name, tanggal, scan1, scan2)

    return [
        {'nama': n, 'nip': '', 'records': emp_map[n]}
        for n in emp_order
        if emp_map[n]
    ]


# ── Main ──────────────────────────────────────────────────────────────────────

def main():
    if len(sys.argv) < 2:
        _err("Usage: python extract_scanlog.py <pdf_path> [--debug]")

    pdf_path   = sys.argv[1]
    debug_mode = '--debug' in sys.argv

    if not os.path.exists(pdf_path):
        _err(f"File tidak ditemukan: {pdf_path}")

    valid_names = load_template_names()

    # ── Urutan ekstraksi ──────────────────────────────────────────────────────
    full_text = ''
    method    = 'none'

    # 1. pdfplumber (hanya efektif untuk PDF digital, bukan scan)
    full_text = extract_via_pdfplumber(pdf_path)
    if full_text and len(full_text.strip()) > 50:
        method = 'pdfplumber'
    else:
        # 2. pdftotext fallback
        full_text = extract_via_pdftotext(pdf_path)
        if full_text and len(full_text.strip()) > 50:
            method = 'pdftotext'
        else:
            # 3. Tesseract OCR — UTAMA untuk PDF scan CamScanner
            full_text = extract_via_tesseract(pdf_path, debug=debug_mode)
            method = 'tesseract'

    if debug_mode:
        employees = parse_from_text(full_text, valid_names) if full_text else []
        print(json.dumps({
            'debug'              : True,
            'method'             : method,
            'preprocess_backend' : backend_info(),
            'valid_names_count'  : len(valid_names),
            'valid_names_sample' : valid_names[:10],
            'text_length'        : len(full_text),
            'raw_text'           : full_text[:5000],
            'employees_found'    : len(employees),
            'employees'          : employees,
        }, ensure_ascii=False, indent=2))
        return

    if not full_text or len(full_text.strip()) < 10:
        _err(f'Tidak bisa membaca teks dari PDF (method: {method}). '
             f'Pastikan Tesseract terinstall: sudo apt-get install tesseract-ocr tesseract-ocr-ind')

    employees = parse_from_text(full_text, valid_names)

    if not employees:
        _err(
            f'Tidak ada data karyawan ditemukan (method: {method}). '
            f'Preview teks OCR: {full_text[:500]}'
        )

    print(json.dumps(employees, ensure_ascii=False))


def _err(msg):
    print(json.dumps({'error': msg}), file=sys.stdout)
    sys.exit(1)


if __name__ == '__main__':
    main()
