import os
from PIL import Image, ImageDraw, ImageFont

public_dir = r"d:\freelance-zekkstore\2026\joki-coding\Padelegan-Lapor\public"
brand_dir = os.path.join(public_dir, "images", "brand")
emblem_path = os.path.join(brand_dir, "lambang-pamekasan.png")

emblem = Image.open(emblem_path).convert("RGBA")

# 1. Generate Favicons
# We will create a clean rounded icon with emerald green background and centered emblem
def create_app_icon(size, bg_color=(23, 98, 72, 255)):
    img = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)
    # Draw rounded rectangle
    radius = int(size * 0.22)
    draw.rounded_rectangle([0, 0, size - 1, size - 1], radius=radius, fill=bg_color)
    
    # Scale emblem with padding
    pad = int(size * 0.12)
    inner_size = size - (pad * 2)
    scaled_emblem = emblem.copy()
    scaled_emblem.thumbnail((inner_size, inner_size), Image.Resampling.LANCZOS)
    
    # Center emblem
    offset_x = (size - scaled_emblem.width) // 2
    offset_y = (size - scaled_emblem.height) // 2
    img.paste(scaled_emblem, (offset_x, offset_y), scaled_emblem)
    return img

icon_16 = create_app_icon(16)
icon_32 = create_app_icon(32)
icon_48 = create_app_icon(48)
icon_180 = create_app_icon(180)
icon_192 = create_app_icon(192)
icon_512 = create_app_icon(512)

# Save png icons
icon_16.save(os.path.join(public_dir, "favicon-16x16.png"), "PNG")
icon_32.save(os.path.join(public_dir, "favicon-32x32.png"), "PNG")
icon_180.save(os.path.join(public_dir, "apple-touch-icon.png"), "PNG")
icon_192.save(os.path.join(public_dir, "android-chrome-192x192.png"), "PNG")
icon_512.save(os.path.join(public_dir, "android-chrome-512x512.png"), "PNG")

# Save multi-size favicon.ico
icon_48.save(
    os.path.join(public_dir, "favicon.ico"),
    format="ICO",
    sizes=[(16, 16), (32, 32), (48, 48)],
    append_images=[icon_32, icon_16]
)
print("Favicons and app icons generated successfully.")

# 2. Generate 1200x630 OpenGraph / WhatsApp / Facebook Share Thumbnail
w, h = 1200, 630
og_img = Image.new("RGB", (w, h), (16, 62, 48)) # #103e30 brand strong
draw = ImageDraw.Draw(og_img)

# Gradient background from deep emerald #0c2e22 to #176248
for y in range(h):
    factor = y / h
    r = int(12 + (23 - 12) * factor)
    g = int(46 + (98 - 46) * factor)
    b = int(34 + (72 - 34) * factor)
    draw.line([(0, y), (w, y)], fill=(r, g, b))

# Add subtle decorative background lines / frame
draw.rounded_rectangle([24, 24, w - 25, h - 25], radius=24, outline=(255, 255, 255, 30), width=2)
draw.rounded_rectangle([32, 32, w - 33, h - 33], radius=18, outline=(16, 185, 129, 60), width=1)

# Subtle top bar accent
draw.rectangle([60, 60, 240, 64], fill=(16, 185, 129))

# Load fonts
font_bold = "C:/Windows/Fonts/segoeuib.ttf" if os.path.exists("C:/Windows/Fonts/segoeuib.ttf") else "C:/Windows/Fonts/arialbd.ttf"
font_reg = "C:/Windows/Fonts/segoeui.ttf" if os.path.exists("C:/Windows/Fonts/segoeui.ttf") else "C:/Windows/Fonts/arial.ttf"

f_title = ImageFont.truetype(font_bold, 58)
f_sub = ImageFont.truetype(font_reg, 26)
f_kicker = ImageFont.truetype(font_bold, 18)
f_badge = ImageFont.truetype(font_bold, 17)
f_footer = ImageFont.truetype(font_reg, 19)

# Kicker
draw.text((60, 85), "LAYANAN PENGADUAN & USULAN WARGA RESMI", font=f_kicker, fill=(110, 231, 183)) # emerald-300

# Main Title
draw.text((60, 125), "Sistem Lapor Padelegan", font=f_title, fill=(255, 255, 255))

# Subtitle / Description
draw.text((60, 210), "Pemerintah Desa Padelegan, Pademawu, Pamekasan", font=ImageFont.truetype(font_bold, 28), fill=(209, 250, 229))
draw.text((60, 260), "Salurkan laporan masalah fasilitas desa & usulan pembangunan secara", font=f_sub, fill=(240, 253, 244))
draw.text((60, 300), "mudah, transparan, dan terlacak langsung oleh petugas desa.", font=f_sub, fill=(240, 253, 244))

# Badges pills
badges = [
    ("Akses Bebas Tanpa Login", (20, 110, 80)),
    ("Identitas Pelapor Privat", (20, 110, 80)),
    ("Resmi Pemdes Padelegan", (16, 185, 129)),
]

bx = 60
by = 385
for text, col in badges:
    bbox = draw.textbbox((0, 0), text, font=f_badge)
    bw = bbox[2] - bbox[0] + 32
    bh = bbox[3] - bbox[1] + 20
    draw.rounded_rectangle([bx, by, bx + bw, by + bh], radius=8, fill=(0, 0, 0, 70), outline=(255, 255, 255, 50), width=1)
    draw.text((bx + 16, by + 9), text, font=f_badge, fill=(255, 255, 255))
    bx += bw + 16

# Footer info
draw.line([(60, 520), (w - 60, 520)], fill=(255, 255, 255, 40), width=1)
draw.text((60, 545), "Website Resmi: sistem-lapor-padelegan.vercel.app  •  Pademawu, Pamekasan, Madura", font=f_footer, fill=(167, 243, 208))

# Paste Large Emblem on Right Side
scaled_emblem = emblem.copy()
scaled_emblem.thumbnail((320, 320), Image.Resampling.LANCZOS)
# Subtle circular background behind emblem
circ_x = 940
circ_y = 260
circ_r = 175
draw.ellipse([circ_x - circ_r, circ_y - circ_r, circ_x + circ_r, circ_y + circ_r], fill=(255, 255, 255, 15), outline=(16, 185, 129, 60), width=2)
og_img.paste(scaled_emblem, (circ_x - scaled_emblem.width // 2, circ_y - scaled_emblem.height // 2), scaled_emblem)

# Save as optimized JPEG (< 200KB for instant WhatsApp loading)
og_thumb_path = os.path.join(brand_dir, "og-thumbnail-padelegan.jpg")
og_img.save(og_thumb_path, "JPEG", quality=90, optimize=True)

# Also save WebP version
og_img.save(os.path.join(brand_dir, "og-thumbnail-padelegan.webp"), "WEBP", quality=90)

size_kb = os.path.getsize(og_thumb_path) / 1024
print(f"OG thumbnail generated at {og_thumb_path} ({size_kb:.1f} KB)")
