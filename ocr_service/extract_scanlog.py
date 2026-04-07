#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
extract_scanlog.py
==================
Membaca PDF Kartu Scanlog (CamScanner) via OCR dan mengekstrak data absensi.

Usage:
    python extract_scanlog.py <path_to_pdf>

Output (stdout):
    JSON array of records:
    [
      {
        "nama": "NASAR SUPRIANTO",
        "nip": "1",
        "records": [
          {"tanggal": "2026-04-01", "hari": "RABU", "scan1": "07:43:19", "scan2": "19:07:10"},
          ...
        ]
      },
      ...
    ]
"""

import sys
import re
import json
import os
from datetime import datetime

try:
    from pdf2image import convert_from_path
    import pytesseract
    from PIL import Image
except ImportError as e:
    print(json.dumps({"error": f"Library tidak tersedia: {e}. Pastikan requirements.txt telah diinstall."}))
    sys.exit(1)

# ─── Konfigurasi Tesseract ───────────────────────────────────────────────────
# Di Linux (Railway), tesseract biasanya ada di /usr/bin/tesseract
# Di Windows, sesuaikan path-nya jika perlu
if sys.platform == 'win32':
    # Coba temukan tesseract di Windows
    possible_paths = [
        r'C:\Program Files\Tesseract-OCR\tesseract.exe',
        r'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe',
    ]
    for p in possible_paths:
        if os.path.exists(p):
            pytesseract.pytesseract.tesseract_cmd = p
            break

HARI_MAP = {
    'SENIN': 'SENIN', 'SELASA': 'SELASA', 'RABU': 'RABU',
    'KAMIS': 'KAMIS', 'JUMAT': 'JUMAT', 'JUM\'AT': 'JUMAT',
    'SABTU': 'SABTU', 'MINGGU': 'MINGGU',
    'MONDAY': 'SENIN', 'TUESDAY': 'SELASA', 'WEDNESDAY': 'RABU',
    'THURSDAY': 'KAMIS', 'FRIDAY': 'JUMAT', 'SATURDAY': 'SABTU', 'SUNDAY': 'MINGGU',
}

HARI_FROM_DATE = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU']

def get_hari(date_obj):
    """Dapatkan nama hari dalam bahasa Indonesia dari objek date."""
    return HARI_FROM_DATE[date_obj.weekday()]

def normalize_time(time_str):
    """Normalisasi string waktu ke format HH:MM:SS."""
    if not time_str:
        return None
    # Bersihkan karakter aneh dari OCR
    time_str = time_str.strip()
    time_str = re.sub(r'[oO]', '0', time_str)  # 'O' OCR error → '0'
    time_str = re.sub(r'[lI]', '1', time_str)  # 'l' atau 'I' → '1'
    time_str = re.sub(r'[^0-9:]', '', time_str)
    # Format HH:MM or HH:MM:SS
    parts = time_str.split(':')
    if len(parts) == 2:
        time_str = f"{parts[0].zfill(2)}:{parts[1].zfill(2)}:00"
    elif len(parts) == 3:
        time_str = f"{parts[0].zfill(2)}:{parts[1].zfill(2)}:{parts[2].zfill(2)}"
    else:
        return None
    # Validasi
    try:
        datetime.strptime(time_str, '%H:%M:%S')
        return time_str
    except ValueError:
        return None

def normalize_date(date_str):
    """Normalisasi string tanggal ke format YYYY-MM-DD."""
    if not date_str:
        return None
    date_str = date_str.strip()
    # Pola: DD-MM-YYYY atau DD/MM/YYYY atau DD MM YYYY
    patterns = [
        r'(\d{1,2})[-/\s](\d{1,2})[-/\s](\d{4})',
        r'(\d{4})[-/](\d{1,2})[-/](\d{1,2})',
    ]
    for pat in patterns:
        m = re.match(pat, date_str)
        if m:
            groups = m.groups()
            if len(groups[0]) == 4:  # YYYY-MM-DD
                return f"{groups[0]}-{groups[1].zfill(2)}-{groups[2].zfill(2)}"
            else:  # DD-MM-YYYY
                return f"{groups[2]}-{groups[1].zfill(2)}-{groups[0].zfill(2)}"
    return None

def parse_text_to_records(full_text):
    """
    Parse teks OCR dari Kartu Scanlog menjadi struktur data.
    Format yang dikenali:
      - Baris "Nama    : NAMA_KARYAWAN"
      - Baris tanggal + scan time: "DD-MM-YYYY  HH:MM:SS  HH:MM:SS"
    """
    employees = []
    current_emp = None

    # Split per baris
    lines = full_text.split('\n')
    i = 0
    while i < len(lines):
        line = lines[i].strip()

        # ── Deteksi nama karyawan ──────────────────────────────────────────
        # Pola: "Nama    : NASAR SUPRIANTO" atau "NAMA : NASAR SUPRIANTO"
        nama_match = re.search(
            r'(?:nama|name)\s*[:]\s*(.+)',
            line, re.IGNORECASE
        )
        if nama_match:
            nama = nama_match.group(1).strip().upper()
            # Bersihkan karakter aneh
            nama = re.sub(r'[^A-Z\s\.\-]', '', nama).strip()
            if nama and len(nama) > 2:
                if current_emp and current_emp['records']:
                    employees.append(current_emp)
                current_emp = {
                    'nama': nama,
                    'nip': '',
                    'records': []
                }
            i += 1
            continue

        # ── Deteksi NIP ───────────────────────────────────────────────────
        nip_match = re.search(r'(?:nip|nik|id)\s*[:]\s*(\w+)', line, re.IGNORECASE)
        if nip_match and current_emp:
            current_emp['nip'] = nip_match.group(1).strip()
            i += 1
            continue

        # ── Deteksi baris data (tanggal + jam) ───────────────────────────
        # Pola: "31-03-2026  07:50:37  20:04:29"
        # Atau:  "31-03-2026  07:50:37"
        date_time_match = re.search(
            r'(\d{1,2}[-/]\d{1,2}[-/]\d{2,4})\s+'
            r'(\d{1,2}:\d{2}(?::\d{2})?)'
            r'(?:\s+(\d{1,2}:\d{2}(?::\d{2})?))?' ,
            line
        )
        if date_time_match and current_emp is not None:
            tanggal_raw = date_time_match.group(1)
            scan1_raw   = date_time_match.group(2)
            scan2_raw   = date_time_match.group(3)

            tanggal = normalize_date(tanggal_raw)
            scan1   = normalize_time(scan1_raw)
            scan2   = normalize_time(scan2_raw) if scan2_raw else None

            if tanggal:
                try:
                    date_obj = datetime.strptime(tanggal, '%Y-%m-%d')
                    hari = get_hari(date_obj)
                except ValueError:
                    hari = '-'

                # Cek apakah tanggal sudah ada (update scan2 jika sudah ada scan1)
                existing = next((r for r in current_emp['records'] if r['tanggal'] == tanggal), None)
                if existing:
                    if scan1 and not existing['scan2']:
                        existing['scan2'] = scan1
                    elif scan2:
                        existing['scan2'] = scan2
                else:
                    current_emp['records'].append({
                        'tanggal': tanggal,
                        'hari': hari,
                        'scan1': scan1,
                        'scan2': scan2,
                    })
            i += 1
            continue

        i += 1

    # Simpan karyawan terakhir
    if current_emp and current_emp['records']:
        employees.append(current_emp)

    return employees

def ocr_pdf(pdf_path):
    """Konversi PDF ke gambar dan OCR setiap halaman."""
    if not os.path.exists(pdf_path):
        return {"error": f"File tidak ditemukan: {pdf_path}"}

    try:
        # Konversi PDF ke gambar (DPI 300 untuk akurasi OCR lebih baik)
        pages = convert_from_path(pdf_path, dpi=300)
    except Exception as e:
        return {"error": f"Gagal konversi PDF ke gambar: {e}"}

    full_text = ""
    for page_num, page_img in enumerate(pages):
        # Preprocessing: grayscale untuk akurasi OCR lebih baik
        page_gray = page_img.convert('L')
        # OCR dengan bahasa Indonesia + Inggris
        text = pytesseract.image_to_string(
            page_gray,
            lang='ind+eng',
            config='--psm 6 --oem 3'
        )
        full_text += f"\n\n=== HALAMAN {page_num + 1} ===\n" + text

    return full_text

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Usage: python extract_scanlog.py <path_to_pdf>"}))
        sys.exit(1)

    pdf_path = sys.argv[1]

    # OCR
    result = ocr_pdf(pdf_path)
    if isinstance(result, dict) and 'error' in result:
        print(json.dumps(result))
        sys.exit(1)

    full_text = result

    # Parse
    employees = parse_text_to_records(full_text)

    if not employees:
        # Kembalikan raw OCR text juga untuk debugging
        print(json.dumps({
            "error": "Tidak ada data yang berhasil di-parse dari PDF.",
            "raw_text_preview": full_text[:2000]
        }))
        sys.exit(1)

    print(json.dumps(employees, ensure_ascii=False))

if __name__ == '__main__':
    main()
