#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
preprocess_image.py — Pre-processing gambar sebelum Tesseract OCR.

Strategi:
  1. Grayscale conversion
  2. Deskew (koreksi sudut miring akibat scan)
  3. Denoise ringan
  4. Binarisasi adaptif (Otsu)
  5. Upscale jika resolusi rendah

Pakai OpenCV bila tersedia, fallback ke Pillow bila tidak ada.
"""

import math
import struct
import zlib

try:
    import cv2
    import numpy as np
    HAS_CV2 = True
except ImportError:
    HAS_CV2 = False

try:
    from PIL import Image, ImageFilter, ImageOps, ImageEnhance
    HAS_PIL = True
except ImportError:
    HAS_PIL = False


# ── OpenCV-based pipeline ────────────────────────────────────────────────────

def _cv2_deskew(img_gray):
    """Deteksi sudut miring dan koreksi menggunakan HoughLines."""
    try:
        edges = cv2.Canny(img_gray, 50, 150, apertureSize=3)
        lines = cv2.HoughLines(edges, 1, math.pi / 180, threshold=150)
        if lines is None:
            return img_gray
        angles = []
        for line in lines[:30]:
            rho, theta = line[0]
            # Sudut dalam derajat, relative ke garis horizontal
            angle_deg = math.degrees(theta) - 90
            if abs(angle_deg) < 20:  # abaikan sudut ekstrem
                angles.append(angle_deg)
        if not angles:
            return img_gray
        median_angle = sorted(angles)[len(angles) // 2]
        if abs(median_angle) < 0.3:  # sudah cukup lurus
            return img_gray
        h, w = img_gray.shape
        center = (w // 2, h // 2)
        M = cv2.getRotationMatrix2D(center, median_angle, 1.0)
        rotated = cv2.warpAffine(
            img_gray, M, (w, h),
            flags=cv2.INTER_LINEAR,
            borderMode=cv2.BORDER_REPLICATE
        )
        return rotated
    except Exception:
        return img_gray


def preprocess_cv2(pil_image):
    """
    Pre-process gambar dengan OpenCV.
    Input : PIL Image
    Output: PIL Image (siap untuk pytesseract)
    """
    img = cv2.cvtColor(np.array(pil_image.convert('RGB')), cv2.COLOR_RGB2GRAY)

    # Deskew
    img = _cv2_deskew(img)

    # Denoise ringan (median blur — efektif untuk noise scan)
    img = cv2.medianBlur(img, 3)

    # Binarisasi adaptif Otsu
    _, img = cv2.threshold(img, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)

    # Upscale jika gambar terlalu kecil (< 1200px lebar)
    h, w = img.shape
    if w < 1200:
        scale = 1200 / w
        img = cv2.resize(img, (int(w * scale), int(h * scale)),
                         interpolation=cv2.INTER_CUBIC)

    # Convert kembali ke PIL
    return Image.fromarray(img)


# ── Pillow-only fallback pipeline ────────────────────────────────────────────

def preprocess_pillow(pil_image):
    """
    Pre-process gambar dengan Pillow saja (tanpa OpenCV).
    Lebih sederhana tapi tetap meningkatkan akurasi OCR.
    """
    # Grayscale
    img = pil_image.convert('L')

    # Tingkatkan kontras
    img = ImageOps.autocontrast(img, cutoff=2)
    enhancer = ImageEnhance.Contrast(img)
    img = enhancer.enhance(2.0)

    # Sharpening
    img = img.filter(ImageFilter.SHARPEN)

    # Upscale ke minimal 300 DPI equivalent (asumsi input ~150 DPI dari pdf2image)
    w, h = img.size
    if w < 1500:
        scale = 1500 / w
        img = img.resize((int(w * scale), int(h * scale)), Image.LANCZOS)

    return img


# ── Public API ────────────────────────────────────────────────────────────────

def preprocess(pil_image):
    """
    Pre-process gambar sebelum OCR.
    Otomatis pilih antara OpenCV (lebih baik) atau Pillow (fallback).

    Args:
        pil_image: PIL.Image.Image — gambar halaman PDF yang sudah dikonversi

    Returns:
        PIL.Image.Image — gambar yang sudah diproses, siap untuk pytesseract
    """
    if HAS_CV2:
        return preprocess_cv2(pil_image)
    elif HAS_PIL:
        return preprocess_pillow(pil_image)
    else:
        return pil_image  # kembalikan apa adanya jika tidak ada library


def backend_info():
    """Return info backend yang dipakai."""
    if HAS_CV2:
        return f"opencv ({cv2.__version__})"
    elif HAS_PIL:
        return "pillow-only"
    return "none"
