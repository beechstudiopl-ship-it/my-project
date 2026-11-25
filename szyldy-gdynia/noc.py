#!/usr/bin/env python3
"""Efekt nocy i podświetlenia liter (halo) na zdjęciu elewacji.
Użycie: python3 noc.py zdjecie.jpg strona/img/hero-index.webp noc-pelne.webp
Obszar liter (box) i kadr tła są ustawione pod zdjęcie Orłowo Square – przy innym zdjęciu popraw je w kodzie."""
import sys
import numpy as np
from PIL import Image, ImageFilter

src, out_hero, out_full = sys.argv[1], sys.argv[2], sys.argv[3]
UNLIT = len(sys.argv) > 4 and sys.argv[4] == '--bez-swiatla'
im = Image.open(src).convert("RGB")
W, H = im.size
a = np.asarray(im).astype(np.float32)
r, g, b = a[..., 0], a[..., 1], a[..., 2]
L = 0.299 * r + 0.587 * g + 0.114 * b

# niebo: wyraźnie niebieskie i jasne
sky0 = ((b - r) > 40) & (b > 140)
sky0 = np.asarray(Image.fromarray((sky0 * 255).astype(np.uint8)).filter(ImageFilter.MinFilter(3)).filter(ImageFilter.MaxFilter(3))) > 0
sky = np.cumprod(sky0, axis=0).astype(bool)  # tylko obszar ciągły od górnej krawędzi
sky_img = Image.fromarray((sky * 255).astype(np.uint8)).filter(ImageFilter.MedianFilter(5))
sky_m = np.asarray(sky_img.filter(ImageFilter.GaussianBlur(1.5))).astype(np.float32) / 255

# litery: ciemne piksele w obszarze logo, bez cienkich linii paneli (otwarcie morfologiczne)
dark = (L < 125) & ~sky
box = np.zeros_like(dark); box[470:960, 500:1470] = True
let = Image.fromarray(((dark & box) * 255).astype(np.uint8))
let = let.filter(ImageFilter.MinFilter(5)).filter(ImageFilter.MaxFilter(5))
let_m = np.asarray(let).astype(np.float32) / 255

# noc: ciemna, chłodna elewacja + nocne niebo
yy = np.linspace(0, 1, H)[:, None]
sky_col = np.stack([10 + 10 * yy, 18 + 14 * yy, 30 + 18 * yy], -1) * np.ones((H, W, 1))
fac = np.stack([L * .17, L * .20, L * .25], -1)
night = fac * (1 - sky_m[..., None]) + sky_col * sky_m[..., None]

# poświata LED na elewację (halo) – kilka warstw rozmycia
amber = np.array([255, 178, 70], np.float32)
glow = np.zeros((H, W), np.float32)
for rad, w in ((60, .55), (26, .8), (10, .9)):
    gl = np.asarray(let.filter(ImageFilter.MaxFilter(5)).filter(ImageFilter.GaussianBlur(rad))).astype(np.float32) / 255
    glow += gl * w
glow = np.clip(glow, 0, 1.4) * (1 - sky_m)
lit = night + glow[..., None] * amber[None, None, :] * (0 if UNLIT else .85)
# ściana tuż przy literach najjaśniejsza, ciepła
near = np.asarray(let.filter(ImageFilter.MaxFilter(9)).filter(ImageFilter.GaussianBlur(4))).astype(np.float32) / 255
lit = lit + (near * (1 - let_m))[..., None] * np.array([255, 214, 150], np.float32) * (0 if UNLIT else .55)

# lico liter: ciemny metal z delikatną ciepłą krawędzią
face = np.stack([L * .28 + 10, L * .28 + 12, L * .30 + 16], -1)
rim = np.clip(let_m - np.asarray(let.filter(ImageFilter.MinFilter(3))).astype(np.float32) / 255, 0, 1)
face = face + rim[..., None] * np.array([120, 85, 35], np.float32) * (0 if UNLIT else 1)
res = lit * (1 - let_m[..., None]) + face * let_m[..., None]
res = np.clip(res, 0, 255).astype(np.uint8)
full = Image.fromarray(res)
full.save(out_full, "WEBP", quality=82)

# kadr do tła górnej części strony: szyld po prawej, płynne przejście w granat
navy = np.array([15, 29, 39], np.float32)
crop = full.crop((380, 300, 1600, 1100)).resize((1000, 656), Image.LANCZOS)
cw, ch = crop.size
hero = np.ones((1000, 1920, 3), np.float32) * navy
X, Y = 920, 280
c = np.asarray(crop).astype(np.float32)
mx = np.clip(np.arange(cw) / 320, 0, 1)[None, :]
my = np.clip(np.minimum(np.arange(ch) / 140, (ch - 1 - np.arange(ch)) / 90), 0, 1)[:, None]
m = (mx * my)[..., None]
hero[Y:Y + ch, X:X + cw] = c * m + hero[Y:Y + ch, X:X + cw] * (1 - m)
Image.fromarray(np.clip(hero, 0, 255).astype(np.uint8)).save(out_hero, "WEBP", quality=80)
print("ok", full.size)
