#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
extract_scanlog.py — Ekstrak data absensi dari PDF Kartu Scanlog (CamScanner).

Strategi:
  1. pdfplumber.extract_text()  — paling akurat, baca teks langsung dari PDF
  2. pdftotext (poppler)         — fallback
  3. Tesseract OCR               — last resort
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
    HAS_TESSERACT = True
except ImportError:
    HAS_TESSERACT = False

HARI_IDX = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU']

SKIP_WORDS = {
    'NIP', 'NAMA', 'JABATAN', 'TANGGAL', 'SCAN', 'PEGAWAI', 'DATA',
    'SCANLOG', 'KARTU', 'KANTOR', 'DEPARTEMEN', 'SCAN 1', 'SCAN 2',
    'TOTAL', 'NO', 'DIPINDAI', 'CAMSCANNER', 'DATASCANLOG',
}

# ── Helpers ───────────────────────────────────────────────────────────────────

def get_hari(date_obj):
    return HARI_IDX[date_obj.weekday()]


def normalize_time(s):
    if not s:
        return None
    s = str(s).strip()
    # Ganti separator titik/koma ke titik dua
    s = re.sub(r'[.,]', ':', s)
    # Sisakan hanya angka dan titik dua
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
    m = re.match(r'(\d{1,2})[-/.](\d{1,2})[-/.](\d{2,4})', s)
    if m:
        d, mo, y = int(m.group(1)), int(m.group(2)), m.group(3)
        if len(m.group(3)) == 2:
            y = '20' + m.group(3)
        else:
            y = int(m.group(3))
        try:
            return datetime(int(y), mo, d).strftime('%Y-%m-%d')
        except ValueError:
            return None
    return None


def is_valid_name(text):
    if not text or len(text.strip()) < 3:
        return False
    t = text.strip().upper()
    if t in SKIP_WORDS:
        return False
    if re.match(r'^[\d\s\-|_]+$', t):
        return False
    alpha = sum(1 for c in t if c.isalpha())
    return alpha >= 3


def load_template_names():
    """Ambil daftar nama karyawan dari file Excel template sebagai referensi."""
    try:
        import openpyxl
        base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        tpl_dir = os.path.join(base_dir, 'TEMPLATE')
        names = set()
        for fn in os.listdir(tpl_dir):
            if fn.endswith('.xlsx'):
                wb = openpyxl.load_workbook(os.path.join(tpl_dir, fn), data_only=True)
                for sheet in wb.worksheets:
                    for row in sheet.iter_rows(min_col=4, max_col=4):
                        v = row[0].value
                        if v and isinstance(v, str):
                            v = v.strip().upper()
                            if is_valid_name(v) and v not in SKIP_WORDS:
                                names.add(v)
        return sorted(names)
    except Exception:
        return []


def fuzzy_match_name(raw, valid_names):
    """Cocokkan nama hasil OCR ke nama baku dari template."""
    if not valid_names:
        return raw.upper().strip()

    raw_up = raw.upper().strip()

    # 1. Exact
    if raw_up in valid_names:
        return raw_up

    # 2. Substring (satu mengandung yang lain)
    for vn in valid_names:
        if raw_up == vn or raw_up in vn or vn in raw_up:
            return vn

    # 3. Word overlap
    raw_words = {w for w in raw_up.split() if len(w) > 2}
    best, best_n = None, 0
    for vn in valid_names:
        vn_words = {w for w in vn.split() if len(w) > 2}
        n = len(raw_words & vn_words)
        if n > best_n:
            best_n, best = n, vn
    if best_n >= 1:
        return best

    # 4. Fuzzy
    m = difflib.get_close_matches(raw_up, valid_names, n=1, cutoff=0.6)
    return m[0] if m else raw_up


# ── Extraction ────────────────────────────────────────────────────────────────

def extract_via_pdfplumber(pdf_path):
    """
    Gunakan pdfplumber.extract_text() — baca teks flat dengan layout spatial.
    JAUH lebih akurat dari extract_tables() untuk CamScanner PDF.
    """
    if not HAS_PDFPLUMBER:
        return ''
    try:
        texts = []
        with pdfplumber.open(pdf_path) as pdf:
            for page in pdf.pages:
                # x_tolerance=3 menjaga spasi antar kolom tetap terbaca
                t = page.extract_text(x_tolerance=3, y_tolerance=3)
                if t:
                    texts.append(t)
        return '\n'.join(texts)
    except Exception:
        return ''


def extract_via_pdftotext(pdf_path):
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


def extract_via_tesseract(pdf_path):
    if not HAS_TESSERACT:
        return ''
    try:
        pages = convert_from_path(pdf_path, dpi=250)
    except Exception:
        return ''
    full = ''
    for page in pages:
        for lang in ['ind+eng', 'eng']:
            try:
                full += '\n' + pytesseract.image_to_string(
                    page.convert('L'), lang=lang, config='--psm 6 --oem 3')
                break
            except Exception:
                continue
    return full


# ── Parsing ───────────────────────────────────────────────────────────────────

