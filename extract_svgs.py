import os, re

html = open('korlantas-icons-no-clash.html', 'r', encoding='utf-8').read()
out_dir = 'public/satupeta_assets/satupeta/korlantas'
os.makedirs(out_dir, exist_ok=True)

# Split by <div class="icon-card">
cards = html.split('<div class="icon-card">')[1:]
for card in cards:
    svg_match = re.search(r'<svg.*?</svg>', card, re.DOTALL)
    code_match = re.search(r'<div class="icon-code">(.*?)</div>', card)
    if svg_match and code_match:
        svg = svg_match.group(0)
        code = code_match.group(1).strip()
        with open(os.path.join(out_dir, f'{code}.svg'), 'w', encoding='utf-8') as f:
            f.write(svg)
        print(f'Saved {code}.svg')
