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
    s = str(time_str).strip()
    # Ganti karakter mirip angka
    s = re.sub(r'[oOqQD]', '0', s)
    s = re.sub(r'[lI|i!\]\[\{\}tT]', '1', s)
    s = re.sub(r'[zZ]', '2', s)
    s = re.sub(r'[aA\+]', '4', s) # kadang 4 dibaca A atau +
    s = re.sub(r'[sS]', '5', s)
    s = re.sub(r'[bB]', '8', s)
    s = re.sub(r'[gG]', '9', s)
    s = re.sub(r'[rR]', '7', s)
    
    # Hanya sisakan angka
    s = re.sub(r'[^0-9:]', '', s)
    
    if len(s) == 4 and ':' not in s:  # e.g., 0750
        s = f"{s[:2]}:{s[2:]}:00"
    elif len(s) == 6 and ':' not in s: # e.g. 075037
        s = f"{s[:2]}:{s[2:4]}:{s[4:]}"
    elif len(s) >= 4:
        parts = s.split(':')
        if len(parts) == 2:
            s = f"{parts[0].zfill(2)}:{parts[1].zfill(2)}:00"
        elif len(parts) >= 3:
            s = f"{parts[0].zfill(2)}:{parts[1].zfill(2)}:{parts[2][:2].zfill(2)}"
        else:
            return None
    else:
        return None
        
    if len(s) == 8:
        try:
            h, m, sec = map(int, s.split(':'))
            # Kadang OCR baca 07 jadi 17 atau 70. Batasi agar masuk akal
            if h >= 24: h = h % 24
            if m >= 60: m = 59
            if sec >= 60: sec = 0
            return f"{h:02d}:{m:02d}:{sec:02d}"
        except:
            pass
    return None

def parse_text(full_text):
    employees = []
    current_emp = None

    lines = full_text.split('\n')
    
    for line in lines:
        line = line.strip()
        if not line:
            continue

        # Format umum dari OCR CamScanner untuk tabel: 
        # [Karakter sampah] [NAMA PEGAWAI] [TANGGAL] [JAM1] [JAM2]
        # Contoh: "20 INasaR SUPRIANTO | — | __|or-04-2026 Jarang [19:07:10"
        
        # Ekstrak dengan regex memecah bagian line
        # Mencari pola tanggal DD-MM-YYYY dulu
        m = re.search(r'(.*?)\s*(\d{1,2}[-/\.]\d{1,2}[-/\.]\d{2,4})\s*(.*)', line)
        if m:
            name_raw = m.group(1).strip()
            date_raw = m.group(2).strip()
            times_raw = m.group(3).strip()
            
            # ── 1. Ekstrak & Rapihkan Nama ──
            # Buang angka, simbol aneh di awal nama
            name_clean = re.sub(r'^[^A-Za-z]+', '', name_raw) 
            name_clean = re.sub(r'[^A-Za-z\s\.\']', '', name_clean).upper().strip()
            
            # Filter kata-kata header
            if name_clean in ('NIP', 'NAMA', 'JABATAN', 'TANGGAL', 'SCAN', 'PEGAWAI', 'DATA SCANLOG'):
                continue
                
            if len(name_clean) >= 3:
                # Jika nama cukup valid
                # Cek apakah ini karyawan baru atau masih yang lama (berdasarkan kemiripan suku kata)
                if current_emp:
                    c_parts = set(current_emp['nama'].split())
                    n_parts = set(name_clean.split())
                    # Jika ada irisan kata yang panjang (minimal 3 huruf) atau panjang total
                    has_intersection = any(len(word) > 2 for word in c_parts.intersection(n_parts))
                    
                    if not has_intersection and len(name_clean) > 4:
                        # Nama baru
                        if current_emp['records']:
                            employees.append(current_emp)
                        elif len(current_emp['nama']) < len(name_clean):
                            # Jika belum ada record, ini mungkin koreksi nama yang lebih lengkap
                            c_parts = set() # skip
                        else:
                            pass # abaikan dan buat yang baru
                        
                        if not c_parts.intersection(n_parts):
                            current_emp = {'nama': name_clean, 'nip': '', 'records': []}
                else:
                    current_emp = {'nama': name_clean, 'nip': '', 'records': []}
                    
            if not current_emp:
                continue

            # ── 2. Tanggal ──
            tanggal = normalize_date(date_raw)
            if not tanggal:
                continue

            try:
                date_obj = datetime.strptime(tanggal, '%Y-%m-%d')
                hari = get_hari(date_obj)
            except ValueError:
                hari = '-'
                
            # ── 3. Waktu ──
            # Waktu bisa berada di dalam name_raw (sebelum tanggal) jika format berantakan
            # atau di times_raw (sesudah tanggal). Kita gabung saja teks yang bukan nama.
            remains = name_raw.replace(current_emp['nama'], '') + " " + times_raw
            remains = re.sub(r'[A-Za-z]+', ' ', remains) # Buang sisa huruf (nama bulan dll)
            
            # Pecah berdasarkan spasi atau simbol pembatas
            chunks = re.split(r'[\s\|\[\]\(\)_]+', remains)
            times = []
            for c in chunks:
                if len(c) >= 4: # Minimal 4 karakter untuk waktu
                    t = normalize_time(c)
                    if t: times.append(t)
                    
            # Tentukan scan 1 dan 2
            scan1 = None
            scan2 = None
            
            if len(times) == 1:
                # Tebak apakah ini masuk atau pulang berdasarkan jam
                h = int(times[0].split(':')[0])
                if h < 14:
                    scan1 = times[0]
                else:
                    scan2 = times[0]
            elif len(times) >= 2:
                # Urutkan berdasarkan waktu
                try:
                    times.sort(key=lambda t: datetime.strptime(t, '%H:%M:%S'))
                except:
                    pass
                scan1 = times[0]
                scan2 = times[-1]
                
            # Simpan atau update record
            existing = next((r for r in current_emp['records'] if r['tanggal'] == tanggal), None)
            if existing:
                if scan1 and not existing['scan1']: existing['scan1'] = scan1
                if scan2 and not existing['scan2']: existing['scan2'] = scan2
                elif scan1 and not existing['scan2'] and scan1 != existing['scan1']: 
                    existing['scan2'] = scan1
            else:
                current_emp['records'].append({
                    'tanggal': tanggal,
                    'hari':    hari,
                    'scan1':   scan1,
                    'scan2':   scan2,
                })
                
    # Jangan lupa masukkan karyawan terakhir
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
