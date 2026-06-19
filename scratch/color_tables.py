import os
import re

base_dir = r"c:\laragon\www\smartbackoffice-laravel\resources\views"

def add_gold_color(match):
    full_td = match.group(0)
    # Check if color is already in style
    if '#D8A808' in full_td:
        return full_td
        
    style_attr = match.group(1)
    if style_attr is not None:
        # has style attribute
        # style_attr includes 'style="' and '"'
        inner_style = re.search(r'style="([^"]*)"', full_td)
        if inner_style:
            existing = inner_style.group(1).strip()
            if not existing.endswith(';') and len(existing) > 0:
                existing += ';'
            new_style = f'style="{existing} color: #D8A808; font-weight: 500;"'
            full_td = full_td.replace(inner_style.group(0), new_style)
    else:
        # no style attribute, inject one
        full_td = full_td.replace('<td', '<td style="color: #D8A808; font-weight: 500;"')
        
    return full_td

changed_files = []

for root, dirs, files in os.walk(base_dir):
    for f in files:
        if f.endswith('.blade.php'):
            filepath = os.path.join(root, f)
            with open(filepath, 'r', encoding='utf-8') as file:
                content = file.read()
                
            # Regex to find <td> containing ->daerah or ->wilayah
            # Match <td ...> ... ->daerah ... </td>
            pattern = re.compile(r'<td(.*?)\s*>\s*\{\{\s*\$[a-zA-Z0-9_]+->(daerah|wilayah).*?\}\}\s*</td>', re.DOTALL)
            
            new_content = pattern.sub(add_gold_color, content)
            
            if new_content != content:
                with open(filepath, 'w', encoding='utf-8') as file:
                    file.write(new_content)
                changed_files.append(filepath)

print("Changed files:")
for c in changed_files:
    print(c)