def parse_from_text(full_text, valid_names):
    """
    Parse teks flat (satu baris = satu record).
    Format per baris: NAMA   DD-MM-YYYY   HH:MM:SS   HH:MM:SS
    """
    emp_map    = {}  # name -> list of records
    emp_order  = []  # urutan kemunculan

    def add_record(name, tanggal, scan1, scan2):
        if name not in emp_map:
            emp_map[name] = []
            emp_order.append(name)
        # Cegah duplikat tanggal
        for rec in emp_map[name]:
            if rec['tanggal'] == tanggal:
                if scan1 and not rec['scan1']: rec['scan1'] = scan1
                if scan2 and not rec['scan2']: rec['scan2'] = scan2
                return
        try:
            d_obj = datetime.strptime(tanggal, '%Y-%m-%d')
            hari  = get_hari(d_obj)
        except ValueError:
            hari = '-'
        emp_map[name].append({
            'tanggal': tanggal,
            'hari'   : hari,
            'scan1'  : scan1,
            'scan2'  : scan2,
        })

    for line in full_text.splitlines():
        line = line.strip()
        if not line or len(line) < 8:
            continue

        # Cari tanggal di baris
        dm = re.search(r'(\d{1,2}[-/.]\d{1,2}[-/.]\d{2,4})', line)
        if not dm:
            continue

        tanggal = normalize_date(dm.group(1))
        if not tanggal:
            continue

        date_start = dm.start()
        date_end   = dm.end()

        # ── Nama: teks SEBELUM tanggal ──────────────────────────────────────
        name_raw = line[:date_start].strip()
        # Bersihkan: buang angka & simbol di awal, sisakan huruf & spasi
        name_raw = re.sub(r'^[^A-Za-z]+', '', name_raw)
        name_raw = re.sub(r'[^A-Za-z\s\'-]', ' ', name_raw)
        name_raw = ' '.join(name_raw.split())

        if not is_valid_name(name_raw):
            continue

        name = fuzzy_match_name(name_raw, valid_names)

        # ── Waktu: teks SETELAH tanggal ─────────────────────────────────────
        after = line[date_end:].strip()
        # Cari semua token yang berbentuk jam (HH:MM atau HH:MM:SS)
        time_tokens = re.findall(r'\b(\d{1,2}:\d{2}(?::\d{2})?)\b', after)

        times = []
        for tk in time_tokens:
            t = normalize_time(tk)
            if t:
                times.append(t)

        # Jika tidak ada waktu di belakang, coba cari di depan tanggal
        if not times:
            before = line[:date_start]
            time_tokens_b = re.findall(r'\b(\d{1,2}:\d{2}(?::\d{2})?)\b', before)
            for tk in time_tokens_b:
                t = normalize_time(tk)
                if t:
                    times.append(t)

        # Urutkan jam dari kecil ke besar
        try:
            times.sort(key=lambda t: datetime.strptime(t, '%H:%M:%S'))
        except Exception:
            pass

        scan1 = times[0] if len(times) >= 1 else None
        scan2 = times[1] if len(times) >= 2 else None

        # Kalau hanya satu waktu dan itu sore/malam, itu scan2
        if scan1 and not scan2:
            h = int(scan1.split(':')[0])
            if h >= 14:
                scan2, scan1 = scan1, None

        add_record(name, tanggal, scan1, scan2)

    return [
        {'nama': n, 'nip': '', 'records': emp_map[n]}
        for n in emp_order if emp_map[n]
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

    # ── Urutan ekstraksi ────────────────────────────────────────────────────
    full_text = ''
    method    = 'none'

    full_text = extract_via_pdfplumber(pdf_path)
    if full_text and len(full_text.strip()) > 30:
        method = 'pdfplumber'
    else:
        full_text = extract_via_pdftotext(pdf_path)
        if full_text and len(full_text.strip()) > 30:
            method = 'pdftotext'
        else:
            full_text = extract_via_tesseract(pdf_path)
            method = 'tesseract'

    # ── Debug mode ──────────────────────────────────────────────────────────
    if debug_mode:
        employees = parse_from_text(full_text, valid_names) if full_text else []
        print(json.dumps({
            'debug'              : True,
            'method'             : method,
            'valid_names_count'  : len(valid_names),
            'text_length'        : len(full_text),
            'raw_text'           : full_text[:3000],
            'employees_found'    : len(employees),
            'employees_preview'  : employees[:3],
        }, ensure_ascii=False))
        return

    # ── Parse & output ──────────────────────────────────────────────────────
    if not full_text or len(full_text.strip()) < 10:
        _err(f'Tidak bisa membaca teks dari PDF (method: {method}).')

    employees = parse_from_text(full_text, valid_names)

    if not employees:
        _err(
            f'Tidak ada data karyawan yang berhasil dibaca (method: {method}). '
            f'Preview teks: {full_text[:300]}'
        )

    print(json.dumps(employees, ensure_ascii=False))


def _err(msg):
    print(json.dumps({'error': msg}))
    sys.exit(1)


if __name__ == '__main__':
    main()
