#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
extract_scanlog.py
==================
Ekstrak data absensi dari PDF Kartu Scanlog (CamScanner).
Strategi ekstraksi (urutan priority):
  1. pdfplumber  — baca tabel langsung dari struktur PDF (paling akurat)
  2. pdftotext   — baca teks terformat dari PDF
  3. Tesseract   — OCR pixel-by-pixel (fallback terakhir)
"""

import sys
import re
import json
import os
import subprocess
import difflib
from datetime import datetime

# ── Optional OCR libraries ────────────────────────────────────────────────────
try:
    import pdfplumber
    HAS_PDFPLUMBER = True
except ImportError:
    HAS_PDFPLUMBER = False

try:
    from pdf2image import convert_from_path
    import pytesseract
    HAS_TESSERACT = True
except ImportError:
    HAS_TESSERACT = False

# ── Constants ─────────────────────────────────────────────────────────────────
HARI_IDX = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU']

HEADER_WORDS = {
    'NIP', 'NAMA', 'JABATAN', 'TANGGAL', 'SCAN', 'PEGAWAI',
    'DATA', 'SCANLOG', 'KARTU', 'KANTOR', 'DEPARTEMEN',
    'SCAN 1', 'SCAN 2', 'TOTAL', 'NO', 'DIPINDAI', 'CAMSCANNER',
}

# ── Helpers ───────────────────────────────────────────────────────────────────

def get_hari(date_obj):
    return HARI_IDX[date_obj.weekday()]


def normalize_time(s):
    """Normalize berbagai format waktu termasuk hasil OCR yang berantakan."""
    if not s:
        return None
    s = str(s).strip()

    # Ganti karakter OCR yang sering keliru
    ocr_map = {
        'o': '0', 'O': '0', 'q': '0', 'Q': '0', 'D': '0',
        'l': '1', 'I': '1', 'i': '1', '|': '1', '!': '1',
        'z': '2', 'Z': '2',
        's': '5', 'S': '5',
        'b': '8', 'B': '8',
    }
    result = ''.join(ocr_map.get(c, c) for c in s)

    # Ganti pemisah titik/spasi dengan titik dua
    result = re.sub(r'[.\s]', ':', result)

    # Sisakan hanya angka dan titik dua
    result = re.sub(r'[^0-9:]', '', result)

    if not result:
        return None

    parts = result.split(':')
    try:
        if len(parts) == 2:
            h, m, sec = int(parts[0]), int(parts[1]), 0
        elif len(parts) >= 3:
            h, m, sec = int(parts[0]), int(parts[1]), int(parts[2][:2])
        elif len(result) == 4:
            h, m, sec = int(result[:2]), int(result[2:]), 0
        elif len(result) == 6:
            h, m, sec = int(result[:2]), int(result[2:4]), int(result[4:])
        else:
            return None

        h = h % 24
        m = min(m, 59)
        sec = min(sec, 59)
        return f"{h:02d}:{m:02d}:{sec:02d}"
    except (ValueError, IndexError):
        return None


def normalize_date(s):
    """Normalize format tanggal ke YYYY-MM-DD."""
    if not s:
        return None
    s = str(s).strip()
    m = re.match(r'(\d{1,2})[-/.](\d{1,2})[-/.](\d{2,4})', s)
    if m:
        d, mo, y = m.group(1), m.group(2), m.group(3)
        if len(y) == 2:
            y = '20' + y
        try:
            dt = datetime(int(y), int(mo), int(d))
            return dt.strftime('%Y-%m-%d')
        except ValueError:
            return None
    m = re.match(r'(\d{4})[-/.](\d{1,2})[-/.](\d{1,2})', s)
    if m:
        try:
            dt = datetime(int(m.group(1)), int(m.group(2)), int(m.group(3)))
            return dt.strftime('%Y-%m-%d')
        except ValueError:
            return None
    return None


def is_valid_name(text):
    """Cek apakah teks adalah nama karyawan (bukan header/garbage)."""
    if not text or len(text.strip()) < 3:
        return False
    t = text.strip().upper()
    if t in HEADER_WORDS:
        return False
    if re.match(r'^\d+$', t):  # Hanya angka
        return False
    if re.match(r'^[-|_\s]+$', t):  # Hanya simbol
        return False
    alpha_count = sum(1 for c in t if c.isalpha())
    if alpha_count < 3:
        return False
    return True


def load_template_names():
    """Load daftar nama karyawan dari template Excel sebagai referensi."""
    try:
        base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        for tpl_file in os.listdir(os.path.join(base_dir, 'TEMPLATE')):
            if tpl_file.endswith('.xlsx'):
                tpl_path = os.path.join(base_dir, 'TEMPLATE', tpl_file)
                import openpyxl
                wb = openpyxl.load_workbook(tpl_path, data_only=True)
                names = set()
                for sheet_name in wb.sheetnames:
                    ws = wb[sheet_name]
                    for row in ws.iter_rows(min_col=4, max_col=4):
                        val = row[0].value
                        if val and isinstance(val, str):
                            v = val.strip().upper()
                            if is_valid_name(v):
                                names.add(v)
                return sorted(names)
    except Exception:
        pass
    return []


def fuzzy_match_name(raw_name, valid_names, cutoff=0.55):
    """Cocokkan nama OCR dengan nama template menggunakan fuzzy matching."""
    if not valid_names:
        return raw_name.upper().strip()

    raw_upper = raw_name.upper().strip()

    # 1. Exact match
    if raw_upper in valid_names:
        return raw_upper

    # 2. Substring match (jika salah satu mengandung yang lain)
    for vn in valid_names:
        if raw_upper in vn or vn in raw_upper:
            return vn

    # 3. Word overlap (jika berbagi >1 kata yang signifikan)
    raw_words = set(w for w in raw_upper.split() if len(w) > 2)
    best_match = None
    best_count = 0
    for vn in valid_names:
        vn_words = set(w for w in vn.split() if len(w) > 2)
        overlap = raw_words & vn_words
        if len(overlap) > best_count:
            best_count = len(overlap)
            best_match = vn
    if best_count >= 1:
        return best_match

    # 4. Fuzzy ratio
    matches = difflib.get_close_matches(raw_upper, valid_names, n=1, cutoff=cutoff)
    if matches:
        return matches[0]

    return raw_upper


# ── Extraction methods ────────────────────────────────────────────────────────

def extract_via_pdfplumber(pdf_path):
    """
    Gunakan pdfplumber untuk ekstrak semua teks dari halaman.
    pdfplumber sangat akurat untuk PDF dengan teks embedded (termasuk CamScanner searchable PDF).
    Returns flat list of (name, date, scan1, scan2) tuples.
    """
    if not HAS_PDFPLUMBER:
        return None

    rows = []
    try:
        with pdfplumber.open(pdf_path) as pdf:
            for page in pdf.pages:
                # Coba ekstrak tabel dulu
                tables = page.extract_tables()
                if tables:
                    for table in tables:
                        for row in table:
                            if not row:
                                continue
                            # Bersihkan None
                            cells = [str(c).strip() if c else '' for c in row]
                            rows.append(cells)
                else:
                    # Fallback: ekstrak teks biasa per halaman
                    text = page.extract_text(x_tolerance=3, y_tolerance=3)
                    if text:
                        rows.append(('__TEXT__', text))
    except Exception as e:
        return None

    return rows if rows else None


def extract_via_pdftotext(pdf_path):
    """Gunakan pdftotext (poppler) untuk ekstrak teks terformat."""
    for args in [['pdftotext', '-layout', pdf_path, '-'],
                 ['pdftotext', pdf_path, '-']]:
        try:
            result = subprocess.run(args, capture_output=True, text=True, timeout=30)
            text = result.stdout
            if text and len(text.strip()) > 50:
                return text
        except Exception:
            continue
    return ''


def extract_via_tesseract(pdf_path):
    """Fallback: Gunakan Tesseract OCR."""
    if not HAS_TESSERACT:
        return ''
    try:
        pages = convert_from_path(pdf_path, dpi=250)
    except Exception:
        return ''

    full_text = ''
    for page_img in pages:
        page_gray = page_img.convert('L')
        for lang in ['ind+eng', 'eng']:
            try:
                text = pytesseract.image_to_string(
                    page_gray, lang=lang,
                    config='--psm 6 --oem 3'
                )
                full_text += '\n' + text
                break
            except Exception:
                continue
    return full_text


# ── Parsing ───────────────────────────────────────────────────────────────────

def parse_pdfplumber_rows(rows, valid_names):
    """Parse baris-baris dari pdfplumber (table format)."""
    emp_map = {}   # nama -> list records
    emp_order = [] # urutan nama supaya hasilnya konsisten

    def add_record(name, tanggal, scan1, scan2):
        if name not in emp_map:
            emp_map[name] = []
            emp_order.append(name)
        existing = next((r for r in emp_map[name] if r['tanggal'] == tanggal), None)
        if existing:
            if scan1 and not existing['scan1']: existing['scan1'] = scan1
            if scan2 and not existing['scan2']: existing['scan2'] = scan2
        else:
            try:
                d_obj = datetime.strptime(tanggal, '%Y-%m-%d')
                hari = get_hari(d_obj)
            except ValueError:
                hari = '-'
            emp_map[name].append({
                'tanggal': tanggal, 'hari': hari,
                'scan1': scan1, 'scan2': scan2,
            })

    for row in rows:
        # Apakah baris teks biasa?
        if len(row) == 2 and row[0] == '__TEXT__':
            parse_from_text(row[1], valid_names, add_record)
            continue

        # Baris tabel: kita cari kolom nama, tanggal, waktu
        if len(row) < 2:
            continue

        # Cari tanggal di semua kolom
        date_col = None
        tanggal = None
        for i, cell in enumerate(row):
            nd = normalize_date(cell)
            if nd:
                date_col = i
                tanggal = nd
                break

        if not tanggal:
            continue

        # Nama: kolom sebelum tanggal yang berisi huruf
        name_raw = ''
        for i in range(date_col - 1, -1, -1):
            candidate = str(row[i]).strip()
            if is_valid_name(candidate):
                name_raw = candidate
                break

        if not name_raw:
            continue

        name = fuzzy_match_name(name_raw, valid_names)

        # Waktu: kolom setelah tanggal
        times = []
        for i in range(date_col + 1, len(row)):
            t = normalize_time(str(row[i]).strip())
            if t:
                times.append(t)

        scan1 = times[0] if len(times) > 0 else None
        scan2 = times[1] if len(times) > 1 else None

        # Tebak scan1/scan2 dari jam
        if scan1 and not scan2:
            h = int(scan1.split(':')[0])
            if h >= 12:
                scan2, scan1 = scan1, None

        add_record(name, tanggal, scan1, scan2)

    return [{'nama': n, 'nip': '', 'records': emp_map[n]} for n in emp_order if emp_map[n]]


def parse_from_text(full_text, valid_names, add_record_fn=None):
    """
    Parse teks flat (pdftotext / tesseract).
    Format: [NAMA] [DD-MM-YYYY] [HH:MM:SS] [HH:MM:SS]
    Nama bisa di kolom kiri, tanggal dan waktu di kanan.
    """
    emp_map = {}
    emp_order = []

    def add_record(name, tanggal, scan1, scan2):
        if add_record_fn:
            add_record_fn(name, tanggal, scan1, scan2)
            return
        if name not in emp_map:
            emp_map[name] = []
            emp_order.append(name)
        existing = next((r for r in emp_map[name] if r['tanggal'] == tanggal), None)
        if existing:
            if scan1 and not existing['scan1']: existing['scan1'] = scan1
            if scan2 and not existing['scan2']: existing['scan2'] = scan2
        else:
            try:
                d_obj = datetime.strptime(tanggal, '%Y-%m-%d')
                hari = get_hari(d_obj)
            except ValueError:
                hari = '-'
            emp_map[name].append({
                'tanggal': tanggal, 'hari': hari,
                'scan1': scan1, 'scan2': scan2,
            })

    for line in full_text.split('\n'):
        line = line.strip()
        if not line or len(line) < 10:
            continue

        # Cari pola tanggal di baris
        date_match = re.search(r'(\d{1,2}[-/.]\d{1,2}[-/.]\d{2,4})', line)
        if not date_match:
            continue

        tanggal = normalize_date(date_match.group(1))
        if not tanggal:
            continue

        date_start = date_match.start()
        date_end = date_match.end()

        # Nama: teks sebelum tanggal
        name_part = line[:date_start].strip()
        # Bersihkan: buang angka & simbol di awal, sisakan huruf & spasi
        name_part = re.sub(r'^[^A-Za-z]+', '', name_part)
        name_part = re.sub(r'[^A-Za-z\s\'-]', ' ', name_part)
        name_part = ' '.join(name_part.split())  # normalize whitespace

        if not is_valid_name(name_part):
            continue

        name = fuzzy_match_name(name_part, valid_names)

        # Waktu: teks setelah tanggal
        time_part = line[date_end:].strip()
        # Ekstrak semua token yang terlihat seperti waktu
        time_tokens = re.findall(r'[\d:oOlIsS\.]{4,8}', time_part)
        times = []
        for tk in time_tokens:
            t = normalize_time(tk)
            if t:
                times.append(t)

        # Juga cek waktu yang mungkin tertulis sebelum tanggal (format tertentu)
        if not times:
            before_tokens = re.findall(r'[\d:oOlIsS\.]{4,8}', line[:date_start])
            for tk in before_tokens:
                t = normalize_time(tk)
                if t:
                    times.append(t)

        # Urutkan waktu
        try:
            times.sort(key=lambda t: datetime.strptime(t, '%H:%M:%S'))
        except Exception:
            pass

        scan1 = times[0] if len(times) >= 1 else None
        scan2 = times[1] if len(times) >= 2 else None

        # Kalau hanya satu scan, tebak berdasarkan jam
        if scan1 and not scan2:
            h = int(scan1.split(':')[0])
            if h >= 12:
                scan2, scan1 = scan1, None

        add_record(name, tanggal, scan1, scan2)

    if add_record_fn:
        return None
    return [{'nama': n, 'nip': '', 'records': emp_map[n]} for n in emp_order if emp_map[n]]


# ── Main ──────────────────────────────────────────────────────────────────────

def main():
    if len(sys.argv) < 2:
        _error("Usage: python extract_scanlog.py <pdf_path> [--debug]")

    pdf_path = sys.argv[1]
    debug_mode = '--debug' in sys.argv

    if not os.path.exists(pdf_path):
        _error(f"File tidak ditemukan: {pdf_path}")

    # Load nama dari template
    valid_names = load_template_names()

    # ── Step 1: pdfplumber (table extraction) ────────────────────────────────
    employees = []
    method = 'unknown'

    plumber_rows = extract_via_pdfplumber(pdf_path)
    if plumber_rows:
        method = 'pdfplumber'
        employees = parse_pdfplumber_rows(plumber_rows, valid_names)

    # ── Step 2: pdftotext fallback ───────────────────────────────────────────
    if not employees:
        method = 'pdftotext'
        text = extract_via_pdftotext(pdf_path)
        if text and len(text.strip()) > 30:
            employees = parse_from_text(text, valid_names) or []

    # ── Step 3: Tesseract fallback ───────────────────────────────────────────
    if not employees:
        method = 'tesseract'
        text = extract_via_tesseract(pdf_path)
        if text and len(text.strip()) > 30:
            employees = parse_from_text(text, valid_names) or []

    # ── Debug mode ───────────────────────────────────────────────────────────
    if debug_mode:
        debug_text = ''
        if plumber_rows:
            debug_text = '\n'.join([str(r) for r in plumber_rows[:50]])
        elif 'text' in dir():
            debug_text = text[:3000] if text else ''

        print(json.dumps({
            'debug': True,
            'method': method,
            'valid_names_count': len(valid_names),
            'employees_found': len(employees),
            'raw_text_preview': debug_text[:2000],
            'employees_preview': employees[:2] if employees else [],
        }, ensure_ascii=False))
        return

    # ── Output ───────────────────────────────────────────────────────────────
    if not employees:
        print(json.dumps({
            'error': f'Tidak ada data yang berhasil dibaca (method: {method}). '
                     f'Pastikan PDF berisi data teks (bukan hanya gambar kosong).'
        }, ensure_ascii=False))
        sys.exit(1)

    print(json.dumps(employees, ensure_ascii=False))


def _error(msg):
    print(json.dumps({'error': msg}))
    sys.exit(1)


if __name__ == '__main__':
    main()
