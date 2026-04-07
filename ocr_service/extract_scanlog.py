#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
extract_scanlog.py — Baca PDF Kartu Scanlog, output JSON
"""

import sys
import re
import json
import os
import subprocess
from datetime import datetime

# ── Try to import OCR libraries ──────────────────────────────────────────────
HAS_OCR = False
try:
    from pdf2image import convert_from_path
    import pytesseract
    from PIL import Image
    HAS_OCR = True
except ImportError:
    pass

HARI_FROM_DATE = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU']

def get_hari(date_obj):
    return HARI_FROM_DATE[date_obj.weekday()]

def normalize_time(time_str):
    if not time_str:
        return None
    s = time_str.strip()
    s = re.sub(r'[oOqQ]', '0', s)
    s = re.sub(r'[lI|]', '1', s)
    s = re.sub(r'[^0-9:]', '', s)
    parts = s.split(':')
    if len(parts) == 2:
        s = f"{parts[0].zfill(2)}:{parts[1].zfill(2)}:00"
    elif len(parts) == 3:
        s = f"{parts[0].zfill(2)}:{parts[1].zfill(2)}:{parts[2].zfill(2)}"
    else:
        return None
    try:
        datetime.strptime(s, '%H:%M:%S')
        return s
    except ValueError:
        return None

def normalize_date(date_str):
    if not date_str:
        return None
    date_str = date_str.strip().replace(' ', '-')
    # Handle various separators
    m = re.match(r'(\d{1,2})[-/.](\d{1,2})[-/.](\d{2,4})', date_str)
    if m:
        d, mo, y = m.group(1), m.group(2), m.group(3)
        if len(y) == 2:
            y = '20' + y
        return f"{y}-{mo.zfill(2)}-{d.zfill(2)}"
    m = re.match(r'(\d{4})[-/.](\d{1,2})[-/.](\d{1,2})', date_str)
    if m:
        return f"{m.group(1)}-{m.group(2).zfill(2)}-{m.group(3).zfill(2)}"
    return None

def extract_via_pdftotext(pdf_path):
    """Gunakan pdftotext (poppler) untuk ekstrak teks dari PDF."""
    try:
        result = subprocess.run(
            ['pdftotext', '-layout', pdf_path, '-'],
            capture_output=True, text=True, timeout=30
        )
        text = result.stdout
        if text and len(text.strip()) > 50:
            return text
    except Exception:
        pass
    # Coba tanpa -layout
    try:
        result = subprocess.run(
            ['pdftotext', pdf_path, '-'],
            capture_output=True, text=True, timeout=30
        )
        return result.stdout
    except Exception:
        pass
    return ''

def extract_via_tesseract(pdf_path):
    """Fallback: Gunakan Tesseract OCR."""
    if not HAS_OCR:
        return ''
    try:
        pages = convert_from_path(pdf_path, dpi=250)
    except Exception as e:
        return ''

    full_text = ''
    for page_img in pages:
        page_gray = page_img.convert('L')
        for lang in ['ind+eng', 'eng', 'ind']:
            try:
                text = pytesseract.image_to_string(
                    page_gray,
                    lang=lang,
                    config='--psm 6 --oem 3'
                )
                full_text += '\n' + text
                break
            except Exception:
                continue
    return full_text

def parse_text(full_text):
    """
    Parse teks Kartu Scanlog ke struktur data.
    Mendukung berbagai format output OCR/pdftotext.
    """
    employees = []
    current_emp = None

    lines = full_text.split('\n')

    for line in lines:
        raw_line = line
        line = line.strip()
        if not line:
            continue

        # ── Deteksi nama karyawan ─────────────────────────────────────────
        # Format 1: "Nama    : NASAR SUPRIANTO"
        # Format 2: "NAMA: NASAR SUPRIANTO"
        # Format 3: "Nama : NASAR SUPRIANTO"
        nama_match = re.search(
            r'(?:^|\s)(?:nama|name)\s*:+\s*(.{3,40}?)(?:\s*$)',
            line, re.IGNORECASE
        )
        if nama_match:
            nama_raw = nama_match.group(1).strip()
            # Bersihkan karakter non-nama
            nama = re.sub(r'[^A-Za-z\s\.\-\']', '', nama_raw).strip().upper()
            if len(nama) >= 3 and not re.match(r'^(NIP|ID|NO|JABATAN|DEPT)', nama):
                if current_emp and current_emp['records']:
                    employees.append(current_emp)
                elif current_emp and not current_emp['records']:
                    # Update nama jika belum ada records
                    current_emp['nama'] = nama
                    continue
                current_emp = {'nama': nama, 'nip': '', 'records': []}
                continue

        # ── Deteksi NIP ────────────────────────────────────────────────────
        nip_match = re.search(
            r'(?:nip|nik|id)\s*:+\s*(\w+)',
            line, re.IGNORECASE
        )
        if nip_match and current_emp is not None:
            val = nip_match.group(1).strip()
            if val.lower() not in ('none', 'null', '-', ''):
                current_emp['nip'] = val
            continue

        # ── Deteksi baris tanggal + waktu ────────────────────────────────
        # Format: "31-03-2026  07:50:37  20:04:29"
        # Format: "31-03-2026 07:50:37"
        # Format: "31/03/2026 07.50.37 20.04.29"
        dt_match = re.search(
            r'(\d{1,2}[\-/\.]\d{1,2}[\-/\.]\d{2,4})'
            r'\s+'
            r'(\d{1,2}[:\.\s]\d{2}(?:[:\.\s]\d{2})?)'
            r'(?:\s+(\d{1,2}[:\.\s]\d{2}(?:[:\.\s]\d{2})?))?',
            line
        )
        if dt_match and current_emp is not None:
            date_raw  = dt_match.group(1)
            scan1_raw = dt_match.group(2)
            scan2_raw = dt_match.group(3)

            # Normalize dot/space separators in times
            scan1_raw = re.sub(r'[\.\s]', ':', scan1_raw.strip()) if scan1_raw else None
            scan2_raw = re.sub(r'[\.\s]', ':', scan2_raw.strip()) if scan2_raw else None

            tanggal = normalize_date(date_raw)
            scan1   = normalize_time(scan1_raw)
            scan2   = normalize_time(scan2_raw) if scan2_raw else None

            if tanggal and scan1:
                try:
                    date_obj = datetime.strptime(tanggal, '%Y-%m-%d')
                    hari = get_hari(date_obj)
                except ValueError:
                    hari = '-'

                existing = next(
                    (r for r in current_emp['records'] if r['tanggal'] == tanggal),
                    None
                )
                if existing:
                    if scan2 and not existing['scan2']:
                        existing['scan2'] = scan2
                    elif scan1 and not existing['scan2']:
                        existing['scan2'] = scan1
                else:
                    current_emp['records'].append({
                        'tanggal': tanggal,
                        'hari':    hari,
                        'scan1':   scan1,
                        'scan2':   scan2,
                    })
            continue

        # ── Detect standalone time on next line (Scan 2 tanpa tanggal) ────
        # Beberapa format PDF output waktu di baris terpisah setelah tanggal
        if current_emp and current_emp['records']:
            time_only = re.fullmatch(
                r'(\d{1,2}[:\.\s]\d{2}(?:[:\.\s]\d{2})?)',
                line.strip()
            )
            if time_only:
                last_rec = current_emp['records'][-1]
                if not last_rec['scan2']:
                    last_rec['scan2'] = normalize_time(
                        re.sub(r'[\.\s]', ':', time_only.group(1))
                    )

    if current_emp and current_emp['records']:
        employees.append(current_emp)

    return employees

def main():
    if len(sys.argv) < 2:
        error_out("Usage: python extract_scanlog.py <path_to_pdf>")

    pdf_path = sys.argv[1]
    debug_mode = '--debug' in sys.argv

    if not os.path.exists(pdf_path):
        error_out(f"File tidak ditemukan: {pdf_path}")

    # ── Step 1: Coba pdftotext (lebih akurat untuk CamScanner PDF) ─────────
    full_text = extract_via_pdftotext(pdf_path)
    method = 'pdftotext'

    # ── Step 2: Fallback ke Tesseract jika teks tidak cukup ────────────────
    if not full_text or len(full_text.strip()) < 30:
        full_text = extract_via_tesseract(pdf_path)
        method = 'tesseract'

    if debug_mode:
        # Mode debug: kembalikan raw text untuk diagnosis
        print(json.dumps({
            "debug": True,
            "method": method,
            "text_length": len(full_text),
            "raw_text": full_text[:3000]
        }, ensure_ascii=False))
        return

    # ── Step 3: Parse ───────────────────────────────────────────────────────
    employees = parse_text(full_text)

    if not employees:
        print(json.dumps({
            "error": f"Tidak ada data karyawan yang berhasil dibaca (method: {method}, chars: {len(full_text)}). "
                     f"Preview teks: {full_text[:400]}"
        }, ensure_ascii=False))
        sys.exit(1)

    print(json.dumps(employees, ensure_ascii=False))

def error_out(msg):
    print(json.dumps({"error": msg}))
    sys.exit(1)

if __name__ == '__main__':
    main()
