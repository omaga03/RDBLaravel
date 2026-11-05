import sys
import os
import json
import pymupdf
from PIL import Image

def rasterize_single(pdf_path, webp_path):
    try:
        pymupdf.TOOLS.mupdf_display_errors(False)
        pymupdf.TOOLS.mupdf_display_warnings(False)
        doc = pymupdf.open(pdf_path)
        if len(doc) == 0:
            doc.close()
            return False

        page = doc[0]
        w = page.rect.width if page.rect.width > 0 else 595.0
        z = max(1.2, min(3.0, 800.0 / w))
        pix = page.get_pixmap(matrix=pymupdf.Matrix(z, z), alpha=False)
        img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
        if img.width > 800:
            new_h = max(1, int(img.height * (800.0 / img.width)))
            img = img.resize((800, new_h), Image.Resampling.LANCZOS)
        
        target_dir = os.path.dirname(webp_path)
        if target_dir and not os.path.isdir(target_dir):
            os.makedirs(target_dir, exist_ok=True)

        img.save(webp_path, "WEBP", quality=85, method=4)
        img.close()
        pix = None
        doc.close()
        return os.path.isfile(webp_path) and os.path.getsize(webp_path) > 0
    except Exception as e:
        return False

def rasterize_batch(in_json, out_json):
    try:
        with open(in_json, 'r', encoding='utf-8') as f:
            items = json.load(f)
    except Exception:
        return

    pymupdf.TOOLS.mupdf_display_errors(False)
    pymupdf.TOOLS.mupdf_display_warnings(False)

    out = {}
    for item in items:
        k = str(item.get('key'))
        pdf = item.get('pdf')
        webp = item.get('webp')
        out[k] = rasterize_single(pdf, webp)

    with open(out_json, 'w', encoding='utf-8') as f:
        json.dump(out, f)

def main():
    if len(sys.argv) < 3:
        sys.exit(1)

    mode = sys.argv[1]
    if mode == 'single' and len(sys.argv) >= 4:
        pdf_path = sys.argv[2]
        webp_path = sys.argv[3]
        if rasterize_single(pdf_path, webp_path):
            print("OK")
            sys.exit(0)
        else:
            print("FAIL")
            sys.exit(1)
    elif mode == 'batch' and len(sys.argv) >= 4:
        in_json = sys.argv[2]
        out_json = sys.argv[3]
        rasterize_batch(in_json, out_json)
        print("OK")
        sys.exit(0)
    else:
        sys.exit(1)

if __name__ == '__main__':
    main()
