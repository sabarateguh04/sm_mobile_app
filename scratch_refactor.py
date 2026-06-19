import os
import re

view_files = [
    r"resources\views\turjagwali\index.blade.php",
    r"resources\views\trafficstuck\index.blade.php",
    r"resources\views\pnpangkutan\index.blade.php",
    r"resources\views\personil\manager\index.blade.php",
    r"resources\views\media\manage\index.blade.php",
    r"resources\views\media\analytic\index.blade.php",
    r"resources\views\layanan\masyarakat\index.blade.php",
    r"resources\views\layanan\data\index.blade.php",
    r"resources\views\data_jalan\index.blade.php",
    r"resources\views\datacctv\index.blade.php",
    r"resources\views\dataperangkat\index.blade.php",
    r"resources\views\contigency\index.blade.php",
    r"resources\views\cctv\index.blade.php",
    r"resources\views\auditarus\index.blade.php",
    r"resources\views\traffics\index.blade.php",
    r"resources\views\profilposko\index.blade.php",
    r"resources\views\arusmudik\index.blade.php",
    r"resources\views\ops_wanjol\index.blade.php"
]

base_dir = r"c:\laragon\www\smartbackoffice-laravel"

def refactor_file(filepath):
    full_path = os.path.join(base_dir, filepath)
    if not os.path.exists(full_path):
        return
        
    with open(full_path, 'r', encoding='utf-8') as f:
        content = f.read()

    if 'col-xl-3 col-lg-4' not in content:
        return
        
    form_match = re.search(r'<form\s+action="([^"]+)"\s+method="GET"(?:.*?id="([^"]+)")?[^>]*>', content)
    if not form_match:
        form_match = re.search(r'<form\s+method="GET"\s+action="([^"]+)"(?:.*?id="([^"]+)")?[^>]*>', content)
        if not form_match:
            return
            
    # The first form might be the search form at the top!
    # Let's find the form inside col-xl-3
    sidebar_block = re.search(r'<div class="col-xl-3 col-lg-4">.*?</form>\s*</div>\s*</div>', content, re.DOTALL)
    if not sidebar_block:
        return
        
    sidebar_content = sidebar_block.group(0)
    form_match = re.search(r'<form\s+action="([^"]+)"\s+method="GET"(?:.*?id="([^"]+)")?[^>]*>', sidebar_content)
    if not form_match:
        form_match = re.search(r'<form\s+method="GET"\s+action="([^"]+)"(?:.*?id="([^"]+)")?[^>]*>', sidebar_content)
        if not form_match:
            return
            
    action = form_match.group(1)
    form_id = form_match.group(2) if form_match.group(2) else ""
    id_attr = f' id="{form_id}"' if form_id else ''

    hidden_inputs = re.findall(r'<input\s+type="hidden"[^>]+>', sidebar_content)
    # Filter out duplicates
    unique_hidden = []
    for h in hidden_inputs:
        if h not in unique_hidden:
            unique_hidden.append(h)
    hidden_inputs_str = "\n        ".join(unique_hidden)
    
    has_tanggal = 'name="tanggal"' in sidebar_content
    tanggal_html = """
            <div class="col">
                <label class="form-label small fw-semibold text-muted text-uppercase">Tanggal</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ request('tanggal') }}">
            </div>""" if has_tanggal else ""

    select_matches = re.findall(r'<select\s+name="([^"]+)"[^>]*>.*?</select>', sidebar_content, re.DOTALL)
    other_selects = []
    for s_name in set(s for s in select_matches if s not in ['daerah', 'wilayah', 'polsek']):
        block = re.search(rf'<div class="mb-3[^"]*">\s*<label[^>]*>.*?<select\s+name="{s_name}".*?</select>\s*</div>', sidebar_content, re.DOTALL)
        if block:
            sel_html = block.group(0).replace('mb-3', 'col')
            other_selects.append(sel_html)

    input_matches = re.findall(r'<input\s+type="([^"]+)"\s+name="([^"]+)"[^>]*>', sidebar_content)
    other_inputs = []
    for itype, iname in set((t,n) for t,n in input_matches if t not in ['hidden', 'submit'] and n not in ['search', 'tanggal', 'daerah', 'wilayah', 'polsek']):
        block = re.search(rf'<div class="mb-3[^"]*">\s*<label[^>]*>.*?<input\s+type="{itype}"\s+name="{iname}".*?</div>', sidebar_content, re.DOTALL)
        if block:
            inp_html = block.group(0).replace('mb-3', 'col')
            other_inputs.append(inp_html)
            
    other_filters = "\n            ".join(other_selects + other_inputs)
    
    reset_cond = "['daerah','wilayah'"
    if has_tanggal:
        reset_cond += ",'tanggal'"
    for s in other_selects:
        name_match = re.search(r'name="([^"]+)"', s)
        if name_match: reset_cond += f",'{name_match.group(1)}'"
    for s in other_inputs:
        name_match = re.search(r'name="([^"]+)"', s)
        if name_match: reset_cond += f",'{name_match.group(1)}'"
    reset_cond += "]"
    
    filter_html = f"""
<form action="{action}" method="GET" class="card bg-light border-0 shadow-sm p-3 mb-3"{id_attr}>
    {hidden_inputs_str}
    <div class="row g-2 align-items-end">
        @include('_partials.territory_daerah_wilayah_filters', ['filterStyle' => 'row'])
        {tanggal_html}
        {other_filters}
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
            @if(request()->hasAny({reset_cond}))
                <a href="{action}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </div>
    </div>
</form>
"""

    parts = content.split('<div class="col-xl-9 col-lg-8">')
    if len(parts) < 2:
        parts = content.split('<div class="col-xl-9">')
        if len(parts) < 2:
            print(f"Could not split {filepath}")
            return
            
    before_col9 = parts[0]
    after_col9 = parts[1]
    
    row_start_idx = before_col9.rfind('<div class="row')
    if row_start_idx == -1:
        print(f"No row found in {filepath}")
        return
        
    before_row = before_col9[:row_start_idx]
    
    after_col9 = after_col9.rstrip()
    if after_col9.endswith('@endsection'):
        after_col9 = after_col9[:-11].rstrip()
        after_col9 = re.sub(r'</div>\s*</div>\s*$', '', after_col9)
        after_col9 += '\n@endsection\n'
    else:
        after_col9 = re.sub(r'</div>\s*</div>\s*$', '', after_col9)
        
    new_content = before_row + filter_html + after_col9
    
    with open(full_path, 'w', encoding='utf-8') as f:
        f.write(new_content)
        
    print(f"Refactored {filepath}")

for f in view_files:
    refactor_file(f)
