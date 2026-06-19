<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Smart Road Safety Policing</title>

<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://unpkg.com/maplibre-gl@2.4.0/dist/maplibre-gl.css" rel="stylesheet" />
<script src="https://unpkg.com/maplibre-gl@2.4.0/dist/maplibre-gl.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6.5.0/turf.min.js?v=99"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<script>
if (window.Chart) {
  Chart.defaults.color = '#fff';
  Chart.defaults.borderColor = 'rgba(255,255,255,0.2)';
  Chart.defaults.plugins.legend.labels.color = '#fff';
  Chart.defaults.plugins.legend.labels.boxWidth = 10;
  Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0,0,0,0.85)';
  Chart.defaults.plugins.tooltip.titleColor = '#fff';
  Chart.defaults.plugins.tooltip.bodyColor  = '#fff';
}
</script>

<style>
  :root {
    --panel-bg: rgba(255,255,255,0.98);
    --accent: #1a237e;
    --shadow: 0 8px 26px rgba(0,0,0,0.12);
    --divider: #e9eef5;
  }
  html,body { height:100%; margin:0; font-family:'Fira Sans'; background:#f5f7fb; }
  #map { position:absolute; inset:0; }

  #main-header { position:fixed; top:0; left:0; right:0; height:40px; background:var(--accent); color:#fff; display:none; align-items:center; justify-content:center; z-index:1600; box-shadow:0 2px 6px rgba(0,0,0,0.12); }
  #main-header h1 { font-size:16px; margin:0; font-weight:600; }

  #filter-screen { position:absolute; inset:0; background:rgba(0,0,0,0.65); display:flex; align-items:center; justify-content:center; z-index:1700; }
  #splash-container {
  width: 100%;
  height: 100%;
  display: flex;
  border-radius: 0;                
  overflow: hidden;
  box-shadow: none;            
}
  #splash-left { flex:0 0 30%; max-width: 30%; background:linear-gradient(180deg,#12203a,#0b2540); color:#fff; padding:40px; display:flex; flex-direction: column; align-items:center; justify-content:center; text-align:center; }
  #splash-left h2 { margin:0; font-size:24px; line-height:1.3; font-weight:700; }
  #splash-right {
    flex: 0 0 70%;
    max-width: 70%;
    background: #0b2540;  
    padding: 0;
    position: relative;
  }
  #splash-right h3 { margin:0; font-size:22px; font-weight:600; }
  #splash-right p { font-size:14px; color:rgba(255,255,255,0.8); margin:4px 0 12px 0; }
  .btn-primary { background:#ffc107; color:#000; border:0; padding:10px 12px; border-radius:8px; cursor:pointer; font-weight:700; transition:background-color .2s; }
  .btn-primary:hover { background:#ffd966; }
  select { padding:10px; border-radius:6px; border:1px solid rgba(0,0,0,0.12); font-size:14px; text-align:center; }
  #splash-map {
    position: absolute;
    inset: 0;           
  }

  #filter-panel {
    position:absolute; top:52px; left:50%; transform:translateX(-50%);
    z-index:1500; background:var(--accent); padding:10px 14px;
    border-radius:12px; box-shadow:var(--shadow);
    display:none; align-items:center; gap:10px;
  }
  #filter-panel label { font-weight:600; color: #ffc107; }

  #bottom-panel-wrapper { position:absolute; bottom:12px; left:50%; transform:translateX(-50%); z-index:1500; display:none; }
  #bottom-panel { width:360px; background:var(--panel-bg); border-radius:12px; box-shadow:var(--shadow); overflow:hidden; transition:all .3s ease; }
  #panel-header { display:flex; justify-content:space-between; align-items:center; padding:4px 8px; border-bottom:1px solid rgba(0,0,0,0.08); }
  #bottom-panel.minimized #panel-header { cursor:pointer; }
  #panel-tabs { display:flex; gap:4px; }
  .panel-tab-btn { display:flex; align-items:center; gap:6px; background:transparent; border:0; padding:8px 10px; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600; color:#555; transition:.2s; }
  .panel-tab-btn svg { width:18px; height:18px; fill:#555; transition:fill .2s; }
  .panel-tab-btn:hover { background:rgba(0,0,0,0.05); }
  .panel-tab-btn.active { background:var(--accent); color:#fff; }
  .panel-tab-btn.active svg { fill:#fff; }
  #panel-controls .min-btn { font-size:20px; width:32px; height:32px; border-radius:8px; }
  #panel-body { padding:12px 14px; max-height:400px; overflow-y:auto; transition:all .3s ease; }
  .panel-content { display:none; }
  .panel-content.active { display:block; }
  .panel-content h4 { margin:0 0 10px 0; font-size:15px; font-weight:600; }
  .legend-item { display:flex; gap:10px; align-items:center; margin-bottom:8px; }
  .legend-icon { width:36px; height:24px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .legend-label { font-size:13px; color:#222; }
  .legend-title { font-size:14px; margin-bottom:8px; border-bottom:1px solid #eee; padding-bottom:4px; font-weight:bold; }
  .legend-title:not(:first-child){ margin-top:12px; }

  .tab-control-group { display: flex; flex-direction: column; gap: 6px; }
  .tab-btn { width: 100%; padding: 8px; font-size: 14px; font-weight: 600; text-align: left; border: 1px solid #ddd; background-color: #f8f9fa; color: #333; border-radius: 6px; cursor: pointer; transition: all 0.2s; }
  .tab-btn:hover { background-color: #e9ecef; border-color: #ccc; }
  .tab-btn.active { background-color: var(--accent); color: #fff; border-color: var(--accent); }

  .min-btn { background:transparent; border:0; font-size:16px; cursor:pointer; color:var(--accent); }
  .min-btn:hover { background:rgba(0,0,0,0.05); }

  #zoom-compass { display:flex; gap:8px; align-items:center; pointer-events:auto; }
  #zoom-controls { display:flex; flex-direction:column; background:var(--panel-bg); border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.06); overflow:hidden; }
  .zoom-btn { width:40px; height:36px; border:0; background:transparent; cursor:pointer; font-size:18px; color:#222; }
  #compass { width:46px; height:46px; background:var(--panel-bg); border-radius:8px; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 6px 18px rgba(0,0,0,0.06); }
  #my-location-btn { width: 46px; height: 46px; background: var(--panel-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
  #my-location-btn:hover { background: #e9eef5; }
  #scale-box { background:var(--panel-bg); border-radius:8px; padding:6px 8px; box-shadow:0 6px 18px rgba(0,0,0,0.06); pointer-events:auto; }
  #map .maplibregl-popup-content { 
      font-family: 'Fira Sans', sans-serif; 
      border-radius: 8px; 
      box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
      padding: 0; 
      max-width: 320px !important; 
      width: 320px !important;
      overflow: visible !important; 
      margin-bottom: 0 !important; 
  }
  #splash-right .maplibregl-popup-content {
    max-width: 280px !important;
    width: auto !important;
    position: relative;
    background: #1a237e;           
    color: #fff;
    border: 2px solid #ffc107; 
    border-radius: 12px;
    font-weight: 700;
    font-size: 12px;
    line-height: 1.3;
    text-align: center;
    padding: 8px 12px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    cursor: pointer;
  }
  #map .maplibregl-popup-content::after {
      content: "";
      position: absolute;
      top: 100%; 
      left: 50%;
      transform: translateX(-50%);
      border-width: 12px 12px 0 12px;
      border-style: solid;
      border-color: #fff transparent transparent transparent;
      z-index: 10;
      filter: drop-shadow(0 3px 2px rgba(0,0,0,0.1));
  }
  .popup-body { 
    padding: 10px 12px; 
    font-size: 13px; 
    max-height: 60vh;
    min-height: 50px;
    overflow-y: auto;
    overflow-x: hidden;
  }
  .popup-body::-webkit-scrollbar { width: 6px; }
  .popup-body::-webkit-scrollbar-track { background: #f1f1f1; }
  .popup-body::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
  .popup-body::-webkit-scrollbar-thumb:hover { background: #bbb; }
  .maplibregl-popup-close-button { font-size:20px; color:#fff; background:rgba(0,0,0,0.2); border-radius:50%; width:24px; height:24px; margin:6px 6px 0 0; line-height:24px; text-align:center; }
  .popup-header { background:var(--accent); color:#fff; padding:8px 36px 8px 12px; font-weight:600; font-size:15px; border-top-left-radius:8px; border-top-right-radius:8px; }
  .popup-body { padding:10px 12px; font-size:13px; max-height:180px; overflow-y:auto; }
  .popup-body strong { color:#333; }

  #time-slider-wrapper {
    position: absolute;
    top: 115px; 
    left: 50%;
    transform: translateX(-50%);
    z-index: 1500;
    display: none; 
  }
  #time-slider-panel {
    background: var(--accent);
    padding: 10px 20px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    display: flex;
    flex-direction: column; 
    color: #fff;
    font-size: 13px;
    font-weight: 600;
  }
  #time-slider-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
  }
  #time-slider-header h4 {
    margin: 0;
    font-size: 14px;
  }
  #time-slider-header .min-btn {
    color: #fff; 
  }
  #time-slider-body {
    display: flex;
    align-items: center;
    gap: 15px;
  }
  #time-slider-minimized-btn {
    display: none; 
    font-size: 14px;
    font-weight: 700;
    border: 3px solid #1a237e;
    border-radius: 12px;
  }
  #slider-container {
    position: relative;
    width: 350px;
  }
  #time-slider {
    width: 100%;
    -webkit-appearance: none;
    appearance: none;
    height: 5px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 5px;
    outline: none;
    cursor: pointer;
  }
  #time-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    background: #ffc107;
    border: 3px solid #fff;
    border-radius: 50%;
    cursor: pointer;
  }
  #time-slider::-moz-range-thumb {
    width: 20px;
    height: 20px;
    background: #ffc107;
    border: 3px solid #fff;
    border-radius: 50%;
    cursor: pointer;
  }
  #slider-tooltip {
    position: absolute;
    background: #000;
    color: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    bottom: 30px;
    transform: translateX(-50%);
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.2s;
    pointer-events: none;
  }

  #dashboard-panel {
    --dash-bg: #1a237e;
    --dash-bg-2: #151b6d;
    --dash-fg: #ffffff;
    --dash-subtle: #eeeeee;
    --dash-divider: rgba(255,255,255,0.3);
    position: fixed;
    top: 52px;
    right: 12px;
    width: 320px;
    z-index: 1500;
    background: var(--dash-bg);
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.4);
    color: var(--dash-fg);
    display: flex; 
    flex-direction: column;
    max-height: calc(100vh - 150px); 
    animation: slide-in-right 0.5s ease-out;
  }

  #dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    border-bottom: 1px solid var(--dash-divider);
    background: var(--dash-bg-2);
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    flex-shrink: 0;
  }
  #dashboard-header .min-btn { color: #fff; }
  #dashboard-header h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--dash-fg);
  }
  #dashboard-body {
    padding: 12px;
    overflow-y: auto; 
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  @keyframes slide-in-right {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
  }
  #dashboard-body::-webkit-scrollbar { width: 6px; }
  #dashboard-body::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); }
  #dashboard-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 3px; }
  #dashboard-body::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.5); }
  .dash-item-container + .dash-item-container { border-top: 1px solid var(--dash-divider); margin-top: 8px; padding-top: 8px; }
  .dash-item-header { display: flex; justify-content: space-between; align-items: center; padding: 0 4px 8px 4px; cursor: pointer; }
  .dash-item-header h5 { margin: 0; font-size: 13px; text-align: center; color: var(--dash-fg); flex: 1; padding-left: 24px; }
  .dash-item-body { position: relative; overflow: hidden; height: 202px; }
  .dash-item-body.doughnut { height: 125px; }
  .dash-item-container.list .dash-item-body { height: auto; min-height: 50px; }
  .dash-item-container.minimized .dash-item-body { display: none; }
  .dash-item-toggle { color: var(--dash-fg); background-color: transparent; border: none; font-size: 20px; padding: 0 8px; cursor: pointer; }
  .dash-item-toggle:hover { background: rgba(255,255,255,0.1); border-radius: 4px; }
  
  .blackspot-list-item { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid var(--dash-divider); font-size:13px; }
  .blackspot-list-item:last-child{ border-bottom:0; }
  .blackspot-list-item .score{ font-weight:700; background:#ff5252; color:#fff; padding:2px 6px; border-radius:4px; font-size:12px; }
  .no-data-info{ font-size:13px; color:var(--dash-subtle); text-align:center; padding:12px 0; }
  .note-info{ margin-top:6px; font-size:12px; color:var(--dash-subtle); text-align:center; }
  
  #search-routing-panel {
    position:absolute; top:52px; left:12px; z-index:1501; background:#1a237e; color:#fff;
    border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,0.4); padding:10px; width:300px;
    display:none; flex-direction:column; gap:10px;
  }
  #search-routing-panel h4 { margin:0; font-size:14px; }
  .field-wrap { position:relative; width:100%; }
  .field-wrap input, .field-wrap select { width:100%; padding:8px; border:none; border-radius:6px; outline:none; font-family:'Fira Sans'; font-size:13px; box-sizing: border-box; }
  .field-wrap input { background:#fff; color:#000; }
  .dropdown-results { position:absolute; left:0; right:0; top:calc(100% + 6px); z-index: 10; background:#fff; color:#000; border-radius:6px; max-height:220px; overflow-y:auto; display:none; font-size:13px; box-shadow:0 4px 10px rgba(0,0,0,0.25); }
  .dropdown-results .result-item { padding:6px 8px; cursor:pointer; border-bottom:1px solid #eee; }
  .dropdown-results .result-item:hover { background:#e3f2fd; }

  #mode-tabs { display: flex; gap: 6px; width: 100%; }
  .mode-tab-btn { flex: 1; padding: 8px; font-size: 13px; font-weight: 600; border: 1px solid rgba(255,255,255,0.3); background-color: transparent; color: #fff; border-radius: 6px; cursor: pointer; transition: background-color 0.2s; }
  .mode-tab-btn:hover { background-color: rgba(255,255,255,0.1); }
  .mode-tab-btn.active { background-color: #ffc107; color: #000; border-color: #ffc107; }

  #route-controls { display:flex; flex-direction:column; background:rgba(255,255,255,0.1); border-radius:8px; padding:8px; transition: all 0.3s ease; }
  #route-header { display:flex; justify-content:space-between; align-items:center; cursor:pointer; margin-bottom: 8px; }
  #route-body { display:flex; flex-direction:column; gap:6px; transition: all 0.3s ease; }
  #route-controls.minimized #route-header { margin-bottom: 0; }
  #route-controls.minimized #route-body { display:none; }
  #route-controls small { color:#e0e0ff; }
  #route-controls .row { display:flex; gap:6px; align-items:center; }
  #route-results { background:rgba(0,0,0,0.28); border-radius:6px; font-size:13px; overflow:hidden; }
  #route-results-header{ display:flex; align-items:center; justify-content:space-between; padding:6px 8px; border-bottom:1px solid rgba(255,255,255,0.2); }
  #route-results-body{ max-height:180px; overflow-y:auto; padding:6px 8px; transition:max-height .2s ease; }
  #route-results.collapsed #route-results-body{ max-height:0; padding:0 8px; }
  
  .btn { background:#ffc107; border:none; border-radius:6px; padding:6px 10px; cursor:pointer; font-weight:700; color:#000; }
  .btn:hover { background:#ffeb3b; }
  
  #route-alert-popup {
    position: absolute;
    top: 52px;
    left: 322px;
    z-index: 1502;
    background: #fff3cd;
    border: 1px solid #ffeeba;
    border-left: 5px solid #ffc107;
    color: #856404;
    padding: 12px 30px 12px 15px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    max-width: 280px;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    display: none;
    animation: slide-in 0.4s ease-out;
  }
  #route-alert-popup .close-btn {
    position: absolute;
    top: 2px;
    right: 5px;
    font-size: 24px;
    font-weight: bold;
    color: #856404;
    cursor: pointer;
    border: none;
    background: none;
    padding: 4px;
    line-height: 1;
  }
  @keyframes slide-in {
    from { transform: translateX(-20px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }

  #btn-swap-route {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: transparent;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: background-color 0.2s;
  }
  #btn-swap-route:hover {
    background: rgba(255, 255, 255, 0.15);
  }

  #loading-overlay {
    position:fixed; inset:0; background:rgba(26, 35, 126, 0.9);
    display:none; align-items:center; justify-content:center;
    z-index:9999; color:#fff; flex-direction:column; gap:20px;
    font-size: 16px; font-weight: 600;
  }
  .loading-logo { width: 120px; height: auto; animation: pulse 1.5s infinite ease-in-out; }
  @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }

  #measure-panel {
    position: fixed;
    top: 4px;           
    left: 140px;        
    z-index: 1602;
    display: none;
    background: rgba(26, 35, 126, 0.95); 
    backdrop-filter: blur(4px);
    border-radius: 20px;
    padding: 0 10px;    
    border: 1px solid rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    gap: 8px;
    height: 32px;       
    box-sizing: border-box;
    transition: opacity 0.2s ease;
  }
  #measure-body {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
    margin: 0;
    height: 100%;
  }
  #measure-controls {
    display: flex;
    gap: 4px;
  }
  .measure-btn {
    background: rgba(255,255,255,0.15);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.3);
    padding: 0 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    height: 24px;
    display: flex;
    align-items: center;
  }
  .measure-btn:hover { background: #ffc107; color: #000; border-color:#ffc107; }
  .measure-btn.active { background: #ffc107; color: #000; border-color: #ffc107; }
  .measure-btn#btn-measure-clear {
    border-color: #ff5252;
    color: #ff5252;
  }
  .measure-btn#btn-measure-clear:hover { background: #ff5252; color: #fff; }
  #measure-result {
    color: #ffc107;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    min-width: 60px;
    text-align: center;
    border-left: 1px solid rgba(255,255,255,0.3);
    padding-left: 8px;
    margin-left: 4px;
  }
  #radius-analysis-btn-wrapper {
    position: absolute;
    top: 52px;          
    left: 350px;        
    bottom: auto;
    right: auto;
    z-index: 1500;
    display: none;
    animation: slide-in-left 0.4s ease-out;
  }
  #btn-radius-analysis {
    background: #1a237e;
    color: #fff;
    border: 2px solid #ffc107;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
  }
  #btn-radius-analysis:hover {
    background: #0d47a1;
    transform: translateY(-1px);
  }
  @keyframes slide-in-left {
    from { opacity: 0; transform: translateX(-10px); }
    to { opacity: 1; transform: translateX(0); }
  }
  @media (max-width: 800px) {
    #splash-container { width:100%; flex-direction:column; height: 100vh; }
    #splash-left { max-width:100%; padding: 20px; flex: 0 0 auto; min-height: 30vh; border-radius: 0; }
    #splash-right { max-width:100%; flex: 1 1 auto; min-height: 50vh; }
    
    #bottom-panel-wrapper { width:96%; bottom: 10px; left: 50%; transform: translateX(-50%); }
    #bottom-panel { width: 100%; }
    
    #filter-panel {
        top: 10px;
        left: 2%;
        right: 2%;
        width: 96%;
        transform: none;
        flex-wrap: wrap;
        justify-content: center;
        box-sizing: border-box;
    }

    #search-routing-panel {
        top: 70px;
        left: 10px;
        right: 10px;
        width: calc(100% - 20px);
    }
    
    #radius-analysis-btn-wrapper { top: 10px; left: 10px; }
    #measure-panel { top: 70px; left: 10px; height: auto; padding: 6px; }

    #dashboard-panel { 
        display: flex !important; /* Timpa !important dari sebelumnya jika ada */
        top: auto; 
        bottom: 60px; /* di atas bottom panel */
        right: 10px; 
        left: 10px; 
        width: auto; 
        max-height: 40vh;
        z-index: 1400;
        border-radius: 12px;
    }
    
    #time-slider-wrapper {
        top: auto;
        bottom: 130px;
        width: 90%;
    }
    
    #zoom-compass {
        position: absolute;
        bottom: 150px;
        right: 10px;
        flex-direction: column;
    }
  }

  .maplibregl-popup {
      display: flex !important;
      flex-direction: column;
      align-items: center;
      justify-content: flex-end;
      z-index: 1000 !important;
  }
  #splash-right .maplibregl-popup {
    margin-bottom: 0 !important;
  }

  #splash-right .maplibregl-popup-content {
    position: relative;
    background: #1a237e;      
    color: #fff;
    border: 2px solid #ffc107; 
    border-radius: 12px;
    font-weight: 700;
    font-size: 12px;
    line-height: 1.3;
    text-align: center;
    padding: 8px 12px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    cursor: pointer;
  }

  #splash-right .maplibregl-popup-content::after {
    content: "";
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 10px solid #1a237e;
  }

  .maplibregl-popup-tip {
    display: none !important;
  }

  @keyframes pin-pop {
    from { transform: scale(0.5) translateY(10px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
  }
  #splash-right .maplibregl-popup-content {
    animation: pin-bounce 2.2s cubic-bezier(0.28, 0.84, 0.42, 1) infinite;
    transform-origin: bottom center;
  }

  @keyframes pin-bounce {
    0%, 100% {
      transform: translateY(0);
    }
    20% {
      transform: translateY(-6px);
    }
    40% {
      transform: translateY(0);
    }
    60% {
      transform: translateY(-3px);
    }
    80% {
      transform: translateY(0);
    }
  }

  #splash-left {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  #splash-left {
      display: flex; 
      flex-direction: column; 
      justify-content: center;
      align-items: center; 
      padding: 0 20px; 
  }

  #splash-left-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      max-width: 380px;
  }

  #splash-logo {
      width: 200px;
      height: auto;
      margin-bottom: 5px;
  }

  #splash-title {
      color: #333;
      margin: 0;
      font-size: 20px;
      line-height: 1.3;
  }

  #splash-chart-container {
      width: 100%;
      height: 200px;
      padding: 10px;
      background: rgba(0, 0, 0, 0.2);
      border-radius: 8px;
      margin-bottom: 20px;
  }

  #splash-left .info-card {
    background-color: white;
    padding: 20px 30px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    text-align: center;
    width: 100%;
    max-width: 380px;
    margin-bottom: 25px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
  #splash-left {
    position: relative;
  }

  #splash-left .corner-header {
    position: absolute;
    top: 20px;
    left: 20px;
    display: flex;              
    align-items: center;          
    flex-direction: row;          
    gap: 10px;                   
    z-index: 10;
  }

  #splash-left .corner-header .corner-logo {
    width: 60px;
    height: auto;
    flex-shrink: 0;
    opacity: 0.95;
    transition: transform 0.3s ease, opacity 0.3s ease;
  }

  #splash-left .corner-header .corner-logo:hover {
    transform: scale(1.05);
    opacity: 1;
  }

  #splash-left .corner-header .corner-text {
    color: #ffffff;
    font-size: 17.5px;
    line-height: 1.25;
    font-weight: 600;
    text-align: left;
    max-width: 180px;
  }

  @media (max-width: 720px) {
    #splash-left .corner-header {
      top: 10px;
      left: 10px;
      gap: 6px;
    }

    #splash-left .corner-header .corner-logo {
      width: 50px;
    }

    #splash-left .corner-header .corner-text {
      font-size: 11px;
      max-width: 160px;
    }
  }

  #splash-left .info-subtext {
    margin-top: 16px;
    color: #ffffff;   
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    opacity: 0.9;
    max-width: 300px;
    line-height: 1.4;
  }

  @media (max-width: 720px) {
    #splash-left .info-subtext {
      font-size: 13px;
      margin-top: 12px;
      max-width: 260px;
    }
  }
  #splash-left .info-subtext {
    animation: fadein-subtext 0.8s ease-out 0.3s both;
  }

  @keyframes fadein-subtext {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  #splash-last-update {
    width: 100%;  
    font-style: italic;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    margin: -10px 0 10px 0;
    text-align: right;
    height: 16px;
  }

  #copyright-footer {
      position: absolute;
      bottom: 15px;
      left: 20px;
      font-size: 11px;
      color: rgba(255, 255, 255, 0.5);
  }

  .route-option-btn {
    width: 100%;
    padding: 8px;
    font-size: 13px;
    font-weight: 600;
    text-align: left;
    border: 1px solid rgba(255,255,255,0.4);
    background-color: transparent;
    color: #fff;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  .route-option-btn:hover {
    background-color: rgba(255,255,255,0.1);
  }
  .route-option-btn.active {
    background-color: #ffc107;
    color: #000;
    border-color: #ffc107;
  }
  .route-option-btn span {
    font-size: 11px;
    opacity: 0.8;
  }

  #splash-left-content {
    position: relative;
    padding: 15px 45px;
  }

  .border-line {
    position: absolute;
    background-color: #ffc107; 
    box-shadow: 0 0 5px #ffc107, 0 0 10px #ffc107; 
  }

  .line-top {
    top: 0;
    left: 0;
    width: 0;
    height: 2px;
  }
  .line-right {
    top: 0; 
    right: 0;
    width: 2px;
    height: 0;
  }
  .line-bottom {
    bottom: 0;
    right: 0;
    width: 0;
    height: 2px;
  }
  .line-left {
    bottom: 0;
    left: 0;
    width: 2px;
    height: 0;
  }

  @keyframes animate-top {
    25% { width: 100%; }
    100% { width: 100%; }
  }
  @keyframes animate-right {
    25% { height: 0; }
    50% { height: 100%; }
    100% { height: 100%; }
  }
  @keyframes animate-bottom {
    50% { width: 0; }
    75% { width: 100%; }
    100% { width: 100%; }
  }
  @keyframes animate-left {
    75% { height: 0; }
    100% { height: 100%; }
  }

  .line-top {
    animation: animate-top 4s linear infinite;
  }
  .line-right {
    animation: animate-right 4s linear infinite;
  }
  .line-bottom {
    animation: animate-bottom 4s linear infinite;
  }
  .line-left {
    animation: animate-left 4s linear infinite;
  }

  .btn-splash-news {
      background: #ffc107;
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      cursor: pointer;
      font-weight: 700;
      color: #000;
      text-decoration: none;
      display: inline-block; 
      margin-top: 15px; 
      transition: background-color 0.2s;
  }

  .btn-splash-news:hover {
      background: #ffeb3b; 
  }

  #btn-show-hint {
      position: absolute;
      top: 20px;
      right: 60px;
      left: auto;
      font-size: 15px;
      border-radius: 8px;
      border: 2px solid rgba(26, 35, 126, 0.9);
      z-index: 10;
  }

  #hint-text-overlay {
      position: absolute;
      top: 80px;
      right: 60px;
      left: auto;
      background: rgba(26, 35, 126, 0.9);
      color: #fff;
      padding: 10px 15px;
      border-radius: 8px;
      border: 1px solid #ffc107;
      font-weight: 600;
      font-size: 14px;
      z-index: 10;
      opacity: 0;
      transform: translateY(-10px);
      transition: opacity 0.3s ease, transform 0.3s ease;
      pointer-events: none;
      text-align: right; 
  }

  #hint-text-overlay.visible {
      opacity: 1;
      transform: translateY(0);
  }

  @keyframes pulse-glow {
      0% {
          transform: scale(1);
          box-shadow: 0 0 5px #ffc107, 0 0 10px #ffc107, 0 0 15px #ffc107;
      }
      50% {
          transform: scale(1.1);
          box-shadow: 0 0 10px #fff, 0 0 20px #fff, 0 0 30px #ffc107;
      }
      100% {
          transform: scale(1);
          box-shadow: 0 0 5px #ffc107, 0 0 10px #ffc107, 0 0 15px #ffc107;
      }
  }

  .highlight-pin .maplibregl-popup-content {
      animation: pulse-glow 1.5s ease-in-out 2; 
  }

  #extra-dests .field-wrap { position: relative; }
  #btn-add-dest-row { margin-top: 6px; }

  :root { --route-gap: 4px; } 

  #route-body { gap: var(--route-gap); }

  .route-stack {
    display: flex;
    flex-direction: column;
    gap: var(--route-gap);
  }

  .route-stack .field-wrap { margin: 0; }

  #extra-dests {
    display: flex;
    flex-direction: column;
    gap: var(--route-gap);
    margin: 0;            
  }

  #btn-add-dest-row { 
    margin: 0;             
  }

  .dropdown-results { margin: 0; }

  #troublespot-note { margin-top: 6px; }
  #blackspot-total { font-weight: 600; }

  .blackspot-list-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  }

  #geofence-status-panel {
    position: absolute;
    top: 180px; 
    left: 50%;
    transform: translateX(-50%);
    z-index: 1500;
    display: none;
    animation: slide-down-fade 0.5s ease-out;
    pointer-events: none;
  }

  #geofence-alert-content {
    background: rgba(211, 47, 47, 0.95);
    color: #fff;
    padding: 12px 24px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    text-align: center;
    border: 2px solid #ffc107;
    pointer-events: auto;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  #geofence-alert-content::before {
    content: "⚠️";
    font-size: 18px;
  }

  @keyframes slide-down-fade {
    from { transform: translate(-50%, -20px); opacity: 0; }
    to { transform: translate(-50%, 0); opacity: 1; }
  }

  .layer-row {
    display: flex;
    gap: 6px;
    margin-bottom: 6px;
    align-items: stretch;
  }
  
  .layer-row .tab-btn {
    flex: 1;
    margin-bottom: 0;
  }

  .btn-info-layer {
    flex: 0 0 32px;
    background: #e3f2fd;
    color: #1565c0;
    border: 1px solid #90caf9;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Times New Roman', serif;
    font-style: italic;
    font-weight: 700;
    font-size: 16px;
    transition: all 0.2s;
  }

  .btn-info-layer:hover {
    background: #2196f3;
    color: #fff;
    border-color: #1e88e5;
  }

  #info-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    z-index: 20000;
    display: none;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(3px);
  }

  #info-modal-box {
    background: #fff;
    width: 90%;
    max-width: 500px;
    max-height: 85vh;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modal-pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  @keyframes modal-pop {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
  }

  #info-modal-header {
    background: var(--accent);
    color: #fff;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #ffc107;
  }

  #info-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
  }

  #info-modal-close {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
    line-height: 1;
  }

  #info-modal-body {
    padding: 20px;
    overflow-y: auto;
    color: #333;
    font-size: 14px;
    line-height: 1.6;
  }

  .info-section { margin-bottom: 15px; }
  .info-label { font-weight: 700; color: #1a237e; display: block; margin-bottom: 4px; }
  .info-content ul { margin: 5px 0 5px 20px; padding: 0; }
  .info-content li { margin-bottom: 4px; }
  .source-badge {
    display: inline-block;
    background: #f1f8e9;
    color: #33691e;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #c5e1a5;
    margin-top: 5px;
  }

  #radius-modal-overlay {
      position: fixed; inset: 0; background: rgba(0, 0, 0, 0.8);
      z-index: 20000; display: none; align-items: center; justify-content: center;
      backdrop-filter: blur(4px);
      animation: fadeIn 0.3s;
  }

  #radius-modal-box {
      background: #fff; width: 90%; max-width: 600px; max-height: 90vh;
      border-radius: 12px; display: flex; flex-direction: column;
      box-shadow: 0 10px 40px rgba(0,0,0,0.5); overflow: hidden;
  }

  #radius-modal-header {
      background: #1a237e; color: #fff; padding: 15px 20px;
      display: flex; justify-content: space-between; align-items: center;
      border-bottom: 4px solid #ffc107;
  }
  #radius-modal-header h3 { margin: 0; font-size: 18px; }
  #radius-modal-close { background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; }

  #radius-modal-body { padding: 20px; overflow-y: auto; background: #f5f7fb; }

  .radius-summary-grid {
      display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px;
  }
  .summary-card {
      background: #fff; padding: 10px; border-radius: 8px; text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #e0e0e0;
  }
  .summary-card .lbl { display: block; font-size: 11px; color: #666; margin-bottom: 4px; font-weight: 600; }
  .summary-card .val { display: block; font-size: 20px; font-weight: 700; color: #1a237e; }

  .chart-section { background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #eee; }
  .chart-section h4 { margin: 0 0 10px 0; font-size: 14px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 5px; }
  .chart-wrapper { height: 200px; position: relative; }

  #radius-modal-footer { padding: 15px; background: #fff; border-top: 1px solid #eee; }

  @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

  #btn-screenshot-radius {
      background: none;
      border: none;
      color: #fff;
      cursor: pointer;
      padding: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
      transition: background 0.2s;
  }

  #btn-screenshot-radius:hover {
      background: rgba(255,255,255,0.2);
  }

  #severe-traffic-alert {
      position: absolute;
      top: 52px;
      right: 345px; 
      z-index: 1550;
      
      background: rgba(211, 47, 47, 0.95);
      color: #fff;
      padding: 10px 14px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.4);
      border: 2px solid #ff5252;
      animation: slide-in-top 0.5s ease-out;
      
      width: 280px; 
      display: flex;
      flex-direction: column;
      transition: all 0.3s ease;
  }

  #severe-traffic-alert.minimized {
      width: auto;
      padding: 6px 12px;
      flex-direction: row;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      background: #b71c1c;
  }

  #severe-traffic-alert.minimized .alert-content-wrapper,
  #severe-traffic-alert.minimized #btn-severe-minimize {
      display: none;
  }

  #severe-traffic-alert.minimized .alert-minimized-label {
      display: block !important;
      font-size: 13px;
      font-weight: 700;
      white-space: nowrap;
  }

  #severe-traffic-alert.minimized .alert-icon-box {
      width: 24px; height: 24px; font-size: 16px;
  }

  .alert-header-row {
      display: flex; justify-content: space-between; align-items: flex-start;
      width: 100%; margin-bottom: 6px;
  }

  .alert-icon-box {
      position: relative; width: 32px; height: 32px;
      display: flex; align-items: center; justify-content: center; font-size: 20px;
  }

  .alert-icon-animate {
      position: absolute; inset: 0; border-radius: 50%;
      border: 2px solid #fff; animation: ripple 1.5s infinite;
  }

  #btn-severe-minimize {
      background: transparent; border: none; color: rgba(255,255,255,0.7);
      font-weight: 800; font-size: 14px; cursor: pointer; padding: 0 4px; line-height: 1;
  }
  #btn-severe-minimize:hover { color: #fff; }

  .alert-content-wrapper { width: 100%; }
  .alert-title { font-size: 10px; font-weight: 800; letter-spacing: 1px; color: #ffcdd2; margin-bottom: 2px; text-transform: uppercase; }
  .alert-desc { font-size: 13px; font-weight: 700; margin-bottom: 10px; line-height: 1.3; }

  #btn-severe-details {
      background: #fff; color: #d32f2f; border: none;
      padding: 8px; border-radius: 6px; font-size: 12px;
      font-weight: 700; cursor: pointer; width: 100%;
      transition: background 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  }
  #btn-severe-details:hover { background: #ffebee; }

  @keyframes slide-in-top {
      from { transform: translateY(-20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
  }
  @keyframes ripple {
      0% { transform: scale(1); opacity: 1; }
      100% { transform: scale(2); opacity: 0; }
  }

  @media (max-width: 1000px) {
      #severe-traffic-alert { top: 110px; right: 10px; width: auto; max-width: 250px; }
  }

  #severe-modal-overlay {
      position: fixed; inset: 0; background: rgba(0, 0, 0, 0.85);
      z-index: 20000; display: flex; align-items: center; justify-content: center;
      backdrop-filter: blur(4px);
  }
  #severe-modal-box {
      background: #fff; width: 90%; max-width: 450px; max-height: 80vh;
      border-radius: 12px; display: flex; flex-direction: column; overflow: hidden;
      box-shadow: 0 10px 40px rgba(0,0,0,0.5);
  }
  #severe-modal-header {
      background: #d32f2f; color: #fff; padding: 15px 20px;
      display: flex; justify-content: space-between; align-items: center;
  }
  #severe-modal-header h3 { margin: 0; font-size: 16px; }
  #severe-modal-close { background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; }
  #severe-modal-body { padding: 20px; overflow-y: auto; background: #fafafa; }

  .severe-summary {
      display: flex; gap: 15px; margin-bottom: 15px; align-items: center;
      background: #ffebee; padding: 10px; border-radius: 8px; border: 1px solid #ffcdd2;
  }
  .severe-summary .count-box {
      background: #fff; padding: 10px; border-radius: 8px; text-align: center;
      min-width: 70px; border: 1px solid #ef9a9a;
  }
  .severe-summary .count-box span { display: block; font-size: 24px; font-weight: 800; color: #d32f2f; line-height: 1; }
  .severe-summary .count-box small { font-size: 10px; color: #666; }
  .severe-summary .desc-box { font-size: 13px; color: #333; line-height: 1.4; }

  .severe-list-header { font-weight: 700; font-size: 13px; color: #555; margin-bottom: 8px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }

  #severe-street-list { list-style: none; padding: 0; margin: 0; }
  #severe-street-list li {
      padding: 10px 12px;
      background: #fff; border-bottom: 1px solid #eee;
      font-size: 13px; color: #333;
      display: flex; justify-content: space-between;
      align-items: center;
  }
  #severe-street-list li:hover { background: #f5f5f5; }

  .street-name { font-weight: 600; display: flex; align-items: center; gap: 8px; }
  .street-name::before { content: "🔴"; font-size: 10px; }

  .street-count {
      background: #ffebee; color: #c62828;
      padding: 2px 8px; border-radius: 10px;
      font-size: 11px; font-weight: 700;
      border: 1px solid #ef9a9a; white-space: nowrap;
  }

.alert-controls {
    display: flex;
    gap: 4px;
    align-items: center;
}

#btn-severe-minimize, #btn-severe-mute {
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.7);
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    padding: 4px;
    line-height: 1;
    border-radius: 4px;
    transition: all 0.2s;
}

#btn-severe-minimize:hover, #btn-severe-mute:hover {
    color: #fff;
    background: rgba(255,255,255,0.1);
}

#severe-traffic-alert.minimized #btn-severe-minimize,
#severe-traffic-alert.minimized #btn-severe-mute {
    display: none;
}

.patrol-alert-card {
  background: rgba(13, 71, 161, 0.95);
  color: #fff;
  width: 300px;
  border-left: 5px solid #00e5ff;
  border-radius: 4px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.4);
  pointer-events: auto;
  animation: slide-in-left 0.5s ease-out;
  overflow: hidden;
  font-family: 'Fira Sans', sans-serif;
}

.patrol-card-header {
  background: rgba(0,0,0,0.2);
  padding: 8px 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.patrol-card-title {
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #00e5ff;
  display: flex;
  align-items: center;
  gap: 6px;
}

.patrol-card-body {
  padding: 10px 12px;
}

.patrol-summary-text {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 8px;
  line-height: 1.4;
}

.patrol-detail-list {
  list-style: none;
  padding: 0;
  margin: 0;
  max-height: 150px;
  overflow-y: auto;
  border-top: 1px solid rgba(255,255,255,0.2);
  padding-top: 5px;
}

.patrol-detail-item {
  font-size: 12px;
  padding: 4px 0;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  display: flex;
  justify-content: space-between;
}
.patrol-detail-item:last-child { border-bottom: none; }

.patrol-badge {
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  color: #000;
}
.badge-geo { background: #ffccbc; }
.badge-cctv { background: #b9f6ca; }

@keyframes slide-in-left {
  from { transform: translateX(-50px); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

.patrol-detail-list::-webkit-scrollbar { width: 4px; }
.patrol-detail-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 2px; }

.header-tool-btn {
  position: fixed;
  top: 6px;
  z-index: 1601;
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.3);
  color: #fff;
  height: 28px;
  padding: 0 10px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-family: 'Fira Sans', sans-serif;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.3px;
  text-transform: uppercase;
  transition: all 0.2s;
  backdrop-filter: blur(4px);
}
.header-tool-btn svg {
  width: 14px;
  height: 14px;
}
#download-control-container {
  position: fixed;
  top: 6px;
  right: 15px;
  z-index: 1601;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}
#btn-download-fab {
  position: static; 
  width: auto; 
  height: 28px;
  box-shadow: none;
  background: rgba(26, 35, 126, 0.8);
  border: 1px solid rgba(255,255,255,0.3);
  margin-bottom: 4px;
}
#btn-download-fab:hover {
  background: #ffc107;
  color: #1a237e;
  border-color: #ffc107;
}
#download-menu-list {
  background: #fff;
  border-radius: 6px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  padding: 4px 0;
  display: none;
  flex-direction: column;
  width: 200px;
  border: 1px solid rgba(0,0,0,0.1);
  margin-top: 0; 
  transform-origin: top right;
  animation: slide-down-fade 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
}
#download-menu-list.visible {
  display: flex;
}
.download-item-btn {
  background: transparent;
  border: none;
  padding: 8px 12px;
  text-align: left;
  font-family: 'Fira Sans', sans-serif;
  font-size: 12px;
  font-weight: 500;
  color: #444;
  cursor: pointer;
  transition: background 0.15s;
  border-bottom: 1px solid #f5f5f5;
  display: flex;
  align-items: center;
  gap: 8px;
}
.download-item-btn:last-child {
  border-bottom: none;
}
.download-item-btn:hover {
  background-color: #e8eaf6;
  color: #1a237e;
  font-weight: 600;
  padding-left: 16px;
}
@keyframes slide-down-fade {
  from { 
    opacity: 0; 
    transform: translateY(-10px) scale(0.98);
  }
  to { 
    opacity: 1; 
    transform: translateY(0) scale(1);
  }
}

#btn-header-measure {
  left: 15px;
}

#controls-bl {
  position: fixed !important;
  left: auto !important;
  top: auto !important;
  right: 10px !important;
  bottom: 10px !important;
  z-index: 1550;
  display: flex;
  flex-direction: row;
  align-items: flex-end;
  gap: 10px;
  pointer-events: none;
  margin: 0 !important;
  padding: 0 !important;
}
#controls-bl > * {
  pointer-events: auto;
  margin-bottom: 0 !important;
}
#scale-box {
  background: rgba(255,255,255,0.8);
  border-radius: 4px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.2);
  padding: 0 4px;
  border: 1px solid #999;
  height: fit-content; 
  margin-bottom: 5px !important;
}
.maplibregl-ctrl-scale {
  margin: 0 !important;
  border: none !important;
  box-shadow: none !important;
  background: transparent !important;
}
.control-btn-circle {
  width: 40px; 
  height: 40px;
  background: #fff;
  border-radius: 50%;
  display: flex; 
  align-items: center; 
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  color: #1a237e;
  transition: transform 0.2s;
  border: 1px solid rgba(0,0,0,0.1);
}
.control-btn-circle:hover { 
  transform: scale(1.1); 
  background-color: #f5f7fb;
}
#zoom-controls {
  display: flex;
  flex-direction: column;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  overflow: hidden;
  border: 1px solid rgba(0,0,0,0.1);
}
.zoom-btn {
  width: 40px; 
  height: 40px; 
  background: transparent; 
  border: 0;
  font-size: 20px; 
  font-weight: bold; 
  color: #333;
  cursor: pointer;
  display: flex; 
  align-items: center; 
  justify-content: center;
}
.zoom-btn:hover { background: #eee; }
.zoom-btn:first-child { border-bottom: 1px solid #eee; }

#patrol-alert-container {
  position: fixed;
  bottom: 20px;
  left: 20px;
  top: auto;
  z-index: 1550;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  z-index: 1550;
  pointer-events: none;
}
.patrol-alert-card {
  background: rgba(13, 71, 161, 0.95);
  color: #fff;
  width: 300px;
  border-left: 5px solid #00e5ff;
  border-radius: 8px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.5);
  pointer-events: auto;
  font-family: 'Fira Sans', sans-serif;
  animation: slide-up-fade 0.5s ease-out;
}
.patrol-minimized-tab {
  background: linear-gradient(90deg, #1a237e, #0d47a1);
  color: #00e5ff;
  padding: 12px 20px;
  border-radius: 30px;
  border: 2px solid #00e5ff;
  font-weight: 700;
  font-size: 13px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.4);
  cursor: pointer;
  pointer-events: auto;
  display: flex; align-items: center; gap: 8px;
  animation: bounce-in 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.patrol-minimized-tab:hover {
  transform: scale(1.05); background: #00e5ff; color: #000;
}
@keyframes slide-up-fade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes bounce-in { 0% { opacity: 0; transform: scale(0.3); } 50% { opacity: 1; transform: scale(1.05); } 100% { transform: scale(1); } }
</style>
</head>
<body>

<header id="main-header"><h1>Smart Road Safety Policing</h1></header>
<button id="btn-header-measure" class="header-tool-btn" title="Alat Ukur">
    <svg width="800px" height="800px" viewBox="0 0 200 200" data-name="Layer 1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:#5f6d7a;}.cls-2,.cls-5,.cls-7{fill:none;}.cls-2{stroke:#5f6d7a;}.cls-2,.cls-5{stroke-linecap:round;stroke-linejoin:round;stroke-width:6px;}.cls-3{fill:#ffffff;}.cls-4{fill:#ffc861;}.cls-5,.cls-7{stroke:#2f4360;}.cls-6{fill:#2d3a44;}.cls-7{stroke-miterlimit:10;stroke-width:2.25px;}</style></defs><title/><polygon class="cls-1" points="171.34 145.76 179.63 167.52 187.02 186.9 167.64 179.51 145.88 171.22 171.34 145.76"/><polygon class="cls-2" points="187.02 186.9 145.88 171.22 171.34 145.76 187.02 186.9"/><rect class="cls-3" height="194.5" transform="translate(-37.18 89.59) rotate(-45)" width="36" x="71.56" y="-7.57"/><rect class="cls-4" height="194.5" transform="translate(-33.84 90.98) rotate(-45)" width="26.54" x="79.63" y="-10.92"/><rect class="cls-5" height="194.5" transform="translate(-37.18 89.6) rotate(-45)" width="36" x="71.56" y="-7.57"/><polygon class="cls-6" points="183.67 170.15 191.93 191.82 170.27 183.56 183.67 170.15"/><rect class="cls-3" height="40" transform="translate(-41.42 100) rotate(-45)" width="232.67" x="-16.34" y="80"/><rect class="cls-4" height="31" transform="translate(-42.74 103.18) rotate(-45)" width="232.67" x="-13.15" y="87.68"/><rect class="cls-5" height="232.67" transform="translate(100 -41.42) rotate(45)" width="40" x="80" y="-16.33"/><line class="cls-7" x1="159.16" x2="173.3" y1="12.56" y2="26.7"/><line class="cls-7" x1="155.06" x2="162.13" y1="16.66" y2="23.73"/><line class="cls-7" x1="150.95" x2="158.02" y1="20.77" y2="27.84"/><line class="cls-7" x1="146.85" x2="153.92" y1="24.87" y2="31.94"/><line class="cls-7" x1="142.74" x2="149.81" y1="28.98" y2="36.05"/><line class="cls-7" x1="142.74" x2="156.88" y1="28.98" y2="43.12"/><line class="cls-7" x1="138.64" x2="145.71" y1="33.08" y2="40.16"/><line class="cls-7" x1="134.53" x2="141.6" y1="37.19" y2="44.26"/><line class="cls-7" x1="130.42" x2="137.5" y1="41.3" y2="48.37"/><line class="cls-7" x1="126.32" x2="133.39" y1="45.4" y2="52.47"/><line class="cls-7" x1="126.32" x2="140.46" y1="45.4" y2="59.54"/><line class="cls-7" x1="122.21" x2="129.28" y1="49.51" y2="56.58"/><line class="cls-7" x1="118.11" x2="125.18" y1="53.61" y2="60.68"/><line class="cls-7" x1="114" x2="121.07" y1="57.72" y2="64.79"/><line class="cls-7" x1="109.9" x2="116.97" y1="61.82" y2="68.89"/><line class="cls-7" x1="109.9" x2="124.04" y1="61.82" y2="75.96"/><line class="cls-7" x1="105.79" x2="112.86" y1="65.93" y2="73"/><line class="cls-7" x1="101.69" x2="108.76" y1="70.03" y2="77.1"/><line class="cls-7" x1="97.58" x2="104.65" y1="74.14" y2="81.21"/><line class="cls-7" x1="93.47" x2="100.55" y1="78.24" y2="85.32"/><line class="cls-7" x1="93.47" x2="107.62" y1="78.24" y2="92.39"/><line class="cls-7" x1="89.37" x2="96.44" y1="82.35" y2="89.42"/><line class="cls-7" x1="85.26" x2="92.33" y1="86.46" y2="93.53"/><line class="cls-7" x1="81.16" x2="88.23" y1="90.56" y2="97.63"/><line class="cls-7" x1="77.05" x2="84.12" y1="94.67" y2="101.74"/><line class="cls-7" x1="77.05" x2="91.19" y1="94.67" y2="108.81"/><line class="cls-7" x1="72.95" x2="80.02" y1="98.77" y2="105.84"/><line class="cls-7" x1="68.84" x2="75.91" y1="102.88" y2="109.95"/><line class="cls-7" x1="64.74" x2="71.81" y1="106.98" y2="114.05"/><line class="cls-7" x1="60.63" x2="67.7" y1="111.09" y2="118.16"/><line class="cls-7" x1="60.63" x2="74.77" y1="111.09" y2="125.23"/><line class="cls-7" x1="56.52" x2="63.6" y1="115.19" y2="122.27"/><line class="cls-7" x1="52.42" x2="59.49" y1="119.3" y2="126.37"/><line class="cls-7" x1="48.31" x2="55.38" y1="123.41" y2="130.48"/><line class="cls-7" x1="44.21" x2="51.28" y1="127.51" y2="134.58"/><line class="cls-7" x1="44.21" x2="58.35" y1="127.51" y2="141.65"/><line class="cls-7" x1="40.1" x2="47.17" y1="131.62" y2="138.69"/><line class="cls-7" x1="36" x2="43.07" y1="135.72" y2="142.79"/><line class="cls-7" x1="31.89" x2="38.96" y1="139.83" y2="146.9"/><line class="cls-7" x1="27.79" x2="34.86" y1="143.93" y2="151"/><line class="cls-7" x1="27.79" x2="41.93" y1="143.93" y2="158.08"/><line class="cls-7" x1="23.68" x2="30.75" y1="148.04" y2="155.11"/><line class="cls-7" x1="19.57" x2="26.65" y1="152.14" y2="159.22"/><line class="cls-7" x1="15.47" x2="22.54" y1="156.25" y2="163.32"/><line class="cls-7" x1="11.36" x2="18.43" y1="160.36" y2="167.43"/></svg>
    <span>Alat Ukur</span>
</button>

<div id="download-control-container">
    <button id="btn-download-fab" class="header-tool-btn" title="Download Data">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        <span>Download Data</span>
    </button>
    <div id="download-menu-list">
        <button class="download-item-btn" onclick="generateExcel('troublespot', 'Data_Troublespot')">Titik Gangguan Lalu Lintas</button>
        <button class="download-item-btn" onclick="generateExcel('blackspot', 'Data_Blackspot')">Black Spot</button>
        <button class="download-item-btn" onclick="generateExcel('traffic_point', 'Data_Titik_Perlambatan')">Titik Perlambatan</button>
        <button class="download-item-btn" onclick="generateExcel('prediksi_risiko', 'Data_Prediksi_Risiko')">Prediksi Risiko Kecelakaan</button>
    </div>
</div>

<div id="loading-overlay">
  <img src="{{ asset('satupeta_webgis') }}/assets/korlantas25.png" alt="Memuat..." class="loading-logo" />
  <span>Memuat Peta & Data...</span>
</div>

<div id="filter-screen">
  <div id="splash-container">
    <div id="splash-left">
        <div class="corner-header">
            <img src="{{ asset('satupeta_webgis') }}/assets/korlantas25.png" alt="Logo Korlantas" class="corner-logo" />
            <span class="corner-text">
                Korps Lalu Lintas<br>
                Kepolisian Negara Republik Indonesia
            </span>
        </div>

        <div id="splash-left-content">
            <span class="border-line line-top"></span>
            <span class="border-line line-right"></span>
            <span class="border-line line-bottom"></span>
            <span class="border-line line-left"></span>
            <div class="info-card"> 
                <img src="{{ asset('satupeta_webgis') }}/assets/logo_STMSPS.png" alt="Logo STMSPS" id="splash-logo" />
                <h2 id="splash-title">Smart Road Safety Policing</h2>
            </div>

            <div id="splash-chart-container">
                <canvas id="splash-troublespot-chart"></canvas>
            </div>

            <p id="splash-last-update"></p>
            <a href="https://www.detik.com/tag/kecelakaan" target="_blank" class="btn-splash-news">Jelajahi Berita Kecelakaan</a>
        </div>

        <div id="copyright-footer">Copyright ©2025 Elang Strategi Adidaya (ESTRADA) | All Rights Reserved</div>
    </div>
    <div id="splash-right">
      <button id="btn-show-hint" class="btn-splash-news">Jelajahi Peta</button>

      <div id="hint-text-overlay" class="hidden">
          <span>Pilih salah satu Satwil untuk menjelajahi peta lebih lanjut</span>
      </div>
      <svg id="arrow-overlay" class="hidden"></svg>
      <div id="splash-map" aria-label="Peta pilih Satwil"></div>
    </div>
  </div>
</div>

<div id="map"></div>

<div id="severe-traffic-alert" style="display: none;">
    <div class="alert-header-row">
        <div class="alert-icon-box">
            <div class="alert-icon-animate"></div>
            <span>⚠️</span>
        </div>
        
        <div class="alert-controls">
            <button id="btn-severe-mute" title="Matikan Suara">🔊</button>
            <button id="btn-severe-minimize" title="Minimize">_</button>
        </div>
    </div>
    
    <div class="alert-content-wrapper">
        <div class="alert-title">PERINGATAN LALU LINTAS</div>
        <div class="alert-desc">Terdapat Perlambatan Parah di Wilayah ini</div>
        <button id="btn-severe-details">Informasi Lengkap Perlambatan Parah</button>
    </div>
    
    <div class="alert-minimized-label" style="display:none;">Perlambatan Parah Terdeteksi</div>

    <audio id="severe-alert-sound" loop preload="auto">
        <source src="{{ asset('satupeta_webgis/assets/traffic-alert.mp3') }}" type="audio/mpeg">
    </audio>
</div>

<div id="severe-modal-overlay" style="display: none;">
    <div id="severe-modal-box">
        <div id="severe-modal-header">
            <h3>Detail Perlambatan Parah</h3>
            <button id="severe-modal-close">&times;</button>
        </div>
        <div id="severe-modal-body">
            <div class="severe-summary">
                <div class="count-box">
                    <span id="severe-count-val">0</span>
                    <small>Titik/Ruas</small>
                </div>
                <div class="desc-box">
                    Daftar ruas jalan yang mengalami perlambatan parah saat ini.
                </div>
            </div>
            <div class="severe-list-header">Daftar Jalan:</div>
            <ul id="severe-street-list"></ul>
        </div>
    </div>
</div>

<div id="search-routing-panel">
  <div class="field-wrap" id="search-box">
    <input id="search-input" type="text" placeholder="Cari tempat atau nama feature..." />
    <div id="search-results" class="dropdown-results"></div>
  </div>

  <div id="patrol-alert-container"></div>
  
  <div id="route-controls" class="minimized">
    <div id="route-header">
       <h4>Pencarian Rute</h4>
       <button id="btn-toggle-route" class="btn" style="padding:4px 8px;">Buka</button>
    </div>
    <div id="route-body">
      <div class="row" style="justify-content:space-between;">
        <small>Aktifkan untuk pilih titik via klik peta (klik pertama untuk Lokasi Asal, klik kedua untuk Lokasi Tujuan)</small>
      </div>
      <div class="row">
        <label style="display:flex; align-items:center; gap:6px; font-size:13px;">
          <input type="checkbox" id="click-pick-toggle" />
          <span>Pilih titik via klik peta</span>
        </label>
      </div>
      <div style="display: flex; align-items: center; gap: 8px;">
        <div class="route-stack" style="flex-grow:1;">
          <div class="field-wrap">
            <input id="from-input" placeholder="Lokasi Asal (ketik atau klik peta)" />
            <div id="from-results" class="dropdown-results"></div>
          </div>

          <div class="field-wrap">
            <input id="to-input" placeholder="Lokasi Tujuan (ketik atau klik peta)" />
            <div id="to-results" class="dropdown-results"></div>
          </div>

          <div id="extra-dests"></div>

          <div id="btn-add-dest-row" class="row">
            <button id="btn-add-dest" class="btn" style="flex:1; background:transparent; color:#fff; border:1px solid rgba(255,255,255,0.4);">
              + Tambah Tujuan
            </button>
          </div>
        </div>
        <button id="btn-swap-route" title="Tukar Lokasi">
          <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="20" fill="currentColor">
            <path d="M6.99 11L3 15l3.99 4v-3H14v-2H6.99v-3zM21 9l-3.99-4v3H10v2h7.01v3L21 9z"/>
          </svg>
        </button>
      </div>
      <div id="mode-tabs">
        <button class="mode-tab-btn active" data-mode="car">Mobil 🚗</button>
        <button class="mode-tab-btn" data-mode="two_wheeler">Motor 🏍️</button>
        <button class="mode-tab-btn" data-mode="foot">Jalan Kaki 🚶</button>
      </div>
      <div class="row">
        <button id="btn-route" class="btn" style="flex:1;">Tampilkan Rute</button>
        <button id="btn-clear-route" class="btn" style="flex:0 0 auto;">Reset Rute</button>
      </div>
    </div>
  </div>

  <div id="route-results" style="display: none;">
    <div id="route-results-header">
      <div style="font-weight:700">Hasil Arah Rute</div>
      <button id="btn-collapse-route" class="btn" style="padding:4px 8px;">Tutup</button>
    </div>
    <div id="route-options-container" style="padding: 6px 8px; display: flex; flex-direction: column; gap: 4px; border-bottom: 1px solid rgba(255,255,255,0.2);">
    </div>
    <div id="route-results-body"></div>
  </div>
</div> <div id="route-alert-popup" style="display:none;"></div>

<div id="filter-panel">
  <label>Satwil</label>
  <select id="wilayah-select" aria-label="Ganti Satwil">
    <option value="" disabled selected hidden>Pilih Satwil</option>
  </select>
  <button id="btn-ganti" class="btn-primary" style="padding:8px 10px; font-size:13px;">Ganti</button>
</div>

<div id="time-slider-wrapper">
  <div id="time-slider-panel">
    <div id="time-slider-header">
      <h4>Sebaran Lokasi Black Spot Selama 7 Hari Terakhir</h4>
      <button id="time-slider-toggle-btn" class="min-btn" title="Minimize">−</button>
    </div>
    <div id="time-slider-body">
      <label id="slider-label-start">Tanggal Awal</label>
      <div id="slider-container">
        <input type="range" id="time-slider" min="0" max="100" value="100">
        <div id="slider-tooltip"></div>
      </div>
      <label id="slider-label-end">Tanggal Akhir</label>
    </div>
  </div>

  <button id="time-slider-minimized-btn" class="btn">Informasi Temporal Black Spot</button>
</div>

<div id="geofence-status-panel">
  <div id="geofence-alert-content"></div>
</div>

<div id="radius-analysis-btn-wrapper">
    <button id="btn-radius-analysis" class="btn-primary" style="box-shadow: 0 4px 15px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        Informasi Lalu Lintas dalam Radius
    </button>
</div>

<div id="radius-modal-overlay" style="display: none;">
  <div id="radius-modal-box">
    <div id="radius-modal-header">
      <h3>Analisis Area Radius</h3>
      <div style="display: flex; align-items: center; gap: 10px;">
          <button id="btn-screenshot-radius" title="Screenshot Analisis">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
          </button>
          <button id="radius-modal-close">&times;</button>
      </div>
    </div>
    <div id="radius-modal-body">
        
        <div class="radius-summary-grid">
            <div class="summary-card">
                <span class="lbl">Titik Gangguan Lalu Lintas</span>
                <span class="val" id="val-troublespot">0</span>
            </div>
            <div class="summary-card">
                <span class="lbl">Titik Lokasi Black Spot</span>
                <span class="val" id="val-blackspot">0</span>
            </div>
            <div class="summary-card">
                <span class="lbl">Titik Lokasi Perlambatan</span>
                <span class="val" id="val-trafficpoint">0</span>
            </div>
             <div class="summary-card">
                <span class="lbl">Prediksi Risiko Kecelakaan Tinggi</span>
                <span class="val" id="val-risk">0</span>
            </div>
        </div>

        <div class="chart-section">
            <h4>Grafik Titik Gangguan Lalu Lintas</h4>
            <div class="chart-wrapper"><canvas id="radius-chart-troublespot"></canvas></div>
        </div>
        
        <div class="chart-section">
            <h4>Grafik Prediksi Risiko Kecelakaan</h4>
            <div class="chart-wrapper"><canvas id="radius-chart-risk"></canvas></div>
        </div>

    </div>
    <div id="radius-modal-footer">
        <button id="btn-download-radius" class="btn-primary" style="width: 100%;">
            Download Data Selengkapnya (Excel)
        </button>
    </div>
  </div>
</div>

<div id="dashboard-panel">
  <div id="dashboard-header">
    <h4>Grafik Statistik</h4>
    <button id="dashboard-minimize-btn" class="min-btn" title="Minimize">−</button>
  </div>
  <div id="dashboard-body">
    <div class="dash-item-container">
      <div class="dash-item-header">
        <h5>Jumlah Titik Gangguan Lalu Lintas</h5>
        <button class="dash-item-toggle min-btn">−</button>
      </div>
      <div class="dash-item-body">
        <canvas id="troublespot-chart"></canvas>
        <div id="troublespot-note" class="note-info" aria-live="polite"></div>
      </div>
    </div>
    <div class="dash-item-container">
      <div class="dash-item-header">
        <h5>Status CCTV</h5>
        <button class="dash-item-toggle min-btn">−</button>
      </div>
      <div class="dash-item-body doughnut">
        <canvas id="cctv-chart"></canvas>
      </div>
    </div>
    <div class="dash-item-container list">
      <div class="dash-item-header">
        <h5>Top 5 Black Spot</h5>
        <button class="dash-item-toggle min-btn">−</button>
      </div>
      <div class="dash-item-body">
        <div id="blackspot-list"></div>
        <div id="blackspot-note" class="note-info"></div>
        <div id="blackspot-total" class="note-info" aria-live="polite"></div>
        <div id="blackspot-trend" class="note-info" style="font-weight: 600; margin-top: 6px;"></div>
      </div>
    </div>
  </div>
</div>

<div id="bottom-panel-wrapper">
  <div id="bottom-panel" class="minimized">
    <div id="panel-header">
      <div id="panel-tabs">
        <button class="panel-tab-btn active" data-content="basemap-content">
          <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5-10-5-10 5zM2 12l10 5 10-5-10-5-10 5z"/></svg>
          <span>Basemap</span>
        </button>
        <button class="panel-tab-btn" data-content="layer-content">
          <svg viewBox="0 0 24 24"><path d="M12 16.5l-7-4.5 7-4.5 7 4.5-7 4.5zM12 2L2 7l10 5 10-5L12 2z"/></svg>
          <span>Layer</span>
        </button>
        <button class="panel-tab-btn" data-content="legend-content">
          <svg viewBox="0 0 24 24"><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
          <span>Legenda</span>
        </button>
      </div>
      <div id="panel-controls">
        <button id="panel-minimize-btn" class="min-btn" title="Minimize">+</button>
      </div>
    </div>
    <div id="panel-body" style="display: none;">
      <div id="basemap-content" class="panel-content active">
        <h4>Pilih Basemap</h4>
        <div id="basemap-tabs" class="tab-control-group">
          <div class="layer-row">
            <button class="tab-btn active" data-value="osm">OpenStreetMap</button>
            <button class="btn-info-layer" data-info="basemap_osm" title="Info OpenStreetMap">i</button>
          </div>
          <div class="layer-row">
            <button class="tab-btn" data-value="esri">ESRI World Topo</button>
            <button class="btn-info-layer" data-info="basemap_esri" title="Info ESRI World Topo">i</button>
          </div>
          <div class="layer-row">
            <button class="tab-btn" data-value="google">Google Maps</button>
            <button class="btn-info-layer" data-info="basemap_google" title="Info Google Maps">i</button>
          </div>
          <div class="layer-row">
            <button class="tab-btn" data-value="imagery">Google Imagery (Satellite)</button>
            <button class="btn-info-layer" data-info="basemap_imagery" title="Info Imagery">i</button>
          </div>
          <div class="layer-row">
            <button class="tab-btn" data-value="google_hybrid">Google Hybrid</button>
            <button class="btn-info-layer" data-info="basemap_hybrid" title="Info Google Hybrid">i</button>
          </div>
          <div class="layer-row">
            <button class="tab-btn" data-value="google_dark">Google Dark Mode</button>
            <button class="btn-info-layer" data-info="basemap_google_dark" title="Info Google Dark">i</button>
          </div>
          <div class="layer-row">
            <button class="tab-btn" data-value="esri_dark">ESRI Gray (Dark)</button>
            <button class="btn-info-layer" data-info="basemap_esri_dark" title="Info ESRI Dark Gray">i</button>
          </div>
          <div class="layer-row">
            <button class="tab-btn" data-value="esri_light">ESRI Gray (Light)</button>
            <button class="btn-info-layer" data-info="basemap_esri_light" title="Info ESRI Light Gray">i</button>
          </div>
          <div class="layer-row">
            <button class="tab-btn" data-value="carto_dark">CartoDB Dark Matter</button>
            <button class="btn-info-layer" data-info="basemap_carto_dark" title="Info CartoDB Dark">i</button>
          </div>
          <div class="layer-row">
            <button class="tab-btn" data-value="stadia">Stadia Maps</button>
            <button class="btn-info-layer" data-info="basemap_stadia" title="Info Stadia Maps">i</button>
          </div>
        </div>
      </div>
      <div id="layer-content" class="panel-content">
        <h4>Pilih Layer Spasial</h4>
          <div id="layer-tabs" class="tab-control-group">
           <div class="layer-row">
             <button class="tab-btn active" data-layer="troublespot">Titik Gangguan Lalu Lintas</button>
             <button class="btn-info-layer" data-info="troublespot" title="Info Titik Gangguan Lalu Lintas">i</button>
           </div>

           <div class="layer-row">
             <button class="tab-btn active" data-layer="blackspot">Black Spot</button>
             <button class="btn-info-layer" data-info="blackspot" title="Info Black Spot">i</button>
           </div>

           <div class="layer-row">
             <button class="tab-btn active" data-layer="traffic_point">Titik Perlambatan</button>
             <button class="btn-info-layer" data-info="traffic_point" title="Info Titik Perlambatan">i</button>
           </div>
           
           <div class="layer-row">
             <button class="tab-btn active" data-layer="traffic">Perlambatan</button>
             <button class="btn-info-layer" data-info="traffic" title="Info Perlambatan">i</button>
           </div>

           <div class="layer-row">
             <button class="tab-btn active" data-layer="blacklink">Black Link</button>
             <button class="btn-info-layer" data-info="blacklink" title="Info Black Link">i</button>
           </div>

           <div class="layer-row">
             <button class="tab-btn active" data-layer="blackarea">Black Area</button>
             <button class="btn-info-layer" data-info="blackarea" title="Info Black Area">i</button>
           </div>

           <div class="layer-row">
             <button class="tab-btn active" data-layer="objek_vital">Objek Vital</button>
             <button class="btn-info-layer" data-info="objek_vital" title="Info Objek Vital">i</button>
           </div>

           <div class="layer-row">
             <button class="tab-btn active" data-layer="cctv">CCTV</button>
             <button class="btn-info-layer" data-info="cctv" title="Info CCTV">i</button>
           </div>

          <div class="layer-row">
            <button class="tab-btn" data-layer="cctv_coverage">Area Pantauan CCTV</button>
            <button class="btn-info-layer" data-info="cctv_coverage" title="Info Area Coverage CCTV">i</button>
          </div>

          <div class="layer-row">
            <button class="tab-btn active" data-layer="gerbang_tol">Gerbang Tol</button>
            <button class="btn-info-layer" data-info="gerbang_tol" title="Info Gerbang Tol">i</button>
          </div>
          
          <div class="layer-row">
            <button class="tab-btn active" data-layer="patrol">Petugas Patroli</button>
            <button class="btn-info-layer" data-info="patrol" title="Info Petugas Patroli">i</button>
          </div>

          <div class="layer-row">
            <button class="tab-btn active" data-layer="rescue">Kendaraan Rescue</button>
            <button class="btn-info-layer" data-info="rescue" title="Info Kendaraan Rescue">i</button>
          </div>

           <div class="layer-row">
            <button class="tab-btn" data-layer="data_kecelakaan">Data Kecelakaan</button>
            <button class="btn-info-layer" data-info="data_kecelakaan" title="Info Data Kecelakaan">i</button>
          </div>

           <div class="layer-row">
             <button class="tab-btn" data-layer="prediksi_risiko">Prediksi Risiko Kecelakaan</button>
             <button class="btn-info-layer" data-info="prediksi_risiko" title="Info Prediksi Risiko">i</button>
           </div>

           <div class="layer-row">
             <button class="tab-btn" data-layer="speedlimit">Batas Kecepatan</button>
             <button class="btn-info-layer" data-info="speedlimit" title="Info Batas Kecepatan">i</button>
           </div>

           <div class="layer-row">
             <button class="tab-btn" data-layer="geofencing">Geofencing</button>
             <button class="btn-info-layer" data-info="geofencing" title="Info Geofencing">i</button>
           </div>

           <div class="layer-row">
             <button class="tab-btn active" data-layer="administrasi">Wilayah Administrasi</button>
             <button class="btn-info-layer" data-info="administrasi" title="Info Wilayah Administrasi">i</button>
           </div>
        </div>
        <div style="margin-top: 10px; border-top: 1px solid #ddd; padding-top: 8px;">
          <button id="btn-toggle-all-layers" class="tab-btn" style="background: #e0e0e0; color: #333; font-weight: 700; text-align: center; width: 100%;">
            Matikan Semua Layer
          </button>
      </div>
      </div><div id="legend-content" class="panel-content"></div>
    </div>
  </div>
</div>

<div id="measure-panel" style="display: none;">
  <div id="measure-body"> <div id="measure-controls">
        <button id="btn-measure-dist" class="measure-btn">Jarak</button>
        <button id="btn-measure-area" class="measure-btn">Luas</button>
        <button id="btn-measure-radius" class="measure-btn">Radius</button>
        <button id="btn-measure-clear" class="measure-btn" title="Hapus">✕</button>
      </div>
      <div id="measure-result">Hasil: 0</div>
  </div>
</div>

<div id="controls-bl">
    <div id="scale-box"></div>
    <div id="my-location-btn" class="control-btn-circle" title="Lokasi Saya">
        <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3c-.46-4.17-3.77-7.48-7.94-7.94V1h-2v2.06C6.83 3.52 3.52 6.83 3.06 11H1v2h2.06c.46 4.17 3.77 7.48 7.94 7.94V23h2v-2.06c4.17-.46 7.48-3.77 7.94-7.94H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/></svg>
    </div>
    <div id="compass" class="control-btn-circle" title="Reset North">
        <svg id="compass-arrow" viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2 L15 11 L12 9 L9 11 z"/><circle cx="12" cy="14" r="3" /></svg>
    </div>
    <div id="zoom-controls">
        <button id="zoom-in" class="zoom-btn" title="Zoom In">+</button>
        <button id="zoom-out" class="zoom-btn" title="Zoom Out">−</button>
    </div>
</div>

<script>
const URLS = {
  wilayah:         'wilayah',        
  troublespot:     'troublespot',
  blackspot:       'blackspot',
  traffic:         'traffic',
  blacklink:       'blacklink',
  blackarea:       'blackarea',
  cctv:            'cctv',
  objek_vital:     'objek_vital',
  prediksi_risiko: 'risk_prediction',
  speedlimit:      'speedlimit',
  geofencing:      'geofencing',
  administrasi:    'kependudukan',
  data_kecelakaan: 'data_kecelakaan',
  gerbang_tol:     'gerbang_tol',
  rescue:          'rescue',
  traffic_point:   'traffic_point',
  cctv_coverage:   'cctv_coverage',
  patrol:          'patrol'
};

const SATWIL_FIELD = {
  troublespot: 'Satwil',
  blackspot: 'Satwil',
  traffic: 'Satwil',
  blacklink: 'Satwil',
  blackarea: 'Satwil',
  cctv: 'Satwil',
  objek_vital: 'Satwil',
  prediksi_risiko: 'Satwil',
  speedlimit: 'Satwil',
  geofencing: 'Satwil',
  administrasi: 'Satwil',
  data_kecelakaan: 'Satwil',
  gerbang_tol: 'Satwil',
  rescue: 'Satwil',
  traffic_point: 'Satwil',
  cctv_coverage: 'Satwil',
  patrol: 'Satwil',
};

let selectedSatwilName = null;

const GLOBAL_DATA_CACHE = {};
let isDataLoaded = false;

function sanitizeForURL(name) {
    if (!name) return '';
    return String(name).trim().replace(/\s+/g, '-');
}

async function fetchGeoJSONLayer(layerKey) {
  if (layerKey === 'cctv') {
      const url = `{{ url('PemetaanNew/api/geojson/cctv') }}`;
      console.log(`Fetching dari Backend Dinamis: ${url}`);
      try {
          const res = await fetch(url);
          if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
          return await res.json();
      } catch (error) {
          console.error(`Fetch error untuk CCTV API:`, error);
          return { type: 'FeatureCollection', features: [] };
      }
  }

  const fileName = URLS[layerKey]; 
  
  if (!fileName) {
     console.error(`Nama file tidak ditemukan di URLS untuk key: ${layerKey}`);
     return { type: 'FeatureCollection', features: [] };
  }

  const url = `{{ asset('satupeta_webgis') }}/data/${fileName}.geojson`;
  console.log(`Fetching: ${url}`);
  
  try {
    const res = await fetch(url);
    if (!res.ok) {
      throw new Error(`Gagal memuat ${url}: ${res.status} ${res.statusText}`);
    }
    return await res.json();
  } catch (error) {
    console.error(`Fetch error untuk ${url}:`, error);
    return { type: 'FeatureCollection', features: [] };
  }
}

function filterDataByAttribute(layerKey, fullGeoJSON, satwilName) {
  const fieldName = SATWIL_FIELD[layerKey];
  if (!fieldName) {
    console.warn(`[${layerKey}] Tidak punya mapping 'Satwil'. Menampilkan semua data.`);
    return fullGeoJSON;
  }

  const filteredFeatures = fullGeoJSON.features.filter(f => {
    const prop = f.properties;
    if (!prop) return false;
    return (
      prop[fieldName] === satwilName || 
      prop.Satwil === satwilName || 
      prop.satwil === satwilName
    );
  });

  return { type: 'FeatureCollection', features: filteredFeatures };
}

const SUPA_URL = "https://unlfuizubjgjiophncxl.supabase.co/rest/v1/locations";
const SUPA_KEY = "sb_publishable_f_jYT8_qd1hwdBdcuHTkew_v2BW8ZLC";

let patrolIntervalId = null; 

async function fetchPatrolRealtime(satwilFilter) {
  try {
    let url = SUPA_URL;
    if (satwilFilter) {
      url += `?satwil=eq.${encodeURIComponent(satwilFilter)}`;
    }

    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'apikey': SUPA_KEY,
        'Authorization': `Bearer ${SUPA_KEY}`,
        'Content-Type': 'application/json'
      }
    });

    if (!response.ok) throw new Error('Gagal fetch Supabase');
    const data = await response.json();

    const geojson = {
      type: 'FeatureCollection',
      features: data
        .filter(item => {
             const lng = parseFloat(item.longitude || item.long || item.lng);
             const lat = parseFloat(item.latitude || item.lat);
             return !isNaN(lng) && !isNaN(lat) && lng !== 0 && lat !== 0;
        })
        .map(item => {
            const lng = parseFloat(item.longitude || item.long || item.lng);
            const lat = parseFloat(item.latitude || item.lat);
            return {
              type: 'Feature',
              geometry: { type: 'Point', coordinates: [lng, lat] },
              properties: {
                ...item,
                Nama: item.nama || item.name || item.petugas || 'Unit Patroli',
                status: item.status || 'Sedang Bertugas',
                Keterangan: item.keterangan || 'Tracking Realtime',
              }
            };
      })
    };
    return geojson;
  } catch (error) {
    console.error("Error fetching patrol GPS:", error);
    return null;
  }
}

async function startPatrolMonitoring(satwilName) {
    if (patrolIntervalId) {
        clearInterval(patrolIntervalId);
        patrolIntervalId = null;
    }

    lastPatrolAlertState = null; 
  
    if (!satwilName) return;

    console.log(`[Patrol] Restart monitoring untuk: ${satwilName}`);

    const runUpdate = async () => {
        if (selectedSatwilName && satwilName !== selectedSatwilName) return;

        const liveData = await fetchPatrolRealtime(satwilName);
        
        if (map && map.getSource('patrol')) {
            if (liveData) {
                map.getSource('patrol').setData(liveData);
                
                filteredLayerData['patrol'] = liveData;

                if (map.getLayer('patrol-symbol')) {
                    map.setLayoutProperty('patrol-symbol', 'visibility', 'visible');
                }

                if (followedPatrolUnit) {
                    const target = liveData.features.find(f => {
                         const pName = f.properties.Nama || f.properties.nama || 'Unit Patroli';
                         return pName === followedPatrolUnit;
                    });
                    if (target) {
                         map.easeTo({ center: target.geometry.coordinates, speed: 0.5 });
                    }
                }
                
                checkPatrolAlerts();

            } else {
                const emptyData = { type: 'FeatureCollection', features: [] };
                map.getSource('patrol').setData(emptyData);
                filteredLayerData['patrol'] = emptyData;
                checkPatrolAlerts();
            }
        }
    };

    await runUpdate();

    patrolIntervalId = setInterval(runUpdate, 3000);
}

const basemapTiles = {
  osm: { tiles:['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png'], tileSize:256 },
  esri:{ tiles:['https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}'], tileSize:256 },
  google: { tiles:['https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}'], tileSize: 256 },  
  imagery:{ tiles:['https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'], tileSize:256 },
  google_hybrid: { tiles:['https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}'], tileSize: 256 },
  google_dark: { tiles: ['https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}&apistyle=s.e%3Ag%7Cp.c%3A%23242f3e%2Cs.e%3Aw%7Cp.c%3A%2317263c%2Cs.t%3A3%7Cs.e%3Ag.f%7Cp.c%3A%2338414e%2Cs.t%3A3%7Cs.e%3Ag.s%7Cp.c%3A%23212a37%2Cs.e%3Al.t.f%7Cp.c%3A%239ca5b3%2Cs.e%3Al.t.s%7Cp.c%3A%23000000'], tileSize: 256  },
  esri_dark: { tiles:['https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}'], tileSize: 256 },
  esri_light: { tiles:['https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}'], tileSize: 256 },
  carto_dark: { tiles:['https://a.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png'], tileSize:256 },
  stadia: { tiles:['https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}.png?api_key=643843e2-983b-4c1b-9ae4-bf2718d64175'], tileSize:256 }
  };

const ICON_SVGS = {
  'road-closed': `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><circle cx='32' cy='32' r='30' fill='#d32f2f'/><rect x='10' y='30' width='44' height='6' rx='3' fill='#fff'/></svg>`,
  'road-disturbance': `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' fill='#ffc107'/><path fill='#000' d='M11 7h2v6h-2zm0 8h2v2h-2z'/></svg>`,
  'construction':`<svg height="175px" width="176px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 53.753 53.753" xml:space="preserve"><path style="fill:#EEAA5B;" d="M51.716,36.875l-12.52-4.006l0.073,0.265c0.406,1.472,0.048,3.07-1.013,4.167	c-1.581,1.634-4.844,3.452-11.54,3.452s-9.959-1.817-11.54-3.452c-1.061-1.097-1.419-2.695-1.013-4.167l0.413-1.496L1.954,36.913	c-1.528,0.873-1.67,3.022-0.27,4.088L25.869,53.25c0.848,0.646,2.017,0.672,2.892,0.063l23.221-12.242	C53.499,40.017,53.353,37.73,51.716,36.875z"/><path style="fill:#F0C419;" d="M26.716,18.753c3.277,0,6.589-1.393,7.86-1.992c0.053-0.025,0.11-0.026,0.165-0.041l-2.104-7.627	c-0.105,0.282-0.32,0.519-0.63,0.613L31.28,9.933c-1.392,0.44-2.593,0.82-4.563,0.82c-2.284,0-3.287-0.348-4.805-0.875l-0.519-0.179	c-0.294-0.101-0.498-0.33-0.6-0.6l-2.127,7.709c0.069,0.019,0.139,0.026,0.205,0.06C21.178,18.084,23.964,18.753,26.716,18.753z"/><path style="fill:#F0C419;" d="M22.039,7.807l0.528,0.182c1.466,0.509,2.201,0.764,4.148,0.764c1.662,0,2.626-0.305,3.96-0.727	l0.749-0.233c0.33-0.101,0.658-0.009,0.906,0.19l-0.615-2.23c-0.479-1.519-0.986-2.708-1.485-3.639	c-1.515-2.823-5.63-2.821-7.108,0.022c-0.483,0.928-0.964,2.111-1.407,3.618L21.1,7.988C21.357,7.781,21.702,7.692,22.039,7.807z"/><path style="fill:#F0C419;" d="M27.071,29.753c4.709,0,8.203-1.898,9.764-3.524c0.13-0.135,0.292-0.209,0.46-0.255l-2.023-7.333	c-1.483,0.686-4.99,2.112-8.555,2.112c-2.996,0-6.031-0.72-8.581-2.02l-1.951,7.073c0.202,0.055,0.393,0.158,0.531,0.337	C18.378,28.302,22.54,29.753,27.071,29.753z"/><path style="fill:#F0C419;" d="M39.27,33.134l-1.415-5.129c-2.041,1.878-5.798,3.748-10.784,3.748	c-4.835,0-9.223-1.491-11.466-3.844l-1.442,5.226c-0.406,1.472-0.048,3.07,1.013,4.167c1.581,1.634,4.844,3.452,11.54,3.452	s9.959-1.817,11.54-3.452C39.318,36.204,39.676,34.606,39.27,33.134z"/><path style="fill:#F9F7E4;" d="M37.855,28.005l-0.56-2.031c-0.168,0.046-0.33,0.12-0.46,0.255c-1.561,1.626-5.055,3.524-9.764,3.524	c-4.53,0-8.692-1.451-10.355-3.61c-0.137-0.179-0.328-0.282-0.531-0.337l-0.58,2.103c2.243,2.353,6.632,3.844,11.466,3.844	C32.056,31.753,35.814,29.883,37.855,28.005z"/><path style="fill:#F9F7E4;" d="M35.272,18.641l-0.53-1.921c-0.055,0.015-0.112,0.015-0.165,0.041	c-1.271,0.599-4.583,1.992-7.86,1.992c-2.752,0-5.538-0.669-7.845-1.885c-0.066-0.035-0.136-0.041-0.205-0.06l-0.531,1.925	c2.55,1.301,5.585,2.02,8.581,2.02C30.281,20.753,33.788,19.328,35.272,18.641z"/><path style="fill:#F9F7E4;" d="M21.393,9.699l0.519,0.179c1.518,0.526,2.521,0.875,4.805,0.875c1.971,0,3.172-0.38,4.563-0.82	l0.728-0.228c0.31-0.094,0.525-0.331,0.63-0.613l-0.306-1.11c-0.248-0.199-0.576-0.291-0.906-0.19l-0.749,0.233	c-1.334,0.422-2.298,0.727-3.96,0.727c-1.947,0-2.683-0.255-4.148-0.764l-0.528-0.182c-0.337-0.115-0.682-0.026-0.94,0.181	l-0.307,1.111C20.895,9.37,21.098,9.599,21.393,9.699z"/><path style="fill:#E09B58;" d="M14.575,31.638L1.987,36.899l23.92,11.377c1.124,0.605,2.482,0.586,3.588-0.053l22.245-11.331	c-0.009-0.005-0.015-0.013-0.025-0.017l-12.52-4.006l0.073,0.265c0.406,1.472,0.048,3.07-1.013,4.167	c-1.581,1.634-4.844,3.452-11.54,3.452s-9.959-1.817-11.54-3.452c-1.061-1.097-1.419-2.695-1.013-4.167L14.575,31.638z"/><path style="fill:#B77D4E;" d="M42.716,34.753c0-0.263-0.03-0.522-0.068-0.779l-3.452-1.105l0.073,0.265	c0.406,1.472,0.048,3.07-1.013,4.167c-1.581,1.634-4.844,3.452-11.54,3.452s-9.959-1.817-11.54-3.452	c-1.061-1.097-1.419-2.695-1.013-4.167l0.413-1.496l-2.415,1.009c-0.28,0.677-0.444,1.379-0.444,2.106c0,4.971,6.94,9,15.5,9	S42.716,39.724,42.716,34.753z"/></svg>`,
  'perlambatan': `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='#d32f2f' d='M18.5,4H5.5C4.67,4,4,4.67,4,5.5v3C4,9.33,4.67,10,5.5,10h13c0.83,0,1.5-0.67,1.5-1.5v-3C20,4.67,19.33,4,18.5,4z M18.5,11h-13C4.67,11,4,11.67,4,12.5v3c0,0.83,0.67,1.5,1.5,1.5h13c0.83,0,1.5-0.67,1.5-1.5v-3C20,11.67,19.33,11,18.5,11z'/><rect x='5' y='6' width='2.8' height='2' fill='#fff'/><rect x='16.2' y='6' width='2.8' height='2' fill='#fff'/><rect x='5' y='13' width='2.8' height='2' fill='#fff'/><rect x='16.2' y='13' width='2.8' height='2' fill='#fff'/></svg>`,
  'kecelakaan': `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='#212121' d='M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11C5.84 5 5.28 5.42 5.08 6.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 12c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9s1.5.67 1.5 1.5S7.33 12 6.5 12zm11 0c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z'/><path fill='red' d='M5 4L3 8l2 1l2-4zm14 0l-2 4l2 1l2-4z'/></svg>`,
  'crash': `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 15 15'><circle cx='7.5' cy='7.5' r='6.5' fill='red'/><g transform='translate(7.5 7.5) scale(0.5) translate(-7.5 -7.5)'><path fill='#fff' d='M12.6,8.7,11.5,6.5a1.05,1.05,0,0,0-.9-.5H4.4a1.05,1.05,0,0,0-.9.5L2.4,8.7,1.16,9.852a.5.5,0,0,0-.16.366V14.5a.5.5,0,0,0,.5.5h2c.2,0,.5-.2.5-.4V14h7v.5c0,.2.2.5.4.5h2.1a.5.5,0,0,0,.5-.5V10.218a.5.5,0,0,0-.16-.366ZM4.5,7h6l1,2h-8ZM5,11.6c0,.2-.3.4-.5.4H2.4c-.2,0-.4-.3-.4-.5V10.4c.1-.3.3-.5.6-.4l2,.4c.2,0,.4.3.4.5Zm8-.1c0,.2-.2.5-.4.5H10.5c-.2,0-.5-.2-.5-.4v-.7c0-.2.2-.5.4-.5l2-.4c.3-.1.5.1.6.4ZM8,4H7L6,0H9ZM1.1,1.8,3.7.3l1.1,4L4,4.7ZM11.3.3l2.6,1.5L11,4.8l-.9-.5Z'/></g></svg>`,
  'blackspot': `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><polygon points='32,6 58,54 6,54' fill='#212121' stroke='#e53935' stroke-width='5' stroke-linejoin='round'/><path fill='#fff' d='M30,22 h4 v14 h-4 z M30,40 h4 v4 h-4 z'/></svg>`,
  'cctv-on': `<svg width="125px" height="125px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="1" d="M6.76017 22H17.2402C20.0002 22 21.1002 20.31 21.2302 18.25L21.7502 9.99C21.8902 7.83 20.1702 6 18.0002 6C17.3902 6 16.8302 5.65 16.5502 5.11L15.8302 3.66C15.3702 2.75 14.1702 2 13.1502 2H10.8602C9.83017 2 8.63017 2.75 8.17017 3.66L7.45017 5.11C7.17017 5.65 6.61017 6 6.00017 6C3.83017 6 2.11017 7.83 2.25017 9.99L2.77017 18.25C2.89017 20.31 4.00017 22 6.76017 22Z" fill="#006c01"/><path d="M13.5 8.75H10.5C10.09 8.75 9.75 8.41 9.75 8C9.75 7.59 10.09 7.25 10.5 7.25H13.5C13.91 7.25 14.25 7.59 14.25 8C14.25 8.41 13.91 8.75 13.5 8.75Z" fill="#fff"/><path d="M11.9991 18.1301C13.8659 18.1301 15.3791 16.6168 15.3791 14.7501C15.3791 12.8834 13.8659 11.3701 11.9991 11.3701C10.1324 11.3701 8.61914 12.8834 8.61914 14.7501C8.61914 16.6168 10.1324 18.1301 11.9991 18.1301Z" fill="#fff"/></svg>`,
  'cctv-off': `<svg width="125px" height="125px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21.7709 2.22988C21.4709 1.92988 20.9809 1.92988 20.6809 2.22988L2.23086 20.6899C1.93086 20.9899 1.93086 21.4799 2.23086 21.7799C2.38086 21.9199 2.57086 21.9999 2.77086 21.9999C2.97086 21.9999 3.16086 21.9199 3.31086 21.7699L21.7709 3.30988C22.0809 3.00988 22.0809 2.52988 21.7709 2.22988Z" fill="#000000"/><path opacity="1" d="M21.7507 9.99L21.2307 18.25C21.1007 20.31 20.0007 22 17.2407 22H6.7607C6.1707 22 5.6607 21.92 5.2207 21.78L20.2507 6.75C21.2307 7.48 21.8407 8.67 21.7507 9.99Z" fill="#df1000"/><path opacity="1" d="M18.0002 6L3.48017 20.52C3.03017 19.89 2.82017 19.1 2.77017 18.25L2.25017 9.99C2.11017 7.83 3.83017 6 6.00017 6C6.61017 6 7.17017 5.65 7.45017 5.11L8.17017 3.66C8.63017 2.75 9.83017 2 10.8602 2H13.1502C14.1702 2 15.3702 2.75 15.8302 3.66L16.5502 5.11C16.8302 5.65 17.3902 6 18.0002 6Z" fill="#df1000"/><path d="M9.5 10.75H6.5C6.09 10.75 5.75 10.41 5.75 10C5.75 9.59 6.09 9.25 6.5 9.25H9.5C9.91 9.25 10.25 9.59 10.25 10C10.25 10.41 9.91 10.75 9.5 10.75Z" fill="#fff"/><path d="M12.5709 11.4299L8.68086 15.3199C8.65086 15.1399 8.63086 14.9399 8.63086 14.7499C8.63086 12.8899 10.1409 11.3799 12.0009 11.3799C12.1909 11.3799 12.3909 11.3999 12.5709 11.4299Z" fill="#fff"/><path d="M15.38 14.75C15.38 16.61 13.86 18.12 12 18.12C11.13 18.12 10.34 17.79 9.75 17.25L14.5 12.5C15.05 13.09 15.38 13.88 15.38 14.75Z" fill="#fff"/></svg>`,
  'ov-plane': `<svg width="200px" height="200px" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--noto" preserveAspectRatio="xMidYMid meet"><path d="M37.08 80.79S9.61 74.92 9.14 75.01c-.47.09-5.97 5.59-5.78 6.44C3.55 82.31 27.61 95 27.61 95l6.91-7.29l2.56-6.92z" fill="#006ca8"></path><path d="M33.29 100.21s13.35 23.11 13.92 23.11s6.72-4.74 6.72-5.21c0-.47-5.68-28.03-5.68-28.03l-9.76 1.14l-5.2 8.99z" fill="#006ca8"></path><path d="M22.79 25.78s-1.92 2.17-2.33 2.94s-.48 2.09.4 2.56c.88.47 1.95.15 2.48-.44s3.14-3.8 3.14-3.8s-3.46-1.44-3.69-1.26z" fill="#9a9a9a"></path><path d="M36.81 33.63s-1.55 1.84-2.18 2.68c-.61.81-.6 2.08.25 2.68c1.22.86 2.43-.1 3.09-.81c.61-.66 2.33-2.68 2.33-2.68l-3.49-1.87z" fill="#9a9a9a"></path><path d="M50.08 40.77s-2.03 2.03-2.63 2.84c-.67.89-.82 2.19.1 2.89c1.01.76 2.13.41 3.04-.41c.91-.81 2.94-3.04 2.94-3.04l-3.45-2.28z" fill="#9a9a9a"></path><path d="M85.11 75.05s-2.38 1.65-2.97 2.29c-.58.64-.97 1.92-.22 2.59c.74.67 1.86.61 2.52.17c.66-.44 3.96-2.93 3.96-2.93s-3.02-2.23-3.29-2.12z" fill="#9a9a9a"></path><path d="M93.13 87.61s-1.97 1.38-2.79 2.03c-.8.63-1.11 1.86-.44 2.66c.95 1.14 2.38.53 3.19.01c.76-.48 2.94-2 2.94-2l-2.9-2.7z" fill="#9a9a9a"></path><path d="M100.47 100.45s-2.45 1.48-3.24 2.12c-.86.71-1.32 1.93-.59 2.83c.8.98 1.97.9 3.05.34s3.58-2.24 3.58-2.24l-2.8-3.05z" fill="#9a9a9a"></path><path d="M57.27 23.73s-.35-2.33 1.54-4.96c1.89-2.63 8.16-8.77 9.13-8.82s3.29 1.18 5.62 3.47c2.22 2.17 3.25 4.96 3.29 5.62c.04.66-4.34 5-5.48 6.05c-1.14 1.05-4.21 4.47-4.21 4.47l-9.89-5.83z" fill="#9a9a9a"></path><path d="M60.13 24.08s7.68-9.39 9.74-7.98c2.08 1.42-6.23 9.48-6.23 9.48l-3.51-1.5z" fill="#c8c8c8"></path><path d="M58.37 23.48s4.35-4.41 4.83-5.11c.48-.7.04-1.75-.35-2.33c-.39-.57-.99-.79-.99-.79s-3.07 2.75-4.12 5.16c-.82 1.87-.68 2.82-.68 2.82l1.31.25z" fill="#848484"></path><path d="M65.86 25.75s1.65-1.71 2.48-2.39c.75-.61 1.86-.55 2.52-.16c.85.5 1.05 1.36 1.05 1.36l-3.06 3.25l-2.99-2.06z" fill="#848484"></path><path d="M101.96 59.45s2.51-2.92 3.97-4.38c1.51-1.51 3.97-3.56 4.47-3.52c.5.05 2.71 1.39 4.79 3.52c2.37 2.42 3.84 5.43 3.88 5.71c.05.27-4.38 4.7-6.35 6.39c-1.41 1.21-10.18 4.79-10.18 4.34c.02-.46-.58-12.06-.58-12.06z" fill="#9a9a9a"></path><path d="M103.41 65.1s7.26-7.96 8.92-6.47c1.66 1.49-8.31 10.45-8.31 10.45l-.61-3.98z" fill="#c8c8c8"></path><path d="M102.87 63.1l2.65-2.92c.94-1.03.88-2.41.6-3.01c-.36-.77-1.29-.95-1.29-.95l-3.45 3.67l1.49 3.21z" fill="#848484"></path><path d="M105.38 69.71s4.16-3.75 4.87-4.34c.71-.59 1.86-1.01 2.77-.38c.68.47.98 1.04.98 1.04s-3.2 3.48-5.13 4.71c-1.21.77-4.07 1.21-4.07 1.21l.58-2.24z" fill="#848484"></path><path d="M11.65 8.92c-.98.08-7.19 7.42-7.41 7.96s0 1.42 1.53 2.18c1.52.76 44.47 23.44 45.13 23.98c.65.55 9.16 10.03 9.16 10.03s21.04-14.5 20.93-14.93c-.11-.44-.76-9.48-.76-9.48S13.07 8.81 11.65 8.92z" fill="#006ca8"></path><path d="M73.03 65.83s12.86 11.66 13.95 13.08c1.09 1.42 23.33 41.97 23.66 42.62c.33.65 1.74 1.2 2.51.44s6.54-5.78 6.65-6.98c.11-1.2-21.37-70.42-21.37-70.42l-16.57 6l-8.83 15.26z" fill="#006ca8"></path><path d="M66.81 43.26s-10.14-9.81-10.9-10.36c-.76-.54-48.29-20.16-48.29-20.16l-1.95 2.15s48.18 20.35 48.95 20.89c.77.54 10.2 10.2 10.2 10.2l1.99-2.72z" fill="#014eac"></path><path d="M82.4 60.45s11.17 13.72 11.84 14.61s20.26 45.65 20.26 45.65l1.95-1.82S96.88 73.22 96.53 72.6c-.35-.62-13.36-16.7-13.36-16.7l-.77 4.55z" fill="#014eac"></path><path d="M24.22 98.4s-4.18 5.98-4.18 6.45c0 .48 2.8 3.54 3.12 3.54s6.4-4.34 6.4-4.34l-.85-4.34l-4.49-1.31z" fill="#9a9a9a"></path><path d="M24.22 98.4s8.28-13.93 17.15-26.38c9.11-12.79 16.5-20.16 27.86-32.52C83.48 24 92.97 15.22 101.75 9.31c7.28-4.9 17.68-8.22 21.39-4.91c4.07 3.64-.42 15.12-4.99 21.66c-10.37 14.84-19.27 21.9-29.48 31.81c-10.07 9.77-18.58 18.25-29.12 26.16c-7.1 5.33-30 20.04-30 20.04l-5.33-5.67z" fill="#c8c8c8"></path><path d="M103.92 11.05l2.77 2.92s4.64-4.47 8.11-1.14c3.56 3.41-1.35 8.25-1.35 8.25l2.99 3.34s8.11-9.18 1.49-15.15c-5.76-5.2-14.01 1.78-14.01 1.78z" fill="#3b5361"></path><path d="M47.84 74c-1.29 1.24-16.53 19.28-17.38 20.35c-1.09 1.38-1.59 3.04-.36 4.06c1.23 1.01 2.56.01 3.77-.8c1.52-1.01 19.18-16.59 20.35-17.67c1.88-1.74 2.2-4.88.22-6.66c-2.18-1.96-4.64-1.16-6.6.72z" fill="#9a9a9a"></path><path d="M47.29 69.91c-.87-.75-1.95-.25-3.09.98c-.98 1.07-1.43 2.73-.45 3.4c.98.67 2.28-.31 3.09-1.3c.81-.98 1.43-2.23.45-3.08z" fill="#dfdfdf"></path><path d="M50.11 64.09c-.9 1.03-2.21 2.73-.98 3.94c1.21 1.19 3.04-.32 3.84-1.22c.79-.9 9.84-11.52 21.29-22.96c11.18-11.18 18.7-18.25 19.86-19.49c.98-1.05.89-2.72.03-3.25c-1.02-.63-2.15-.23-3.55 1.17S79.7 32.37 70.74 41.43S51.23 62.8 50.11 64.09z" fill="#dfdfdf"></path><path d="M105.47 41.61s-.55-3.72-2.86-2.26c-2.48 1.57-.63 5.83-.63 5.83s.93-.87 1.92-1.91c.82-.85 1.3-1.35 1.57-1.66z" fill="#3b5361"></path><path d="M96.6 50.36s-.39-4.77-3.01-2.81c-2.09 1.57-.67 6.26-.67 6.26s1.2-1.09 1.98-1.83c.79-.75 1.7-1.62 1.7-1.62z" fill="#3b5361"></path><path d="M88.14 58.38s-.3-3.96-2.65-2.65s-.78 5.99-.78 5.99s1.04-.94 1.74-1.64s1.69-1.7 1.69-1.7z" fill="#3b5361"></path><path d="M78.56 67.64s-.18-3.94-2.49-2.89c-2.4 1.09-.96 6.14-.96 6.14s1.17-1.09 1.82-1.7c.66-.6 1.63-1.55 1.63-1.55z" fill="#3b5361"></path></svg>`,
  'ov-hospital': `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 15 15'><rect x='1' y='1' width='13' height='13' fill='#ffffff' stroke='#e53935' stroke-width='1'/><g transform='translate(7.5 7.5) scale(0.7) translate(-7.5 -7.5)'><path fill='#e53935' d='m12.5038 3h-10.00777c-.26639.00235-.52096.11039-.70772.30036-.18677.18998-.29046.44634-.28828.71274v5.5807c-.00063.18713.05047.37079.14765.5307.09718.1599.23667.2899.40305.3755l4.78 2.8394c.20725.1056.43653.1606.6691.1606.23258 0 .46186-.055.6691-.1606l4.78037-2.8394c.1664-.0856.3059-.2155.4031-.3755.0972-.15989.1483-.34357.1476-.5307v-5.5807c.0012-.13194-.0237-.2628-.0732-.38512-.0494-.12232-.1225-.2337-.215-.32777-.0925-.09408-.2027-.16901-.3241-.22052-.1215-.05151-.2519-.07859-.3839-.07969zm-2.0038 5.5h-1.99997v2h-2v-2h-2v-2h2v-2h2v2h1.99997z'/></g></svg>`,
  'ov-school': `<svg width="150px" height="150px" viewBox="0 0 2050 2050" data-name="Layer 2" id="Layer_2" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:#4d4c78;}.cls-2{fill:#323150;}.cls-3{fill:#3f3e6b;}.cls-4{fill:#64639c;}.cls-5{fill:#f4c23f;}.cls-6{fill:#f4a93f;}.cls-7{fill:#de3226;}.cls-8{fill:#b11a31;}.cls-9{fill:#f8881b;}.cls-10{fill:#f08000;}.cls-11{fill:#fad564;}.cls-12{fill:#f44533;}</style></defs><title/><path class="cls-1" d="M978.4,193.8,170.6,511.4c-18.8,7.5-18.8,34.1,0,41.6l854.9,336.1a21.9,21.9,0,0,0,16.3,0L1896.7,553c18.9-7.5,18.9-34.1,0-41.6L1088.9,193.8A150.6,150.6,0,0,0,978.4,193.8Z"/><polygon class="cls-2" points="741 492.6 473.1 671.9 736.7 775.6 741 492.6"/><polygon class="cls-2" points="1326.3 492.6 1594.2 671.9 1330.7 775.6 1326.3 492.6"/><path class="cls-1" d="M1434.8,1108.8H632.5L706.8,530A44.3,44.3,0,0,1,741,492.6q319.8-71.2,586.9-.9a43.7,43.7,0,0,1,32.4,36.9Z"/><path class="cls-2" d="M833.3,620.9a44.2,44.2,0,0,1,34.2-37.4q267-59.4,497.3-20.3l-4.5-34.6a43.7,43.7,0,0,0-32.4-36.9q-267-70.3-586.9.9A44.3,44.3,0,0,0,706.8,530l-74.3,578.8H770.7Z"/><ellipse class="cls-2" cx="1033.7" cy="1108.8" rx="401.1" ry="49.3"/><path class="cls-2" d="M1631.4,1016.8a19.9,19.9,0,0,1-20-20V429a20,20,0,0,1,40,0V996.8A20,20,0,0,1,1631.4,1016.8Z"/><path class="cls-1" d="M1628.6,1213.3a69.4,69.4,0,0,1-69.3-69.3V1032.8a69.3,69.3,0,0,1,138.5,0V1144A69.4,69.4,0,0,1,1628.6,1213.3Z"/><path class="cls-2" d="M1594.2,1182V1070.8a69.2,69.2,0,0,1,99.1-62.5,69.2,69.2,0,0,0-134,24.5V1144a69.4,69.4,0,0,0,39.4,62.5A68.8,68.8,0,0,1,1594.2,1182Z"/><path class="cls-1" d="M1827.5,1285.4a67.5,67.5,0,0,0-54.3-27.3H240.8a67.6,67.6,0,0,0-54.3,27.3q-94.6,127.6,0,255.2a67.6,67.6,0,0,0,54.3,27.3H1773.2a67.5,67.5,0,0,0,54.3-27.3Q1922.3,1413,1827.5,1285.4Z"/><path class="cls-3" d="M1867.2,1457.2H334.8a67.5,67.5,0,0,1-54.3-27.3q-63.7-85.8-41.7-171.8a68.2,68.2,0,0,0-52.3,27.3q-94.6,127.6,0,255.2a67.6,67.6,0,0,0,54.3,27.3H1773.2a67.5,67.5,0,0,0,54.3-27.3q31.1-41.7,41.7-83.4Z"/><rect class="cls-2" height="309.82" width="124.9" x="433.6" y="1258.1"/><rect class="cls-2" height="309.82" width="124.9" x="1455.5" y="1258.1"/><rect class="cls-4" height="379.76" rx="60.3" ry="60.3" transform="translate(-104.3 198.6) rotate(-8.2)" width="120.7" x="1272.3" y="636.3"/><rect class="cls-4" height="379.76" rx="60.3" ry="60.3" transform="translate(536.5 1470) rotate(-70.1)" width="120.7" x="1256.6" y="162.3"/><rect class="cls-4" height="114.13" rx="28.4" ry="28.4" width="56.8" x="1639.5" y="1044"/><path class="cls-5" d="M1283.9,1533.8c0,27.9-40.4,49.1-48.6,74.2s11.5,66.9-4.3,88.6-61,15.4-82.9,31.4-29.4,60.7-55.5,69.2-57.7-23.4-85.6-23.4-60.4,31.6-85.5,23.4-33.8-53.4-55.5-69.2-67-9.5-82.9-31.4,4.1-62.5-4.3-88.6-48.6-46.3-48.6-74.2,40.4-49,48.6-74.1-11.5-66.9,4.3-88.6,61-15.5,82.9-31.4,29.4-60.8,55.5-69.2,57.7,23.4,85.5,23.4,60.5-31.6,85.6-23.4,33.8,53.4,55.5,69.2,67,9.5,82.9,31.4-4.2,62.5,4.3,88.6S1283.9,1506,1283.9,1533.8Z"/><path class="cls-6" d="M1000.6,1732.2A181.2,181.2,0,0,1,872.5,1423a182.8,182.8,0,0,1,57.6-38.8,181.1,181.1,0,0,1,237.3,237.4,181.1,181.1,0,0,1-166.8,110.6Z"/><path class="cls-6" d="M1115.4,1590.5a15.1,15.1,0,0,0-19.9-7.6l-111.2,50a18.1,18.1,0,0,0-4.2,2.8,18.1,18.1,0,0,0-4.2-2.8l-111.2-50a15.1,15.1,0,0,0-19.9,7.6l-51.6,114.9c20.1,11.6,54.5,9.3,72.8,22.6,21,15.3,28.9,57.8,53,68.3l61.1-135.9,54.2,120.5c20.3,8.8,41.1,21.9,58.3,16.3,26.1-8.5,33.8-53.4,55.5-69.2,6.6-4.8,15.3-7.6,24.7-9.7Z"/><path class="cls-7" d="M891.4,1859.1l-26.3-46.6a15,15,0,0,0-16.3-7.3l-52.3,11.2a15.1,15.1,0,0,1-16.9-20.9l92.2-205a15.1,15.1,0,0,1,19.9-7.6l111.2,50a15.2,15.2,0,0,1,7.5,19.9l-92.2,205A15,15,0,0,1,891.4,1859.1Z"/><path class="cls-8" d="M904,1628.1a31.1,31.1,0,0,0-15.2-41.3l-8.9-4.1a14.7,14.7,0,0,0-8.1,7.8l-92.2,205a15.1,15.1,0,0,0,16.9,20.9l22.6-4.9Z"/><path class="cls-7" d="M1122.7,1859.1l26.3-46.6a15,15,0,0,1,16.3-7.3l52.3,11.2a15.1,15.1,0,0,0,16.9-20.9l-92.2-205a15.1,15.1,0,0,0-19.9-7.6l-111.2,50a15.1,15.1,0,0,0-7.5,19.9l92.1,205A15.1,15.1,0,0,0,1122.7,1859.1Z"/><path class="cls-8" d="M1040.1,1643.8l-33.7,15,89.4,199a15.1,15.1,0,0,0,26.9,1.3l7.5-13.3Z"/><path class="cls-8" d="M1002.9,1632.9l-111.2-50a15.1,15.1,0,0,0-19.9,7.6l-25.2,55.9a181.7,181.7,0,0,0,128.9,84.1l34.9-77.7A15.2,15.2,0,0,0,1002.9,1632.9Z"/><path class="cls-8" d="M1161.8,1633.7l-19.5-43.2a15.1,15.1,0,0,0-19.9-7.6l-111.2,50a15.1,15.1,0,0,0-7.5,19.9l33.9,75.6a180.7,180.7,0,0,0,33.5-10.5,182.6,182.6,0,0,0,90.7-84.2Z"/><circle class="cls-9" cx="1007" cy="1529.4" r="169.5"/><circle class="cls-10" cx="1007" cy="1529.4" r="149.7"/><circle class="cls-5" cx="1007" cy="1529.4" r="132.9"/><path class="cls-11" d="M1125.4,1333.5h0a31.4,31.4,0,0,0,9.5-8.6c-13.1-19.8-22.2-47.9-42.3-54.4-6.9-2.3-14.3-1.5-22.1.8a30.3,30.3,0,0,0,.7,29.2l12.6,21.9A30.5,30.5,0,0,0,1125.4,1333.5Z"/><path class="cls-11" d="M1007.7,1303.9a30.6,30.6,0,0,0,4.3-10.3,41.9,41.9,0,0,1-5,.3c-24.4,0-52.5-24.4-76-24.8a30.4,30.4,0,0,0,13.4,29.7l21.2,13.9a30.5,30.5,0,0,0,42.1-8.8Z"/><path class="cls-11" d="M1235.3,1459.7c-8.5-26.1,11.5-66.9-4.3-88.6-9.9-13.7-31.3-16.3-51.2-20.2a48.4,48.4,0,0,0-6.7,29.9l3.9,40.8a49.3,49.3,0,0,0,53.8,44.4h0a49.3,49.3,0,0,0,6.7-1.1A38.5,38.5,0,0,1,1235.3,1459.7Z"/><rect class="cls-12" height="85.82" rx="22.5" ry="22.5" transform="translate(833.7 -224.2) rotate(24.8)" width="45" x="903.9" y="1739.5"/><rect class="cls-12" height="85.82" rx="22.5" ry="22.5" transform="translate(-600.1 629) rotate(-24)" width="45" x="1156" y="1682.4"/><rect class="cls-4" height="89.05" rx="44.5" ry="44.5" width="181.2" x="1594.2" y="1258.1"/><rect class="cls-4" height="89.05" rx="44.5" ry="44.5" width="181.2" x="1242.1" y="1258.1"/><rect class="cls-4" height="89.05" rx="44.5" ry="44.5" width="139.5" x="267.3" y="1258.1"/></svg>`,
  'ov-government': `<svg width="150px" height="150px" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--twemoji" preserveAspectRatio="xMidYMid meet"><path fill="#DAC8B1" d="M34 13a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2v-2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2zm-22 0a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2z"></path><path fill="#F1DCC1" d="M36 34a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V13a2 2 0 0 1 2-2h32a2 2 0 0 1 2 2v21z"></path><path fill="#DAC8B1" d="M22 9V7c0-.738-.404-1.376-1-1.723V5a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v.277c-.595.347-1 .985-1 1.723v2h-1v27h10V9h-1z"></path><path fill="#55ACEE" d="M14 7h2v2h-2zm6 0h2v2h-2zm-3 0h2v2h-2z"></path><path fill="#3B88C3" d="M15 15h2v14h-2zm4 0h2v14h-2z"></path><path fill="#55ACEE" d="M24 17h2v12h-2zm4 0h2v12h-2zm4 0h2v12h-2zM2 17h2v12H2zm4 0h2v12H6zm4 0h2v12h-2zM2 30h2v2H2zm4 0h2v2H6zm4 0h2v2h-2z"></path><path fill="#3B88C3" d="M15 30h2v2h-2zm4 0h2v2h-2z"></path><path fill="#55ACEE" d="M24 30h2v2h-2zm4 0h2v2h-2zm4 0h2v2h-2z"></path><path fill="#66757F" d="M2 33h2v3H2zm4 0h2v3H6zm4 0h2v3h-2zm5 0h2v3h-2zm4 0h2v3h-2zm5 0h2v3h-2zm4 0h2v3h-2zm4 0h2v3h-2z"></path></svg>`,
  'ov-police': `<svg width="150px" height="150px" viewBox="0 0 1024 1024" class="icon"  version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M213.333333 896h597.333334c46.933333 0 85.333333-38.4 85.333333-85.333333V334.933333c0-34.133333-21.333333-66.133333-55.466667-78.933333L512 128 183.466667 256C149.333333 268.8 128 300.8 128 334.933333V810.666667c0 46.933333 38.4 85.333333 85.333333 85.333333z" fill="#3F51B5" /><path d="M725.333333 362.666667l-42.666666-42.666667c-34.133333 34.133333-136.533333 36.266667-170.666667 0-36.266667 36.266667-138.666667 34.133333-170.666667 0l-42.666666 42.666667s25.6 44.8 25.6 85.333333c0 64-25.6 89.6-25.6 172.8 0 155.733333 181.333333 187.733333 213.333333 211.2 34.133333-23.466667 213.333333-55.466667 213.333333-211.2 0-85.333333-25.6-106.666667-25.6-172.8 0-38.4 25.6-85.333333 25.6-85.333333z" fill="#FFC107" /><path d="M529.066667 765.866667c-6.4 2.133333-10.666667 4.266667-14.933334 6.4-4.266667-2.133333-10.666667-4.266667-17.066666-6.4-74.666667-29.866667-142.933333-64-142.933334-142.933334 0-38.4 6.4-61.866667 12.8-85.333333s12.8-51.2 12.8-87.466667c0-19.2-4.266667-38.4-8.533333-55.466666 23.466667 6.4 44.8 6.4 57.6 6.4 17.066667 0 53.333333-2.133333 85.333333-17.066667 32 14.933333 68.266667 17.066667 85.333334 17.066667 12.8 0 34.133333 0 57.6-6.4-4.266667 17.066667-8.533333 36.266667-8.533334 55.466666 0 38.4 6.4 66.133333 12.8 89.6s12.8 46.933333 12.8 83.2c-4.266667 78.933333-70.4 113.066667-145.066666 142.933334z" fill="#E09E00" /><path d="M578.133333 667.733333L512 627.2l-66.133333 38.4 14.933333-74.666667-57.6-51.2 76.8-6.4 32-70.4 32 70.4 76.8 6.4-57.6 51.2z" fill="#FFEB3B" /></svg>`,
  'ov-train': `<svg width="150px" height="150px" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--noto" preserveAspectRatio="xMidYMid meet"><path d="M29.3 105.76s-.37 1.11-.38 1.98s.12 2.64 1.68 2.64c2.02 0 24.86-.07 35.87 0s31.28-.14 32.7-.22c1.42-.07 1.03-3.22.94-4.3l-70.81-.1z" fill="#543529"></path><path d="M102.23 116.02l2.89 1.99s1.87 1.63 2.12 2.61s-.33 2.84-1.16 3.23l-43.34.15l-40.42-.12c-.63-.16-1.39-.77-1.58-1.89s.43-2.37.82-3.15c.39-.78 8.43-2.81 8.43-2.81l72.24-.01z" fill="#874d36"></path><path d="M25.89 113.94c-.78.01-1.35.94-1.82 1.55c-1.22 1.57-1.99 2.56-2.5 3.35c-.7 1.09.7 2.03 1.95 2.03s83.39.12 83.59-.41c.27-.7-1.33-3.09-2.65-5.29c-.46-.77-1.46-1.82-2.36-1.81l-76.21.58z" fill="#b88956"></path><path d="M32.22 100.82l-2.78 4.53c-.48.78.08 1.78 1 1.78l68.52-.04c.91 0 1.47-.99 1.01-1.77l-2.69-4.51H32.22z" fill="#a06841"></path><path d="M47.25 101.55l-8.15 22.39h-4.94l-2.83-5.39l9.04-19.45c0-.01 7.33 1 6.88 2.45z" fill="#88857c"></path><path d="M28.81 123.97c.12-.42 8.45-17.94 8.45-17.94c.52-1.09 4.91-.6 4.2 1.46s-5.74 16.44-5.74 16.44l-6.91.04z" fill="#c8c9c6"></path><path d="M79.99 101.55l8.19 22.36l4.89-.01l2.84-5.35l-9.04-19.45c.01-.01-7.33 1-6.88 2.45z" fill="#88857c"></path><path d="M98.42 123.88c-.12-.42-8.47-17.91-8.47-17.91c-.52-1.09-4.91-.6-4.2 1.46l5.76 16.47l6.91-.02z" fill="#c8c9c6"></path><path d="M100.2 98.08c.14 1.71-.9 3.24-2.36 3.48c-4.85.77-16.44 2.24-34.54 2.24c-17.97 0-29.3-1.45-34.09-2.23c-1.48-.24-2.51-1.8-2.35-3.53l-.02-17.14h73.38l-.02 17.18z" fill="#82aec0"></path><path d="M26.84 80.9v6.06s17.38.9 36.68.9s36.69-1.04 36.69-1.04V80.9H26.84z" fill="#546e7a"></path><path d="M63.38 84.79l.14-24.05l-37.7-7.16L25.16 77v5.59c0 .89.69 1.62 1.57 1.67c3.17.19 12.25.53 36.65.53z" fill="#e0e0e0"></path><path d="M96.76 8.01C91.92 6.04 82.26 4 63.53 4s-28.4 2.04-33.24 4.01C28.3 8.82 27 11.96 27 14.14l-1.18 39.43l10.53 15.61l26.91 13.01c33.58 0 38.83-.29 38.83-.29l-2.05-67.76c.01-2.18-1.29-5.32-3.28-6.13z" fill="#2167a1"></path><path d="M94.49 9.42c-4.53-1.84-13.44-3.68-30.96-3.68S37.09 7.58 32.57 9.42c-1.86.76-3.08 3.03-3.08 5.07l.16 24.39c.03 5.42 3.57 10.2 8.74 11.83l25.86 8.75l24.82-8.67c5.18-1.67 8.68-6.51 8.65-11.95l-.16-24.25c0-2.04-1.21-4.41-3.07-5.17z" fill="#2686c6"></path><g><path fill="#bae2fd" d="M93.51 31.48H66.25l.36-16.67h24.87z"></path><path d="M91.64 12.95H67.06c-.92 0-1.65.99-1.59 2.16l.46 16.19c.05 1.07.75 1.9 1.59 1.9h25.35c1.22 0 2.12-1.44 1.86-2.96l-1.55-15.7c-.15-.93-.8-1.59-1.54-1.59zm-22.41 2.77h20.45c.73 0 1.36.63 1.54 1.53l.95 10.64c.26 1.29-.5 2.54-1.54 2.54H69.5c-.83 0-1.52-.82-1.58-1.88l-.26-10.64c-.08-1.18.65-2.19 1.57-2.19z" fill="#37474f"></path><g><path fill="#bae2fd" d="M34.49 31.48h27.26l-.36-16.67H36.52z"></path><path d="M34.81 14.54l-1.55 15.7c-.26 1.52.64 2.96 1.86 2.96h25.35c.84 0 1.53-.83 1.59-1.9l.46-16.19c.06-1.17-.67-2.16-1.59-2.16H36.36c-.74 0-1.39.66-1.55 1.59zm25.54 3.37l-.26 10.64c-.06 1.06-.75 1.88-1.58 1.88H37.36c-1.04 0-1.8-1.25-1.54-2.54l.95-10.64c.18-.9.81-1.53 1.54-1.53h20.45c.93 0 1.66 1.01 1.59 2.19z" fill="#37474f"></path></g></g><g><path d="M84.01 43.89c-1.15-2.57-3.12-3.54-5.19-3.03c-2.07.51-3.14 2.55-2.83 4.66c.41 2.87 3.06 5.7 6.42 4.12c2.18-1.02 2.74-3.21 1.6-5.75z" fill="#01579b"></path><circle cx="79.73" cy="44.6" r="3.13" fill="#f44336"></circle><g><path d="M43.99 43.89c1.15-2.57 3.12-3.54 5.19-3.03c2.07.51 3.14 2.55 2.83 4.66c-.41 2.87-3.06 5.7-6.42 4.12c-2.18-1.02-2.74-3.21-1.6-5.75z" fill="#01579b"></path><circle cx="48.27" cy="44.6" r="3.13" fill="#f44336"></circle></g></g><path d="M63.53 60.73l-37.7-7.16L25.16 77v5.59c0 .89.69 1.62 1.57 1.67c3.17.19 12.25.53 36.65.53h.14s2.15-4.36 2.15-11.7s-2.14-12.36-2.14-12.36z" fill="#e0e0e0"></path><path d="M63.53 60.73l37.7-7.16l.66 23.43v5.59c0 .89-.69 1.62-1.57 1.67c-3.17.19-12.25.53-36.65.53h-.14V60.73z" fill="#bdbdbd"></path><path d="M25.16 82.59c0 .89.69 1.62 1.57 1.67c3.17.19 12.25.53 36.65.53h.28c24.4 0 33.48-.34 36.65-.53c.88-.05 1.57-.78 1.57-1.67v-5.54H25.16v5.54z" opacity=".2" fill="#424242"></path><path d="M31.7 89.75a2.34 2.34 0 0 0-2.57 2.34c0 3.12.05 6.52 2.21 7.19c2.38.74 15.73 1.48 22.07 1.68c1.38.04 2.49-1.11 2.41-2.48l-.3-5.1a2.35 2.35 0 0 0-2.29-2.21c-7.87-.13-17.11-.97-21.53-1.42z" fill="#94d1e0"></path><g><circle cx="85.76" cy="66.23" r="5.88" fill="#757575"></circle><circle cx="85.76" cy="66.82" r="4.32" fill="#fffde7"></circle><g><circle cx="42.24" cy="66.23" r="5.88" fill="#9e9e9e"></circle><circle cx="42.24" cy="66.82" r="4.32" fill="#fffde7"></circle></g></g><g><path fill="#ffffff" d="M57.55 30.42h-7.33l-9.5-14.7h7.34z"></path><path fill="#ffffff" d="M87.28 30.42h-7.34l-9.49-14.7h7.33z"></path></g></svg>`,
  'ov-bus': `<svg version="1.0" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="125px" height="125px" viewBox="0 0 64 64" enable-background="new 0 0 64 64" xml:space="preserve"><g><g><rect x="10" y="56" fill="#506C7F" width="6" height="6"/><rect x="48" y="56" fill="#506C7F" width="6" height="6"/></g><path fill="#F76D57" d="M58,52c0,1.104-0.896,2-2,2H8c-1.104,0-2-0.896-2-2V10c0-4.418,3.582-8,8-8h36c4.418,0,8,3.582,8,8V52z"/><g><path fill="#B4CCB9" d="M2,28c0,1.104,0.896,2,2,2V19.999H2V28z"/><path fill="#B4CCB9" d="M60,19.999V30c1.104,0,2-0.896,2-2v-8.001H60z"/></g><rect x="10" y="12" fill="#F9EBB2" width="44" height="24"/><path fill="#394240" d="M51,42c-2.209,0-4,1.791-4,4s1.791,4,4,4s4-1.791,4-4S53.209,42,51,42z M51,48c-1.104,0-2-0.896-2-2	s0.896-2,2-2s2,0.896,2,2S52.104,48,51,48z"/><path fill="#394240" d="M13,42c-2.209,0-4,1.791-4,4s1.791,4,4,4s4-1.791,4-4S15.209,42,13,42z M13,48c-1.104,0-2-0.896-2-2 s0.896-2,2-2s2,0.896,2,2S14.104,48,13,48z"/><path fill="#394240" d="M28,7h8c0.553,0,1-0.447,1-1s-0.447-1-1-1h-8c-0.553,0-1,0.447-1,1S27.447,7,28,7z"/><path fill="#394240" d="M50.293,17.708c0.195,0.195,0.451,0.293,0.707,0.293s0.512-0.098,0.707-0.293	c0.391-0.391,0.391-1.023,0-1.414l-2-2c-0.391-0.391-1.023-0.391-1.414,0s-0.391,1.023,0,1.414L50.293,17.708z"/><path fill="#394240" d="M50.293,22.707C50.488,22.902,50.744,23,51,23s0.512-0.098,0.707-0.293c0.391-0.391,0.391-1.023,0-1.414	l-7-7c-0.391-0.391-1.023-0.391-1.414,0s-0.391,1.023,0,1.414L50.293,22.707z"/><path fill="#394240" d="M9,10c-0.553,0-1,0.447-1,1v26c0,0.553,0.447,1,1,1h46c0.553,0,1-0.447,1-1V11c0-0.553-0.447-1-1-1H9z M18,36c0-2.209,1.791-4,4-4s4,1.791,4,4H18z M31,36h-3c0-3.314-2.686-6-6-6s-6,2.686-6,6h-6V12h21V36z M54,36H33V12h21V36z"/><path fill="#394240" d="M43,41H21c-0.553,0-1,0.447-1,1v8c0,0.553,0.447,1,1,1h22c0.553,0,1-0.447,1-1v-8	C44,41.447,43.553,41,43,41z M42,49H22v-6h20V49z"/><path fill="#394240" d="M64,18.999c0-0.553-0.447-1-1-1h-3V10c0-5.523-4.478-10-10-10H14C8.477,0,4,4.478,4,10v7.999H1	c-0.553,0-1,0.447-1,1v9L0.003,28H0c0,2.209,1.791,4,4,4v20c0,2.211,1.789,4,4,4v7c0,0.553,0.447,1,1,1h8c0.553,0,1-0.447,1-1v-7 h28v7c0,0.553,0.447,1,1,1h8c0.553,0,1-0.447,1-1v-7c2.211,0,4-1.789,4-4V32c2.209,0,4-1.791,4-4h-0.003L64,27.999V18.999z M2,28	v-8.001h2V30C2.896,30,2,29.104,2,28z M16,62h-6v-6h6V62z M54,62h-6v-6h6V62z M58,52c0,1.104-0.896,2-2,2H8c-1.104,0-2-0.896-2-2	V10c0-4.418,3.582-8,8-8h36c4.418,0,8,3.582,8,8V52z M62,28c0,1.104-0.896,2-2,2V19.999h2V28z"/><rect x="22" y="43" fill="#B4CCB9" width="20" height="6"/><g><circle fill="#F9EBB2" cx="51" cy="46" r="2"/><circle fill="#F9EBB2" cx="13" cy="46" r="2"/></g></g></svg>`,
  'ov-pariwisata': `<svg width="175px" height="175px" viewBox="0 0 1024 1024" class="icon"  version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M910.3 851.7H173.6c-27.4 0-49.7-22.3-49.7-49.7V305.5c0-27.4 22.3-49.7 49.7-49.7h736.7c27.4 0 49.7 22.3 49.7 49.7V802c0 27.4-22.3 49.7-49.7 49.7z" fill="#A7B8C6" /><path d="M913.7 235.6c0-27.4-22.3-49.7-49.7-49.7H127.3c-27.4 0-49.7 22.3-49.7 49.7v326.8h836.1V235.6z" fill="#E7E2D3" /><path d="M127.3 781.7H864c27.4 0 49.7-22.3 49.7-49.7V481H77.5v251c0 27.4 22.3 49.7 49.8 49.7z" fill="#4C4B4C" /><path d="M864 795.3H127.3C92.4 795.3 64 766.9 64 732V235.6c0-34.9 28.4-63.3 63.3-63.3H864c34.9 0 63.3 28.4 63.3 63.3V732c-0.1 34.9-28.4 63.3-63.3 63.3zM127.3 199.4c-19.9 0-36.2 16.2-36.2 36.2V732c0 19.9 16.2 36.2 36.2 36.2H864c19.9 0 36.2-16.2 36.2-36.2V235.6c0-19.9-16.2-36.2-36.2-36.2H127.3z" fill="#3E3A39" /><path d="M495.6 459.9m-187.6 0a187.6 187.6 0 1 0 375.2 0 187.6 187.6 0 1 0-375.2 0Z" fill="#E7E2D3" /><path d="M495.6 655.1c-107.6 0-195.1-87.5-195.1-195.1S388 264.9 495.6 264.9 690.7 352.4 690.7 460c0 107.5-87.5 195.1-195.1 195.1z m0-375.2c-99.3 0-180.1 80.8-180.1 180.1s80.8 180 180.1 180 180.1-80.8 180.1-180.1-80.8-180-180.1-180z" fill="#595757" /><path d="M495.6 459.9m-159 0a159 159 0 1 0 318 0 159 159 0 1 0-318 0Z" fill="#666765" /><path d="M495.6 459.9m-97.8 0a97.8 97.8 0 1 0 195.6 0 97.8 97.8 0 1 0-195.6 0Z" fill="#95D4EB" /><path d="M495.6 563.7c-57.2 0-103.8-46.6-103.8-103.8s46.6-103.8 103.8-103.8c57.2 0 103.8 46.6 103.8 103.8s-46.6 103.8-103.8 103.8z m0-195.5c-50.6 0-91.7 41.2-91.7 91.7s41.2 91.7 91.7 91.7c50.6 0 91.7-41.2 91.7-91.7s-41.1-91.7-91.7-91.7z" fill="#595757" /><path d="M539.1 419.9m-26.7 0a26.7 26.7 0 1 0 53.4 0 26.7 26.7 0 1 0-53.4 0Z" fill="#FFFFFF" /><path d="M351.5 701h281.1c11.4 0 21.7 5.3 28.4 13.5 9-3.4 15.5-12.2 15.5-22.4 0-13.2-10.8-24-24-24H334.6c-13.2 0-24 10.8-24 24 0 9.4 5.5 17.5 13.3 21.5 6.8-7.7 16.6-12.6 27.6-12.6z" fill="#1A1A1A" /><path d="M669.1 737.2c0-8.6-3-16.4-8.1-22.6-2.6 1-5.5 1.6-8.5 1.6H334.6c-3.8 0-7.4-0.9-10.6-2.5-5.5 6.3-8.9 14.6-8.9 23.6L301 800.6c0 19.9 17.7 36.2 39.4 36.2h303.5c21.7 0 39.4-16.3 39.4-36.2l-14.2-63.4z" fill="#E7E2D3" /><path d="M652.5 716.1c3 0 5.8-0.6 8.5-1.6-6.7-8.2-16.9-13.5-28.4-13.5H351.5c-11 0-20.8 4.9-27.5 12.6 3.2 1.6 6.8 2.5 10.6 2.5h317.9z" fill="#717071" /><path d="M792.4 413.5m-33 0a33 33 0 1 0 66 0 33 33 0 1 0-66 0Z" fill="#717071" /><path d="M792.4 449.4c-19.8 0-36-16.1-36-36 0-19.8 16.1-36 36-36 19.8 0 36 16.1 36 36s-16.1 36-36 36z m0-65.9c-16.5 0-30 13.4-30 30 0 16.5 13.4 30 30 30s30-13.4 30-30-13.5-30-30-30z" fill="#595757" /><path d="M800.193895 406.233563a12.4 9.3 55.105 1 0 15.255753-10.640582 12.4 9.3 55.105 1 0-15.255753 10.640582Z" fill="#B4B4B5" /><path d="M173.4 413.5m-33.1 0a33.1 33.1 0 1 0 66.2 0 33.1 33.1 0 1 0-66.2 0Z" fill="#DA8080" /><path d="M173.4 451.1c-20.8 0-37.7-16.9-37.7-37.7s16.9-37.7 37.7-37.7 37.7 16.9 37.7 37.7-16.9 37.7-37.7 37.7z m0-66.3c-15.8 0-28.6 12.8-28.6 28.6s12.8 28.6 28.6 28.6 28.6-12.8 28.6-28.6-12.8-28.6-28.6-28.6z" fill="#FFFFFF" /><path d="M212.6 336.3h-78.4c-2 0-3.6-1.6-3.6-3.6V285c0-2 1.6-3.6 3.6-3.6h78.4c2 0 3.6 1.6 3.6 3.6v47.7c0 2-1.6 3.6-3.6 3.6z" fill="#4C4B4C" /><path d="M822.5 336.3h-52.4c-1.7 0-3-1.4-3-3v-52.4c0-1.7 1.4-3 3-3h52.4c1.7 0 3 1.4 3 3v52.4c0 1.6-1.3 3-3 3z" fill="#656767" /><path d="M816.4 339.3h-40.2c-6.7 0-12.1-5.4-12.1-12.1V287c0-6.7 5.4-12.1 12.1-12.1h40.2c6.7 0 12.1 5.4 12.1 12.1v40.2c0.1 6.7-5.4 12.1-12.1 12.1z m-40.2-58.4c-3.4 0-6.1 2.7-6.1 6.1v40.2c0 3.4 2.7 6.1 6.1 6.1h40.2c3.4 0 6.1-2.7 6.1-6.1V287c0-3.4-2.7-6.1-6.1-6.1h-40.2z" fill="#3E3A39" /></svg>`,
  'ov-port': `<svg height="125px" width="125px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 509.287 509.287" xml:space="preserve"><circle style="fill:#4B5AA7;" cx="254.644" cy="254.644" r="254.644"/><path style="fill:#FCFCFD;" d="M391.968,294.315c0,0-4.069,41.706-43.74,60.694l23.735,4.408c0,0-41.706,53.574-77.309,34.925	c-32.551-16.954-23.057-190.898-21.022-221.415h25.43c12.546,0,22.379-10.172,22.379-22.379c0-12.546-10.172-22.379-22.379-22.379	h-26.787v-7.799c17.971-7.121,30.517-24.413,30.517-44.758c0-26.448-21.362-48.148-48.148-48.148	c-26.448,0-48.148,21.362-48.148,48.148c0,20.344,12.546,37.637,30.517,44.758v7.799h-26.787c-12.546,0-22.379,10.172-22.379,22.379	c0,12.546,10.172,22.379,22.379,22.379h25.43c2.034,30.517,11.528,204.461-21.023,221.415 c-35.603,18.649-77.309-34.925-77.309-34.925l23.735-4.408c-39.672-18.988-43.74-60.694-43.74-60.694 c-30.856,56.625,0,100.026,0,100.026l8.816-26.448c0,0,15.936,47.131,61.711,63.746c55.947,20.344,66.458,50.522,66.458,50.522	s10.511-29.838,66.458-50.522c46.114-16.615,61.711-63.746,61.711-63.746l8.816,26.448	C391.968,394.342,422.824,350.94,391.968,294.315z M227.179,75.274c0-15.258,12.207-27.465,27.465-27.465	c15.258,0,27.465,12.207,27.465,27.465s-12.207,27.465-27.465,27.465C239.385,103.078,227.179,90.532,227.179,75.274z"/></svg>`,
  'acc-ringan': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#FFEB3B" stroke="#FBC02D" stroke-width="2" d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>`,
  'acc-sedang': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#FF9800" stroke="#E65100" stroke-width="2" d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>`,
  'acc-berat':  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#D32F2F" stroke="#B71C1C" stroke-width="2" d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>`,
  'gt-operasi': `<svg width="125px" height="125px" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="14" y="34" width="20" height="9" fill="#11f400" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><rect x="14" y="4" width="20" height="6" fill="#11f400" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 10V34" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M20 10V34" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 16V20" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 26L14 26" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M34 10V34" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 44H44" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
  'gt-konstruksi': `<svg width="125px" height="125px" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="14" y="34" width="20" height="9" fill="#f48500" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><rect x="14" y="4" width="20" height="6" fill="#f48500" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 10V34" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M20 10V34" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 16V20" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 26L14 26" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M34 10V34" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 44H44" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
  'rescue-car': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#ffffff" stroke="#b0bec5" stroke-width="0.5" d="M20 8h-3l1.5-2L20 8zm-1 7c0 1.66-1.34 3-3 3s-3-1.34-3-3H9c0 1.66-1.34 3-3 3s-3-1.34-3-3H2v-9c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v1h3l1 2v6h-2zM8 15c.55 0 1-.45 1-1s-.45-1-1-1-1 .45-1 1 .45 1 1 1zm8 0c.55 0 1-.45 1-1s-.45-1-1-1-1 .45-1 1 .45 1 1 1zm-4-3v-2h-2v2H8v2h2v2h2v-2h2v-2h-2z"/><path fill="#d32f2f" d="M18 6h-2V4h-2v2h-2v2h2v2h2V8h2V6zm-4 7.5h-1.5v-2h-2v-1.5h2v-2h1.5v2h2v1.5h-2v2zM4 10v3h16v-3H4z"/><circle cx="7" cy="15" r="2" fill="#37474f"/><circle cx="16" cy="15" r="2" fill="#37474f"/></svg>`,
  'ov-mosque': `<svg width="200px" height="200px" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--noto" preserveAspectRatio="xMidYMid meet"><path fill="#eebf72" d="M10.43 124.25l.15-44.32l1.71-1.34h102.89l2.1.96l-.08 44.7z"></path><path d="M10.72 75.91c-.42.14-.16 4.45-.16 4.45l106.75-.21s.73-4.21.17-4.77c-.56-.55-106.76.53-106.76.53z" fill="#cf701d"></path><path d="M94.66 21.59l.06 13.12l-4.95.05s-.07 5.72.1 5.78c.37.12 11.56 2.65 11.56 2.65l14.25-2.52l.17-5.62l-5.03-.05l.11-14.19l-11.3-.79l-4.97 1.57z" fill="#fbdaa6"></path><path fill="#fbdaa6" d="M93.84 45.2l-.14 78.98h17.03l.86-79.98z"></path><path d="M42.52 66.3s-1.63 3.14-1.63 3.37c0 .23.35 54.7.35 54.7h45.39s1.16-54.46.81-54.93c-.35-.46-44.92-3.14-44.92-3.14z" fill="#fbdaa6"></path><path d="M17.23 21.36l-.03 13.46l-4.88-.01s-.23 5.62.12 5.93c.52.46 14.94 2.69 14.94 2.69s10.57-2.14 10.81-2.71c.15-.36.19-5.54.19-5.54l-5.2-.05l.08-14.33l-9.66-.89l-6.37 1.45z" fill="#fbdaa6"></path><path fill="#fbdaa6" d="M16.67 45.76l.6 78.49h16.98l-.05-79.16z"></path><path fill="#eebf72" d="M12.41 40.71H38.2l-4 5.49l-17.53.11z"></path><path fill="#eebf72" d="M16.82 67.46l17.31-.13l.07 5.62l-17.32.06z"></path><path fill="#eebf72" d="M23.24 118.77h4.91V82.91l-2.19-2.9l-2.39 2.84z"></path><path fill="#eebf72" d="M100.02 119.78l4.59-.06l.25-37.08l-2.39-3.23l-2.32 3.23z"></path><path fill="#eebf72" d="M93.95 66.1l-.07 5.75h17.25l.07-5.81z"></path><path fill="#eebf72" d="M93.82 46.46l17.76-.06l4.1-5.73l-25.87-.15z"></path><path fill="#cf701d" d="M40.97 85.06l-.06-5.45l46.53-.09l-.07 5.58z"></path><path fill="#b7885a" d="M97.79 24.07l3.79.01l-.03 10.02l-3.79.03z"></path><path fill="#b7885a" d="M104.38 24.1H108l-.03 10.03l-3.65.03z"></path><path fill="#b7885a" d="M20.04 24.06h3.68l-.03 10.02l-3.68.04z"></path><path fill="#b7885a" d="M26.62 24.09h3.63l-.03 10.03l-3.66.03z"></path><path d="M47.79 70.38c-4.96 0-4.55 5.77-4.55 5.77l8.82-.06c-.01 0 .45-5.71-4.27-5.71z" fill="#b7885a"></path><path d="M54.13 76.15s-.52-5.65 4.55-5.77c4.73-.11 4.27 5.71 4.27 5.71l-8.82.06z" fill="#b7885a"></path><path d="M85.73 75.91s.98-5.59-4.44-5.7c-5.2-.11-4.6 5.76-4.6 5.76l9.04-.06z" fill="#b7885a"></path><path d="M74.37 75.97s.98-5.82-4.44-5.7c-5.14.12-4.6 5.76-4.6 5.76l9.04-.06z" fill="#b7885a"></path><path d="M79.05 124.37l.13-14.7l2.52-3.08s-2.63-1.2-2.64-4.15c-.02-3.98 4.1-6.61 4.1-6.61s4 2.35 3.93 6.75c-.04 2.6-2.65 4.06-2.65 4.06l2.49 3.2s-.22 14.49-.3 14.55c-.09.05-7.58-.02-7.58-.02z" fill="#b7885a"></path><path d="M41.07 124.37c-.1-.03.05-14.93.05-14.93l2.6-2.79s-2.5-1.7-2.58-4.01c-.2-5.4 3.99-6.82 3.99-6.82s4.18 2.3 3.83 6.75c-.21 2.72-2.55 4.05-2.55 4.05l2.52 3l-.13 14.78s-7.63 0-7.73-.03z" fill="#b7885a"></path><path d="M64.32 88.06s-11.29 3.52-10.84 12.74c.2 4.04 3.13 5.81 3.13 5.81l-2.9 3.05l-.11 14.7l20.73.02l.06-14.76l-2.79-2.96s2.79-1.73 2.91-6.15c.21-8.76-10.19-12.45-10.19-12.45z" fill="#966737"></path><path d="M40.89 69.68l46.55-.23s2.48-2.96 2-10.18c-.45-6.73-4.79-10.84-4.79-10.84l-28.64.49l-13-.11s-3.61 3.97-3.93 10.66c-.27 5.96 1.81 10.21 1.81 10.21z" fill="#ffa828"></path><path d="M64.11 34.26s-8.47 3.1-15.1 8.49c-5.04 4.11-6.46 6.67-6.46 6.67s-1.73 17.83 21.61 18.29c20.63.41 20.51-19.28 20.51-19.28s-4.23-5.03-8.71-7.78c-5.95-3.64-11.85-6.39-11.85-6.39z" fill="#fcc11a"></path><path d="M67.26 33.53c0-1.73-1.4-3.13-3.12-3.13c-1.72 0-3.17 1.07-3.28 2.79c-.15 2.18 1.21 3.55 3.21 3.54c1.72.01 3.19-1.47 3.19-3.2z" fill="#ffa828"></path><path d="M54.43 15.99l-2.38 3.36s-.74 12.86 12.25 12.86c11.72 0 12.57-11.27 11.78-11.78c-.39-.26-5.49 5.84-5.72 5.9c-.23.06-9.18-.63-9.41-.92c-.22-.3-6.52-9.42-6.52-9.42z" fill="#ffa828"></path><path d="M64.19 12.65c-.37.32.9 4.12.9 4.12s-1.99 2.97-1.7 3.44s3.23.23 3.23.23s2.37 2.42 3 2.31c.63-.12 1.33-3.17 1.33-3.17s3.41-.92 3.46-1.5c.06-.58-2.99-2.64-2.99-2.64s.22-3.42-.07-3.65c-.29-.24-3.35 1.72-3.35 1.72s-3.35-1.27-3.81-.86z" fill="#fcc11a"></path><path d="M67.19 8.66c.04.46-6.06 1.56-7.16 6.93c-1.12 5.49 1.96 9.18 7.68 9.52c5.71.35 6.75-5.48 8.37-4.73c.54.25-1.67 9.18-11.72 8.95c-8.9-.2-12.2-6.74-12.29-9.35c-.23-6.35 3.58-10.22 7.45-11.66c3.51-1.31 7.55-1.04 7.67.34z" fill="#fcc11a"></path><path d="M94.38 21.82c.41.25 16.54.25 16.78.06c.24-.19.49-2.67.52-3.75c.1-3.39-.74-4.95-.74-4.95s-15.83-.33-16.21.06c-.39.39-.76 2.17-.81 4.45c-.04 2.5.22 3.98.46 4.13z" fill="#ffa828"></path><path d="M17.09 21.47c.32.27 16.16.25 16.31.06c.15-.19.72-2.63.66-4.23c-.08-2.23-.49-3.08-.79-3.66c-.62-1.21-15.36-1.62-15.8-.79s-1.03 2.59-1.05 4.7c-.02 1.62.33 3.63.67 3.92z" fill="#ffa828"></path><path d="M27.95 4.12c0 1.45-1.35 2.81-2.81 2.81s-2.62-1.32-2.62-2.76s1.26-2.81 2.72-2.81c1.46 0 2.71 1.31 2.71 2.76z" fill="#fcc11a"></path><path d="M105.6 4.11c0 1.46-1.19 2.6-2.68 2.6s-2.66-1.18-2.66-2.64s1.18-2.64 2.66-2.64s2.68 1.22 2.68 2.68z" fill="#fcc11a"></path><path d="M94.47 13.69s1.04 4.73 8.06 4.98c7.56.27 8.48-5.39 8.48-5.39s-1.09-2.23-2.73-4.02c-1.74-1.91-4.4-3.67-5.43-3.67c-.95 0-3.69 1.5-5.49 3.48c-1.98 2.17-2.89 4.4-2.89 4.62z" fill="#fcc11a"></path><path d="M25.23 5.36c-.45 0-3.67 2.27-4.99 3.75c-1.92 2.17-2.77 3.73-2.77 3.73s.51 5.63 7.53 5.78c7.55.16 8.28-4.97 8.28-4.97s-1.24-2.39-3.23-4.56c-2.1-2.29-4.82-3.73-4.82-3.73z" fill="#fcc11a"></path></svg>`,
  'ov-church': `<svg width="200px" height="200px" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--noto" preserveAspectRatio="xMidYMid meet"><path fill="#614f46" d="M71.8 8.78h-5.83l.02-7.16h-4.4l.01 7.16h-5.96v4.06h5.97l.04 13.19l4.28-1.78l.03-11.41h5.84z"></path><path d="M49.69 59.86S16.06 90.75 16.14 91.23c.08.48 3.62 3.06 3.62 3.06L53 83.73l-3.31-23.87z" fill="#896159"></path><path d="M78 58.32s34.27 32.47 34.1 33.1c-.12.47-3.76 1.97-3.76 1.97S72.7 76.05 72.79 75.23c.09-.82 5.21-16.91 5.21-16.91z" fill="#896159"></path><path d="M49.75 66.45L19.69 94.23l-.01 28.2c0 .94.5 1.33 1.35 1.42s86.87.29 87.34.11c.07-.03.19-1.46.17-2.5c-.09-6.45-.07-28.13-.07-28.13l-30.95-28.7l-27.77 1.82z" fill="#c0b8b3"></path><path d="M45.4 44.39c-.22-1.2 2.22-2.08 3.08-3.22S59 26.21 60.14 24.7c1.15-1.5 2.22-3.36 3.51-3.36c1.29 0 3.25 3 4.29 4.37c1.57 2.08 10.46 14.38 11.02 15.03c.86 1 3.08 2.22 3.08 3.08c0 .79-4.65 4.51-4.65 4.51l-28.13-1.15s-3.58-1.22-3.86-2.79z" fill="#896159"></path><path d="M48.61 45.07s29.72-.61 29.86-.25s.13 4.82.13 4.82l-18.1 2.12l-11.95-2.08l.06-4.61z" fill="#bdb8b1"></path><path fill="#5f4d4e" d="M48.77 121.38l-.22-71.7l30.05-.08l1.03 71.78z"></path><path fill="#efebea" d="M106.08 95.87l-23.87-21.7l.54 47.12l23.42.09z"></path><path fill="#efebea" d="M45.07 75.99L21.92 96.87l-.9 16.43l1.09 8.08h23.33z"></path><path fill="#efebea" d="M50.98 49.57l.45 71.81l25.83.01l-.68-71.79z"></path><path fill="#5f4d4e" d="M106.08 95.87l2.44 2.36l.03 23.22l-2.38-.07z"></path><path fill="#5f4d4e" d="M21.92 96.87l-2.24 2.2v22.38l2.43-.07z"></path><path d="M54.44 121.38s-.21-18.88-.16-24.07c.06-5.18 3.19-10.16 9.74-10.14c6.99.02 9.86 4.88 9.86 10.32c0 5.02.18 23.9.18 23.9l-19.62-.01z" fill="#d27856"></path><path fill="#ac5832" d="M69.55 100.05h-3.76v-6.78h-3.3v6.78h-3.82v3.41h3.82v13.73h3.3v-13.73h3.76z"></path><path d="M72.35 120l-16.32-.13v-.74c0-.86-.04-21.17 0-22.08c.08-1.83.89-7.8 7.91-8.01c2.49-.06 4.47.6 5.92 2.01c2.26 2.2 2.38 5.41 2.38 6.04c0 1.31.11 22.16.11 22.16v.75zm-14.82-1.61l13.31.1c-.02-3.76-.11-20.25-.11-21.41c0-.32-.05-3.15-1.92-4.96c-1.14-1.11-2.78-1.64-4.83-1.58c-5.57.16-6.37 4.65-6.45 6.58c-.03.68-.01 13.8 0 21.27z" fill="#ac5832"></path><path d="M70.4 104.6c-.87.03-.87.86-.87 2.34s-.03 2.36.93 2.38c.99.03.89-1.04.85-2.47c-.02-1.13.17-2.29-.91-2.25z" fill="#f5bf0a"></path><path fill="#e4bbab" d="M29.83 96.41l.01 10.53l2.91 8.33h2.15l2.91-9.04l.09-9.82z"></path><path d="M29.84 106.68s1.61.86 2.22 3.18c.61 2.32.69 5.42.69 5.42s-1.66.03-2.15 0c-.69-.04-.67-.72-.72-1.25s-.04-7.35-.04-7.35z" fill="#6da5b0"></path><path d="M37.81 106.23s-1.51.55-2.28 3.4c-.67 2.47-.63 5.64-.63 5.64h2.33c.49 0 .67-.72.67-1.16c0-.45-.09-7.88-.09-7.88z" fill="#6da5b0"></path><path d="M37.85 101.07s-1.75 1.55-4.06 1.55c-2.56 0-3.96-1.75-3.96-1.75v3.56s1.17.24 2.46 1.34c.87.75 1.44 2.26 1.44 2.26s.35-1.36 1.5-2.46c1.32-1.26 2.58-1.47 2.58-1.47l.04-3.03z" fill="#fccb7e"></path><path d="M29.8 96.23c-.05 1.5.85 4.19 4.15 4.2c2.94.01 3.94-2.26 3.94-4.11s-1.03-4.14-4.13-4.18c-2.43-.02-3.89 1.96-3.96 4.09z" fill="#9f7cdc"></path><path d="M33.86 94.57c-.97.02-1.66.58-1.75 1.61s.76 1.87 1.8 1.87s1.8-.87 1.73-1.89c-.05-.9-.78-1.62-1.78-1.59z" fill="#d05e53"></path><path fill="#e4bbab" d="M90.18 96.5l.06 10.22l2.94 8.3h2.12l2.97-9.12l-.03-9.72z"></path><path d="M95.19 115.02s.08-3.89.61-5.32c.73-1.97 2.48-3.8 2.48-3.8v7.83c0 .49-.05 1.25-.48 1.27c-.6.04-2.61.02-2.61.02z" fill="#6da5b0"></path><path d="M90.24 106.72s1.48.6 2.28 2.73c.81 2.17.67 5.56.67 5.56s-1.85-.04-2.22-.05c-.77-.04-.78-.61-.75-1.58c.01-.87.02-6.66.02-6.66z" fill="#6da5b0"></path><path d="M90.21 100.67l.03 3.28s1.52.69 2.31 1.45c.85.81 1.62 2.31 1.62 2.31s.86-1.81 1.66-2.52c1.25-1.12 2.43-1.52 2.43-1.52l-.01-2.93s-1.63 1.8-4.15 1.73s-3.89-1.8-3.89-1.8z" fill="#fccb7e"></path><path d="M94.21 91.99c-1.81-.04-3.94 1.3-4.02 4.22c-.08 2.76 2.25 4.06 4.14 3.98s3.82-1.18 3.92-4.02c.09-3.07-2.31-4.14-4.04-4.18z" fill="#9f7cdc"></path><path d="M94.24 94.36c-.95 0-1.74.71-1.7 1.85c.04 1.14.8 1.73 1.75 1.73c1.02 0 1.75-.55 1.76-1.92c.02-1.03-.65-1.66-1.81-1.66z" fill="#d05e53"></path><path d="M63.66 55.32c-7.3-.04-10.92 5.75-10.61 11.29c.34 6.05 4.45 10.37 10.85 10.29c6.75-.09 10.51-4.93 10.46-11.11c-.04-6.14-4.42-10.44-10.7-10.47z" fill="#63697f"></path><path d="M63.43 56.79c-5.1.13-8.99 4.15-9.04 9.56c-.04 5.28 4.15 9.04 9.21 9.08c4.84.04 9.12-3.24 9.34-9.08c.23-6.21-4.46-9.69-9.51-9.56z" fill="#6da5b0"></path><path d="M63.85 61.51c-2.83-.1-4.71 1.9-4.69 4.58c.02 2.31 1.32 4.77 4.6 4.78c3.06.02 4.7-2.18 4.74-4.51c.02-2.09-.92-4.72-4.65-4.85z" fill="#e4bbab"></path><path d="M69.1 63.22l1.6-.94a7.168 7.168 0 0 0-3.35-3.16l-.72 1.83c1.02.48 1.89 1.25 2.47 2.27z" fill="#9f7cdc"></path><path d="M69.64 67.75l1.64.64c.17-.63.28-1.3.3-2.02c.03-.8-.05-1.55-.21-2.24l-1.73.47c.13.55.2 1.14.18 1.79c-.01.49-.08.94-.18 1.36z" fill="#fccb7e"></path><path d="M68.99 69.33c-.5.82-1.21 1.45-2.02 1.9l.93 1.65c1.08-.63 2-1.52 2.65-2.67l-1.56-.88z" fill="#9f7cdc"></path><path d="M65.63 71.76c-.59.16-1.22.25-1.86.24c-.45 0-.89-.06-1.31-.15l-.48 1.96a8.48 8.48 0 0 0 4.25-.2l-.6-1.85z" fill="#fccb7e"></path><path d="M58.86 69.43l-1.75 1.19a7.516 7.516 0 0 0 3.07 2.59l.98-1.81a5.716 5.716 0 0 1-2.3-1.97z" fill="#9f7cdc"></path><path d="M58.09 64.64l-1.97-.42c-.18.68-.28 1.4-.29 2.15c-.01.98.18 1.89.49 2.72l1.91-.9c-.2-.56-.32-1.15-.35-1.79c-.01-.62.06-1.21.21-1.76z" fill="#fccb7e"></path><path d="M59.71 59.42c-1.21.71-2.2 1.72-2.88 2.98l1.76 1c.51-.94 1.28-1.7 2.21-2.22l-1.09-1.76z" fill="#9f7cdc"></path><path d="M63.51 58.35c-.74.02-1.45.14-2.12.34l.69 1.94a6.69 6.69 0 0 1 1.63-.26c.51-.01 1.01.04 1.5.13l.39-1.95a8.57 8.57 0 0 0-2.09-.2z" fill="#fccb7e"></path></svg>`,
  'ov-temple': `<svg width="200px" height="200px" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--noto" preserveAspectRatio="xMidYMid meet"><path d="M66.87 22.9s1.77-1.09 1.79-3.5c.02-2.64-2.09-3.43-2.09-3.43s1.31-.54 1.33-2.17c.02-1.74-2.25-2.17-2.25-2.17s1.12-1.35 1.04-3.61c-.08-2.33-1.94-4.99-2.57-5.05c-.31-.03-.22 9-.22 9l-.44 10.49l3.1 3.69l.31-3.25z" fill="#ffa72b"></path><path d="M61.31 22.77s-2.06-1.25-1.92-3.55c.17-2.74 2.66-3.32 2.66-3.32s-1.81-.59-1.7-2.14c.15-1.99 2.44-2.22 2.44-2.22s-1.1-1.18-.96-3.4c.22-3.62 2.29-5.17 2.29-5.17l.08 20.76l-2.89-.96z" fill="#ffc020"></path><path d="M50.94 28.54s2.7-7.05 12.95-7c11.34.06 13.18 7.34 13.18 7.34l-16.84 3.13l-9.29-3.47z" fill="#8c1b0d"></path><path fill="#513630" d="M51.72 33.3l-.05 4.53l12.93 1.23l11.75-.35l.04-5.27z"></path><path d="M47.61 31.38c-.01 2.11 1.64 2.97 3.65 3.02c2.02.06 24.49-.21 26.6-.25c2.11-.04 2.43-1.87 2.45-3.05c.02-1.57-1.04-2.73-2.66-2.8c-1.62-.06-25.87.09-27.15.12c-1.28.02-2.89 1.04-2.89 2.96z" fill="#d35113"></path><path fill="#a62714" d="M24.88 116.41l.2-19.61l2.41-.41l-.1-10.37l2.71-.26v-8.88l1.95-1.61l-.08-7.45l1.86-.68l.08-7.79l5.76-.51l-1.44 59.53z"></path><path fill="#a62714" d="M89.46 60.87l4.15.51l-.09 6.27L95.39 69l-.09 7.96l1.78 1.27v8.05l1.95 1.86l.08 8.3l1.95 1.69l-.09 19.14l-13.22.34z"></path><path d="M89.87 62.41l3.84.1s2.34-1.1 2.26-3.5c-.14-3.93-4.85-5.95-4.85-5.95s1.1-.38 1.01-2.16c-.08-1.39-1.1-2.02-2.21-2.06c-1.1-.05-2.06.91-2.06 1.87s2.01 11.7 2.01 11.7z" fill="#503730"></path><path d="M39.92 53.39l-.69-1.12s.62-.41.62-2.02c0-.82-.66-1.94-2.1-1.94s-2.19 1.05-2.19 2.16c0 1.2 1 2 1 2s-5.02 2.28-4.94 5.85c.05 2.21 2.06 2.54 2.06 2.54l5.28.14l.96-7.61z" fill="#503730"></path><path d="M37.65 65.64s-5.52-.16-6-.11s-1.36.78-1.32 1.81c.05 1.03.71 1.57 1.57 1.63s5.95.16 5.95.16l-.2-3.49z" fill="#503730"></path><path d="M37.02 74.2l-7.6-.27s-1.5.1-1.53 1.83c-.03 1.74 1.68 1.72 1.68 1.72l7.82.28l-.37-3.56z" fill="#503730"></path><path d="M36.85 84.11s-9.79-.28-10.75-.28s-1.49.72-1.49 1.72s.62 1.72 1.68 1.77c1.06.06 10.51.28 10.51.28l.05-3.49z" fill="#503730"></path><path d="M36.99 95.41s-12.38-.27-12.96-.27s-1.44.44-1.54 1.7s.53 1.81 1.63 1.87c.73.04 13.48.38 13.48.38l-.61-3.68z" fill="#503730"></path><path d="M90.2 66.92s4.29.05 5.27.15c.98.1 1.67.81 1.67 1.58s-.64 1.84-1.8 1.84c-1.16 0-6.47.1-6.47-.05s1.33-3.52 1.33-3.52z" fill="#503730"></path><path d="M90.03 75.33s6.35.17 7.38.23c1.3.08 1.85.84 1.85 1.7s-.75 1.77-1.95 1.72c-1.21-.05-9.02-.09-9.02-.09l1.74-3.56z" fill="#503730"></path><path d="M89.96 85.08s8.41.02 8.94.06c.54.04 2.41.28 2.4 1.77c-.01 1.43-1.34 1.79-2.28 1.79s-9.64-.04-9.64-.04l.58-3.58z" fill="#503730"></path><path d="M89.81 95.85s11.46 0 12.49.06c1.34.08 1.77.92 1.75 1.68c-.02.65-.27 1.56-1.92 1.61c-1.65.04-13.21 0-13.21 0l.89-3.35z" fill="#503730"></path><path d="M51.36 37.65c.19-.01 24.68.1 24.68.1s5.42 1.77 11.08 11.13c4.76 7.88 3.98 17.96 3.98 29.61s-.45 37.5-.45 37.5l-26.84 3.95l-27.49-3.66s-.13-26.05-.13-38.16s1.89-24.33 5.91-30.61c4.56-7.15 9.26-9.86 9.26-9.86z" fill="#d55113"></path><path d="M77.68 40.12h-.02l-5.18-.08c-.72-.01-1.3-.61-1.29-1.33c.01-.72.59-1.29 1.31-1.29h.02l5.18.08a1.31 1.31 0 0 1-.02 2.62z" fill="#fa8960"></path><path d="M56.42 39.67h-5.48c-.72 0-1.31-.59-1.31-1.31c0-.72.59-1.31 1.31-1.31h5.48c.72 0 1.31.59 1.31 1.31c0 .72-.58 1.31-1.31 1.31z" fill="#fa8960"></path><path d="M49.59 46.65h-.02l-5.33-.07c-.72-.01-1.3-.61-1.29-1.33c.01-.72.63-1.31 1.33-1.29l4.13.06l.11-1.13c.07-.68.6-1.2 1.33-1.18l7.74.15c.72.01 1.3.61 1.28 1.34c-.01.72-.58 1.31-1.34 1.28L51 44.35l-.11 1.12c-.06.67-.63 1.18-1.3 1.18z" fill="#fa8960"></path><path d="M84.52 46.8h-.02l-5.97-.07a1.31 1.31 0 0 1-1.29-1.19l-.08-.93l-6.64-.13c-.72-.01-1.3-.61-1.28-1.34c.01-.72.6-1.33 1.34-1.28l7.81.15c.67.01 1.22.53 1.28 1.19l.08.92l4.79.06a1.31 1.31 0 0 1-.02 2.62z" fill="#fa8960"></path><path d="M69.65 42.72h-.01l-10.9-.12c-.72-.01-1.3-.6-1.3-1.33c.01-.72.6-1.31 1.33-1.3l10.89.12c.72.01 1.3.6 1.3 1.33c-.01.72-.59 1.3-1.31 1.3z" fill="#fa8960"></path><path d="M45.91 54.09h-.04l-5.48-.15c-.72-.02-1.29-.62-1.27-1.35c.02-.72.61-1.28 1.35-1.27l4.18.11l.03-1.03c.02-.72.62-1.28 1.35-1.27l8.04.23c.72.02 1.29.62 1.27 1.35c-.02.72-.63 1.28-1.35 1.27l-6.73-.19l-.03 1.02a1.33 1.33 0 0 1-1.32 1.28z" fill="#fa8960"></path><path d="M43.28 62.5h-.04l-5.63-.15c-.72-.02-1.29-.62-1.27-1.35c.02-.72.64-1.27 1.35-1.27l4.34.12l.03-.73c.03-.72.59-1.27 1.35-1.26l7.89.23c.72.02 1.29.62 1.27 1.35c-.02.72-.62 1.28-1.35 1.27l-6.59-.19l-.03.73c-.04.7-.62 1.25-1.32 1.25z" fill="#fa8960"></path><path d="M41.85 71.44h-5.11c-.72 0-1.31-.59-1.31-1.31s.59-1.31 1.31-1.31h3.8v-1.09c0-.72.59-1.31 1.31-1.31h8.26c.72 0 1.31.59 1.31 1.31c0 .72-.59 1.31-1.31 1.31h-6.95v1.09c0 .72-.58 1.31-1.31 1.31z" fill="#fa8960"></path><path d="M41.93 80.38h-.01l-5.67-.06c-.72-.01-1.3-.6-1.3-1.32c.01-.73.59-1.3 1.33-1.3l4.34.05v-1c0-.35.14-.69.39-.93c.25-.25.58-.36.94-.38l11.87.15c.72.01 1.3.6 1.29 1.33c-.01.72-.59 1.29-1.31 1.29h-.02l-10.54-.13v1c0 .35-.14.69-.39.93c-.25.23-.58.37-.92.37z" fill="#fa8960"></path><path d="M82.95 54.6c-.72 0-1.3-.58-1.31-1.29l-.01-1.05l-7.28-.1c-.72-.01-1.3-.6-1.29-1.33c.01-.72.59-1.29 1.31-1.29h.02l8.56.12c.71.01 1.28.58 1.29 1.29l.01 1.03l4.18-.03h.01c.72 0 1.3.58 1.31 1.3c.01.72-.58 1.31-1.3 1.32l-5.49.04c0-.01 0-.01-.01-.01z" fill="#fa8960"></path><path d="M90.22 62.96h-.02l-5.63-.07a1.31 1.31 0 0 1-1.29-1.31v-.73l-6.59-.07c-.72-.01-1.3-.6-1.3-1.32c.01-.72.59-1.3 1.31-1.3h.01l7.89.09c.72.01 1.3.59 1.3 1.31v.73l4.34.06c.72.01 1.3.6 1.29 1.33c-.01.7-.59 1.28-1.31 1.28z" fill="#fa8960"></path><path d="M90.45 71.9h-.04l-5.11-.15c-.72-.02-1.29-.62-1.27-1.35l.03-1.09l-6.95-.2c-.72-.02-1.29-.62-1.27-1.35c.02-.71.6-1.27 1.31-1.27h.04l8.26.24c.72.02 1.29.62 1.27 1.35l-.03 1.09l3.8.11c.72.02 1.29.62 1.27 1.35c-.02.71-.6 1.27-1.31 1.27z" fill="#fa8960"></path><path d="M90.92 80.99h-.04l-5.56-.15c-.72-.02-1.28-.61-1.27-1.33l.01-1l-10.54-.02c-.72 0-1.31-.59-1.31-1.31c0-.72.59-1.31 1.31-1.31l11.87.02c.35 0 .69.14.93.39c.25.25.38.59.38.94l-.01 1.04l4.26.12c.72.02 1.29.62 1.27 1.35c-.01.7-.59 1.26-1.3 1.26z" fill="#fa8960"></path><path d="M73.55 50.11h-.03l-18.62-.38c-.72-.01-1.3-.61-1.28-1.34c.01-.72.62-1.33 1.34-1.28l18.63.38c.72.01 1.3.61 1.28 1.34c-.02.71-.61 1.28-1.32 1.28z" fill="#fa8960"></path><path d="M76.85 67.61h-.02l-26.44-.45a1.31 1.31 0 0 1 .02-2.62h.02l26.44.45c.72.01 1.3.61 1.29 1.33c-.01.72-.59 1.29-1.31 1.29z" fill="#fa8960"></path><path d="M90.45 104.12h-.02l-53.97-.69c-.72-.01-1.3-.6-1.29-1.33c.01-.72.58-1.34 1.33-1.29l53.97.69c.72.01 1.3.6 1.29 1.33c0 .72-.59 1.29-1.31 1.29z" fill="#fa8960"></path><path d="M90.07 115.37l-54.45-.35c-.72 0-1.31-.59-1.3-1.32c0-.72.59-1.3 1.31-1.3h.01l54.44.35c.72 0 1.31.59 1.3 1.32c0 .72-.59 1.3-1.31 1.3z" fill="#fa8960"></path><path d="M61.22 59.74c-.16.24-15.15 29.11-15.15 29.11l-.58 28.41l35.54.46l.61-26.2l-14.67-31.78h-5.75z" fill="#ee6d33"></path><path d="M58.75 63.08c-.29.08-3.33 5-3.08 5.42c.12.21 3.93.26 8.03.26c4.23 0 8.22.13 8.29 0c.13-.26-2.35-5.38-2.61-5.51c-.25-.12-10.33-.25-10.63-.17z" fill="#fdbba0"></path><path d="M53.88 71.28c-.38.21-4.74 7.73-4.48 8.07c.23.31 6.89.25 13.67.34c7.09.1 14.3.34 14.44.21c.26-.26-3.76-8.11-4.19-8.33c-.21-.1-4.81-.18-9.44-.26c-4.88-.06-9.8-.13-10-.03z" fill="#fdbba0"></path><path fill="#8b200d" d="M44.91 91.15l-.38 25.14l2.43-.12l.37-26.14z"></path><path fill="#8b200d" d="M80.13 91.84v25.07l2.67-.68V91.21z"></path><path d="M47.65 82.82c-.63.4-4.87 8.41-4.57 8.8c.15.19 10.07.23 20.16.34c10.43.12 21.03.3 21.14.17c.21-.26-4.83-8.58-5.17-8.8c-.34-.22-31.22-.73-31.56-.51z" fill="#fdbba0"></path><path d="M15.75 114.91c-.92.46-1.01 8.71-.57 9.44c.44.73 94.4.63 95.12.34s.28-8.71-.15-9.29c-.11-.15-23.97-.07-47.73-.18c-23.28-.12-46.45-.42-46.67-.31z" fill="#ee6d33"></path><path d="M53.02 97.23c-.14.24-.38 18.84-.38 18.84l21.34-.01s.39-18.14.26-18.45c-.13-.32-21.03-.7-21.22-.38z" fill="#51362f"></path><path d="M51.83 115.87c-.06.62 0 3.87 0 3.87s-1.19-.06-1.25.11c-.14.38-.01 3.35-.01 3.35l24.91 1.16s.11-4.08-.1-4.25c-.15-.12-.99-.1-.99-.1s.17-4-.14-4c-.85.01-6.04.02-11.62-.07c-5.55-.07-10.79-.17-10.8-.07z" fill="#fa8960"></path><path fill="#d05314" d="M50.6 124.88l24.88.14l.03-2.51l-24.98-.19z"></path><path fill="#d05314" d="M51.78 117.86l22.67.21l-.05 1.95l-22.58-.27z"></path><path d="M76.43 58.82h-.03l-25.3-.48c-.72-.01-1.3-.61-1.28-1.34c.01-.72.62-1.33 1.34-1.28l25.3.48c.72.01 1.3.61 1.29 1.34a1.33 1.33 0 0 1-1.32 1.28z" fill="#fa8960"></path><path d="M59.84 58.35s1.12-2.65 4.22-2.69c3.33-.04 4.66 2.85 4.66 2.85s1.52.39 1.77 1.49c.16.72-.21 1.28-1.56 1.27c-.94-.01-2.33-.01-4.79-.06c-2.21-.05-3.8-.07-4.74-.09c-1.17-.02-1.82-.51-1.6-1.43c.28-1.25 2.04-1.34 2.04-1.34z" fill="#fdbba0"></path><path d="M64.22 51.81c-1.45 0-2.01 1.04-2.01 2.17s.76 2.01 2.13 2.01c1.37 0 1.97-.96 2.01-2.13c.02-.89-.93-2.05-2.13-2.05z" fill="#fdbba0"></path></svg>`,
  'ov-vihara': `<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve"><rect x="179.04" y="204.693" style="fill:#FFCF65;" width="153.921" height="34.205"/><rect x="110.63" y="367.165" style="fill:#FF4B4B;" width="290.739" height="94.063"/><rect x="136.284" y="307.307" style="fill:#FFCF65;" width="239.432" height="34.205"/><g><path style="fill:#625D6B;" d="M420.889,367.324l-45.172-25.813H136.284l-45.173,25.813c-3.999,2.285-2.377,8.392,2.229,8.392	h325.32C423.266,375.716,424.888,369.609,420.889,367.324z"/><path style="fill:#625D6B;" d="M256,84.977c-26.767,51.527-79.719,74.776-101.481,82.449c-4.378,1.543-5.597,7.15-2.314,10.432 l26.835,26.835H332.96l26.835-26.835c3.283-3.283,2.064-8.889-2.314-10.432C335.719,159.753,282.767,136.504,256,84.977z"/></g><circle style="fill:#FFCF65;" cx="256" cy="67.875" r="17.102"/><path style="fill:#504B5A;" d="M332.96,238.898H179.04l-68.996,30.664c-3.838,1.706-4.772,6.732-1.801,9.702l28.041,28.042h239.432	l28.041-28.041c2.97-2.97,2.037-7.996-1.801-9.702L332.96,238.898z"/><path style="fill:#625D6B;" d="M405.039,273.102c-0.516-1.481-1.477-2.826-3.083-3.54l-68.996-30.664H179.04l-68.996,30.664	c-1.605,0.713-2.566,2.059-3.083,3.54H405.039z"/><path style="fill:#504B5A;" d="M360.767,170.489H151.233c-1.214,2.326-1.119,5.279,0.972,7.371l26.835,26.834H332.96l26.834-26.834	C361.886,175.768,361.98,172.814,360.767,170.489z"/><rect x="230.347" y="170.489" style="fill:#5091D7;" width="51.307" height="76.96"/><g><rect x="25.119" y="427.023" style="fill:#EDEDEE;" width="196.676" height="34.205"/><rect x="290.205" y="427.023" style="fill:#EDEDEE;" width="196.676" height="34.205"/></g><path d="M503.983,453.211h-9.086v-26.188c0-4.427-3.589-8.017-8.017-8.017h-77.495v-35.273h26.188c3.635,0,6.815-2.446,7.748-5.959 c0.933-3.513-0.615-7.215-3.77-9.018l-55.82-31.896v-26.232l31.856-31.856c2.293-2.293,2.979-5.741,1.738-8.736	c-0.803-1.938-2.314-3.428-4.156-4.247l0.005-0.011l-72.199-32.09v-25.675l31.856-31.856c2.293-2.293,2.979-5.741,1.738-8.736	c-0.98-2.368-3.017-4.065-5.427-4.681l0.004-0.018c-0.699-0.179-66.992-17.686-101.122-72.801	c7.794-4.27,13.092-12.55,13.092-22.046c0-13.851-11.268-25.119-25.119-25.119c-13.851,0-25.119,11.268-25.119,25.119	c0,9.495,5.296,17.775,13.089,22.044c-34.06,54.954-100.427,72.624-101.119,72.802l0.004,0.018	c-2.409,0.616-4.446,2.314-5.427,4.681c-1.241,2.996-0.555,6.443,1.738,8.736l31.857,31.857v25.675l-72.199,32.088l0.005,0.011	c-1.842,0.819-3.353,2.309-4.156,4.247c-1.241,2.996-0.555,6.443,1.738,8.736l31.856,31.857v26.232l-55.819,31.897	c-3.156,1.803-4.703,5.505-3.77,9.018c0.932,3.513,4.112,5.959,7.747,5.959h26.188v35.273H25.119c-4.427,0-8.017,3.589-8.017,8.017	v26.188H8.017C3.589,453.211,0,456.8,0,461.228s3.589,8.017,8.017,8.017h495.967c4.427,0,8.017-3.589,8.017-8.017	S508.411,453.211,503.983,453.211z M144.301,315.324h223.399v18.171H144.301V315.324z M273.637,178.505v60.927h-35.273v-60.927	H273.637z M324.944,230.881H289.67V212.71h35.273V230.881z M222.33,230.881h-35.273V212.71h35.273V230.881z M180.741,246.914h41.589	v0.534c0,4.427,3.589,8.017,8.017,8.017h51.307c4.427,0,8.017-3.589,8.017-8.017v-0.534h41.588l40.885,18.171H139.856	L180.741,246.914z M329.64,196.676h-39.97v-18.171h58.141L329.64,196.676z M256,58.789c5.01,0,9.086,4.076,9.086,9.086	S261.01,76.96,256,76.96c-5.01,0-9.086-4.076-9.086-9.086S250.99,58.789,256,58.789z M256,100.919	c19.546,30.287,47.3,49.732,69.947,61.553H186.053C208.7,150.651,236.454,131.206,256,100.919z M222.33,178.505v18.171h-39.97	l-18.171-18.171H222.33z M390.567,281.119l-18.171,18.171H139.604l-18.171-18.171H390.567z M138.413,349.528h235.175l31.8,18.171	H106.614L138.413,349.528z M118.647,383.733h274.706v35.273h-52.376v-9.086c0-4.427-3.589-8.017-8.017-8.017	c-4.427,0-8.017,3.589-8.017,8.017v9.086h-34.739c-4.427,0-8.017,3.589-8.017,8.017v26.188h-18.171v-43.29	c0-4.427-3.589-8.017-8.017-8.017c-4.427,0-8.017,3.589-8.017,8.017v43.29h-18.171v-26.188c0-4.427-3.589-8.017-8.017-8.017h-34.739	v-9.086c0-4.427-3.589-8.017-8.017-8.017s-8.017,3.589-8.017,8.017v9.086h-52.376V383.733z M33.136,453.211V435.04h180.643v18.171	H33.136z M298.221,453.211V435.04h180.643v18.171H298.221z"/></svg>`,
  'ov-klenteng': `<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve"><path style="fill:#FF1D25;" d="M176.381,71.938c17.639,0,32.173-14.3,32.173-31.94V7.821h94.892v32.177 c0,17.641,14.648,31.94,32.287,31.94"/><rect x="184.132" y="71.938" style="fill:#808080;" width="143.735" height="79.855"/><rect x="216.072" y="103.881" style="fill:#FFFFFF;" width="79.855" height="47.91"/><rect x="152.244" y="151.786" style="fill:#FBB03B;" width="207.511" height="23.955"/><path style="fill:#FF1D25;" d="M463.609,288.037c-61.74,0-111.79-48.721-111.79-111.288h-191.64 c0,62.566-50.05,111.539-111.79,111.539"/><rect x="104.287" y="264.363" style="fill:#FBB03B;" width="303.436" height="22.937"/><g><rect x="447.64" y="319.473" style="fill:#FF1D25;" width="31.94" height="63.88"/><rect x="32.42" y="319.473" style="fill:#FF1D25;" width="31.94" height="63.88"/></g><rect x="128.24" y="287.533" style="fill:#808080;" width="255.52" height="167.688"/><rect x="168.168" y="367.378" style="fill:#FF1D25;" width="175.675" height="87.833"/><rect x="96.3" y="287.533" style="fill:#4D4D4D;" width="31.94" height="167.688"/><rect x="96.3" y="407.306" style="fill:#FF1D25;" width="31.94" height="47.91"/><rect x="383.76" y="287.533" style="fill:#4D4D4D;" width="31.94" height="167.688"/><rect x="383.76" y="407.306" style="fill:#FF1D25;" width="31.94" height="47.91"/><rect x="80.325" y="455.221" style="fill:#FBB03B;" width="351.34" height="24.455"/><rect x="49.01" y="479.174" style="fill:#FF1D25;" width="413.98" height="23.956"/><rect y="495.837" width="512" height="15.642"/><rect x="49.01" y="471.853" width="413.98" height="15.642"/><polygon points="352.456,439.528 336.815,439.528 336.815,374.876 175.185,374.876 175.185,439.528 159.544,439.528 159.544,359.234 352.456,359.234 "/><g><rect x="159.544" y="336.293" style="fill:#FFFFFF;" width="192.912" height="15.642"/><rect x="208.554" y="311.267" style="fill:#FFFFFF;" width="94.892" height="15.642"/><rect x="224.196" y="407.202" style="fill:#FFFFFF;" width="15.642" height="15.642"/><rect x="272.163" y="407.202" style="fill:#FFFFFF;" width="15.642" height="15.642"/></g><polygon points="303.446,136.081 287.805,136.081 287.805,112.098 224.196,112.098 224.196,136.081 208.554,136.081 208.554,96.456 303.446,96.456 "/><path d="M368.098,32.847v15.642h14.391c-3.395,9.011-12.413,15.642-22.685,15.642h-24.032v-0.014	c-13.556,0-23.984-10.82-23.984-24.119V0.521H200.212v39.476c0,13.299-10.428,24.119-23.984,24.119v0.014h-24.035	c-10.272,0-19.291-6.631-22.686-15.642h14.395V32.847h-31.283v7.821c0,21.196,18.021,39.104,39.574,39.104h25.078v63.609h-25.026 v15.642h207.511v-15.642h-25.026V79.772h25.074c21.553,0,39.577-17.907,39.577-39.104v-7.821H368.098z M215.853,39.998V16.163	h80.293v23.835c0,9.07,3.041,17.435,8.148,24.133h-16.49V23.462h-15.642V64.13h-9.385V23.462h-15.642V64.13h-7.299V23.462h-15.642	V64.13h-16.49C212.813,57.433,215.853,49.068,215.853,39.998z M319.088,143.381h-56.31V120.44h-15.642v22.941h-54.224V79.772 h126.175V143.381z"/><path d="M463.511,199.69h-7.821v31.283h15.642v-14.625c14.477,3.232,24.332,15.447,24.332,31.395	c0,17.634-14.151,31.999-31.631,32.23v-0.018c-28.155,0-54.161-10.67-73.694-30.044c-5.745-5.698-10.74-11.973-14.942-18.699v-0.239	h-0.148c-10.057-16.196-15.493-35.003-15.493-54.745v-7.821H152.244v7.821c0,19.734-5.429,38.54-15.472,54.745h-0.17v0.274	c-4.203,6.735-9.2,13.02-14.946,18.728c-18.527,18.406-42.862,28.964-69.337,30.008h-3.932c-17.674,0-32.054-14.463-32.054-32.24	c0-15.364,10.201-27.9,24.334-31.316v14.547H56.31V199.69h-7.821c-26.801,0-47.796,21.117-47.796,47.966 c0,26.261,21.169,47.728,47.275,47.957v0.103c1.542,0,40.668-0.093,40.668-0.093v151.202h-8.342v15.642h351.413v-15.642h-8.342	V295.624h40.245c26.301,0,47.696-21.566,47.696-47.968C511.306,220.361,490.759,199.69,463.511,199.69z M132.737,261.073 c1.323-1.314,2.611-2.656,3.866-4.022v14.59h15.642v-35.922c8.992-15.644,14.346-33.225,15.509-51.67h0.132v87.593h15.642v-87.593	h16.684v87.593h15.642v-87.593h15.642v87.593h15.642v-87.593h17.727v87.593h15.642v-87.593h15.642v87.593h15.642v-87.593h16.684	v87.593h15.642v-87.593h0.129c1.163,18.441,6.519,36.012,15.512,51.642v35.951h15.642v-14.643c1.256,1.365,2.544,2.705,3.869,4.019	c7.541,7.48,15.927,13.826,24.958,18.967H107.934C116.904,274.85,125.238,268.524,132.737,261.073z M104.277,415.544h16.684v31.283	h-16.684V415.544z M391.039,446.827v-31.283h16.684v31.283H391.039z M407.723,399.902h-16.684v-96.978h-15.642v143.902H262.778 v-63.609h-15.642v63.609H136.603V302.925h-15.642v96.978h-16.684V295.625h303.446V399.902z"/><path d="M471.332,302.925H455.69v8.728h-15.873v79.522h15.873v16.027h15.642v-16.027h16.067v-79.522h-16.067V302.925z M471.757,375.533h-16.298v-48.239h16.298V375.533z"/><path d="M56.31,302.925H40.668v8.728H24.597v79.522h16.071v16.027H56.31v-16.027h15.869v-79.522H56.31V302.925z M56.537,375.533 H40.238v-48.239h16.298V375.533z"/></svg>`,
  'ov-ambulance': `<svg height="150px" width="150px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 496 496" xml:space="preserve"><path style="fill:#BC043D;" d="M239.2,128c0,4-11.2,8-15.2,8h-65.6c-4,0-15.2-3.2-15.2-8l7.2-19.2c0-4,3.2-7.2,7.2-7.2H224	c4,0,7.2,3.2,7.2,7.2L239.2,128z"/><path style="fill:#9B053B;" d="M239.2,128l-7.2-19.2c0-0.8,0-1.6,0-1.6h-80c0,0.8,0,1.6,0,1.6l-8.8,19.2c0,4,11.2,8,15.2,8H224	C228,136,239.2,132.8,239.2,128z"/><path style="fill:#D3DDDD;" d="M203.2,123.2c0,0.8-0.8,2.4-2.4,2.4H180c-0.8,0-2.4-0.8-2.4-2.4l4-20.8c0-0.8,0.8-2.4,2.4-2.4h12	c0.8,0,2.4,0.8,2.4,2.4L203.2,123.2z"/><path style="fill:#DB0940;" d="M19.2,323.2c0,4.8-4,4-8,4l0,0c-4.8,0-8-3.2-8-8v-36c0-4.8,3.2-8,8-8l0,0c4.8,0,8,3.2,8,8V323.2z"/><path style="fill:#BC043D;" d="M11.2,275.2L11.2,275.2c4.8,0,8,3.2,8,8v40c0,4.8-4,4-8,4l0,0"/><g><path style="fill:#E1EAEA;" d="M472,268c0,8.8-7.2,16-16,16h-43.2c-8.8,0-16-7.2-16-16v-10.4c0-8.8,7.2-16,16-16H456	c8.8,0,16,7.2,16,16V268z"/>	<path style="fill:#E1EAEA;" d="M376.8,164c0,9.6-7.2,16.8-16.8,16.8H38.4c-9.6,0-16.8-7.2-16.8-16.8v-27.2	c0-8.8,7.2-16.8,16.8-16.8H360c9.6,0,16.8,7.2,16.8,16.8V164z"/></g><path style="fill:#D3DDDD;" d="M484,260.8c0-6.4-5.6-12-12-12h-35.2l-42.4-96c0-8.8-7.2-16-16-16H376c-2.4-6.4-8.8-12-16-12	l-314.4,8.8c-8.8,0-23.2-2.4-24,6.4c-4,3.2-6.4,7.2-6.4,12.8v96.8c-4.8,1.6-8.8,5.6-8.8,11.2v78.4c0,6.4,5.6,12,12,12H480 c6.4,0,12-5.6,12-12L484,260.8z"/><path style="fill:#C9D3D3;" d="M488.8,306.4l-3.2-30.4H6.4v63.2c0,6.4,5.6,12,12,12H480c3.2,0,6.4-1.6,8.8-4V306.4z"/><path style="fill:#ADC4C3;" d="M496,355.2c0,4.8-3.2,8-8,8H8c-4.8,0-8-3.2-8-8v-4.8c0-4.8,3.2-8,8-8h480c4.8,0,8,3.2,8,8V355.2z"/><path d="M49.6,348.8c0-27.2,22.4-49.6,49.6-49.6s49.6,22.4,49.6,49.6"/><path style="fill:#ADC4C3;" d="M151.2,348.8h-4c0-26.4-21.6-48-48-48s-48,21.6-48,48h-4c0-28.8,23.2-52,52-52	S151.2,320.8,151.2,348.8z"/><circle style="fill:#203547;" cx="99.2" cy="351.2" r="44.8"/><path style="fill:#122130;" d="M131.2,319.2c17.6,17.6,17.6,45.6,0,63.2s-45.6,17.6-63.2,0"/><circle style="fill:#6E7577;" cx="99.2" cy="351.2" r="28"/><path style="fill:#4C5456;" d="M119.2,331.2c11.2,11.2,11.2,28.8,0,40s-28.8,11.2-40,0"/><circle style="fill:#CED8DD;" cx="99.2" cy="351.2" r="24.8"/><path style="fill:#B2BBC1;" d="M116.8,333.6c9.6,9.6,9.6,25.6,0,35.2s-25.6,9.6-35.2,0"/><path style="opacity:0.2;fill:#888F91;enable-background:new    ;" d="M110.4,351.2c0,6.4-4.8,13.6-11.2,13.6	c-6.4,0-11.2-7.2-11.2-13.6S92.8,340,99.2,340C105.6,339.2,110.4,344.8,110.4,351.2z"/><circle style="fill:#E7EFF2;" cx="99.2" cy="351.2" r="11.2"/><path style="fill:#DAE6EA;" d="M99.2,339.2c6.4,0,11.2,4.8,11.2,11.2s-4.8,11.2-11.2,11.2"/><path style="fill:#666E70;" d="M99.2,326.4c-0.8,0-2.4,0-3.2,0c0.8,3.2,1.6,6.4,3.2,6.4s3.2-2.4,3.2-6.4	C101.6,326.4,100,326.4,99.2,326.4z"/><path style="fill:#535A5B;" d="M99.2,332.8c1.6,0,3.2-2.4,3.2-6.4c-0.8,0-2.4,0-3.2,0"/><path style="fill:#666E70;" d="M96,375.2c2.4,0,4.8,0,6.4,0c-0.8-3.2-1.6-6.4-3.2-6.4C97.6,369.6,96.8,372,96,375.2z"/><path style="fill:#535A5B;" d="M102.4,375.2c-0.8-3.2-1.6-6.4-3.2-6.4"/><path style="fill:#666E70;" d="M80,336c3.2,2.4,5.6,3.2,6.4,1.6c0.8-0.8,0-4-1.6-6.4C82.4,332.8,80.8,334.4,80,336z"/><path style="fill:#535A5B;" d="M86.4,338.4c0.8-0.8,0-4-1.6-6.4"/><path style="fill:#666E70;" d="M119.2,365.6c-3.2-2.4-5.6-3.2-6.4-1.6c-0.8,0.8,0,4,1.6,6.4c0.8-0.8,1.6-1.6,2.4-2.4	C117.6,368,118.4,367.2,119.2,365.6z"/><path style="fill:#535A5B;" d="M116.8,368.8c0.8-0.8,1.6-1.6,2.4-2.4c-3.2-2.4-5.6-3.2-6.4-1.6"/><path style="fill:#666E70;" d="M75.2,354.4c3.2-0.8,6.4-1.6,6.4-3.2s-2.4-3.2-6.4-3.2c0,0.8,0,2.4,0,3.2	C74.4,352,74.4,353.6,75.2,354.4z"/><path style="fill:#535A5B;" d="M80.8,351.2c0-1.6-2.4-3.2-6.4-3.2c0,0.8,0,2.4,0,3.2"/><path style="fill:#666E70;" d="M117.6,351.2c0,1.6,2.4,3.2,6.4,3.2c0-2.4,0-4.8,0-6.4C120,348,117.6,349.6,117.6,351.2z"/><path style="fill:#535A5B;" d="M124,348c-3.2,0.8-6.4,1.6-6.4,3.2"/><path style="fill:#666E70;" d="M80,365.6c1.6,1.6,3.2,3.2,4.8,4.8c2.4-3.2,3.2-5.6,1.6-6.4C85.6,363.2,82.4,364,80,365.6z"/><path style="fill:#535A5B;" d="M84,370.4c2.4-3.2,3.2-5.6,1.6-6.4"/><path style="fill:#666E70;" d="M114.4,331.2c-2.4,3.2-3.2,5.6-1.6,6.4c0.8,0.8,4,0,6.4-1.6C117.6,334.4,116,332.8,114.4,331.2z"/><path style="fill:#535A5B;" d="M114.4,331.2c-2.4,3.2-3.2,5.6-1.6,6.4"/><path d="M344,348.8c0-27.2,22.4-49.6,49.6-49.6s49.6,22.4,49.6,49.6"/><path style="fill:#ADC4C3;" d="M445.6,348.8h-4c0-26.4-21.6-48-48-48s-48,21.6-48,48h-4c0-28.8,23.2-52,52-52	S445.6,320.8,445.6,348.8z"/><circle style="fill:#203547;" cx="394.4" cy="351.2" r="44.8"/><path style="fill:#122130;" d="M425.6,319.2c17.6,17.6,17.6,45.6,0,63.2s-45.6,17.6-63.2,0"/><circle style="fill:#6E7577;" cx="394.4" cy="351.2" r="28"/><path style="fill:#4C5456;" d="M413.6,331.2c11.2,11.2,11.2,28.8,0,40s-28.8,11.2-40,0"/><circle style="fill:#CED8DD;" cx="394.4" cy="351.2" r="24.8"/><path style="fill:#B2BBC1;" d="M411.2,333.6c9.6,9.6,9.6,25.6,0,35.2s-25.6,9.6-35.2,0"/><path style="opacity:0.2;fill:#888F91;enable-background:new    ;" d="M405.6,351.2c0,6.4-4.8,13.6-11.2,13.6s-11.2-7.2-11.2-13.6	S388,340,394.4,340S405.6,344.8,405.6,351.2z"/><circle style="fill:#E7EFF2;" cx="394.4" cy="351.2" r="11.2"/><path style="fill:#DAE6EA;" d="M394.4,339.2c6.4,0,11.2,4.8,11.2,11.2s-4.8,11.2-11.2,11.2"/><path style="fill:#666E70;" d="M394.4,326.4c-0.8,0-2.4,0-3.2,0c0.8,3.2,1.6,6.4,3.2,6.4s3.2-2.4,3.2-6.4	C396,326.4,395.2,326.4,394.4,326.4z"/><path style="fill:#535A5B;" d="M394.4,332.8c1.6,0,3.2-2.4,3.2-6.4c-0.8,0-2.4,0-3.2,0"/><path style="fill:#666E70;" d="M390.4,375.2c2.4,0,4.8,0,6.4,0c-0.8-3.2-1.6-6.4-3.2-6.4C392.8,369.6,391.2,372,390.4,375.2z"/><path style="fill:#535A5B;" d="M397.6,375.2c-0.8-3.2-1.6-6.4-3.2-6.4"/><path style="fill:#666E70;" d="M374.4,336c3.2,2.4,5.6,3.2,6.4,1.6c0.8-0.8,0-4-1.6-6.4C377.6,332.8,376,334.4,374.4,336z"/><path style="fill:#535A5B;" d="M380.8,338.4c0.8-0.8,0-4-1.6-6.4"/><path style="fill:#666E70;" d="M413.6,365.6c-3.2-2.4-5.6-3.2-6.4-1.6c-0.8,0.8,0,4,1.6,6.4c0.8-0.8,1.6-1.6,2.4-2.4	C412,368,412.8,367.2,413.6,365.6z"/><path style="fill:#535A5B;" d="M411.2,368.8c0.8-0.8,1.6-1.6,2.4-2.4c-3.2-2.4-5.6-3.2-6.4-1.6"/><path style="fill:#666E70;" d="M369.6,354.4c3.2-0.8,6.4-1.6,6.4-3.2s-2.4-3.2-6.4-3.2c0,0.8,0,2.4,0,3.2S369.6,353.6,369.6,354.4z"	/><path style="fill:#535A5B;" d="M376,351.2c0-1.6-2.4-3.2-6.4-3.2c0,0.8,0,2.4,0,3.2"/><path style="fill:#666E70;" d="M412,351.2c0,1.6,2.4,3.2,6.4,3.2c0-2.4,0-4.8,0-6.4C415.2,348,412,349.6,412,351.2z"/><path style="fill:#535A5B;" d="M418.4,348c-3.2,0.8-6.4,1.6-6.4,3.2"/><path style="fill:#666E70;" d="M374.4,365.6c1.6,1.6,3.2,3.2,4.8,4.8c2.4-3.2,3.2-5.6,1.6-6.4C380,363.2,377.6,364,374.4,365.6z"/><path style="fill:#535A5B;" d="M379.2,370.4c2.4-3.2,3.2-5.6,1.6-6.4"/><path style="fill:#666E70;" d="M408.8,331.2c-2.4,3.2-3.2,5.6-1.6,6.4c0.8,0.8,4,0,6.4-1.6C412,334.4,410.4,332.8,408.8,331.2z"/><path style="fill:#535A5B;" d="M408.8,331.2c-2.4,3.2-3.2,5.6-1.6,6.4"/><path style="fill:#51859B;" d="M433.6,240.8l-36.8-84H312c-4.8,0-8.8,4-8.8,8.8v67.2c0,4.8,4,8.8,8.8,8.8h119.2	C432,241.6,432.8,241.6,433.6,240.8z"/><g><polygon style="fill:#81B6C9;" points="341.6,156.8 318.4,156.8 302.4,172.8 302.4,196 "/><path style="fill:#81B6C9;" d="M396,241.6h35.2c0.8,0,1.6,0,2.4-0.8l-11.2-25.6L396,241.6z"/></g><path style="fill:#D3DDDD;" d="M400,247.2c0.8,2.4-0.8,4.8-3.2,5.6l0,0c-2.4,0.8-4.8-0.8-5.6-3.2L364,144c-0.8-2.4,0.8-4.8,3.2-5.6	l0,0c2.4-0.8,4.8,0.8,5.6,3.2L400,247.2z"/><path style="fill:#8E8E8E;" d="M325.6,261.6c0,3.2-2.4,5.6-5.6,5.6h-12.8c-3.2,0-5.6-2.4-5.6-5.6l0,0c0-3.2,2.4-5.6,5.6-5.6H320	C323.2,256,325.6,258.4,325.6,261.6L325.6,261.6z"/><g>	<path style="fill:#DDEDED;" d="M176.8,311.2c0,0.8-0.8,1.6-1.6,1.6h-10.4c-0.8,0-1.6-0.8-1.6-1.6l0,0c0-0.8,0.8-1.6,1.6-1.6h10.4		C176,309.6,176.8,310.4,176.8,311.2L176.8,311.2z"/><path style="fill:#DDEDED;" d="M176.8,320c0,0.8-0.8,1.6-1.6,1.6h-10.4c-0.8,0-1.6-0.8-1.6-1.6l0,0c0-0.8,0.8-1.6,1.6-1.6h10.4	C176,318.4,176.8,318.4,176.8,320L176.8,320z"/><path style="fill:#DDEDED;" d="M176.8,328c0,0.8-0.8,1.6-1.6,1.6h-10.4c-0.8,0-1.6-0.8-1.6-1.6l0,0c0-0.8,0.8-1.6,1.6-1.6h10.4	C176,326.4,176.8,327.2,176.8,328L176.8,328z"/></g><path style="fill:#FFA300;" d="M488.8,314.4c0,4-3.2,6.4-6.4,6.4l0,0c-4,0-6.4-3.2-6.4-6.4v-32.8c0-4,3.2-6.4,6.4-6.4l0,0	c4,0,6.4,3.2,6.4,6.4V314.4z"/><path style="fill:#FF7300;" d="M481.6,321.6L481.6,321.6c-4,0-6.4-3.2-6.4-6.4v-32.8c0-4,3.2-6.4,6.4-6.4l0,0"/><path id="SVGCleanerId_0" style="fill:#BC043D;" d="M151.2,201.6h-30.4v-30.4c0-6.4-5.6-12-12-12H96c-6.4,0-12,5.6-12,12v30.4H55.2	c-6.4,0-12,5.6-12,12v12.8c0,6.4,5.6,12,12,12h30.4v30.4c0,6.4,5.6,12,12,12h12.8c6.4,0,12-5.6,12-12v-30.4h30.4c6.4,0,12-5.6,12-12	v-12.8C163.2,206.4,157.6,201.6,151.2,201.6z"/><g><path id="SVGCleanerId_0_1_" style="fill:#BC043D;" d="M151.2,201.6h-30.4v-30.4c0-6.4-5.6-12-12-12H96c-6.4,0-12,5.6-12,12v30.4 H55.2c-6.4,0-12,5.6-12,12v12.8c0,6.4,5.6,12,12,12h30.4v30.4c0,6.4,5.6,12,12,12h12.8c6.4,0,12-5.6,12-12v-30.4h30.4	c6.4,0,12-5.6,12-12v-12.8C163.2,206.4,157.6,201.6,151.2,201.6z"/></g></svg>`,
  'ov-firetruck': `<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512.003 512.003" xml:space="preserve"><path style="fill:#FF8189;" d="M435.621,145.795c-0.783-2.832-3.827-5.147-6.764-5.147h-31.509c-2.937,0-5.981,2.316-6.763,5.147	l-7.058,25.526c-0.782,2.832,0.981,5.147,3.918,5.147h51.319c2.937,0,4.7-2.317,3.917-5.147L435.621,145.795z"/><rect x="78.96" y="134.809" style="fill:#A4A9AD;" width="26.97" height="26.927"/><rect x="78.96" y="134.809" style="opacity:0.1;enable-background:new ;" width="26.97" height="11.749"/><rect x="144.11" y="134.809" style="fill:#A4A9AD;" width="26.97" height="26.927"/><rect x="144.11" y="134.809" style="opacity:0.1;enable-background:new    ;" width="26.97" height="11.749"/><rect x="209.266" y="134.809" style="fill:#A4A9AD;" width="26.97" height="26.927"/><rect x="209.266" y="134.809" style="opacity:0.1;enable-background:new    ;" width="26.97" height="11.749"/><path style="fill:#FF5959;" d="M24.852,357.551V188.446c0-14.687,12.016-26.703,26.703-26.703h408.889	c14.687,0,26.703,12.016,26.703,26.703v169.105H24.852z"/><rect x="24.852" y="266.188" style="fill:#D1D3D3;" width="462.292" height="20.984"/><g><path style="fill:#A4A9AD;" d="M503.989,365.624c0,5.464-4.471,9.937-9.936,9.937h-22.357c-5.464,0-9.937-4.472-9.937-9.937	v-16.146c0-5.464,4.471-9.937,9.937-9.937h22.357c5.464,0,9.936,4.471,9.936,9.937V365.624z"/><path style="fill:#A4A9AD;" d="M45.966,365.624c0,5.464-4.472,9.937-9.937,9.937H17.947c-5.466,0-9.936-4.472-9.936-9.937v-16.146	c0-5.464,4.47-9.937,9.936-9.937h18.084c5.464,0,9.937,4.471,9.937,9.937v16.146H45.966z"/></g><path style="opacity:0.2;enable-background:new ;" d="M199.864,357.551c0-35.918-29.118-65.036-65.036-65.036	s-65.036,29.118-65.036,65.036H199.864z"/><circle style="fill:#333E48;" cx="134.828" cy="357.555" r="53.822"/><circle style="fill:#A4A9AD;" cx="134.828" cy="357.555" r="27.28"/><path style="opacity:0.2;enable-background:new    ;" d="M442.206,357.551c0-35.918-29.118-65.036-65.036-65.036	s-65.036,29.118-65.036,65.036H442.206z"/><circle style="fill:#333E48;" cx="377.174" cy="357.555" r="53.822"/><circle style="fill:#A4A9AD;" cx="377.174" cy="357.555" r="27.28"/><path style="fill:#D1D3D3;" d="M377.17,111.314c0-5.875-4.807-10.681-10.681-10.681H60.511c-5.875,0-10.681,4.807-10.681,10.681	v12.817c0,5.875,4.807,10.681,10.681,10.681h305.978c5.875,0,10.681-4.807,10.681-10.681V111.314z"/><path style="fill:#333E48;" d="M382.511,229.349c0,4.7,3.845,8.545,8.545,8.545h62.189c4.7,0,8.545-3.845,8.545-8.545v-37.028	c0-4.7-3.845-8.545-8.545-8.545h-62.189c-4.7,0-8.545,3.845-8.545,8.545V229.349z"/><path style="opacity:0.1;enable-background:new ;" d="M453.245,183.776h-62.189c-4.7,0-8.545,3.845-8.545,8.545v11.035	c0-4.7,3.845-8.545,8.545-8.545h62.189c4.7,0,8.545,3.845,8.545,8.545v-11.035C461.79,187.621,457.945,183.776,453.245,183.776z"/><path style="fill:#333E48;" d="M304.646,229.349c0,4.7,3.845,8.545,8.545,8.545h36.566c4.699,0,8.545-3.845,8.545-8.545v-37.028	c0-4.7-3.846-8.545-8.545-8.545h-36.566c-4.7,0-8.545,3.845-8.545,8.545V229.349z"/><path style="opacity:0.1;enable-background:new ;" d="M349.757,183.776h-36.566c-4.7,0-8.545,3.845-8.545,8.545v11.035	c0-4.7,3.845-8.545,8.545-8.545h36.566c4.699,0,8.545,3.845,8.545,8.545v-11.035C358.302,187.621,354.457,183.776,349.757,183.776z"	/><rect x="215.888" y="253.37" style="fill:#A4A9AD;" width="80.216" height="104.184"/><g>	<path style="fill:#FFB819;" d="M487.147,339.542h-22.864c-2.937,0-5.341-2.403-5.341-5.341v-13.536	c0-2.937,2.403-5.341,5.341-5.341h22.864V339.542z"/><path style="fill:#FFB819;" d="M24.852,339.542h22.865c2.937,0,5.341-2.403,5.341-5.341v-13.536c0-2.937-2.403-5.341-5.341-5.341	H24.852V339.542z"/></g><g><path style="fill:#D1D3D3;" d="M265.206,196.947c0-2.937-2.403-5.341-5.341-5.341h-18.176c-2.937,0-5.341,2.403-5.341,5.341v27.779	c0,2.937,2.403,5.341,5.341,5.341h18.176c2.937,0,5.341-2.403,5.341-5.341V196.947z"/><path style="fill:#D1D3D3;" d="M89.327,196.947c0-2.937-2.403-5.341-5.341-5.341H65.811c-2.937,0-5.341,2.403-5.341,5.341v27.779	c0,2.937,2.403,5.341,5.341,5.341h18.175c2.937,0,5.341-2.403,5.341-5.341V196.947z"/></g><path style="fill:#1E252B;" d="M377.17,322.261c-19.46,0-35.291,15.832-35.291,35.291c0,19.459,15.831,35.291,35.291,35.291 c19.46,0,35.291-15.832,35.291-35.291S396.63,322.261,377.17,322.261z M377.17,376.82c-10.625,0-19.269-8.644-19.269-19.269	s8.644-19.269,19.269-19.269s19.269,8.644,19.269,19.269C396.439,368.176,387.795,376.82,377.17,376.82z M349.757,175.765h-36.566	c-9.129,0-16.556,7.427-16.556,16.556v37.028c0,9.129,7.427,16.556,16.556,16.556h36.566c9.129,0,16.556-7.427,16.556-16.556	v-37.028C366.313,183.192,358.886,175.765,349.757,175.765z M350.291,229.349c0,0.275-0.26,0.534-0.534,0.534h-36.566	c-0.274,0-0.534-0.26-0.534-0.534v-37.028c0-0.275,0.26-0.534,0.534-0.534h36.566c0.275,0,0.534,0.26,0.534,0.534V229.349z M134.828,322.261c-19.459,0-35.291,15.832-35.291,35.291c0,19.459,15.832,35.291,35.291,35.291s35.291-15.832,35.291-35.291	S154.289,322.261,134.828,322.261z M134.828,376.82c-10.625,0-19.269-8.644-19.269-19.269s8.644-19.269,19.269-19.269	s19.269,8.644,19.269,19.269C154.097,368.176,145.454,376.82,134.828,376.82z M495.158,331.586V188.446	c0-19.142-15.572-34.714-34.714-34.714h-14.318l-2.784-10.073c-1.739-6.284-7.966-11.022-14.486-11.022h-31.509	c-6.521,0-12.748,4.74-14.484,11.024l-2.785,10.071H244.251v-10.909h122.239c10.306,0,18.692-8.386,18.692-18.692v-12.817	c0-10.306-8.386-18.692-18.692-18.692H60.511c-10.306,0-18.692,8.386-18.692,18.692v12.817c0,10.306,8.386,18.692,18.692,18.692	h10.438v10.909H51.555c-19.141,0-34.714,15.572-34.714,34.714v143.141C7.461,332.162,0,339.954,0,349.477v16.146	c0,9.896,8.05,17.948,17.947,17.948h18.084c9.896,0,17.948-8.051,17.948-17.948v-0.062h19.557	c3.942,30.321,29.917,53.82,61.295,53.82s57.351-23.5,61.295-53.82h119.752c3.942,30.321,29.918,53.82,61.295,53.82	s57.352-23.5,61.295-53.82h15.285v0.062c0,9.896,8.051,17.948,17.948,17.948h22.357c9.896,0,17.947-8.051,17.947-17.948v-16.146	C512,339.954,504.538,332.162,495.158,331.586z M398.103,148.658h29.998l1.404,5.074h-32.804L398.103,148.658z M60.511,126.802	c-1.447,0-2.67-1.223-2.67-2.67v-12.817c0-1.447,1.223-2.67,2.67-2.67h305.978c1.447,0,2.67,1.223,2.67,2.67v12.817	c0,1.447-1.223,2.67-2.67,2.67H60.511z M228.229,142.823v10.909h-10.948v-10.909H228.229z M201.259,142.823v10.909h-22.163v-10.909	H201.259z M163.074,142.823v10.909h-10.948v-10.909H163.074z M136.104,142.823v10.909H113.94v-10.909H136.104z M97.919,142.823	v10.909H86.97v-10.909H97.919z M37.956,365.624c0,1.044-0.882,1.926-1.926,1.926H17.947c-1.042,0-1.925-0.882-1.925-1.926v-16.146	c0-1.044,0.881-1.926,1.925-1.926h18.084c1.044,0,1.926,0.882,1.926,1.926v16.146H37.956z M134.828,403.361	c-25.259,0-45.809-20.55-45.809-45.809c0-25.259,20.55-45.809,45.809-45.809c25.259,0,45.809,20.55,45.809,45.809	C180.638,382.811,160.088,403.361,134.828,403.361z M288.091,349.54h-64.19v-88.161h64.19V349.54z M377.17,403.361	c-25.259,0-45.809-20.55-45.809-45.809c0-25.259,20.55-45.809,45.809-45.809c25.259,0,45.809,20.55,45.809,45.809	S402.43,403.361,377.17,403.361z M453.75,349.477v0.063h-15.285c-3.942-30.321-29.918-53.82-61.295-53.82s-57.352,23.5-61.295,53.82	h-11.764v-96.172c0-4.424-3.586-8.011-8.011-8.011h-80.211c-4.424,0-8.011,3.587-8.011,8.011v96.172h-11.756	c-3.942-30.321-29.917-53.82-61.295-53.82c-31.377,0-57.352,23.5-61.295,53.82H53.975v-0.063c0-9.896-8.051-17.948-17.948-17.948	h-3.167V188.445c0-10.307,8.386-18.692,18.692-18.692h408.891c10.306,0,18.692,8.385,18.692,18.692V331.53h-7.439	C461.802,331.531,453.75,339.581,453.75,349.477z M495.978,365.624c0,1.044-0.881,1.926-1.925,1.926h-22.357	c-1.044,0-1.926-0.882-1.926-1.926v-16.146c0-1.044,0.881-1.926,1.926-1.926h22.357c1.044,0,1.925,0.882,1.925,1.926V365.624z	 M259.866,183.596h-18.176c-7.361,0-13.351,5.989-13.351,13.351v5.879h-131v-5.879c0-7.363-5.989-13.351-13.351-13.351H65.811	c-7.361,0-13.351,5.989-13.351,13.351v27.779c0,7.363,5.99,13.351,13.351,13.351h18.175c7.363,0,13.351-5.989,13.351-13.351v-5.878	h131v5.878c0,7.363,5.99,13.351,13.351,13.351h18.176c7.363,0,13.351-5.989,13.351-13.351v-27.779	C273.217,189.584,267.228,183.596,259.866,183.596z M81.316,222.055H68.481v-22.438h12.834V222.055z M257.195,222.055H244.36	v-22.438h12.836V222.055z M453.245,175.765h-62.189c-9.129,0-16.556,7.427-16.556,16.556v37.028c0,9.129,7.427,16.556,16.556,16.556	h62.189c9.129,0,16.556-7.427,16.556-16.556v-37.028C469.801,183.192,462.374,175.765,453.245,175.765z M453.779,229.349	c0,0.275-0.26,0.534-0.534,0.534h-62.189c-0.274,0-0.534-0.26-0.534-0.534v-37.028c0-0.275,0.26-0.534,0.534-0.534h62.189	c0.275,0,0.534,0.26,0.534,0.534V229.349z M240.093,339.534h31.805c4.424,0,8.011-3.587,8.011-8.011	c0-4.424-3.587-8.011-8.011-8.011h-31.805c-4.424,0-8.011,3.587-8.011,8.011C232.082,335.948,235.668,339.534,240.093,339.534z M271.899,288.257c4.127,0,7.473-3.346,7.473-7.473c0-4.127-3.345-7.473-7.473-7.473c-4.126,0-7.473,3.345-7.473,7.473	C264.426,284.911,267.772,288.257,271.899,288.257z M240.1,288.257c4.127,0,7.473-3.346,7.473-7.473	c0-4.127-3.345-7.473-7.473-7.473s-7.473,3.345-7.473,7.473C232.627,284.911,235.974,288.257,240.1,288.257z M240.093,315.073	h31.805c4.424,0,8.011-3.587,8.011-8.011s-3.587-8.011-8.011-8.011h-31.805c-4.424,0-8.011,3.587-8.011,8.011	S235.668,315.073,240.093,315.073z"/></svg>`,
  'ov-policecar': `<svg width="150px" height="150px" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--twemoji" preserveAspectRatio="xMidYMid meet"><path fill="#E1E8ED" d="M32 22s-2-8-5-8H16c-3 0-7 7-7 7s-8 1-8 5v2c0 .09.021.175.026.263A1.992 1.992 0 0 0 0 30a2 2 0 0 0 2 2h30a4 4 0 0 0 4-4v-2a4 4 0 0 0-4-4z"></path><path fill="#292F33" d="M1 26v2c0 .09.021.175.026.263A1.992 1.992 0 0 0 0 30a2 2 0 0 0 2 2h30a4 4 0 0 0 4-4v-2c0-.348-.059-.679-.142-1H1.201A2.82 2.82 0 0 0 1 26z"></path><path fill="#66757F" d="M15.91 32c.055-.326.09-.658.09-1a6 6 0 0 0-12 0c0 .342.035.674.09 1h11.82zm17 0c.055-.326.09-.658.09-1a6 6 0 0 0-12 0c0 .342.035.674.09 1h11.82z"></path><circle fill="#292F33" cx="10" cy="31" r="4"></circle><circle fill="#E1E8ED" cx="10" cy="31" r="2"></circle><circle fill="#292F33" cx="27" cy="31" r="4"></circle><circle fill="#E1E8ED" cx="27" cy="31" r="2"></circle><path fill="#DD2E44" d="M21 13a1 1 0 0 1-1 1h-3a1 1 0 0 1 0-2h3a1 1 0 0 1 1 1z"></path><path fill="#55ACEE" d="M22 13a1 1 0 0 1-1 1h-2a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1zm1 8h7s-2-5-3-5h-4v5zm-2-5h-5c-2 0-5 5-5 5h10v-5z"></path></svg>`,
  'ov-motor:': `<svg height="150px" width="150px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 496 496" xml:space="preserve"><path style="fill:#0C91C4;" d="M365.6,171.959c0,13.6-6.4,24-19.2,24c-13.6,0-28.8-10.4-28.8-24s15.2-24,28.8-24	C359.2,147.959,365.6,158.359,365.6,171.959z"/><path style="fill:#0E81C1;" d="M341.6,148.759c-12,1.6-24.8,12-24.8,23.2s11.2,20.8,22.4,23.2c10.4-1.6,16-12,16-24	C355.2,160.759,351.2,151.159,341.6,148.759z"/><path style="fill:#2A3738;" d="M295.2,328.759h-104c-0.8,0-1.6,0-2.4-0.8l-35.2-17.6c-2.4-0.8-3.2-4-2.4-6.4s4-3.2,6.4-2.4	l34.4,17.6h98.4v-45.6l-10.4-11.2c-1.6-1.6-1.6-4.8,0-6.4c1.6-1.6,4.8-1.6,6.4,0l12,12c0.8,0.8,1.6,2.4,1.6,3.2v52.8	C300,326.359,298.4,328.759,295.2,328.759z"/><path style="fill:#7B8484;" d="M287.2,291.159c0,18.4-14.4,32.8-32.8,32.8l0,0c-18.4,0-32.8-14.4-32.8-32.8v-32.8	c0-18.4,14.4-32.8,32.8-32.8l0,0c18.4,0,32.8,14.4,32.8,32.8V291.159z"/><path style="fill:#5A6363;" d="M274.4,276.759c0,18.4-14.4,32.8-32.8,32.8l0,0c-18.4,0-32.8-14.4-32.8-32.8v-32.8	c0-18.4,14.4-32.8,32.8-32.8l0,0c18.4,0,32.8,14.4,32.8,32.8V276.759z"/><path style="fill:#414747;" d="M256.8,266.359c0,18.4-14.4,32.8-32.8,32.8l0,0c-18.4,0-32.8-14.4-32.8-32.8v-32.8	c0-18.4,14.4-32.8,32.8-32.8l0,0c18.4,0,32.8,14.4,32.8,32.8V266.359z"/><g><path style="fill:#7B8484;" d="M280,215.959c2.4,4,0.8,9.6-4.8,13.6l-107.2,74.4c-4.8,3.2-11.2,3.2-14.4,0l0,0	c-2.4-4-0.8-9.6,4.8-13.6l107.2-74.4C270.4,211.959,276.8,211.959,280,215.959L280,215.959z"/><path style="fill:#7B8484;" d="M244.8,246.359c-1.6,4.8-6.4,6.4-12,4.8l-105.6-33.6c-4.8-1.6-8-6.4-6.4-11.2l0,0	c1.6-4.8,6.4-6.4,12-4.8l105.6,33.6C243.2,237.559,246.4,242.359,244.8,246.359L244.8,246.359z"/></g><path style="fill:#0E81C1;" d="M263.2,234.359c0,6.4-4.8,11.2-11.2,11.2l-64.8,20c-6.4,0-17.6-26.4-17.6-32.8l9.6,0.8	c0-6.4,4.8-11.2,11.2-11.2h60.8C257.6,223.159,263.2,227.959,263.2,234.359L263.2,234.359z"/><path style="fill:#074577;" d="M179.2,234.359L179.2,234.359l-9.6-0.8c0,6.4,11.2,32.8,17.6,32.8l6.4-1.6v-3.2	C193.6,249.559,188,239.959,179.2,234.359z"/><path style="fill:#3F4F51;" d="M256,227.959c-0.8,6.4-40-1.6-47.2-3.2l-99.2-14.4c-7.2-0.8-12-7.2-11.2-14.4l0,0	c0.8-6.4,8-11.2,15.2-10.4l133.6,12.8c7.2,0.8,1.6,8.8,0,16L256,227.959z"/><path style="fill:#2A3738;" d="M246.4,199.159c7.2,0.8,1.6,8.8,0,16l9.6,12.8c-0.8,6.4-58.4,0.8-65.6,0l-80.8-16.8	c-7.2-0.8-12-7.2-11.2-14.4l0,0"/><path style="fill:#3F4F51;" d="M81.6,384.759c-44.8,0-81.6-36.8-81.6-81.6s36.8-81.6,81.6-81.6s81.6,36.8,81.6,81.6	C162.4,347.959,126.4,384.759,81.6,384.759z M81.6,245.559c-32,0-58.4,26.4-58.4,58.4s26.4,58.4,58.4,58.4s58.4-26.4,58.4-58.4	C139.2,271.159,113.6,245.559,81.6,245.559z"/><path style="fill:#7A8687;" d="M81.6,364.759c-33.6,0-60.8-27.2-60.8-60.8s27.2-60.8,60.8-60.8s60.8,27.2,60.8,60.8	S115.2,364.759,81.6,364.759z M81.6,248.759c-30.4,0-54.4,24.8-54.4,54.4c0,30.4,24.8,54.4,54.4,54.4c30.4,0,54.4-24.8,54.4-54.4	C136,273.559,111.2,248.759,81.6,248.759z"/><g><path style="fill:#98A3A3;" d="M82.4,357.559c0,0.8-0.8,0.8-1.6,0.8l0,0c-0.8,0-1.6-0.8-1.6-0.8v-108c0-0.8,0.8-0.8,1.6-0.8l0,0 c0.8,0,1.6,0.8,1.6,0.8V357.559z"/><path style="fill:#98A3A3;" d="M44,342.359c-0.8,0.8-1.6,0.8-1.6,0l0,0c-0.8-0.8-0.8-1.6,0-1.6l76-76c0.8-0.8,1.6-0.8,1.6,0l0,0 c0.8,0.8,0.8,1.6,0,1.6L44,342.359z"/><path style="fill:#98A3A3;" d="M27.2,304.759c-0.8,0-0.8-0.8-0.8-1.6l0,0c0-0.8,0.8-0.8,0.8-0.8h108c0.8,0,0.8,0.8,0.8,0.8l0,0	c0,0.8-0.8,1.6-0.8,1.6H27.2z"/><path style="fill:#98A3A3;" d="M42.4,266.359c-0.8-0.8-0.8-1.6,0-1.6l0,0c0.8-0.8,1.6-0.8,1.6,0l76,76c0.8,0.8,0.8,1.6,0,1.6l0,0	c-0.8,0.8-1.6,0.8-1.6,0L42.4,266.359z"/><path style="fill:#98A3A3;" d="M60.8,353.559c0,0.8-0.8,0.8-1.6,0.8l0,0c-0.8,0-0.8-0.8-0.8-1.6l43.2-99.2c0-0.8,0.8-0.8,1.6-0.8	l0,0c0.8,0,0.8,0.8,0.8,1.6L60.8,353.559z"/><path style="fill:#98A3A3;" d="M31.2,324.759c-0.8,0-1.6,0-1.6-0.8l0,0c0-0.8,0-1.6,0.8-1.6l100.8-39.2c0.8,0,1.6,0,1.6,0.8l0,0	c0,0.8,0,1.6-0.8,1.6L31.2,324.759z"/><path style="fill:#98A3A3;" d="M31.2,283.159c-0.8,0-0.8-0.8-0.8-1.6l0,0c0-0.8,0.8-0.8,1.6-0.8l99.2,43.2c0.8,0,0.8,0.8,0.8,1.6	l0,0c0,0.8-0.8,0.8-1.6,0.8L31.2,283.159z"/>	<path style="fill:#98A3A3;" d="M60,253.559c0-0.8,0-1.6,0.8-1.6l0,0c0.8,0,1.6,0,1.6,0.8l39.2,100.8c0,0.8,0,1.6-0.8,1.6l0,0	c-0.8,0-1.6,0-1.6-0.8L60,253.559z"/></g><path style="fill:#3F4F51;" d="M422.4,384.759c-40.8,0-73.6-32.8-73.6-73.6s32.8-73.6,73.6-73.6s73.6,32.8,73.6,73.6	S463.2,384.759,422.4,384.759z M422.4,257.559c-29.6,0-52.8,24-52.8,52.8s24,52.8,52.8,52.8s52.8-24,52.8-52.8	S451.2,257.559,422.4,257.559z"/><path style="fill:#7A8687;" d="M422.4,366.359c-30.4,0-55.2-24.8-55.2-55.2s24.8-55.2,55.2-55.2c30.4,0,55.2,24.8,55.2,55.2	S452.8,366.359,422.4,366.359z M422.4,260.759c-27.2,0-49.6,22.4-49.6,49.6s22.4,49.6,49.6,49.6s49.6-22.4,49.6-49.6	S449.6,260.759,422.4,260.759z"/><g><path style="fill:#98A3A3;" d="M423.2,359.959c0,0.8-0.8,0.8-0.8,0.8l0,0c-0.8,0-0.8-0.8-0.8-0.8v-98.4c0-0.8,0.8-0.8,0.8-0.8l0,0	c0.8,0,0.8,0.8,0.8,0.8V359.959z"/><path style="fill:#98A3A3;" d="M388,346.359c-0.8,0.8-0.8,0.8-1.6,0l0,0c-0.8-0.8-0.8-0.8,0-1.6l69.6-69.6c0.8-0.8,0.8-0.8,1.6,0	l0,0c0.8,0.8,0.8,0.8,0,1.6L388,346.359z"/><path style="fill:#98A3A3;" d="M372.8,311.959c-0.8,0-0.8-0.8-0.8-0.8l0,0c0-0.8,0.8-0.8,0.8-0.8h98.4c0.8,0,0.8,0.8,0.8,0.8l0,0	c0,0.8-0.8,0.8-0.8,0.8H372.8z"/><path style="fill:#98A3A3;" d="M386.4,276.759c-0.8-0.8-0.8-0.8,0-1.6l0,0c0.8-0.8,0.8-0.8,1.6,0l69.6,69.6c0.8,0.8,0.8,0.8,0,1.6	l0,0c-0.8,0.8-0.8,0.8-1.6,0L386.4,276.759z"/><path style="fill:#98A3A3;" d="M403.2,355.959c0,0.8-0.8,0.8-1.6,0.8l0,0c-0.8,0-0.8-0.8-0.8-1.6l39.2-90.4c0-0.8,0.8-0.8,1.6-0.8 l0,0c0.8,0,0.8,0.8,0.8,1.6L403.2,355.959z"/><path style="fill:#98A3A3;" d="M376.8,329.559c-0.8,0-0.8,0-1.6-0.8l0,0c0-0.8,0-0.8,0.8-1.6l91.2-36c0.8,0,0.8,0,1.6,0.8l0,0	c0,0.8,0,0.8-0.8,1.6L376.8,329.559z"/><path style="fill:#98A3A3;" d="M376.8,291.959c-0.8,0-0.8-0.8-0.8-1.6l0,0c0-0.8,0.8-0.8,1.6-0.8l90.4,39.2c0.8,0,0.8,0.8,0.8,1.6	l0,0c0,0.8-0.8,0.8-1.6,0.8L376.8,291.959z"/><path style="fill:#98A3A3;" d="M403.2,265.559c0-0.8,0-1.6,0.8-1.6l0,0c0.8,0,0.8,0,1.6,0.8l36,91.2c0,0.8,0,0.8-0.8,1.6l0,0	c-0.8,0-1.6,0-1.6-0.8L403.2,265.559z"/><path style="fill:#98A3A3;" d="M226.4,303.159c0,6.4-4.8,12-12,12h-140c-6.4,0-12-4.8-12-12l0,0c0-6.4,4.8-12,12-12h140.8	C221.6,291.959,226.4,296.759,226.4,303.159L226.4,303.159z"/></g><g>	<path style="fill:#B1BCBC;" d="M226.4,303.159c0,6.4-4.8,12-12,12l-132-15.2c-6.4,0-20,10.4-20,4l0,0c0-6.4,4.8-12,12-12h140.8	C221.6,291.959,226.4,296.759,226.4,303.159L226.4,303.159z"/><path style="fill:#B1BCBC;" d="M251.2,301.559c0,16.8-13.6,30.4-30.4,30.4l0,0c-16.8,0-30.4-13.6-30.4-30.4l0,0	c0-16.8,13.6-30.4,30.4-30.4l0,0C237.6,271.159,251.2,284.759,251.2,301.559L251.2,301.559z"/></g><path style="fill:#98A3A3;" d="M242.4,279.959L242.4,279.959c12,12,12,31.2,0,42.4l0,0c-12,12-31.2,12-42.4,0l0,0"/><path style="fill:#0E81C1;" d="M337.6,219.159c0,22.4-18.4,40.8-40.8,40.8H284c-22.4,0-40.8-18.4-40.8-40.8l0,0	c0-22.4,13.6-28.8,36-28.8l27.2-11.2C328.8,179.159,337.6,196.759,337.6,219.159L337.6,219.159z"/><path style="fill:#0C91C4;" d="M335.2,201.559c-4-13.6-12.8-22.4-28.8-22.4l-27.2,11.2c-22.4,0-36,6.4-36,28.8c0,6.4,1.6,12,4,16.8	c2.4,0,4.8,0,7.2,0h20C305.6,235.959,331.2,220.759,335.2,201.559z"/><g><path style="fill:#B1BCBC;" d="M307.2,176.759c0.8,1.6,0,4-1.6,4.8l-17.6,7.2c-1.6,0.8-4,0-4.8-1.6l0,0c-0.8-1.6,0-4,1.6-4.8	l17.6-7.2C304,173.559,306.4,174.359,307.2,176.759L307.2,176.759z"/><path style="fill:#B1BCBC;" d="M375.2,227.959c1.6,2.4,0.8,6.4-1.6,8l0,0c-2.4,1.6-6.4,0.8-8-1.6l-37.6-57.6	c-1.6-2.4-0.8-6.4,1.6-8l0,0c2.4-1.6,6.4-0.8,8,1.6L375.2,227.959z"/></g><path style="fill:#0E81C1;" d="M429.6,308.759c2.4,4,1.6,8.8-2.4,11.2l0,0c-4,2.4-8.8,1.6-11.2-2.4l-49.6-76.8	c-2.4-4-1.6-8.8,2.4-11.2l0,0c4-2.4,8.8-1.6,11.2,2.4L429.6,308.759z"/><path style="fill:#B1BCBC;" d="M292.8,268.759c0,2.4-1.6,4-4,4h-35.2c-2.4,0-4-1.6-4-4l0,0c0-2.4,1.6-4,4-4H288	C290.4,263.959,292.8,266.359,292.8,268.759L292.8,268.759z"/><path style="fill:#98A3A3;" d="M292.8,276.759c0,2.4-1.6,4-4,4h-35.2c-2.4,0-4-1.6-4-4l0,0c0-2.4,1.6-4,4-4H288	C290.4,272.759,292.8,274.359,292.8,276.759L292.8,276.759z"/><path style="fill:#B1BCBC;" d="M292.8,285.559c0,2.4-1.6,4-4,4h-35.2c-2.4,0-4-1.6-4-4l0,0c0-2.4,1.6-4,4-4H288	C290.4,281.559,292.8,283.159,292.8,285.559L292.8,285.559z"/><path style="fill:#98A3A3;" d="M292.8,294.359c0,2.4-1.6,4-4,4h-35.2c-2.4,0-4-1.6-4-4l0,0c0-2.4,1.6-4,4-4H288	C290.4,289.559,292.8,291.959,292.8,294.359L292.8,294.359z"/><path style="fill:#B1BCBC;" d="M292.8,302.359c0,2.4-1.6,4-4,4h-35.2c-2.4,0-4-1.6-4-4l0,0c0-2.4,1.6-4,4-4H288	C290.4,298.359,292.8,299.959,292.8,302.359L292.8,302.359z"/><circle style="fill:#3F4F51;" cx="324" cy="163.959" r="12.8"/><path style="fill:#0C91C4;" d="M187.2,225.559c-2.4,9.6-12.8,15.2-22.4,12.8c-9.6-2.4-94.4-32.8-92-42.4s91.2,4.8,100.8,7.2	C184,206.359,189.6,215.959,187.2,225.559z"/><path style="fill:#0E81C1;" d="M187.2,225.559c-2.4,9.6-12.8,15.2-22.4,12.8c-9.6-2.4-94.4-32.8-92-42.4"/><path style="fill:#3F4F51;" d="M255.2,324.759c0,4.8-4,9.6-9.6,9.6l0,0c-4.8,0-9.6-4-9.6-9.6l0,0c0-4.8,4-9.6,9.6-9.6l0,0	C251.2,315.159,255.2,319.159,255.2,324.759L255.2,324.759z"/><g><path style="fill:#CE1963;" d="M172.8,278.359c1.6,1.6,1.6,3.2,0.8,4l0,0c-0.8,0.8-2.4,0.8-4-0.8l-9.6-10.4c-1.6-1.6-1.6-3.2-0.8-4	l0,0c0.8-0.8,2.4-0.8,4,0.8L172.8,278.359z"/><path style="fill:#CE1963;" d="M177.6,273.559c1.6,1.6,1.6,3.2,0.8,4l0,0c-0.8,0.8-2.4,0.8-4-0.8l-10.4-10.4	c-1.6-1.6-1.6-3.2-0.8-4l0,0c0.8-0.8,2.4-0.8,4,0.8L177.6,273.559z"/>	<path style="fill:#CE1963;" d="M182.4,268.759c1.6,1.6,1.6,3.2,0.8,4l0,0c-0.8,0.8-2.4,0.8-4-0.8l-10.4-10.4	c-1.6-1.6-1.6-3.2-0.8-4l0,0c0.8-0.8,2.4-0.8,4,0.8L182.4,268.759z"/><path style="fill:#CE1963;" d="M187.2,263.959c1.6,1.6,1.6,3.2,0.8,4l0,0c-0.8,0.8-2.4,0.8-4-0.8l-10.4-10.4	c-1.6-1.6-1.6-3.2-0.8-4l0,0c0.8-0.8,2.4-0.8,4,0.8L187.2,263.959z"/><path style="fill:#CE1963;" d="M191.2,259.959c1.6,1.6,1.6,3.2,0.8,4l0,0c-0.8,0.8-2.4,0.8-4-0.8l-10.4-10.4	c-1.6-1.6-1.6-3.2-0.8-4l0,0c0.8-0.8,2.4-0.8,4,0.8L191.2,259.959z"/></g><path style="fill:#0E81C1;" d="M244.8,255.159c0,12-10.4,22.4-22.4,22.4h-7.2c-12.8,0-22.4-10.4-22.4-22.4l0,0	c0-12,10.4-22.4,22.4-22.4h7.2C234.4,232.759,244.8,243.159,244.8,255.159L244.8,255.159z"/><path style="fill:#0C91C4;" d="M244,251.959c-1.6-10.4-11.2-18.4-22.4-18.4h-7.2c-12.8,0-22.4,10.4-22.4,22.4c0,2.4,0.8,4.8,1.6,7.2	c0.8,0,1.6,0,1.6,0H208C224,263.159,236.8,258.359,244,251.959z"/><path d="M173.6,311.959c-2.4,5.6-8.8,7.2-13.6,4.8l-64-28.8c-5.6-2.4-7.2-8.8-4.8-13.6l0,0c2.4-5.6,12.8-16,17.6-13.6l59.2,37.6 C173.6,300.759,176,307.159,173.6,311.959L173.6,311.959z"/><path style="fill:#2A3738;" d="M173.6,311.959c-2.4,5.6-8.8,7.2-13.6,4.8l-65.6-30.4c-5.6-2.4,4-3.2,6.4-8.8l-4.8-4	c2.4-5.6,8-16,12.8-13.6l59.2,37.6C173.6,300.759,176,307.159,173.6,311.959L173.6,311.959z"/><g><polygon style="fill:#3F4F51;" points="326.4,139.159 296.8,123.159 335.2,133.559 333.6,167.159 	"/><ellipse transform="matrix(0.2875 -0.9578 0.9578 0.2875 94.6344 373.4209)" style="fill:#3F4F51;" cx="298.302" cy="123.105" rx="11.2" ry="17.6"/></g></svg>`,
  'ov-bpbd': `<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve"><g><path style="fill:#E5563C;" d="M419.006,439.315H290.739V182.781h94.063c18.891,0,34.205,15.314,34.205,34.205V439.315z"/><path style="fill:#E5563C;" d="M504.518,439.315H290.739V319.599h179.574c18.891,0,34.205,15.314,34.205,34.205V439.315z"/><rect x="8.551" y="319.599" style="fill:#E5563C;" width="282.188" height="119.716"/></g><rect x="8.551" y="319.599" style="fill:#CF442B;" width="25.653" height="119.716"/><circle style="fill:#74757B;" cx="419.006" cy="422.213" r="51.307"/><circle style="fill:#D7D8D9;" cx="419.006" cy="422.213" r="25.653"/><circle style="fill:#74757B;" cx="94.063" cy="422.213" r="51.307"/><g><circle style="fill:#D7D8D9;" cx="94.063" cy="422.213" r="25.653"/><circle style="fill:#D7D8D9;" cx="94.063" cy="422.213" r="25.653"/><path style="fill:#D7D8D9;" d="M393.353,293.946h-76.96v-85.511h59.858c9.446,0,17.102,7.656,17.102,17.102V293.946z"/></g><g><path style="fill:#9B9DA1;" d="M384.241,210.42l-67.848,67.848v15.677h14.252l62.709-62.709v-5.7	C393.353,218.979,389.658,213.29,384.241,210.42z"/>	<polygon style="fill:#9B9DA1;" points="316.392,208.434 316.392,264.73 372.688,208.434 	"/><polygon style="fill:#9B9DA1;" points="393.353,293.946 393.353,263.661 363.068,293.946 	"/></g><rect x="8.551" y="37.411" style="fill:#FDDD85;" width="282.188" height="282.188"/><rect x="8.551" y="37.411" style="fill:#FDD042;" width="25.653" height="282.188"/><polygon style="fill:#F2C127;" points="196.676,148.576 153.921,122.923 111.165,148.576 111.165,37.411 196.676,37.411 "/><polygon style="fill:#E1A527;" points="136.818,37.411 111.165,37.411 111.165,148.576 136.818,133.184 "/><rect x="59.858" y="182.781" style="fill:#EBEBEC;" width="188.125" height="34.205"/><rect x="59.858" y="182.781" style="fill:#D7D8D9;" width="25.653" height="34.205"/><path d="M470.313,312.117h-43.825v-95.132c0-23.281-18.406-41.687-41.687-41.687h-86.58v-52.376c0-4.427-3.588-8.017-8.017-8.017	s-8.017,3.589-8.017,8.017v189.194H16.033V45.962h86.58v102.614c0,6.11,7.158,10.023,12.409,6.874l38.764-23.179l38.431,23.179	c5.255,3.152,11.941-0.771,11.941-6.874V45.962h78.029v42.756c0,4.427,3.588,8.017,8.017,8.017s8.017-3.589,8.017-8.017V37.411	c0-4.427-3.054-7.482-7.482-7.482H8.551C4.124,29.929,0,32.984,0,37.411v401.904c0,4.427,4.124,8.551,8.551,8.551h31.786	c9.476,19.24,29.99,34.205,53.726,34.205s44.25-14.965,53.726-34.205h217.491c9.476,19.24,29.99,34.205,53.726,34.205	s44.25-14.965,53.726-34.205h31.787c4.428,0,7.482-4.124,7.482-8.551v-85.511C512,330.523,493.594,312.117,470.313,312.117z	 M188.125,45.962v88.454l-30.347-18.369c-2.514-1.508-5.869-1.508-8.383,0l-30.748,18.369V45.962H188.125z M384.802,191.332	c14.441,0,25.653,11.214,25.653,25.653v95.132H298.221V191.332H384.802z M94.063,465.503c-23.871,0-43.29-19.42-43.29-43.29	s19.42-43.29,43.29-43.29s43.29,19.42,43.29,43.29S117.933,465.503,94.063,465.503z M94.063,362.889	c-32.711,0-59.324,26.879-59.324,59.591c0,3.089,0.238,6.146,0.696,9.353H16.033V328.15h266.154v103.683H152.69	c0.457-3.207,0.696-6.264,0.696-9.353C153.386,389.769,126.774,362.889,94.063,362.889z M419.006,465.503	c-23.871,0-43.29-19.42-43.29-43.29s19.42-43.29,43.29-43.29c23.871,0,43.29,19.42,43.29,43.29S442.877,465.503,419.006,465.503z	 M495.967,431.833h-18.333c0.458-3.207,0.696-6.264,0.696-9.353c0-32.711-26.612-59.457-59.324-59.457s-59.324,26.812-59.324,59.525	c0,3.089,0.238,6.08,0.696,9.287h-62.157V328.151h172.092c14.441,0,25.653,11.214,25.653,25.653V431.833z"/><path d="M419.006,388.543c-18.566,0-33.67,15.105-33.67,33.67s15.105,33.67,33.67,33.67c18.566,0,33.67-15.105,33.67-33.67	S437.572,388.543,419.006,388.543z M419.006,439.85c-9.725,0-17.637-7.912-17.637-17.637s7.912-17.637,17.637-17.637	c9.725,0,17.637,7.912,17.637,17.637S428.731,439.85,419.006,439.85z"/><path d="M94.063,388.543c-18.566,0-33.67,15.105-33.67,33.67s15.105,33.67,33.67,33.67s33.67-15.105,33.67-33.67	S112.628,388.543,94.063,388.543z M94.063,439.85c-9.725,0-17.637-7.912-17.637-17.637s7.912-17.637,17.637-17.637	s17.637,7.912,17.637,17.637S103.787,439.85,94.063,439.85z"/><path d="M333.495,337.77h-17.102c-4.428,0-8.017,3.589-8.017,8.017c0,4.427,3.588,8.017,8.017,8.017h17.102	c4.428,0,8.017-3.589,8.017-8.017C341.511,341.36,337.923,337.77,333.495,337.77z"/><path d="M316.392,302.497h76.96c4.428,0,7.482-4.124,7.482-8.551v-68.409c0-13.851-10.734-24.585-24.585-24.585h-59.858	c-4.428,0-8.551,3.055-8.551,7.482v85.511C307.841,298.373,311.964,302.497,316.392,302.497z M323.875,216.985h52.376	c5.01,0,8.551,3.541,8.551,8.551v60.927h-60.927V216.985z"/><path d="M34.205,268.292v25.653c0,4.427,3.589,8.017,8.017,8.017s8.017-3.589,8.017-8.017v-25.653c0-4.427-3.589-8.017-8.017-8.017	S34.205,263.865,34.205,268.292z"/><path d="M59.858,268.292v25.653c0,4.427,3.589,8.017,8.017,8.017s8.017-3.589,8.017-8.017v-25.653c0-4.427-3.589-8.017-8.017-8.017	S59.858,263.865,59.858,268.292z"/><path d="M222.33,268.292v25.653c0,4.427,3.589,8.017,8.017,8.017s8.017-3.589,8.017-8.017v-25.653c0-4.427-3.589-8.017-8.017-8.017	S222.33,263.865,222.33,268.292z"/><path d="M256,301.962c4.428,0,8.017-3.589,8.017-8.017v-25.653c0-4.427-3.588-8.017-8.017-8.017c-4.427,0-8.017,3.589-8.017,8.017	v25.653C247.983,298.373,251.573,301.962,256,301.962z"/><path d="M102.614,268.292v25.653c0,4.427,3.589,8.017,8.017,8.017c4.427,0,8.017-3.589,8.017-8.017v-25.653	c0-4.427-3.589-8.017-8.017-8.017C106.203,260.276,102.614,263.865,102.614,268.292z"/><path d="M128.267,268.292v25.653c0,4.427,3.589,8.017,8.017,8.017c4.427,0,8.017-3.589,8.017-8.017v-25.653	c0-4.427-3.589-8.017-8.017-8.017C131.857,260.276,128.267,263.865,128.267,268.292z"/><path d="M153.921,268.292v25.653c0,4.427,3.589,8.017,8.017,8.017s8.017-3.589,8.017-8.017v-25.653c0-4.427-3.589-8.017-8.017-8.017	S153.921,263.865,153.921,268.292z"/><path d="M179.574,268.292v25.653c0,4.427,3.589,8.017,8.017,8.017s8.017-3.589,8.017-8.017v-25.653c0-4.427-3.589-8.017-8.017-8.017	S179.574,263.865,179.574,268.292z"/><path d="M255.466,183.315c0-4.427-3.589-8.017-8.017-8.017H59.324c-4.427,0-8.017,3.589-8.017,8.017v34.205	c0,4.427,3.589,8.017,8.017,8.017h188.125c4.427,0,8.017-3.589,8.017-8.017V183.315z M239.432,209.503H67.34v-18.171h172.092	V209.503z"/></svg>`,
  'ov-towing': `<svg fill="#3fbec9" stroke="#000000" stroke-width="1" width="150px" height="150px" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M4.96875 4C4.726563 4.007813 4.464844 4.097656 4.28125 4.28125L3.28125 5.28125C2.917969 5.644531 2.890625 6.226563 3.21875 6.625L17 23.34375L17 26L27 26L27 23C27 22.707031 26.878906 22.441406 26.65625 22.25L5.65625 4.25C5.457031 4.082031 5.210938 3.992188 4.96875 4 Z M 4 10.71875L4 19C4 19.550781 4.449219 20 5 20C5.550781 20 6 20.449219 6 21C6 21.550781 5.550781 22 5 22C4.449219 22 4 21.550781 4 21L2 21C2 22.652344 3.347656 24 5 24C6.652344 24 8 22.652344 8 21C8 19.695313 7.164063 18.570313 6 18.15625L6 13.15625 Z M 32 13C30.347656 13 29 14.347656 29 16L29 38L33.09375 38C33.574219 40.832031 36.03125 43 39 43C41.96875 43 44.429688 40.832031 44.90625 38L47 38C48.652344 38 50 36.652344 50 35L50 25.375C50 23.363281 48.550781 21.308594 48.375 21.0625L44.21875 15.5C43.265625 14.351563 41.773438 13 40 13 Z M 38 19L44.34375 19L46.78125 22.25C47.085938 22.675781 47.816406 23.902344 47.96875 25L38 25C37.550781 25 37 24.449219 37 24L37 20C37 19.445313 37.546875 19 38 19 Z M 0 28L0 35C0 36.652344 1.347656 38 3 38L7.09375 38C7.574219 40.832031 10.03125 43 13 43C15.96875 43 18.425781 40.832031 18.90625 38L27 38L27 28 Z M 13 33C15.207031 33 17 34.792969 17 37C17 39.207031 15.207031 41 13 41C10.792969 41 9 39.207031 9 37C9 34.792969 10.792969 33 13 33 Z M 39 33C41.207031 33 43 34.792969 43 37C43 39.207031 41.207031 41 39 41C36.792969 41 35 39.207031 35 37C35 34.792969 36.792969 33 39 33Z"/></svg>`,
  'patrol-icon': `<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 496.2 496.2" xml:space="preserve"><path style="fill:#4ABC96;" d="M496.2,248.1C496.2,111.1,385.1,0,248.1,0S0,111.1,0,248.1s111.1,248.1,248.1,248.1 S496.2,385.1,496.2,248.1z"/><path style="fill:#D3C624;" d="M248.1,496.2c70.2,0,133.6-29.2,178.7-76c-2.8-15.1-5.6-28.9-8.3-37.4 c-8.5-27.3-81.2-49.3-170.8-49.3s-161.5,22-170,49.3c-2.6,8.5-5.5,22.2-8.3,37.4C114.5,467,177.9,496.2,248.1,496.2z"/><path style="fill:#334D5C;" d="M157.4,474.8v4.2c28.1,11.1,58.7,17.1,90.7,17.1s62.6-6.1,90.7-17.1v-4.2H157.4z"/><g><path style="fill:#B7A921;" d="M226.6,384.8v110.5c4,0.3,8,0.6,12,0.7V384.8H226.6z"/><path style="fill:#B7A921;" d="M269.6,384.8h-12V496c4-0.2,8-0.4,12-0.7V384.8z"/></g><g><path style="fill:#3A3A38;" d="M227.2,424.1h-35.1c-2.3,0-4.2,1.9-4.2,4.2v60.6c14.1,3.5,28.6,5.8,43.5,6.8v-67.4 C231.4,425.9,229.5,424.1,227.2,424.1z"/><path style="fill:#3A3A38;" d="M231.4,463.4c0,2.3-1.9,4.2-4.2,4.2h-35.1c-2.3,0-4.2-1.9-4.2-4.2V434c0-2.3,1.9-4.2,4.2-4.2h35.1 c2.3,0,4.2,1.9,4.2,4.2V463.4z"/></g><path style="fill:#444442;" d="M229.1,464.3c0,0.6-0.5,1.1-1.1,1.1h-36.7c-0.6,0-1.1-0.5-1.1-1.1v-10c0-0.6,0.5-1.1,1.1-1.1h36.8 c0.6,0,1.1,0.5,1.1,1.1v10H229.1z"/><path style="fill:#9CB797;" d="M224.7,461.9c0,0.4-0.3,0.6-0.6,0.6h-28.9c-0.4,0-0.6-0.3-0.6-0.6v-5c0-0.4,0.3-0.6,0.6-0.6h28.9 c0.4,0,0.6,0.3,0.6,0.6V461.9z"/><g><path style="fill:#3A3A38;" d="M227.6,427.6c0,0.9-0.8,1.7-1.7,1.7h-6.5c-0.9,0-1.7-0.8-1.7-1.7V418c0-0.9,0.8-1.7,1.7-1.7h6.5 c0.9,0,1.7,0.8,1.7,1.7V427.6z"/><path style="fill:#3A3A38;" d="M223.5,427.2c0,0.6-0.5,1-1,1l0,0c-0.6,0-1-0.5-1-1v-30.9c0-0.6,0.5-1,1-1l0,0c0.6,0,1,0.5,1,1 V427.2z"/><path style="fill:#3A3A38;" d="M224.9,396.1c0,1.5-1.1,1.7-2.6,1.7c-1.4,0-2.6-0.2-2.6-1.7s1.1-1.7,2.6-1.7 S224.9,394.6,224.9,396.1z"/></g><path style="fill:#545454;" d="M225,433.1c0,13.4-2.7,17.7-15.3,17.7c-12.5,0-15.3-4.7-15.3-17.7c0-5.6,6.8-5.2,15.3-5.2 C218.2,428,225,427.5,225,433.1z"/><path style="fill:#4C4C4B;" d="M221.7,434.5c0,10.5-2.1,13.9-11.9,13.9c-9.8,0-11.9-3.7-11.9-13.9c0-4.4,5.3-4,11.9-4 S221.7,430.1,221.7,434.5z"/><g><path style="fill:#172023;" d="M426.8,420.1c-2.8-15.1-5.6-28.9-8.3-37.4c-5-16-32.2-30.3-71.9-39.3v132.3 c20.4-8.8,39.4-20.4,56.5-34.1C411.5,435.1,419.4,427.8,426.8,420.1z"/><path style="fill:#172023;" d="M77.7,382.8c-2.6,8.5-5.5,22.2-8.3,37.4c7.4,7.7,15.3,14.9,23.7,21.6c17.1,13.7,36.1,25.2,56.5,34.1 V343.4C109.8,352.4,82.7,366.7,77.7,382.8z"/></g><g><path style="fill:#D6D6D6;" d="M329.6,340.1v142.4c11.6-3.7,27-11.2,27-11.2V345.9C348.2,343.7,339.2,341.8,329.6,340.1z"/><path style="fill:#D6D6D6;" d="M166.6,340c-9.6,1.7-18.6,3.6-27,5.8v125.4c0,0,12.8,6.3,27,11.2V340z"/></g><path style="fill:#172023;" d="M353.1,311.1c-24.6-20.7-70.9,5-104.9,5s-80.3-25.7-104.9-5c-16,13.4-14,52.7,23.5,63.5 c58.1,16.8,79.5,25.2,81.4,25.9v0.1c0,0,0,0,0.1,0h0.1v-0.1c1.9-0.7,23.2-9.2,81.4-25.9C367.1,363.8,369.1,324.5,353.1,311.1z"/><path style="fill:#E2A379;" d="M287,282.1h-77.8c12.1,36.6,1,53.3,1,53.3s11.9,13.2,37.9,13.2s37.9-13.2,37.9-13.2 S274.9,318.7,287,282.1z"/><g><path style="fill:#F4B382;" d="M336.4,175.2c0-92.4-39.5-113.6-88.3-113.6c-48.7,0-88.3,21.2-88.3,113.6c0,31.3,6.2,55.8,15.5,74.7 c20.4,41.6,55.7,56.1,72.8,56.1s52.4-14.5,72.8-56.1C330.2,231,336.4,206.5,336.4,175.2z"/><path style="fill:#F4B382;" d="M170.4,238.7c-8.4,1.4-14.4,0.1-19.1-27.7s1.7-31.5,10.1-32.9L170.4,238.7z"/><path style="fill:#F4B382;" d="M325.9,238.7c8.4,1.4,14.3,0.1,19.1-27.7c4.8-27.8-1.7-31.5-10.1-32.9L325.9,238.7z"/></g><path style="fill:#E5A173;" d="M274.8,257c0,2.6-11.9,9.5-26.7,9.5s-26.7-7-26.7-9.5c0-1.6,8.5-6.1,14.1-6.9 c3.6-0.5,12.5,2.9,12.5,2.9s8.8-3.4,12.4-2.9C266.2,250.9,274.8,257,274.8,257z"/><path style="fill:#D89364;" d="M274.8,257c0,2.6-11.9,12.4-26.7,12.4s-26.7-9.9-26.7-12.4c0,0,10.9,1.3,26.7,1.3 S274.8,257,274.8,257z"/><g><path style="fill:#47200D;" d="M336.4,175.2c0-92.4-30.3-116.1-88.3-116.1s-88.3,23.7-88.3,116.1c0,0,16.3-34.1,28.3-53.1 s38-18.3,38-18.3h49c0,0,27,0.3,34.5,18.3L336.4,175.2z"/><path style="fill:#47200D;" d="M165.5,140.8c-4.2,10.9-5.6,24.4-5.6,37.5c0,0,8.8-1.9,8.8,2.6l2.8,27.6l3.9-1.9 c-0.2-23,4.9-83.5,4.9-83.5L165.5,140.8z"/><path style="fill:#47200D;" d="M330.8,140.8c4.2,10.9,5.6,24.4,5.6,37.5c0,0-8.8-1.9-8.8,2.6l-2.8,27.6l-3.9-1.9 c0.2-23-4.9-81.7-4.9-81.7L330.8,140.8z"/></g><path style="fill:#191919;" d="M247.9,40.4c-60.8,0-113.4,37.5-113,38c3.4,4.4,27.4,30.7,27.4,30.7l1.2,15.6c0,0-0.2,14.5,13.9,14.5 s31.6,4,70.6,4s56.3-4,70.6-4c14.4,0,14.2-14.9,14.2-14.9l0.9-15.2c6.9-6.8,23.6-25.8,27.2-30.4C361.5,78.1,308.8,40.4,247.9,40.4z"/><path style="fill:#2872BC;" d="M333.9,109.1l0.1-0.1c-14.5-6.1-46.2-17-85.9-17c-39.8,0-71.4,11-86,17.1c0.1,0.1,0.2,0.1,0.2,0.1 l1.1,15.6c15.1-8.3,46-16.7,84.7-16.7s69.6,8.3,84.7,16.7L333.9,109.1z"/><path style="fill:#F7EEC1;" d="M269.4,68.3c2.9-4.3-0.1-11.7-7-9.3c-6.6,2.4-14-2.7-14.3-2.9l0,0l0,0l0,0l0,0l0,0l0,0l0,0 c0.3,0.2-7.6,5.2-14.3,2.9c-6.9-2.4-9.9,4.9-7,9.3c3.2,4.7,3.7,7,3.7,7.9c0,2.5-9.2,7.8-2,19.5c2.7,4.3,4.5,3.7,14.5,4.7 c0,0,4.5,1.6,5.1,4.6v-1.4v1.4c0.6-3,5.1-4.6,5.1-4.6c10-1,11.8-0.4,14.5-4.7c7.2-11.7-2-17-2-19.5 C265.7,75.4,266.2,73.1,269.4,68.3z"/><path style="fill:#D3C624;" d="M260.9,76.6c-0.2-0.6-0.7-1.1-1.4-1.2l-6.8-1l-3.1-6.2c-0.6-1.2-2.5-1.2-3.1,0l-3.1,6.2l-6.8,1 c-0.7,0.1-1.2,0.6-1.4,1.2s0,1.3,0.4,1.8l4.9,4.8l-1.2,6.8c-0.1,0.7,0.2,1.3,0.7,1.7c0.5,0.4,1.3,0.4,1.8,0.1l6.1-3.2l6.1,3.2 c0.3,0.1,0.5,0.2,0.8,0.2c0.4,0,0.7-0.1,1-0.3c0.5-0.4,0.8-1.1,0.7-1.7l-1.2-6.8l4.9-4.8C260.9,77.9,261.1,77.2,260.9,76.6z"/><g><path style="fill:#FFFFFF;" d="M311.5,337.4L311.5,337.4L311.5,337.4L311.5,337.4z"/><path style="fill:#FFFFFF;" d="M298.5,335.9L298.5,335.9L298.5,335.9z"/></g></svg>`,
  'default': `<svg xmlns="http://www.w3.org/2000/svg' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' fill='#9e9e9e' stroke='#fff' stroke-width='1.5'/></svg>`
};

const ALIAS_MAP = {
  city:'Kabupaten / Kota', street:'Jalan', 
  Nm_Ruas:'Nama Ruas Jalan', nm_ruas:'Nama Ruas Jalan', Status:'Status Jalan', Propinsi:'Provinsi',
  Kab_Kot:'Kabupaten / Kota', SkorBS:'Skor Black Spot', Ket:'Keterangan',
  Coverage:'Keterangan Blindspot', tanggal:'Tanggal', Wilayah:'Wilayah',
  level:'Level Perlambatan', speedKMH:'Kecepatan', Nama:'Nama',
  Keterangan:'Keterangan', nama_cctv:'Nama CCTV', status:'Status', ruas: 'Nama Ruas Jalan', kode_gt: "Kode Gerbang Tol", 
  sumber:'Sumber', nama_jalan:'Nama Jalan', risk_prediction:'Risiko Kecelakaan',
  nama_prop:'Nama Provinsi', nama_kab:'Nama Kabupaten / Kota', nama_kec:'Nama Kecamatan',
  jumlah_pen: 'Jumlah Penduduk', luas: 'Luas (ha)', kepadatan: 'Kepadatan (Jiwa/ha)', jumlah_laka: 'Kejadian Kecelakaan', korban_l: 'Korban Laki-Laki', korban_p: 'Korban Perempuan', 
  jumlah_md: 'Meninggal Dunia', jumlah_lb: 'Luka Berat', jumlah_lr: 'Luka Ringan', jumlah_lokal: 'Jumlah Laka Orang Lokal', persen_lokal:'Persentase Laka Orang Lokal', 
  speed_limit: 'Batas Kecepatan (km/jam)', tipe_zona: 'Tipe Zona Geofencing', nama_zona: 'Zona Geofencing', keterangan_status: 'Keterangan Zona Geofencing', geofence_limit: 'Batas Kecepatan (km/jam)',
  jam_mulai: 'Waktu Mulai Berlaku Batas Kecepatan', jam_selesai: 'Waktu Selesai Berlaku Batas Kecepatan', 
  vehicleName: 'Nama Kendaraan', nopol: 'Nomor Polisi', vehicleGroup: "Grup Kendaraan", merkName: 'Merk Kendaraan', tipeName: 'Tipe Kendaraan', arah_cctv: 'Arah Pantauan', radius: 'Jarak Pandang',
  keterangan:'Keterangan', no_laka:'Nomor Kecelakaan', tanggal_kejadian:'Tanggal Kejadian', keterangan_kerugian:'Keterangan Kerugian', last_update:'Data Terakhir Diperbarui'
};

const HOST_CCTV = Object.freeze({
    bali: "https://stream.elingbali.com",
    solo: "https://stream.elingsolo.com",
    yogya: "https://stream.elingjogja.com",
    bandung: "https://stream.elingbandung.com",
    medan: "https://stream.elingmedan.com",
});

const AUTH_CCTV = "Basic " + btoa("viewer:password123");
let pc = null;

function mapSatwilToRegion(satwilName) {
    if (!satwilName) return null;
    const s = satwilName.toLowerCase();
    if (s.includes('bali')) return 'bali';
    if (s.includes('surakarta') || s.includes('solo')) return 'solo';
    if (s.includes('yogyakarta') || s.includes('jogja')) return 'yogya';
    if (s.includes('bandung')) return 'bandung';
    if (s.includes('medan')) return 'medan';
    return null;
}

function getHost(region) {
    return HOST_CCTV[region?.toLowerCase()] || null;
}

async function loadCamera(id, region) {
    cleanupPC();

    const host = getHost(region);
    const videoEl = document.getElementById("video-" + id);

    if (!host || !videoEl) {
        console.warn("Host/Video Element missing");
        return; 
    }

    videoEl.parentElement.style.background = '#000';
    videoEl.style.opacity = '0.5';

    const WHEP_URL = `${host}/transcode/${id}/whep`;
    const TRIGGER_URL = `${host}/resolve-path`;
    const INTERNAL_PATH = "http://localhost:15000/transcode/" + id;

    try {
        console.log(`[CCTV] Menyiapkan ${region} (ID: ${id})...`);
        
        try {
            const triggerResp = await fetch(TRIGGER_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    path: INTERNAL_PATH,
                    quality: "hd",
                }),
            });
            
            if (!triggerResp.ok) {
                console.warn(`[CCTV] Trigger ditolak (${triggerResp.status}). Mencoba koneksi langsung...`);
            } else {
                console.log("[CCTV] Trigger Server Sukses.");
            }
        } catch (ign) {
            console.warn("[CCTV] Gagal akses trigger, lanjut ke WHEP.");
        }

        console.log("[CCTV] Memulai handshake WebRTC...");
        await playWHEP(WHEP_URL, videoEl);
        
        videoEl.style.opacity = '1';
        console.log("[CCTV] Koneksi Terbentuk!");

    } catch (e) {
        console.error("Gagal memuat CCTV:", e);
        if (e.message.includes("WHEP")) {
             videoEl.parentElement.innerHTML = `<div style="color:salmon;text-align:center;padding:20px;font-size:12px;">Stream Offline / Tidak Dapat Dijangkau</div>`;
        }
    }
}

async function waitCameraReady(url) {
    try {
        await fetch(url, {
            method: "OPTIONS",
            headers: { Authorization: AUTH_CCTV },
        });
    } catch (e) {}
}

async function playWHEP(endpoint, videoEl) {
    const ice = await getICE(endpoint);
    pc = new RTCPeerConnection({ iceServers: ice });

    pc.oniceconnectionstatechange = () => {
        const state = pc.iceConnectionState;
        console.log(`[CCTV] Status Koneksi: ${state}`);
        if (state === 'failed' || state === 'disconnected') {
             console.warn("[CCTV] Koneksi terputus/gagal. Cek firewall atau stream server.");
        }
    };

    const stream = new MediaStream();
    videoEl.srcObject = stream;

    videoEl.play().catch(e => console.log("Autoplay prevent:", e));

    pc.ontrack = (ev) => {
        console.log("[CCTV] Data Video Diterima!");
        stream.addTrack(ev.track);
    };

    pc.addTransceiver("video", { direction: "recvonly" });

    const offer = await pc.createOffer();
    await pc.setLocalDescription(offer);

    await new Promise(resolve => {
        if (pc.iceGatheringState === "complete") resolve();
        else {
            const check = () => {
                if (pc.iceGatheringState === "complete") {
                    pc.removeEventListener("icegatheringstatechange", check);
                    resolve();
                }
            }
            pc.addEventListener("icegatheringstatechange", check);
            setTimeout(resolve, 1000); 
        }
    });

    const resp = await fetch(endpoint, {
        method: "POST",
        headers: {
            "Content-Type": "application/sdp",
            Authorization: AUTH_CCTV,
        },
        body: pc.localDescription.sdp,
    });

    if (!resp.ok) {
        throw new Error(`WHEP Handshake Gagal: ${resp.status}`);
    }

    const ansSDP = await resp.text();
    if (!ansSDP || ansSDP.trim() === "") {
        throw new Error("Server mengirim SDP kosong (Stream mungkin mati)");
    }

    await pc.setRemoteDescription({ type: "answer", sdp: ansSDP });
}

function cleanupPC() {
    if (!pc) return;
    try { pc.getSenders().forEach((s) => s.track && s.track.stop()); } catch (e) {}
    try { pc.close(); } catch (e) {}
    pc = null;
}

async function getICE(endpoint) {
    const defaultIce = [{ urls: "stun:stun.l.google.com:19302" }];
    try {
        const r = await fetch(endpoint, { method: "OPTIONS", headers: { Authorization: AUTH_CCTV } });
        if (!r.ok) return defaultIce;
        const link = r.headers.get("Link");
        return link ? [{ urls: "stun:stun.l.google.com:19302" }] : defaultIce;
    } catch (e) {
        return defaultIce;
    }
}

let map;
let wilayahData;
let selectedPolygon = null;
let administrasiColorMap = {};
let popup = new maplibregl.Popup({ 
    closeButton: true, 
    closeOnClick: true, 
    offset: 25,
    anchor: 'bottom'
});
let troublespotChart, cctvChart;
let highlightMarker = null;
let riskExclamationMarkers = [];
let isExclamationAnimationRunning = false;
let exclamationAnimationStartTime = 0;
let isRiskAnimationRunning = false;
let isLevel4AnimationRunning = false;
let level4AnimationStartTime = 0;
let isAlertMuted = false;
let riskAnimationStartTime = 0;
let filteredLayerData = {};
let isPatrolAlertMinimized = false;
let lastPatrolAlertState = "";
let timeSliderData = {
    allFeatures: { blackspot: [], blacklink: [], blackarea: [] },
    uniqueDates: [],
    slider: null,
    labelStart: null,
    labelEnd: null,
    tooltip: null,
    panel: null
};

console.log('[DEBUG] sample blackspot tanggal (RAW vs NORMALIZED)');
const samp = (filteredLayerData.blackspot?.features || []).slice(0, 5);
for (const f of samp) {
  const raw = f.properties?.tanggal || f.properties?.tgl || f.properties?.date || f.properties?.datetime;
  const parsed = parseLocalDateLoose(raw);
  console.log({ raw, normalized: parsed && toYMD(parsed) });
}

console.log('[DEBUG] sample data_kecelakaan (RAW vs NORMALIZED)');
const samp2 = (filteredLayerData?.data_kecelakaan?.features || []).slice(0, 5);
for (const f of samp2) {
  const raw =
    f.properties?.tanggal_kejadian ??
    f.properties?.tanggal ??
    f.properties?.tgl_kejadian ??
    f.properties?.tgl ??
    f.properties?.date ??
    f.properties?.waktu ??
    f.properties?.datetime;
  const parsed = parseLocalDateLoose(raw);
  console.log({ raw, normalized: parsed && toYMD(parsed) });
}

function initTimeSlider() {
  let slider = document.getElementById('time-slider');
  let toggleBtn = document.getElementById('time-slider-toggle-btn');
  let minimizedBtn = document.getElementById('time-slider-minimized-btn');
  const labelStart = document.getElementById('slider-label-start');
  const labelEnd   = document.getElementById('slider-label-end');
  const tooltip    = document.getElementById('slider-tooltip');
  const panel      = document.getElementById('time-slider-panel');
  const wrapper    = document.getElementById('time-slider-wrapper');

  if (!slider || !toggleBtn || !minimizedBtn || !labelStart || !labelEnd || !tooltip || !panel || !wrapper) {
    return;
  }

  wrapper.style.display = 'none';
  panel.style.display = 'none';
  minimizedBtn.style.display = 'none';

  const features = [
    ...(timeSliderData.allFeatures.blackspot || []),
    ...(timeSliderData.allFeatures.blacklink || []),
    ...(timeSliderData.allFeatures.blackarea || [])
  ];

  if (!features.length) {
    wrapper.style.display = 'none';
    updateDashboard(null);
    return;
  }

  const uniqueYMD = [...new Set(
    features.map(f => (f.properties?.tanggal || '').slice(0,10)).filter(Boolean)
  )].sort();

  timeSliderData.uniqueDates = uniqueYMD.map(ymd => safeParseYMDLocal(ymd));

  if (timeSliderData.uniqueDates.length < 2) {
    wrapper.style.display = 'none';
    updateDashboard(timeSliderData.uniqueDates.length ? timeSliderData.uniqueDates[0] : null);
    return;
  }

  const sliderClone = slider.cloneNode(true);
  slider.parentNode.replaceChild(sliderClone, slider);
  slider = sliderClone;

  const toggleClone = toggleBtn.cloneNode(true);
  toggleBtn.parentNode.replaceChild(toggleClone, toggleBtn);
  toggleBtn = toggleClone;

  const miniClone = minimizedBtn.cloneNode(true);
  minimizedBtn.parentNode.replaceChild(miniClone, minimizedBtn);
  minimizedBtn = miniClone;

  const fmt = { day: 'numeric', month: 'short', year: 'numeric' };
  const firstDate = timeSliderData.uniqueDates[0];
  const lastDate  = timeSliderData.uniqueDates[timeSliderData.uniqueDates.length - 1];

  slider.min = 0;
  slider.max = timeSliderData.uniqueDates.length - 1;
  slider.value = timeSliderData.uniqueDates.length - 1;

  labelStart.textContent = firstDate.toLocaleDateString('id-ID', fmt);
  labelEnd.textContent   = lastDate.toLocaleDateString('id-ID', fmt);

  slider.addEventListener('input', () => {
    const idx = parseInt(slider.value, 10);
    const selectedDate = timeSliderData.uniqueDates[idx];
    if (selectedDate) {
      filterLayersByDate(selectedDate);
      updateDashboard(selectedDate);
    }
  });

  const dateOptions = { day: 'numeric', month: 'short', year: 'numeric' };

  slider.addEventListener('mousemove', (e) => {
    const rect = slider.getBoundingClientRect();
    const percent = Math.min(Math.max((e.clientX - rect.left) / rect.width, 0), 1);
    const idx = Math.round(percent * (timeSliderData.uniqueDates.length - 1));
    const hoverDate = timeSliderData.uniqueDates[idx];
    if (hoverDate) {
      tooltip.textContent = hoverDate.toLocaleDateString('id-ID', dateOptions);
      tooltip.style.left = `${percent * 100}%`;
      tooltip.style.opacity = 1;
    }
  });

  slider.addEventListener('mouseleave', () => { tooltip.style.opacity = 0; });

  toggleBtn.addEventListener('click', () => {
    panel.style.display = 'none';
    minimizedBtn.style.display = 'block';
  });

  minimizedBtn.addEventListener('click', () => {
    panel.style.display = 'flex';
    minimizedBtn.style.display = 'none';
  });

  wrapper.style.display = 'block';
  panel.style.display = 'none';
  minimizedBtn.style.display = 'block';
  filterLayersByDate(lastDate);
  updateDashboard(lastDate);
}

function filterLayersByDate(selectedDate) {
  const selYMD = toYMD(new Date(
    selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate()
  ));

  ['blackspot', 'blacklink', 'blackarea'].forEach(layerKey => {
    const src = map.getSource(layerKey);
    if (!src || !timeSliderData.allFeatures[layerKey]) return;

    const filteredFeatures = timeSliderData.allFeatures[layerKey].filter(f => {
      const d = parseLocalDateLoose(f.properties?.tanggal);
      return d && toYMD(d) === selYMD;
    });

    src.setData({ type:'FeatureCollection', features: filteredFeatures });
  });
}

let splashMap;
const START_SATWIL = ['Polda Bali', 'Polrestabes Bandung', 'Polresta Surakarta', 'Polda DI Yogyakarta', 'Polrestabes Medan'];

let splashChartInstance = null;

function toYYYYMMDD(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function safeParseYMDLocal(s) {
  const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(s).trim());
  if (!m) return null;
  return new Date(+m[1], +m[2] - 1, +m[3], 0, 0, 0, 0);
}

const Z_DATE_FIX_DAYS = 1;

function addDays(d, n) {
  const c = new Date(d.getFullYear(), d.getMonth(), d.getDate());
  c.setDate(c.getDate() + n);
  return c;
}

function toLocalDateYMD(d) {
  return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

function parseIsoUtcToLocalDate(isoStr) {
  const d = new Date(isoStr);
  if (isNaN(d)) return null;
  return toLocalDateYMD(new Date(
    d.getUTCFullYear(),
    d.getUTCMonth(),
    d.getUTCDate()
  ));
}

function parseLocalDateLoose(raw) {
  if (!raw) return null;
  if (raw instanceof Date) return toLocalDateYMD(raw);

  const s = String(raw).trim();

  if (/[T ]\d{2}:\d{2}(:\d{2}(\.\d{1,3})?)?(Z|[+\-]\d{2}:\d{2})$/.test(s) || /Z$/.test(s)) {
    const d = parseIsoUtcToLocalDate(s);
    if (d) return d;
  }

  const mYmd = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
  if (mYmd) {
    const y = +mYmd[1], m = +mYmd[2] - 1, d = +mYmd[3];
    return new Date(y, m, d);
  }

  const mDmy = s.match(/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/);
  if (mDmy) {
    const d = +mDmy[1], m = +mDmy[2] - 1, y = +mDmy[3];
    return new Date(y, m, d);
  }

  const d = new Date(s);
  if (!isNaN(d)) return toLocalDateYMD(d);

  return null;
}

function toYMD(d) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

function getLast7DaysRangeYMD() {
  const today = new Date();
  const end = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  const start = new Date(end.getFullYear(), end.getMonth(), end.getDate() - 6);

  const pad = n => String(n).padStart(2,'0');
  const toYMD = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

  return { startYMD: toYMD(start), endYMD: toYMD(end) };
}

async function fetchAccidents7D() {
  console.log('Fetching data kecelakaan untuk splash chart...');
  
  return await fetchGeoJSONLayer('data_kecelakaan'); 
}

function buildDailyCounts(accidentFeatures, startLocal, endLocal) {
  const counts = new Map();

  for (const f of (accidentFeatures || [])) {
    const p = f.properties || {};
    const raw =
      p.tanggal_kejadian ?? p.tanggal ?? p.tgl_kejadian ?? p.tgl ??
      p.date ?? p.waktu ?? p.datetime;

    const d = parseLocalDateLoose(raw);
    if (!d) continue;

    if (d >= startLocal && d <= endLocal) {
      const key = toYMD(d);
      counts.set(key, (counts.get(key) || 0) + 1);
    }
  }

  const out = [];
  for (let cur = new Date(startLocal); cur <= endLocal; cur.setDate(cur.getDate() + 1)) {
    const key = toYMD(cur);
    out.push({ ymd: key, n: counts.get(key) || 0 });
  }
  return out;
}

async function createSplashChart(preloadedAccidentData) {
    try {
        const accidentData = preloadedAccidentData ?? await fetchAllWFS(URLS.data_kecelakaan);
        normalizeProperties('data_kecelakaan', accidentData);

        const container = document.getElementById('splash-chart-container');
        if (container) {
          container.innerHTML = '<canvas id="splash-troublespot-chart"></canvas>';
        }
        
        const today = new Date();
        const endLocal = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59, 999);
        const startLocal = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 6, 0, 0, 0, 0);

        const dailyCounts = new Map();
        for (let d = new Date(startLocal); d <= endLocal; d.setDate(d.getDate() + 1)) {
          dailyCounts.set(toYMD(d), 0);
        }

        accidentData.features.forEach(f => {
          const raw =
            f.properties?.tanggal_kejadian ??
            f.properties?.tanggal ??
            f.properties?.tgl_kejadian ??
            f.properties?.tgl ??
            f.properties?.date ??
            f.properties?.waktu ??
            f.properties?.datetime;

          const d = parseLocalDateLoose(raw);
          if (!d) return;

          const ymd = toYMD(d);
          if (dailyCounts.has(ymd)) {
            dailyCounts.set(ymd, dailyCounts.get(ymd) + 1);
          }
        });
        console.table(Array.from(dailyCounts.entries()).map(([ymd, n]) => ({ ymd, n })));
        const labels = Array.from(dailyCounts.keys()).map(dateStr => {
            const [year, month, day] = dateStr.split('-');
            const localDate = new Date(year, month - 1, day);
            return localDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        });
        const dataValues = Array.from(dailyCounts.values());
        
        const ctx = document.getElementById('splash-troublespot-chart')?.getContext('2d');
        if (!ctx) return;
        if (splashChartInstance) {
            splashChartInstance.destroy();
        }

        splashChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Kecelakaan',
                    data: dataValues,
                    backgroundColor: 'rgba(255, 193, 7, 0.2)',
                    borderColor: '#ffc107',
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#ffc107',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Tren Kejadian Kecelakan dalam 7 Hari Terakhir',
                        color: '#fff',
                        font: { size: 14 }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: 'rgba(255, 255, 255, 0.7)' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { 
                            color: 'rgba(255, 255, 255, 0.7)',
                            precision: 0,
                            stepSize: 1
                        },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });

        const now = new Date();
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        const day = now.getDate();
        const month = monthNames[now.getMonth()];
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const timestampText = `Last Update: ${day} ${month} ${year} Pukul ${hours}:${minutes}`;

        const updateElement = document.getElementById('splash-last-update');
        if (updateElement) {
            updateElement.textContent = timestampText;
        }
    } catch (error) {
        console.error("Gagal membuat splash chart:", error);
        const container = document.getElementById('splash-chart-container');
        if(container) container.innerHTML = '<p style="color: #ff8a80;">Gagal memuat data grafik.</p>';
    }
}

function startAppWithSatwil(feature) {
  selectedPolygon = feature;
  const sat = feature?.properties?.Satwil ?? feature?.properties?.satwil;

  selectedSatwilName = sat || null;

  if (sat) {
      const newHash = '#' + sanitizeForURL(sat);
      if (window.location.hash !== newHash) {
          history.pushState({ satwil: sat }, document.title, window.location.pathname + window.location.search + newHash);
      }
    }

  try { if (splashMap) { splashMap.remove(); splashMap = null; } } catch {}
  showOverlay();
  document.getElementById('filter-screen').style.display = 'none';

  const dlBtn = document.getElementById('download-control-container');
  if (dlBtn) dlBtn.style.display = 'flex';

  const panelSel = document.getElementById('wilayah-select');
  if (panelSel && sat) panelSel.value = sat;

  if (!map) {
    initMap(feature);
  } else {
    updateForSatwil(feature).finally(hideOverlay);
  }
}

function createSplashCallout(label, coord, feature) {
  const stacked = label;

  const html = `<div class="splash-callout-content" tabindex="0" role="button">${stacked}</div>`;

  const popup = new maplibregl.Popup({
      closeButton: false,
      closeOnClick: false,
      anchor: 'bottom',
      offset: 10
    })
    .setLngLat(coord)
    .setHTML(html)
    .addTo(splashMap);

  const content = popup.getElement().querySelector('.maplibregl-popup-content');
  content.addEventListener('click', () => startAppWithSatwil(feature));
  content.addEventListener('keydown', (ev) => {
    if (ev.key === 'Enter' || ev.key === ' ') startAppWithSatwil(feature);
  });
}

function initSplashMap() {
  splashMap = new maplibregl.Map({
    container: 'splash-map',
    style: {
      version: 8,
      sources: {
        'base-raster': {
          type: 'raster',
          tiles: ['https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}'],
          tileSize: 256
        }
      },
      layers: [{ id:'base-raster-layer', type:'raster', source:'base-raster' }]
    },
    center: [118.0149, -2.5489],
    zoom: 4.2, 
    attributionControl: false,
    renderWorldCopies: false    
  });
  splashMap.setMaxBounds([[94.5, -12.5], [142.0, 8.0]]);
  splashMap.on('load', () => {
    const targets = [];
    const targetNames = new Set(START_SATWIL.map(s => s.toLowerCase()));

    for (const f of (wilayahData?.features || [])) {
      const sat = (f.properties?.Satwil ?? f.properties?.satwil ?? '').toString();
      if (!sat) continue;
      if (targetNames.has(sat.toLowerCase())) {
        let cen;
        try {
          cen = turf.centerOfMass(f).geometry.coordinates;
        } catch(e) {
          try { cen = turf.centroid(f).geometry.coordinates; }
          catch { cen = null; }
        }
        try {
          if (!cen || !turf.booleanPointInPolygon(turf.point(cen), f)) {
            const bb = turf.bbox(f);
            cen = [(bb[0]+bb[2])/2, (bb[1]+bb[3])/2];
          }
        } catch {}
        createSplashCallout(sat, cen, f);
        targets.push(cen);
      }
    }

    if (targets.length) {
      const fc = {
        type:'FeatureCollection',
        features: targets.map(c => ({ type:'Feature', geometry:{ type:'Point', coordinates:c } }))
      };
      splashMap.fitBounds( turf.bbox(fc), { padding: { top: 80, right: 80, bottom: 140, left: 80 }, maxZoom: 5.8 });
    }
  });
}

const overlay = document.getElementById('loading-overlay');
function showOverlay() { if(overlay) overlay.style.display = 'flex'; }
function hideOverlay() { if(overlay) overlay.style.display = 'none'; }

document.addEventListener('DOMContentLoaded', () => {
    const hintButton = document.getElementById('btn-show-hint');
    const hintText = document.getElementById('hint-text-overlay');

    if (hintButton && hintText) {
        hintButton.addEventListener('click', () => {
            hintText.classList.add('visible');

            const splashMapContainer = document.getElementById('splash-map');
            if (splashMapContainer) {
                const popups = splashMapContainer.querySelectorAll('.maplibregl-popup');
                
                popups.forEach(popup => {
                    popup.classList.add('highlight-pin');
                });

                setTimeout(() => {
                    hintText.classList.remove('visible');
                    popups.forEach(popup => {
                        popup.classList.remove('highlight-pin');
                    });
                }, 3000);
            }
        });
    }
});

const splashCont = document.getElementById('splash-chart-container');
if (splashCont) {
  splashCont.innerHTML = `
    <div style="height:100%;display:flex;align-items:center;justify-content:center;color:#fff;opacity:.75">
      Memuat grafik 7 hari…
    </div>`;
}

Promise.all([
  fetchGeoJSONLayer('wilayah'),
  fetchAccidents7D()
]).then(([wil, kec7d]) => {
  wilayahData = wil;

  const panelSel = document.getElementById('wilayah-select');
  const seen = new Set();
  for (const f of wil.features || []) {
    const sat = f.properties?.Satwil ?? f.properties?.satwil;
    if (!sat || seen.has(sat)) continue;
    seen.add(sat);
    const o = document.createElement('option');
    o.value = sat; o.textContent = sat;
    panelSel.appendChild(o);
  }

  createSplashChart(kec7d);

  const hash = window.location.hash.substring(1);
    let featureFromHash = null;

    if (hash) {
      featureFromHash = (wil.features || []).find(f => {
          const sat = f.properties?.Satwil ?? f.properties?.satwil;
          return sat && sanitizeForURL(sat) === hash;
      });
    }

    if (featureFromHash) {
      console.log('Satwil ditemukan di URL, melewati splash:', featureFromHash.properties.Satwil);
      startAppWithSatwil(featureFromHash);
    } else {
      console.log('Tidak ada Satwil valid di URL, tampilkan splash screen.');
      initSplashMap(); 
    }
  
}).catch(err => {
  console.error('Gagal init paralel wilayah + kecelakaan', err);
  alert('Gagal memuat wilayah atau data kecelakaan');
});

window.addEventListener('hashchange', () => {
  location.reload();
});

document.getElementById('btn-ganti').addEventListener('click', () => {
  const v = document.getElementById('wilayah-select').value;
  if (!v) return alert('Pilih Satwil');
  
  selectedSatwilName = v;

  const newHash = '#' + sanitizeForURL(v);
  if (window.location.hash !== newHash) {
      history.pushState({ satwil: v }, document.title, window.location.pathname + window.location.search + newHash);
  }

  switchSatwilView(v);
});

function normalizeProperties(layerKey, fc) {
  if (!fc || !Array.isArray(fc.features)) return fc;
  for (const f of fc.features) {
    const p = f.properties || {};
    if (['blackspot','blacklink','blackarea'].includes(layerKey)) {
      const d = parseLocalDateLoose(p.tanggal || p.tgl || p.date || p.tanggal_kejadian || p.waktu || p.datetime);
      p.tanggal = d ? toYMD(d) : null;
    }
    if (layerKey === 'data_kecelakaan') {
      const d = parseLocalDateLoose(p.tanggal_kejadian || p.tanggal || p.tgl || p.date || p.waktu || p.datetime);
      p.tanggal_kejadian = d ? toYMD(d) : null;
    }
    if (layerKey === 'cctv') {
      const raw = (p.status || p.STATUS || p.stat || '').toString().toLowerCase();
      if (['on','hidup','1','aktif'].includes(raw)) p.status = 'Hidup';
      else if (['off','mati','0','nonaktif'].includes(raw)) p.status = 'Mati';
      else p.status = 'Unknown';
    }
    if (layerKey === 'objek_vital') {
      p.Keterangan = p.Keterangan || p.keterangan || p.kategori || p.category || 'Lainnya';
    }
    if (layerKey === 'data_kecelakaan') {
       p.bobot = Number(p.bobot || 0);
       const d = parseLocalDateLoose(p.tanggal_kejadian || p.tanggal || p.tgl_kejadian);
       p.tanggal_kejadian = d ? toYMD(d) : null;
    }
    if (layerKey === 'blackspot') {
      p.SkorBS = Number(p.SkorBS ?? p.skor ?? p.score ?? 0);
      p.Nama   = p.Nama || p.Nm_Ruas || p.nama || p.nama_ruas || 'Black Spot';
    }
    if (layerKey === 'speedlimit') {
      p.speed_limit = Number(p.speed_limit || p.speed || 0);
    }
    if (layerKey === 'geofencing') {
      p.tipe_zona = p.tipe_zona || p.zone_type || 'Lainnya';
    }
    f.properties = p;
  }
  return fc;
}

function initMap(polygon){
  map = new maplibregl.Map({
    container:'map',
    style:{
      version:8,
      glyphs:"https://demotiles.maplibre.org/font/{fontstack}/{range}.pbf",
      sources:{ 'base-raster':{ type:'raster', tiles:basemapTiles.osm.tiles, tileSize:basemapTiles.osm.tileSize } },
      layers:[ { id:'base-raster-layer', type:'raster', source:'base-raster' } ]
    },
    center:[118.0149, -2.5489], zoom:5, attributionControl:false
  });

  map.on('load', async ()=>{
    ['main-header','filter-panel','bottom-panel-wrapper','controls-bl','dashboard-panel','search-routing-panel'].forEach(id=>document.getElementById(id).style.display='flex');
    map.addControl(new maplibregl.AttributionControl({ compact:true }), 'bottom-right');
    const scl = new maplibregl.ScaleControl({ maxWidth:120, unit:'metric' });
    document.getElementById('scale-box').appendChild(scl.onAdd(map));
    document.getElementById('zoom-in').addEventListener('click', ()=>map.zoomIn());
    document.getElementById('zoom-out').addEventListener('click', ()=>map.zoomOut());
    document.getElementById('compass').addEventListener('click', ()=>map.easeTo({ bearing:0, pitch:0 }));
    const updateCompass = () => document.getElementById('compass-arrow').style.transform = `rotate(${-map.getBearing()}deg)`;
    map.on('rotate', updateCompass); updateCompass();

    document.querySelectorAll('.dash-item-header').forEach(header => {
      header.addEventListener('click', () => {
        const container = header.closest('.dash-item-container');
        const button = header.querySelector('.dash-item-toggle');
        
        container.classList.toggle('minimized');
        
        const isMinimized = container.classList.contains('minimized');
        button.textContent = isMinimized ? '+' : '−';
      });
    });

    const myLocationBtn = document.getElementById('my-location-btn');
    let myLocationMarker = null;

    myLocationBtn.addEventListener('click', () => {
      if (!navigator.geolocation) {
        alert("Geolocation tidak didukung oleh browser Anda.");
        return;
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { longitude, latitude } = position.coords;
          
          if (myLocationMarker) {
            myLocationMarker.remove();
          }
          map.flyTo({
            center: [longitude, latitude],
            zoom: 16, 
            speed: 1.5
          });

          myLocationMarker = new maplibregl.Marker({ color: '#1976d2' })
            .setLngLat([longitude, latitude])
            .addTo(map);
        },
        (error) => {
          switch (error.code) {
            case error.PERMISSION_DENIED:
              alert("Anda menolak permintaan untuk Geolocation.");
              break;
            case error.POSITION_UNAVAILABLE:
              alert("Informasi lokasi tidak tersedia.");
              break;
            case error.TIMEOUT:
              alert("Permintaan untuk mendapatkan lokasi pengguna timed out.");
              break;
            default:
              alert("Terjadi kesalahan yang tidak diketahui.");
              break;
          }
        }
      );
    });

    initSearchAndRouting();
    initMeasureTools(map);
    
    await loadIcons();
    addOrUpdateSatwilLayer(polygon);
    await initAllLayers();
    if (selectedSatwilName) {
        switchSatwilView(selectedSatwilName);
    }
    initTimeSlider();
    renderLegend();
    updateDashboard();
    window.updateSearchIndex();
    
    hideOverlay();

    try{ map.fitBounds(turf.bbox(polygon), { padding:50, maxZoom:14 }); }catch(e){}

    const basemapBtns = document.querySelectorAll('#basemap-tabs .tab-btn');
    basemapBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        basemapBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        switchBasemap(btn.dataset.value);
      });
    });

    const layerBtns = document.querySelectorAll('#layer-tabs .tab-btn');
    layerBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        btn.classList.toggle('active');
        const isActive = btn.classList.contains('active');
        const layerName = btn.dataset.layer;

        applyVisibility(layerName);
        renderLegend();
      });
    });

    const btnToggleAll = document.getElementById('btn-toggle-all-layers');
    if (btnToggleAll) {
      btnToggleAll.addEventListener('click', () => {
        const allButtons = Array.from(document.querySelectorAll('#layer-tabs .tab-btn'));
        const isAnyActive = allButtons.some(btn => btn.classList.contains('active'));
        const shouldTurnOn = !isAnyActive;

        allButtons.forEach(btn => {
          const isActive = btn.classList.contains('active');
          if ((shouldTurnOn && !isActive) || (!shouldTurnOn && isActive)) {
            btn.click(); 
          }
        });

        btnToggleAll.textContent = shouldTurnOn ? "Matikan Semua Layer" : "Hidupkan Semua Layer";
        btnToggleAll.style.backgroundColor = shouldTurnOn ? "#e0e0e0" : "#ffc107"; 
      });
    }

      map.on('click', (e)=>{
      if (popup && popup.isOpen()) {
          popup.remove();
          cleanupPC();
      }

      const features = map.queryRenderedFeatures(e.point);
      if (!features.length) return;

      const topFeature = features[0];
      const layerId = topFeature.layer.id;

      if (topFeature.properties?.cluster){
        const clusterId = topFeature.properties.cluster_id;
        const sourceId = topFeature.layer.source;
        const source = map.getSource(sourceId);
        if (source && typeof source.getClusterExpansionZoom === 'function'){
          source.getClusterExpansionZoom(clusterId, (err, zoom)=>{
            if (err) return;
            map.easeTo({ center: topFeature.geometry.coordinates, zoom: zoom + 1 });
          });
        }
        return;
      }
      const titleMap = {
        troublespot:'Titik Gangguan Lalu Lintas', blackspot:'Black Spot', traffic_point:'Titik Perlambatan', cctv:'CCTV',
        objek_vital:'Objek Vital', gerbang_tol:'Gerbang Tol', data_kecelakaan:'Data Kecelakaan', 
        rescue: 'Kendaraan Rescue', patrol: 'Petugas Patroli',
        prediksi_risiko:'Prediksi Risiko Kecelakaan',
        administrasi:'Wilayah Administrasi', traffic:'Perlambatan',
        blacklink:'Black Link', blackarea:'Black Area', speedlimit: 'Batas Kecepatan',
        geofencing: 'Geofencing Area'
      };
      const prefix = Object.keys(titleMap).find(p => layerId.startsWith(p));
      if (!prefix) return;
      let title = titleMap[prefix] || 'Informasi';
      if (prefix === 'objek_vital'){
        const ovLabel = OV_META[ovKeyFromKeterangan(topFeature.properties)]?.label || 'Lainnya';
        title = `Objek Vital • ${ovLabel}`;
      }
      let bodyHtml = '';
      for (const key in ALIAS_MAP) {
        if (topFeature.properties.hasOwnProperty(key)) {
          let val = topFeature.properties[key];
          let labelStyle = ''; 
          let valueStyle = '';
          if (key === 'level') {
            const numVal = parseInt(val, 10);
            if (!isNaN(numVal) && numVal >= 3) {
              labelStyle = 'color: #d32f2f;'; 
              valueStyle = 'color: #d32f2f; font-weight: 800;'; 
            }
          }
          bodyHtml += `<div><strong style="${labelStyle}">${ALIAS_MAP[key]}:</strong> <span style="${valueStyle}">${val}</span></div>`;
        }
      }
      if (bodyHtml === '') bodyHtml = '<div>Tidak ada informasi detail.</div>';
      const popupHtml = `<div class="popup-header">${title}</div><div class="popup-body">${bodyHtml}</div>`;
      popup.setLngLat(e.lngLat).setHTML(popupHtml).addTo(map);
      showFeaturePopup(map, topFeature, e.lngLat, layerId);
    });

    const panelMinimizeBtn = document.getElementById('panel-minimize-btn');
    const panelBody = document.getElementById('panel-body');
    const bottomPanel = document.getElementById('bottom-panel');
    const toggleMinimize = (panel, body, btn) => {
      const isMinimized = panel.classList.toggle('minimized');
      body.style.display = isMinimized ? 'none' : 'block';
      btn.textContent = isMinimized ? '+' : '−';
    };
    panelMinimizeBtn.addEventListener('click', ()=>toggleMinimize(bottomPanel, panelBody, panelMinimizeBtn));
    document.getElementById('panel-header').addEventListener('click', (e) => {
        if (bottomPanel.classList.contains('minimized') && e.target.closest('#panel-tabs')) {
             toggleMinimize(bottomPanel, panelBody, panelMinimizeBtn);
        }
    });

    document.querySelectorAll('.panel-tab-btn').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        if (bottomPanel.classList.contains('minimized')) {
            toggleMinimize(bottomPanel, panelBody, panelMinimizeBtn);
        }
        document.querySelectorAll('.panel-tab-btn').forEach(b=>b.classList.remove('active'));
        document.querySelectorAll('.panel-content').forEach(c=>c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.content).classList.add('active');
      });
    });

    const dashboardBody = document.getElementById('dashboard-body');
    const dashboardMinimizeBtn = document.getElementById('dashboard-minimize-btn');
    dashboardMinimizeBtn.addEventListener('click', ()=>{
      const hidden = dashboardBody.style.display === 'none';
      dashboardBody.style.display = hidden ? 'block' : 'none';
      dashboardMinimizeBtn.textContent = hidden ? '−' : '+';
    });
  });
}
async function loadIcons(){
  for (const [id, svg] of Object.entries(ICON_SVGS)){
    await new Promise((resolve, reject)=>{
      if (map.hasImage(id)) return resolve();
      
      const img = new Image(150, 150); 
      
      img.onload = () => { 
          try { 
              if (!map.hasImage(id)) map.addImage(id, img, { sdf: false }); 
              resolve(); 
          } catch(e){ reject(e); } 
      };
      img.onerror = (e) => {
          console.warn('Gagal load icon:', id); 
          resolve(); 
      };
      
      img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
    });
  }
}
function switchBasemap(key){
  if (!map || !basemapTiles[key]) return;
  const layers = map.getStyle().layers || [];
  const firstNonBaseId = layers.find(l => l.id !== 'base-raster-layer')?.id;
  if (map.getLayer('base-raster-layer')) map.removeLayer('base-raster-layer');
  if (map.getSource('base-raster')) map.removeSource('base-raster');
  map.addSource('base-raster', { type:'raster', tiles: basemapTiles[key].tiles, tileSize: basemapTiles[key].tileSize });
  if (firstNonBaseId) map.addLayer({ id:'base-raster-layer', type:'raster', source:'base-raster' }, firstNonBaseId);
  else map.addLayer({ id:'base-raster-layer', type:'raster', source:'base-raster' });
}
function addOrUpdateSatwilLayer(feature){
  const source = map.getSource('selected-satwil');
  if (source) source.setData(feature);
  else map.addSource('selected-satwil', { type:'geojson', data:feature });
}
function toPointFeatureArray(fc){
  const out = [];
  if (!fc || !fc.features) return out;
  for (const f of fc.features){
    if (!f || !f.geometry) continue;
    const g = f.geometry;
    if (g.type === 'Point') out.push(f);
    else if (g.type === 'MultiPoint'){
      for (const c of g.coordinates || []) out.push({ type:'Feature', geometry:{ type:'Point', coordinates:c }, properties:{ ...f.properties } });
    } else {
      try {
        const cen = turf.centroid(f).geometry.coordinates;
        out.push({ type:'Feature', geometry:{ type:'Point', coordinates:cen }, properties:{ ...f.properties, _from:g.type } });
      } catch(e){}
    }
  }
  return out;
}

function toLineStringFeatures(feature) {
  if (!feature || !feature.geometry) return [];
  const { type, coordinates } = feature.geometry;
  if (type === 'LineString') return [feature];
  if (type === 'MultiLineString') {
    return (coordinates || []).map(coords => ({
      type: 'Feature',
      geometry: { type: 'LineString', coordinates: coords },
      properties: { ...(feature.properties || {}) }
    }));
  }
  return [];
}

function polygonBoundaryLineStrings(polygon) {
  const boundary = turf.polygonToLine(polygon);
  const g = boundary && boundary.geometry;
  if (!g) return [];
  if (g.type === 'LineString') return [boundary];
  if (g.type === 'MultiLineString') {
    return (g.coordinates || []).map(coords => ({
      type: 'Feature',
      geometry: { type: 'LineString', coordinates: coords },
      properties: {}
    }));
  }
  return [];
}

function splitLineByBoundaries(lineLS, boundaryLines) {
  let pieces = [lineLS];
  for (const b of boundaryLines) {
    const next = [];
    for (const p of pieces) {
      try {
        const result = turf.lineSplit(p, b);
        if (result && result.features && result.features.length) {
          for (const s of result.features) {
            s.properties = { ...(p.properties || {}) };
            next.push(s);
          }
        } else {
          next.push(p);
        }
      } catch (e) {
        next.push(p);
      }
    }
    pieces = next;
  }
  return pieces;
}

function keepSegmentsInsidePolygon(pieces, polygon) {
  const kept = [];
  for (const seg of pieces) {
    try {
      const coords = seg?.geometry?.coordinates || [];
      if (coords.length < 2) continue;
      const lenKm = turf.length(seg, { units: 'kilometers' });
      const mid = turf.along(seg, Math.max(lenKm / 2, 0), { units: 'kilometers' });
      if (turf.booleanPointInPolygon(mid, polygon)) {
        seg.properties = { ...(seg.properties || {}) };
        kept.push(seg);
      }
    } catch (e) {}
  }
  return kept;
}

function chunkApproxInside(lineLS, polygon, chunkKm = 0.5) {
  const out = [];
  try {
    const chunks = turf.lineChunk(lineLS, chunkKm, { units: 'kilometers' });
    for (const ch of (chunks.features || [])) {
      try {
        const lenKm = turf.length(ch, { units: 'kilometers' });
        const mid = turf.along(ch, Math.max(lenKm / 2, 0), { units: 'kilometers' });
        if (turf.booleanPointInPolygon(mid, polygon)) {
          ch.properties = { ...(lineLS.properties || {}) };
          out.push(ch);
        }
      } catch (e) {}
    }
  } catch (e) {}
  return out;
}

function clipLineToPolygonRobust(lineFeature, polygon) {
  try {
    if (!turf.booleanIntersects(lineFeature, polygon)) return [];
  } catch {
    return [];
  }

  const boundaryLines = polygonBoundaryLineStrings(polygon);
  const lines = toLineStringFeatures(lineFeature);

  const out = [];
  for (const ls of lines) {
    try {
      const pieces = splitLineByBoundaries(ls, boundaryLines);
      let kept = keepSegmentsInsidePolygon(pieces, polygon);

      if (!kept.length) {
        try {
          if (turf.booleanWithin(ls, polygon)) {
            const clone = JSON.parse(JSON.stringify(ls));
            clone.properties = { ...(lineFeature.properties || {}) };
            kept = [clone];
          }
        } catch {}
      }

      if (!kept.length) {
        kept = chunkApproxInside(ls, polygon, 0.5); 
      }

      for (const k of kept) {
        k.properties = { ...(lineFeature.properties || {}) };
        out.push(k);
      }
    } catch (e) {
      const approx = chunkApproxInside(ls, polygon, 0.5);
      for (const k of approx) {
        k.properties = { ...(lineFeature.properties || {}) };
        out.push(k);
      }
    }
  }
  return out;
}
function filterData(layerKey, geojsonData, filterPolygon) {
  if (!geojsonData || !Array.isArray(geojsonData.features) || !filterPolygon) {
    return { type: 'FeatureCollection', features: [] };
  }

  const pointLayers = ['data_kecelakaan', 'cctv', 'objek_vital', 'traffic_point', 'troublespot', 'blackspot'];

  if (pointLayers.includes(layerKey)) {
    const points = toPointFeatureArray(geojsonData);
    const filteredPoints = points.filter(pt => {
      try { return turf.booleanPointInPolygon(pt, filterPolygon); }
      catch (e) { console.error(`Error filtering point for ${layerKey}:`, e); return false; }
    });
    return { type: 'FeatureCollection', features: filteredPoints };
  } else {
    if (layerKey === 'prediksi_risiko') {
      const out = [];
      for (const f of geojsonData.features) {
        if (!f?.geometry) continue;
        const gType = f.geometry.type;

        if (gType === 'LineString' || gType === 'MultiLineString') {
          const parts = clipLineToPolygonRobust(f, filterPolygon);
          if (parts.length) out.push(...parts);
        } else if (gType === 'Polygon' || gType === 'MultiPolygon') {
          try {
            const inter = turf.intersect(f, filterPolygon);
            if (inter) { inter.properties = { ...f.properties }; out.push(inter); }
          } catch (e) { console.error('intersect error (prediksi_risiko polygon):', e); }
        } else {
          try { if (turf.booleanIntersects(f, filterPolygon)) out.push(f); } catch {}
        }
      }

      return { type: 'FeatureCollection', features: out };
    }

    return {
      type: 'FeatureCollection',
      features: geojsonData.features.filter(f => {
        if (!f?.geometry) return false;
        try { return turf.booleanIntersects(f, filterPolygon); }
        catch (e) { console.error(`Error filtering feature for ${layerKey}:`, e); return false; }
      })
    };
  }
}
function normalizeCCTVStatus(props){
  const v = (props?.status ?? '').toString().trim().toLowerCase();
  if (v === 'hidup') return 'on';
  if (v === 'mati') return 'off';
  return 'unknown';
}
function ovKeyFromKeterangan(props){
  const v = (props?.Keterangan ?? props?.keterangan ?? '').toString().trim().toLowerCase();
  if (v === 'bandara') return 'bandara';
  if (v === 'fasilitas kesehatan') return 'faskes';
  if (v === 'fasilitas pendidikan') return 'pendidikan';
  if (v === 'kantor pemerintahan') return 'pemerintahan';
  if (v === 'kantor polisi') return 'polisi';
  if (v === 'stasiun') return 'stasiun';
  if (v === 'terminal') return 'terminal';
  if (v === 'pariwisata') return 'pariwisata';
  if (v === 'pelabuhan') return 'pelabuhan';
  if (v === 'masjid') return 'masjid';
  if (v === 'gereja') return 'gereja';
  if (v === 'vihara') return 'vihara';
  if (v === 'pura') return 'pura';
  if (v === 'klenteng') return 'klenteng';
  return 'default';
}
const OV_META = {
  bandara:{ icon:'ov-plane', color:'navy', label:'Bandara' },
  faskes:{ icon:'ov-hospital', color:'red', label:'Fasilitas Kesehatan' },
  pendidikan:{ icon:'ov-school', color:'saddlebrown', label:'Fasilitas Pendidikan' },
  pemerintahan:{ icon:'ov-government', color:'dimgray', label:'Kantor Pemerintahan' },
  polisi:{ icon:'ov-police', color:'blue', label:'Kantor Polisi' },
  stasiun:{ icon:'ov-train', color:'black', label:'Stasiun' },
  terminal:{ icon:'ov-bus', color:'darkorange', label:'Terminal' },
  pariwisata:{ icon:'ov-pariwisata', color:'#4e342e', label:'Pariwisata' },
  pelabuhan:{ icon:'ov-port', color:'#006064', label:'Pelabuhan' },
  masjid: { icon: 'ov-mosque', color: '#54fc5d', label: 'Masjid' },
  gereja: { icon: 'ov-church', color: '#6c54fc', label: 'Gereja' },
  vihara: { icon: 'ov-vihara', color: '#fcd054', label: 'Vihara' },
  pura: { icon: 'ov-temple', color: '#7c6d7b', label: 'Pura' },
  klenteng: { icon: 'ov-klenteng', color: '#da0700', label: 'Klenteng' },
  default:{ icon:'default', color:'#9e9e9e', label:'Lainnya' }
};
function setLayerInteractivity(layerId){
  map.on('mouseenter', layerId, ()=>{ map.getCanvas().style.cursor='pointer'; });
  map.on('mouseleave', layerId, ()=>{ map.getCanvas().style.cursor=''; });
}
function rescueKeyFromVehicleGroup(props) {
  const v = (props?.vehicleGroup || '').toString().trim().toUpperCase();
  if (v.includes('AMBULANCE') || v.includes('PMI') || v.includes('DINKES')) return 'ambulance';
  if (v.includes('BPBD')) return 'bpbd';
  if (v.includes('DAMKAR')) return 'damkar';
  if (v.includes('DISHUB')) return 'dishub';
  if (v.includes('KORLANTAS') || v.includes('POLRES') || v.includes('SATPOL')) return 'police';
  if (v.includes('MOTOR')) return 'motor';
  
  return 'default';
}
const RESCUE_META = {
  ambulance: { icon: 'ov-ambulance', label: 'Ambulance / Medis' },
  bpbd:      { icon: 'ov-bpbd',      label: 'BPBD' },
  damkar:    { icon: 'ov-firetruck', label: 'Pemadam Kebakaran' },
  dishub:    { icon: 'ov-towing',    label: 'Dishub / Derek' },
  police:    { icon: 'ov-policecar', label: 'Polisi / Satpol PP' },
  motor:     { icon: 'ov-motor:',    label: 'Motor Rescue' },
  default:   { icon: 'rescue-car',   label: 'Unit Rescue Lainnya' }
};
function accKeyFromBobot(props) {
  const val = Number(props?.bobot || 0);
  if (val === 10) return 'berat';
  if (val === 5) return 'sedang';
  return 'ringan';
}
const ACC_META = {
  berat:  { icon: 'acc-berat',  color: '#D32F2F', textColor: '#fff', label: 'Kecelakaan Berat' },
  sedang: { icon: 'acc-sedang', color: '#FF9800', textColor: '#000', label: 'Kecelakaan Sedang' },
  ringan: { icon: 'acc-ringan', color: '#FFEB3B', textColor: '#000', label: 'Kecelakaan Ringan' }
};
function normalizeGTStatus(props) {
  const st = (props?.status || props?.Status || '').toString().trim().toLowerCase();
  if (st.includes('operasi') || st === 'beroperasi') return 'operasi';
  if (st.includes('konstruksi') || st === 'pembangunan') return 'konstruksi';
  return 'operasi';
}
const GT_META = {
  operasi:    { icon: 'gt-operasi',    color: '#2E7D32', textColor: '#fff', label: 'Gerbang Tol Operasi' },
  konstruksi: { icon: 'gt-konstruksi', color: '#FF6F00', textColor: '#fff', label: 'Gerbang Tol Konstruksi' }
};

async function initAllLayers() {
  const layerOrder = ['administrasi','geofencing','blackarea','speedlimit','traffic','prediksi_risiko','blacklink', 'cctv_coverage', 'data_kecelakaan','rescue','patrol','gerbang_tol','cctv','objek_vital','traffic_point','blackspot','troublespot'];

  console.log("Memulai inisialisasi data global...");

  const promises = layerOrder.map(async (key) => {
      if (key === 'patrol') {
          return { key, data: { type: 'FeatureCollection', features: [] } };
      }

      const data = await fetchGeoJSONLayer(key); 
      const dataNorm = normalizeProperties(key, data);
      GLOBAL_DATA_CACHE[key] = dataNorm;
      return { key, data: dataNorm };
  });

  const results = await Promise.all(promises);

  for (const res of results) {
      const { key, data } = res;
      addLayerStyle(key, data);
      
      if (key !== 'patrol') {
          applyLayerFilter(key, "INITIAL_HIDE");
      }
  }

  isDataLoaded = true;
  console.log("Semua data berhasil di-load ke Memory.");
}

function switchSatwilView(satwilName) {
    if (!satwilName) return;
    console.log(`Switching view to: ${satwilName}`);
    
    const layerKeys = Object.keys(GLOBAL_DATA_CACHE);
    layerKeys.forEach(key => {
        applyLayerFilter(key, satwilName);
    });

    filteredLayerData = {}; 
    timeSliderData.allFeatures = { blackspot: [], blacklink: [], blackarea: [] }; 

    layerKeys.forEach(key => {
        const fc = GLOBAL_DATA_CACHE[key];
        if (fc && fc.features) {
             const fieldName = SATWIL_FIELD[key] || 'Satwil';

             const filtered = fc.features.filter(f => {
                 const s = f.properties[fieldName] || f.properties.Satwil || f.properties.satwil;
                 return s === satwilName;
             });

             filteredLayerData[key] = { type: 'FeatureCollection', features: filtered };

             if (['blackspot', 'blacklink', 'blackarea'].includes(key)) {
                timeSliderData.allFeatures[key] = filtered;
             }
        }
    });

    updateRiskMarkers(satwilName);

    if (filteredLayerData['traffic']) {
        checkAndNotifySevereTraffic(filteredLayerData['traffic']);
    }

    const satwilFeature = wilayahData.features.find(f => 
        (f.properties.Satwil || f.properties.satwil) === satwilName
    );
    if (satwilFeature) {
        addOrUpdateSatwilLayer(satwilFeature);
        try { 
            map.fitBounds(turf.bbox(satwilFeature), { padding:50, maxZoom:14 }); 
            
            setTimeout(() => {
                let adminFeature = satwilFeature;
                if (GLOBAL_DATA_CACHE['administrasi'] && GLOBAL_DATA_CACHE['administrasi'].features) {
                    const found = GLOBAL_DATA_CACHE['administrasi'].features.find(f => 
                        (f.properties.Satwil || f.properties.satwil || f.properties.nama_kab) === satwilName
                    );
                    if (found) adminFeature = found;
                }
                try {
                    const center = turf.centroid(adminFeature).geometry.coordinates;
                    showFeaturePopup(map, adminFeature, center, 'administrasi-fill');
                } catch(err) {}
            }, 800);
        } catch(e) {}
    }

    initTimeSlider(); 

    if (!timeSliderData.allFeatures.blackspot.length) {
        updateDashboard(null); 
    }
    
    renderLegend();
    window.updateSearchIndex();
    startPatrolMonitoring(satwilName);
    checkPatrolAlerts();
}

function updateRiskMarkers(satwilName) {
    if (riskExclamationMarkers) {
        riskExclamationMarkers.forEach(m => m.remove());
    }
    riskExclamationMarkers = [];

    const riskData = GLOBAL_DATA_CACHE['prediksi_risiko'];
    if (!riskData || !riskData.features) return;

    const highRiskFeatures = riskData.features.filter(f => {
        const s = f.properties.Satwil || f.properties.satwil;
        const val = f.properties.risk_prediction;
        return s === satwilName && val === 2;
    });

    if (highRiskFeatures.length === 0) return;

    const btn = document.querySelector(`.tab-btn[data-layer="prediksi_risiko"]`);
    const isVisible = btn && btn.classList.contains('active');

    highRiskFeatures.forEach(f => {
        let coord = null;
        if (f.geometry.type === 'Point') coord = f.geometry.coordinates;
        else if (f.geometry.type.includes('Line')) {
             try { coord = turf.center(f).geometry.coordinates; } catch(e){}
        }

        if (coord) {
            createRiskMarker(coord, f.properties);
        }
    });

    riskExclamationMarkers.forEach(marker => {
        marker.getElement().style.display = isVisible ? 'block' : 'none';
    });
}

function applyLayerFilter(layerKey, satwilName) {
    if (['cctv', 'objek_vital', 'rescue', 'gerbang_tol', 'data_kecelakaan', 'patrol'].includes(layerKey)) {
         handleClusterDataUpdate(layerKey, satwilName);
         return;
    }

    const layers = map.getStyle().layers || [];
    const targetLayers = layers.filter(l => l.source === layerKey);
    
    let filterExpr;
    
    if (satwilName === "INITIAL_HIDE") {
        filterExpr = ['==', ['get', 'Satwil'], 'HIDE_ALL_INITIAL_STATE'];
    } else {
        const fieldName = SATWIL_FIELD[layerKey] || 'Satwil';
        filterExpr = ['any',
            ['==', ['get', fieldName], satwilName],
            ['==', ['get', 'Satwil'], satwilName],
            ['==', ['get', 'satwil'], satwilName]
        ];
    }

    targetLayers.forEach(l => {
        if(l.type !== 'background' && l.type !== 'raster') {
            if (l.id === 'traffic-level4-highlight') {
                map.setFilter(l.id, ['all', ['==', ['to-number', ['get', 'level']], 4], filterExpr]);
            } 
            else if (l.id === 'prediksi_risiko-spotlight') {
                map.setFilter(l.id, ['all', ['==', ['to-number', ['get', 'risk_prediction']], 2], filterExpr]);
            }
            else {
                map.setFilter(l.id, filterExpr);
            }
        }
    });
}

function handleClusterDataUpdate(key, satwilName) {
    if (key === 'cctv') {
        ['cctv-on', 'cctv-off'].forEach(subKey => {
            updateSourceDataFromCache(subKey, satwilName);
        });
    } else if (key === 'objek_vital') {
        const categories = ['bandara','faskes','pendidikan','pemerintahan','polisi','stasiun','terminal','pariwisata','pelabuhan','masjid','gereja','vihara','pura','klenteng','default'];
        categories.forEach(cat => updateSourceDataFromCache(`objek_vital-${cat}`, satwilName));
    } else if (key === 'gerbang_tol') {
        ['gerbang_tol-operasi', 'gerbang_tol-konstruksi'].forEach(k => updateSourceDataFromCache(k, satwilName));
    } else if (key === 'data_kecelakaan') {
        ['data_kecelakaan-berat', 'data_kecelakaan-sedang', 'data_kecelakaan-ringan'].forEach(k => updateSourceDataFromCache(k, satwilName));
    } else if (key === 'rescue') {
         const rescueData = GLOBAL_DATA_CACHE['rescue'];
         if(!rescueData) return;
         const filtered = satwilName === "INITIAL_HIDE" ? [] : rescueData.features.filter(f => (f.properties.Satwil || f.properties.satwil) === satwilName);

         const buckets = { ambulance: [], bpbd: [], damkar: [], dishub: [], police: [], motor: [], default: [] };
         for (const f of filtered) {
            const k = rescueKeyFromVehicleGroup(f.properties);
            if (buckets[k]) buckets[k].push(f); else buckets.default.push(f);
         }
         Object.entries(buckets).forEach(([k, arr]) => {
             const s = map.getSource(`rescue-${k}`);
             if(s) s.setData({ type: 'FeatureCollection', features: arr });
         });
    }
}

function updateSourceDataFromCache(sourceId, satwilName) {
    const src = map.getSource(sourceId);
    if (!src) return;
    
    let parentKey = sourceId.split('-')[0]; 
    if(parentKey.startsWith('gerbang')) parentKey = 'gerbang_tol';
    if(parentKey.startsWith('data')) parentKey = 'data_kecelakaan'; 
    
    const fullData = GLOBAL_DATA_CACHE[parentKey];
    if(!fullData) return;

    let filtered = [];
    if (satwilName !== "INITIAL_HIDE") {
        const fieldName = SATWIL_FIELD[parentKey] || 'Satwil';
        
        const bySatwil = fullData.features.filter(f => {
            const s = f.properties[fieldName] || f.properties.Satwil || f.properties.satwil;
            return s === satwilName;
        });

        if (sourceId.includes('cctv-on')) filtered = bySatwil.filter(f => normalizeCCTVStatus(f.properties)==='on');
        else if (sourceId.includes('cctv-off')) filtered = bySatwil.filter(f => normalizeCCTVStatus(f.properties)==='off');
        else if (sourceId.includes('objek_vital')) {
            const cat = sourceId.replace('objek_vital-', '');
            filtered = bySatwil.filter(f => ovKeyFromKeterangan(f.properties) === cat);
        }
        else if (sourceId.includes('gerbang_tol')) {
             const stat = sourceId.includes('operasi') ? 'operasi' : 'konstruksi';
             filtered = bySatwil.filter(f => normalizeGTStatus(f.properties) === stat);
        }
        else if (sourceId.includes('data_kecelakaan')) {
             const lvl = sourceId.replace('data_kecelakaan-', '');
             filtered = bySatwil.filter(f => accKeyFromBobot(f.properties) === lvl);
        }
        else {
            filtered = bySatwil;
        }
    }
    src.setData({ type: 'FeatureCollection', features: filtered });
}

function applyVisibility(layerPrefix) {
    const isActive = document
      .querySelector(`.tab-btn[data-layer="${layerPrefix}"]`)
      ?.classList.contains('active');

    const ids = (map.getStyle().layers || []).map(l => l.id);
    const matches = ids.filter(id => {
      if (layerPrefix === 'traffic' && id.startsWith('traffic_point')) {
          return false; 
      }
      
      if (layerPrefix === 'cctv' && id.startsWith('cctv_coverage')) {
          return false;
      }

      return id.startsWith(layerPrefix) || 
             (layerPrefix === 'traffic_point' && id.startsWith('traffic_point-')) ||
             (layerPrefix === 'data_kecelakaan' && id.startsWith('data_kecelakaan-')) || 
             (layerPrefix === 'prediksi_risiko' && id.startsWith('prediksi_risiko')) ||  
             (layerPrefix === 'gerbang_tol' && id.startsWith('gerbang_tol-')) ||
             (layerPrefix === 'objek_vital' && id.startsWith('objek_vital-')) ||
             (layerPrefix === 'rescue' && id.startsWith('rescue-')) ||
             (layerPrefix === 'patrol' && id.startsWith('patrol-')) ||
             (layerPrefix === 'cctv' && (id.startsWith('cctv-') && !id.startsWith('cctv_coverage')));
    });

    matches.forEach(id => {
      map.setLayoutProperty(id, 'visibility', isActive ? 'visible' : 'none');
    });

    if (layerPrefix === 'prediksi_risiko') {
      const displayValue = isActive ? 'block' : 'none';
      if (typeof riskExclamationMarkers !== 'undefined') {
          riskExclamationMarkers.forEach(marker => {
            marker.getElement().style.display = displayValue;
          });
      }

      if (isActive) {
        if (!isRiskAnimationRunning) {
          isRiskAnimationRunning = true;
          riskAnimationStartTime = 0;
          requestAnimationFrame(animateRiskSpotlight);
        }
        if (!isExclamationAnimationRunning) {
          isExclamationAnimationRunning = true;
          exclamationAnimationStartTime = 0;
          requestAnimationFrame(animateExclamationMarkers);
        }
      } else {
        isRiskAnimationRunning = false;
        isExclamationAnimationRunning = false;
      }
    }
}

function addLayerStyle(key, dataNorm) {
  if (!map.getSource(key) && !['cctv','objek_vital','rescue','gerbang_tol','data_kecelakaan'].includes(key)) {
      map.addSource(key, { type: 'geojson', data: dataNorm });
  }

  switch (key) {
    case 'troublespot':
        map.addLayer({
        id:'troublespot-symbol', type:'symbol', source:'troublespot',
        layout:{ 'icon-image':['match',['get','keterangan'],'Jalan Ditutup','road-closed','Gangguan Jalan','construction','Kecelakaan','crash','construction'], 'icon-size':0.15, 'icon-allow-overlap':true }
        });
        setLayerInteractivity('troublespot-symbol');
        break;
    case 'blackspot':
        map.addLayer({
        id:'blackspot-symbol', type:'symbol', source:'blackspot',
        layout:{ 'icon-image':'blackspot','icon-size':0.15,'icon-allow-overlap':true }
        });
        setLayerInteractivity('blackspot-symbol');
        break;
    case 'traffic_point':
        map.addLayer({
        id: 'traffic_point-symbol', type: 'symbol', source: 'traffic_point',
        layout: { 'icon-image': 'kecelakaan', 'icon-size': 0.15, 'icon-allow-overlap': true }
        });
        setLayerInteractivity('traffic_point-symbol');
        break;
    case 'traffic':
        map.addLayer({
        id:'traffic-outline', type:'line', source:'traffic',
        layout:{ 'line-join':'round', 'line-cap':'round' },
        paint:{ 'line-width': 4, 'line-opacity': 0.75, 'line-color': '#000000' }
        });
        map.addLayer({
        id:'traffic-line', type:'line', source:'traffic',
        layout:{ 'line-join':'round', 'line-cap':'round' },
        paint:{ 'line-width': 3, 'line-opacity': 1, 'line-color':['interpolate',['linear'],['get','level'],1,'#ffeb3b',3,'#f44336',5,'#5d4037'] }
        });
        map.addLayer({
        id: 'traffic-level4-highlight', type: 'line', source: 'traffic',
        filter: ['==', ['to-number', ['get', 'level']], 4], 
        layout: { 'line-join': 'round', 'line-cap': 'round' },
        paint: { 'line-color': '#d50000', 'line-width': 4, 'line-opacity': 0.8, 'line-blur': 1 }
        });
        setLayerInteractivity('traffic-line');
        break;
    case 'blacklink':
        map.addLayer({ id:'blacklink-outline', type:'line', source:'blacklink', paint:{ 'line-width':4,'line-color':['match',['get','coverage'],'Black Link Blindspot','#e53935','Black Link Tercover CCTV','#212121','#777'] }});
        map.addLayer({ id:'blacklink-line', type:'line', source:'blacklink', paint:{ 'line-width':2,'line-color':['match',['get','coverage'],'Black Link Blindspot','#000','Black Link Tercover CCTV','#e53935','#444'] }});
        setLayerInteractivity('blacklink-line');
        break;
    case 'blackarea':
        map.addLayer({ 
            id:'blackarea-fill', 
            type:'fill', 
            source:'blackarea', 
            layout: { 'visibility': 'visible' }, 
            paint:{ 
                'fill-color': '#212121', 
                'fill-opacity': 0.2      
            }
        });
        map.addLayer({ 
            id:'blackarea-outline', 
            type:'line', 
            source:'blackarea', 
            layout: { 'visibility': 'visible' }, 
            paint:{ 
                'line-color': '#000000', 
                'line-width': 1.5,
                'line-opacity': 0.5
            }
        });
        setLayerInteractivity('blackarea-fill');
        break;
    case 'cctv_coverage':
        map.addLayer({ 
            id: 'cctv_coverage-fill', 
            type: 'fill', 
            source: 'cctv_coverage', 
            layout: { 'visibility': 'none' },
            paint: { 'fill-color': '#00FF00', 'fill-opacity': 0.7 } 
        });
        map.addLayer({ 
            id: 'cctv_coverage-outline', 
            type: 'line', 
            source: 'cctv_coverage', 
            layout: { 'visibility': 'none' },
            paint: { 'line-color': '#006400', 'line-width': 1, 'line-opacity': 0.8 } 
        });
        setLayerInteractivity('cctv_coverage-fill');
        break;
    case 'prediksi_risiko':
        map.addLayer({ 
            id:'prediksi_risiko-outline', type:'line', source:'prediksi_risiko', 
            layout:{ 'line-join':'round', 'line-cap':'round', 'visibility': 'none' },
            paint:{ 'line-width':3, 'line-color':'#000', 'line-opacity':0.5 }
        });
        map.addLayer({ 
            id:'prediksi_risiko-line', type:'line', source:'prediksi_risiko', 
            layout:{ 'line-join':'round', 'line-cap':'round', 'visibility': 'none' },
            paint:{ 'line-width':2, 'line-opacity':0.95, 'line-color':[ 'match', ['get', 'risk_prediction'], 0,'#4caf50', 1,'#bfb100', 2,'#b8005c', '#cccccc' ] }
        });
        map.addLayer({
            id: 'prediksi_risiko-spotlight', type: 'line', source: 'prediksi_risiko',
            filter: ['==', 'risk_prediction', 2], 
            layout: { 'line-join': 'round', 'line-cap': 'round', 'visibility': 'none' },
            paint: { 'line-color': '#b8005c', 'line-width': 5, 'line-opacity': 0.8, 'line-blur': 2 }
        });
        setLayerInteractivity('prediksi_risiko-line');
        break;
    case 'speedlimit':
        map.addLayer({ 
            id:'speedlimit-outline', type:'line', source:'speedlimit', 
            layout:{ 'line-join':'round', 'line-cap':'round', 'visibility': 'none' },
            paint:{ 'line-width':3, 'line-color':'#000', 'line-opacity':0.5 }
        });
        map.addLayer({
            id: 'speedlimit-line', type: 'line', source: 'speedlimit', 
            layout: { 'line-join': 'round', 'line-cap': 'round', 'visibility': 'none' },
            paint: { 'line-width': 2, 'line-color': [ 'step', ['get', 'speed_limit'], '#0d47a1', 20, '#1565c0', 40, '#1976d2', 60, '#42a5f5', 80, '#90caf9' ], 'line-opacity': 0.8 }
        });
        setLayerInteractivity('speedlimit-line');
        break;
    case 'geofencing':
        const softColors = [ 'match', ['get', 'tipe_zona'], 'Fasilitas Pendidikan', '#f48fb1', 'Fasilitas Kesehatan', '#ffcc80', 'Kantor Pemerintahan', '#a5d6a7', 'Kantor Polisi', '#90caf9', 'Bandara', '#b0bec5', 'Stasiun', '#ce93d8', 'Terminal', '#81d4fa', 'Pelabuhan', '#80cbc4', 'Pariwisata', '#ffe082', 'Masjid', '#b39ddb', 'Gereja', '#ffab91', 'Vihara', '#80deea', 'Pura', '#c5e1a5', 'Klenteng', '#ffccbc', '#eeeeee' ];
        map.addLayer({ 
            id: 'geofencing-fill', type: 'fill', source: 'geofencing', 
            layout: { 'visibility': 'none' },
            paint: { 'fill-color': softColors, 'fill-opacity': 0.2 } 
        });
        map.addLayer({ 
            id: 'geofencing-outline', type: 'line', source: 'geofencing', 
            layout: { 'visibility': 'none' },
            paint: { 'line-color': softColors, 'line-width': 1.5, 'line-opacity': 0.5 } 
        });
        setLayerInteractivity('geofencing-fill');
        break;
    case 'administrasi':
        const softPalette = [
            '#FFB3BA', '#FFDFBA', '#FFFFBA', '#BAFFC9', '#BAE1FF',
            '#E6E6FA', '#F4C2C2', '#F5F5DC', '#D3F8E2', '#E0BBE4',
            '#957DAD', '#D291BC', '#FEC8D8', '#FFDFD3', '#A0C4FF'
        ];

        const uniqueKec = new Set();
        if (dataNorm && dataNorm.features) {
            dataNorm.features.forEach(f => {
                const kecName = f.properties.nama_kec || f.properties.KECAMATAN || f.properties.WADMKC || f.properties.kecamatan; 
                if (kecName) uniqueKec.add(kecName);
            });
        }

        const inputExpression = ['coalesce', ['get', 'nama_kec'], ['get', 'KECAMATAN'], ['get', 'WADMKC'], ['get', 'kecamatan'], ''];
        const colorMatch = ['match', inputExpression];
        
        let colorIdx = 0;
        uniqueKec.forEach(kec => {
            colorMatch.push(kec);
            colorMatch.push(softPalette[colorIdx % softPalette.length]);
            colorIdx++;
        });

        colorMatch.push('#cccccc'); 

        map.addLayer({ 
            id: 'administrasi-fill', 
            type: 'fill', 
            source: 'administrasi', 
            paint: { 
                'fill-color': colorMatch, 
                'fill-opacity': 0.5
            }
        });
        
        map.addLayer({ 
            id: 'administrasi-outline', 
            type: 'line', 
            source: 'administrasi', 
            paint: { 
                'line-color': '#ffffff',
                'line-width': 1.5,
                'line-opacity': 0.5
            }
        });
        
        setLayerInteractivity('administrasi-fill');
        break;
    case 'cctv':
         const onFC  = { type:'FeatureCollection', features: dataNorm.features.filter(f => normalizeCCTVStatus(f.properties)==='on') };
         const offFC = { type:'FeatureCollection', features: dataNorm.features.filter(f => normalizeCCTVStatus(f.properties)==='off') };
         const defsC = [ { key:'cctv-on', icon:'cctv-on', fc:onFC }, { key:'cctv-off', icon:'cctv-off', fc:offFC } ];
         defsC.forEach(d => setupClusterLayer(d));
         break;
    case 'objek_vital':
         const buckets = { bandara:[], faskes:[], pendidikan:[], pemerintahan:[], polisi:[], stasiun:[], terminal:[], pariwisata:[], pelabuhan:[], masjid:[], gereja:[], vihara:[], pura:[], klenteng:[], default:[] };
         for (const f of dataNorm.features) (buckets[ovKeyFromKeterangan(f.properties)] ?? buckets.default).push(f);
         const defsOV = Object.entries(buckets).map(([k,arr])=>({ key:`objek_vital-${k}`, fc:{ type:'FeatureCollection', features:arr }, ...OV_META[k] }));
         defsOV.forEach(d => setupClusterLayer(d));
         break;
    case 'gerbang_tol':
         const bucketsGT = { operasi: [], konstruksi: [] };
         for (const f of dataNorm.features) {
            const key = normalizeGTStatus(f.properties);
            if (bucketsGT[key]) bucketsGT[key].push(f);
         }
         const defsGT = Object.entries(bucketsGT).map(([k, arr]) => ({ key: `gerbang_tol-${k}`, fc: { type: 'FeatureCollection', features: arr }, ...GT_META[k] }));
         defsGT.forEach(d => setupClusterLayer(d));
         break;
    case 'data_kecelakaan':
         const bucketsAcc = { berat: [], sedang: [], ringan: [] };
         for (const f of dataNorm.features) {
            const key = accKeyFromBobot(f.properties);
            if (bucketsAcc[key]) bucketsAcc[key].push(f);
         }
         const defsAcc = Object.entries(bucketsAcc).map(([k, arr]) => ({ 
             key: `data_kecelakaan-${k}`, 
             fc: { type: 'FeatureCollection', features: arr }, 
             ...ACC_META[k],
             visible: false
         }));
         defsAcc.forEach(d => setupClusterLayer(d));
         break;
    case 'patrol':
         if (!map.getSource('patrol')) {
             map.addSource('patrol', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
         }

         if (map.getLayer('patrol-symbol')) map.removeLayer('patrol-symbol');

         map.addLayer({
            id: 'patrol-symbol', 
            type: 'symbol', 
            source: 'patrol',
            layout: {
               'visibility': 'visible', 
               'icon-image': 'patrol-icon', 
               'icon-size': 0.18,           
               'icon-allow-overlap': true,
               'icon-ignore-placement': true,
               'text-field': ['get', 'Nama'], 
               'text-font': ['Noto Sans Bold'], 
               'text-size': 11, 
               'text-offset': [0, 2], 
               'text-anchor': 'top'
            },
            paint: { 
                'text-color': '#ffffff', 
                'text-halo-color': '#000000', 
                'text-halo-width': 2 
            }
         });
         setLayerInteractivity('patrol-symbol');  
         break;
    case 'rescue':
         const bucketsRes = { ambulance: [], bpbd: [], damkar: [], dishub: [], police: [], motor: [], default: [] };
         for (const f of dataNorm.features) {
            const key = rescueKeyFromVehicleGroup(f.properties);
            if (bucketsRes[key]) bucketsRes[key].push(f); else bucketsRes.default.push(f);
         }
         const defsRes = Object.entries(bucketsRes).map(([k, arr]) => ({ key: `rescue-${k}`, fc: { type: 'FeatureCollection', features: arr }, ...RESCUE_META[k] }));
         defsRes.forEach(d => setupClusterLayer(d));
         break;
  }
}

function setupClusterLayer(d) {
    if (!map.getSource(d.key)) {
        map.addSource(d.key,{ type:'geojson', data:d.fc, cluster:true, clusterRadius:50, clusterMaxZoom:15 });
    }

    const visibilityState = (d.visible === false) ? 'none' : 'visible';

    if (!map.getLayer(`${d.key}-cluster`)) {
        map.addLayer({
            id:`${d.key}-cluster`, type:'symbol', source:d.key, filter:['has','point_count'],
            layout:{ 
                'visibility': visibilityState,
                'icon-image':d.icon,'icon-size':['interpolate',['linear'],['get','point_count'],2,0.2,50,0.3], 'icon-allow-overlap':true, 'text-field':['get','point_count'], 'text-font':['Noto Sans Bold'], 'text-size':12, 'text-allow-overlap':true 
            },
            paint:{ 'text-color':'#fff','text-halo-color':'#000','text-halo-width':1.2 }
        });
    }
    if (!map.getLayer(`${d.key}-unclustered`)) {
        map.addLayer({
            id:`${d.key}-unclustered`, type:'symbol', source:d.key, filter:['!', ['has','point_count']],
            layout:{ 
                'visibility': visibilityState,
                'icon-image':d.icon,'icon-size':0.15,'icon-allow-overlap':true 
            }
        });
    }
    setLayerInteractivity(`${d.key}-cluster`);
    setLayerInteractivity(`${d.key}-unclustered`);
}

function renderLegend(){
  const body = document.getElementById('legend-content');
  const show = (id)=>document.querySelector(`.tab-btn[data-layer="${id}"]`)?.classList.contains('active');
  let html = '<h4>Legenda</h4>';

  if (show('troublespot')) { html += `<div class="legend-title">Titik Gangguan Lalu Lintas</div>${legendRow('road-closed','Jalan Ditutup')}${legendRow('construction','Gangguan Jalan')}${legendRow('crash','Kecelakaan')}`; }
  if (show('blackspot')) { html += `<div class="legend-title">Black Spot</div>${legendRow('blackspot','Black Spot')}`; }
  if (show('prediksi_risiko')) {
    const colors = [
      '#bfb100',
      '#b8005c'
    ];
    const labels = [
      'Risiko Sedang',
      'Risiko Tinggi'
      ];  
    html += `<div class="legend-title">Prediksi Risiko Kecelakaan</div>
     <div style="width: 100%; margin-bottom: 4px;">
            <div style="display: flex; width: 100%; height: 16px; border-radius: 4px; overflow: hidden; border: 1px solid #000;">`;
    for (let i = 0; i < colors.length; i++) {
      const dividerStyle = (i > 0) ? 'border-left: 1px solid #333;' : '';
      html += `<div style="flex: 1; background-color: ${colors[i]}; ${dividerStyle}"></div>`;
    }
      html += `</div></div>`;
      html += `<div style="display: flex; width: 100%; font-size: 10px; line-height: 1.1; justify-content: space-between;">`;
        for (const label of labels) {
      html += `<span style="flex: 1; text-align: center; white-space: nowrap;">${label}</span>`;
    }
      html += `</div>`;
  }
  if (show('traffic_point')) {
    html += `<div class="legend-title">Titik Perlambatan</div>${legendRow('kecelakaan','Titik Perlambatan')}`;
  }
  if (show('traffic')) {
  const colors = [
      '#ffeb3b',
      '#f9a825',
      '#f44336',
      '#a94136',
      '#5d4037'
    ];
  const labels = [
      'Ringan',
      'Sedang',
      'Padat',
      'Sangat Padat',
      'Stop Total'
      ];
    html += `<div class="legend-title">Perlambatan</div>
     <div style="width: 100%; margin-bottom: 4px;">
            <div style="display: flex; width: 100%; height: 16px; border-radius: 4px; overflow: hidden; border: 1px solid #000;">`;
    for (let i = 0; i < colors.length; i++) {
      const dividerStyle = (i > 0) ? 'border-left: 1px solid #333;' : '';
      html += `<div style="flex: 1; background-color: ${colors[i]}; ${dividerStyle}"></div>`;
    }
      html += `</div></div>`;
      html += `<div style="display: flex; width: 100%; font-size: 10px; line-height: 1.1; justify-content: space-between;">`;
        for (const label of labels) {
      html += `<span style="flex: 1; text-align: center; white-space: nowrap;">${label}</span>`;
    }
      html += `</div>`;
  }
  if (show('blacklink')) {
    html += `<div class="legend-title">Black Link</div>
      <div class="legend-item"><div class="legend-icon"><svg xmlns='http://www.w3.org/2000/svg' viewBox="0 0 30 10" width="36"><line x1="0" y1="5" x2="30" y2="5" stroke="#e53935" stroke-width="4"/><line x1="0" y1="5" x2="30" y2="5" stroke="#212121" stroke-width="2"/></svg></div><div class="legend-label">Black Link Blindspot</div></div>
      <div class="legend-item"><div class="legend-icon"><svg xmlns='http://www.w3.org/2000/svg' viewBox="0 0 30 10" width="36"><line x1="0" y1="5" x2="30" y2="5" stroke="#212121" stroke-width="4"/><line x1="0" y1="5" x2="30" y2="5" stroke="#e53935" stroke-width="2"/></svg></div><div class="legend-label">Black Link Tercover CCTV</div></div>`;
  }
  if (show('blackarea')) {
    html += `<div class="legend-title">Black Area</div>
      <div class="legend-item"><div class="legend-icon" style="background:rgba(192,192,192,0.5); border:1px solid #000; border-radius:50%; height:20px; width:20px;"></div><div class="legend-label">Black Area</div></div>`;
  }
  if (show('speedlimit')) {
    const colors = ['#0d47a1', '#1565c0', '#1976d2', '#42a5f5', '#90caf9'];
    const labels = ['<20', '20-40', '40-60', '60-80', '>80'];
    
    html += `<div class="legend-title">Batas Kecepatan (km/h)</div>
      <div style="width: 100%; margin-bottom: 4px;">
        <div style="display: flex; width: 100%; height: 16px; border-radius: 4px; overflow: hidden; border: 1px solid #000;">`;
    for (let i = 0; i < colors.length; i++) {
      html += `<div style="flex: 1; background-color: ${colors[i]};"></div>`;
    }
    html += `</div></div>
      <div style="display: flex; width: 100%; font-size: 10px; line-height: 1.1; justify-content: space-between;">`;
    for (const label of labels) {
      html += `<span style="flex: 1; text-align: center;">${label}</span>`;
    }
    html += `</div>`;
  }
  if (show('geofencing')) {
    html += `<div class="legend-title">Geofencing Area</div>`;
    html += legendRowCustom('#f48fb1', 'Fasilitas Pendidikan');
    html += legendRowCustom('#ffcc80', 'Fasilitas Kesehatan');
    html += legendRowCustom('#a5d6a7', 'Kantor Pemerintahan');
    html += legendRowCustom('#90caf9', 'Kantor Polisi');
    html += legendRowCustom('#b0bec5', 'Bandara');
    html += legendRowCustom('#ce93d8', 'Stasiun');
    html += legendRowCustom('#81d4fa', 'Terminal');
    html += legendRowCustom('#80cbc4', 'Pelabuhan');
    html += legendRowCustom('#ffe082', 'Pariwisata');
    html += legendRowCustom('#b39ddb', 'Masjid');
    html += legendRowCustom('#ffab91', 'Gereja');
    html += legendRowCustom('#80deea', 'Vihara');
    html += legendRowCustom('#c5e1a5', 'Pura');
    html += legendRowCustom('#ffccbc', 'Klenteng');
  }
function legendRowCustom(color, label){
return `<div class="legend-item">
            <div class="legend-icon" style="
              background:${color}; 
              opacity:0.7; 
              border:1px solid #555; 
              width:20px; 
              height:20px; 
              border-radius: 50%;
            "></div>
            <div class="legend-label">${label}</div>
          </div>`;
}
  if (show('gerbang_tol')) {
    html += `<div class="legend-title">Gerbang Tol</div>`;
    Object.values(GT_META).forEach(meta => {
        html += legendRow(meta.icon, meta.label);
    });
  }
  if (show('patrol')) { 
    html += `<div class="legend-title">Petugas Patroli</div>${legendRow('patrol-icon','Petugas Patroli')}`; 
  }
  if (show('rescue')) {
    html += `<div class="legend-title">Kendaraan Rescue</div>
      ${legendRow('ov-ambulance', 'Ambulance / Medis')}
      ${legendRow('ov-bpbd', 'BPBD')}
      ${legendRow('ov-firetruck', 'Damkar')}
      ${legendRow('ov-towing', 'Dishub / Derek')}
      ${legendRow('ov-policecar', 'Polisi / Satpol PP')}
      ${legendRow('ov-motor:', 'Motor Rescue')}`;
  }
  if (show('data_kecelakaan')) {
      html += `<div class="legend-title">Data Kecelakaan</div>`;
      Object.values(ACC_META).forEach(meta => {
          html += legendRow(meta.icon, meta.label);
      });
  }
  if (show('objek_vital')) {
    html += `<div class="legend-title">Objek Vital</div>
      ${legendRow('ov-plane','Bandara')} ${legendRow('ov-hospital','Fasilitas Kesehatan')}
      ${legendRow('ov-school','Fasilitas Pendidikan')} ${legendRow('ov-government','Kantor Pemerintahan')}
      ${legendRow('ov-police','Kantor Polisi')} ${legendRow('ov-train','Stasiun')}
      ${legendRow('ov-bus','Terminal')} ${legendRow('ov-pariwisata','Tempat Pariwisata')}
      ${legendRow('ov-port','Pelabuhan')}
      ${legendRow('ov-mosque','Masjid')} ${legendRow('ov-church','Gereja')}
      ${legendRow('ov-vihara','Vihara')} ${legendRow('ov-temple','Pura')}
      ${legendRow('ov-klenteng','Klenteng')}`;
  }
  if (show('cctv_coverage')) {
    html += `
      <div class="legend-title">Area Pantauan CCTV</div>
      <div class="legend-item">
        <div class="legend-icon" style="
          background: #00FF00; 
          opacity: 0.7; 
          border: 1px solid #006400; 
          width: 20px; height: 20px; 
          border-radius: 3px;">
        </div>
        <div class="legend-label">Area Pantauan</div>
      </div>`;
  }    
  if (show('cctv')) {
    html += `<div class="legend-title">CCTV</div> ${legendRow('cctv-on','Hidup')} ${legendRow('cctv-off','Mati')}`;
  }

  body.innerHTML = html.replace('<h4>Legenda</h4>', '') || '<div style="font-size:13px;color:#666">Tidak ada layer aktif.</div>';
}
function legendRow(iconId, label){
  const svg = ICON_SVGS[iconId] || '';
  return `<div class="legend-item"><div class="legend-icon"><img src="${'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg)}" alt="${label}" style="width:24px;height:24px"/></div><div class="legend-label">${label}</div></div>`;
}
function resetCanvas(id){
  const old = document.getElementById(id);
  if (!old) return null;
  const parent = old.parentNode;
  const next = document.createElement('canvas');
  next.id = id;
  parent.replaceChild(next, old);
  return next.getContext('2d');
}
function updateDashboard(filterDate = null) {
    const ts = filteredLayerData.troublespot?.features ?? [];
    const cctv = filteredLayerData.cctv?.features ?? [];
    const tp = filteredLayerData.traffic_point?.features ?? [];
    const tsCounts = ts.reduce((acc, f) => {
        const k = (f.properties?.keterangan || f.properties?.Keterangan || 'Lainnya') + '';
        acc[k] = (acc[k] || 0) + 1; return acc;
    }, {});
    if (tp.length > 0) {
        tsCounts['Titik Perlambatan'] = tp.length;
    }
    const tsEntries = Object.entries(tsCounts).sort((a,b)=>b[1]-a[1]);
    if (troublespotChart) troublespotChart.destroy();
    const tsCtx = resetCanvas('troublespot-chart');
    if (tsCtx){
        troublespotChart = new Chart(tsCtx, {
            type:'bar', 
            data:{ 
                labels: tsEntries.map(([k])=>k), 
                datasets:[{ 
                    label:'Jumlah', 
                    data: tsEntries.map(([,v])=>v), 
                    backgroundColor: '#e74c3c', 
                    borderColor:'#ffffff', 
                    borderWidth:1, 
                    barThickness:14 
                }] 
            },
            options:{ 
                indexAxis:'y', 
                responsive:true, 
                maintainAspectRatio:false, 
                plugins:{ legend:{ display:false } }, 
                scales:{ 
                    x:{ beginAtZero:true, ticks:{ precision:0, color:'#fff' }, grid:{ color:'rgba(255,255,255,0.2)' } }, 
                    y:{ ticks:{ font:{ size:11 }, color:'#fff' }, grid:{ color:'rgba(255,255,255,0.2)' } } 
                } 
            }
        });
    }
    const tsNoteEl = document.getElementById('troublespot-note');
    const tsCanvasEl = document.getElementById('troublespot-chart');

    if (tsEntries.length === 0) {
      if (tsCanvasEl) tsCanvasEl.style.display = 'none';
      if (tsNoteEl)  tsNoteEl.textContent = 'Tidak ada laporan gangguan/perlambatan di wilayah ini';
    } else {
      if (tsCanvasEl) tsCanvasEl.style.display = 'block';
      if (tsNoteEl)  tsNoteEl.textContent = '';
    }
    const onCount = cctv.filter(f => normalizeCCTVStatus(f.properties)==='on').length;
    const offCount = cctv.filter(f => normalizeCCTVStatus(f.properties)==='off').length;
    if (cctvChart) cctvChart.destroy();
    const cctvCtx = resetCanvas('cctv-chart');
    if (cctvCtx){
        cctvChart = new Chart(cctvCtx, {
            type:'doughnut', data:{ labels:['Hidup','Mati'], datasets:[{ data:[onCount, offCount], backgroundColor:['#00e676','#e74c3c'], borderColor:'#ffffff', borderWidth:1 }] },
            options:{ responsive:true, maintainAspectRatio:false, cutout:'60%', plugins:{ legend:{ position:'bottom', labels:{ color:'#fff', boxWidth:10 } } } }
        });
    }

    let bs = [];
    const masterBlackspots = timeSliderData.allFeatures.blackspot || [];

    let targetDate = filterDate;
    if (!targetDate && timeSliderData.uniqueDates?.length) {
      targetDate = timeSliderData.uniqueDates[timeSliderData.uniqueDates.length - 1];
    }

    const listEl   = document.getElementById('blackspot-list');
    const noteEl   = document.getElementById('blackspot-note');
    const totalEl  = document.getElementById('blackspot-total');

    if (totalEl) totalEl.textContent = '';

    if (targetDate) {
      const selYMD = toYMD(targetDate);
      bs = masterBlackspots.filter(f => {
        const d = parseLocalDateLoose(f.properties?.tanggal);
        return d && toYMD(d) === selYMD;
      });
    }

    if (!bs.length) {
      listEl.innerHTML = `<div class="no-data-info">Tidak ada data black spot pada tanggal ini.</div>`;
      if (noteEl) noteEl.textContent = '';
      if (totalEl) totalEl.textContent = '';

    } else {
     const parsed = bs
      .map(f => ({ f, skor: Number(f.properties?.SkorBS ?? 0), d: new Date(f.properties?.tanggal || 0) }))
      .sort((a, b) => b.skor - a.skor || b.d - a.d);

      listEl.innerHTML = parsed.slice(0, 5).map(({ f, skor }, index) => {
        const name = (f.properties?.Nama || f.properties?.Nm_Ruas || 'Black Spot') + '';
        return `<div class="blackspot-list-item" 
                     style="cursor: pointer;" 
                     data-index="${index}">
                  <span>${name.length > 30 ? name.slice(0, 27) + '...' : name}</span>
                  <span class="score">${skor}</span>
              </div>`;
      }).join('');
      listEl.querySelectorAll('.blackspot-list-item[data-index]').forEach(item => {
        item.addEventListener('click', () => {
          try {
            const index = parseInt(item.dataset.index, 10);
            const { f } = parsed[index];
            if (!f) return;
            const center = turf.centroid(f).geometry.coordinates;
            if (highlightMarker) {
              highlightMarker.remove();
            }
            const el = document.createElement('div');
            el.className = 'highlight-pulse';
            highlightMarker = new maplibregl.Marker({
              element: el,
              anchor: 'center'
            })
            .setLngLat(center)
            .addTo(map);
            showFeaturePopup(map, f, center, 'blackspot');
            popup.once('close', () => {
              if (highlightMarker) {
                highlightMarker.remove();
                highlightMarker = null;
              }
            });

          } catch (e) {
            console.error('Gagal menyorot Black Spot:', e);
          }
        });
      });
      if (noteEl) noteEl.textContent = parsed.length < 5
        ? `Hanya ada ${parsed.length} Black Spot pada tanggal ini.`
        : '';
      if (totalEl) {
        if (parsed.length >= 5) {
          const dateText = targetDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
          totalEl.textContent = `Total Black Spot pada ${dateText}: ${parsed.length}`;
        } else {
          totalEl.textContent = '';
        }
      }
    }
    const trendEl = document.getElementById('blackspot-trend');
    if (trendEl) {
      const allDates = timeSliderData.uniqueDates;
      const allBlackspots = timeSliderData.allFeatures.blackspot || [];

      if (allDates.length < 3) {
        trendEl.innerHTML = '';
      } else {
        const dailyCounts = allDates.map((date, index) => {
          const ymd = toYMD(date);
          const count = allBlackspots.filter(f => f.properties.tanggal === ymd).length;
          return { x: index, y: count };
        });
        const dataForTrend = dailyCounts.slice(-7);
        const slope = calculateLinearRegressionSlope(dataForTrend);
        let trendIcon = '↔️';
        let trendText = 'Stabil';
        let trendColor = '#ffffff';
        const threshold = 0.1; 
        if (slope > threshold) {
          trendIcon = '⬆️';
          trendText = 'Meningkat';
          trendColor = '#ff5252';
        } else if (slope < -threshold) {
          trendIcon = '⬇️';
          trendText = 'Menurun';
          trendColor = '#4caf50';
        }
        const satwilName = selectedSatwilName || 'Wilayah Ini';
        trendEl.innerHTML = 
          `<span style="font-size: 16px; font-weight: 700; color: ${trendColor};">${trendIcon} Tren Black Spot ${trendText}</span>
           <br>di Wilayah ${satwilName} (Dalam ${dataForTrend.length} Hari Terakhir)`;
      }
    }
}

function decodePolyline5(str) {
  let index = 0, lat = 0, lng = 0, coords = [];
  while (index < str.length) {
    let b, shift = 0, result = 0;
    do { b = str.charCodeAt(index++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
    const dlat = (result & 1) ? ~(result >> 1) : (result >> 1);
    lat += dlat;
    shift = 0; result = 0;
    do { b = str.charCodeAt(index++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
    const dlng = (result & 1) ? ~(result >> 1) : (result >> 1);
    lng += dlng;
    coords.push([lng / 1e5, lat / 1e5]);
  }
  return coords;
}
</script>

<script>
function esc(s){ return String(s)
  .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
  .replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

function showFeaturePopup(map, feature, coordinates, layerKey) {
    if (popup && popup.isOpen()) {
        popup.remove();
        cleanupPC(); 
    }

    const titleMap = {
        troublespot:'Titik Gangguan Lalu Lintas', blackspot:'Black Spot', traffic_point: 'Titik Perlambatan', cctv:'CCTV',
        objek_vital:'Objek Vital', gerbang:'Gerbang Tol', data_kecelakaan:'Data Kecelakaan', 
        patrol: 'Petugas Patroli',rescue: 'Kendaraan Rescue', prediksi_risiko:'Prediksi Risiko Kecelakaan',
        administrasi:'Wilayah Administrasi', traffic:'Perlambatan',
        blacklink:'Black Link', blackarea:'Black Area', speedlimit: 'Batas Kecepatan',
        geofencing: 'Geofencing Area'
    };
    
    let prefix = layerKey;
    if(layerKey.startsWith('cctv')) prefix = 'cctv';
    else if(layerKey.startsWith('objek_vital')) prefix = 'objek_vital';
    
    if (!prefix || !titleMap[prefix]) {
        prefix = Object.keys(titleMap).find(p => layerKey.startsWith(p));
    }

    let title = titleMap[prefix] || 'Informasi';
    if (prefix === 'objek_vital'){
        const ovLabel = OV_META[ovKeyFromKeterangan(feature.properties)]?.label || 'Lainnya';
        title = `Objek Vital • ${ovLabel}`;
    }
    
    let bodyHtml = '';
    const props = feature.properties || {};

    if (prefix === 'cctv') {
        const cctvId = props.rowid || props.id || props.cctv_id || props.id_cctv || props.ID; 
        const satwil = props.Satwil || props.satwil;
        const region = mapSatwilToRegion(satwil);

        if (cctvId && region) {
            bodyHtml += `
                <div style="width:100%; height:auto; margin-bottom:10px; border-radius:6px; overflow:hidden; background:#000;">
                    <video id="video-${cctvId}" 
                           style="width:100%; aspect-ratio:16/9; display:block;" 
                           autoplay playsinline muted controls>
                    </video>
                </div>
                <div style="font-size:11px; text-align:center; color:#666; margin-bottom:8px;">
                    Region: ${region.toUpperCase()} | ID: ${cctvId}
                </div>`;
            
            setTimeout(() => {
                loadCamera(cctvId, region);
            }, 300);

            // Fetch dynamic detail
            bodyHtml += `<div id="cctv-detail-${cctvId}">Memuat detail...</div>`;
            
            setTimeout(async () => {
                try {
                    const res = await fetch(`{{ url('PemetaanNew/api/detail/cctv') }}/${cctvId}`);
                    if (!res.ok) throw new Error('Network error');
                    const cctvDetail = await res.json();
                    
                    let detailHtml = '';
                    const detailKeys = {
                        'Nama/Pemilik': cctvDetail.owner,
                        'Merk/Brand': cctvDetail.brand,
                        'Lokasi': cctvDetail.lokasi,
                        'IP Address': cctvDetail.ipaddres,
                        'Fungsi': cctvDetail.fungsicctv,
                        'Status': props.status
                    };
                    
                    for (const [k, v] of Object.entries(detailKeys)) {
                        detailHtml += `<div><strong>${k}:</strong> <span>${esc(v)}</span></div>`;
                    }
                    
                    const el = document.getElementById(`cctv-detail-${cctvId}`);
                    if (el) el.innerHTML = detailHtml;
                } catch (e) {
                    const el = document.getElementById(`cctv-detail-${cctvId}`);
                    if (el) el.innerHTML = `<span style="color:red;">Gagal memuat detail dari server.</span>`;
                }
            }, 100);

        } else {
            bodyHtml += `<div style="color:red; font-size:12px; margin-bottom:5px;">ID CCTV atau Region tidak dikenali.</div>`;
        }

    } else if (prefix === 'administrasi') {
        bodyHtml += `<div><strong>Provinsi:</strong> ${props.nama_prop || '-'}</div>`;
        bodyHtml += `<div><strong>Kab/Kota:</strong> ${props.nama_kab || '-'}</div>`;
        bodyHtml += `<div><strong>Kecamatan:</strong> ${props.nama_kec || props.KECAMATAN || '-'}</div>`;
        bodyHtml += `<div><strong>Penduduk:</strong> ${Number(props.jumlah_pen || 0).toLocaleString('id-ID')} Jiwa</div>`;
        bodyHtml += `<div><strong>Kepadatan:</strong> ${Number(props.kepadatan || 0).toLocaleString('id-ID')} Jiwa/ha</div>`;
        bodyHtml += `<div><strong>Jumlah Kecelakaan:</strong> ${props.jumlah_laka || 0} Kejadian</div>`;
        
        bodyHtml += `<hr style="margin: 8px 0; border: 0; border-top: 1px solid #ddd;">`;

        bodyHtml += `<div style="height: 140px; width: 100%; position: relative;">
                        <canvas id="popup-chart-severity"></canvas>
                     </div>`;
        
        bodyHtml += `<div style="height: 140px; width: 100%; position: relative; margin-top: 10px;">
                        <canvas id="popup-chart-gender"></canvas>
                     </div>`;

        const persenLokal = props.persen_lokal || 0;
        bodyHtml += `<div style="margin-top: 12px; text-align: center; background: #f5f7fb; padding: 8px; border-radius: 6px; border: 1px solid #e0e0e0;">
                        <div style="font-size: 11px; color: #555; font-weight: 600;">Persentase Orang Lokal Terlibat Kejadian Kecelakaan</div>
                        <div style="font-size: 20px; font-weight: 800; color: #1a237e; line-height: 1.2;">
                            ${persenLokal}%
                        </div>
                        <div style="font-size: 11px; color: #555; font-weight: 600;">Berdasarkan Plat Nomor Kendaraan</div>
                     </div>`;

        setTimeout(() => {
            renderAdministrasiCharts(props);
        }, 150);

    } else {
        for (const key in ALIAS_MAP) {
            if (props.hasOwnProperty(key)) {
                let rawVal = props[key];
                let displayVal = esc(rawVal);
                
                let labelStyle = '';
                let valueStyle = '';
                if (key === 'level') {
                    const numVal = parseInt(rawVal, 10);
                    if (!isNaN(numVal) && numVal >= 3) {
                        labelStyle = 'color: #d32f2f;';
                        valueStyle = 'color: #d32f2f; font-weight: 800;';
                    }
                }

                bodyHtml += `<div><strong style="${labelStyle}">${esc(ALIAS_MAP[key])}:</strong> <span style="${valueStyle}">${displayVal}</span></div>`;
            }
        }
    }

    if (bodyHtml === '') bodyHtml = '<div>Tidak ada informasi detail.</div>';
    
    const popupHtml = `<div class="popup-header">${esc(title)}</div><div class="popup-body">${bodyHtml}</div>`;
    
    popup.setLngLat(coordinates).setHTML(popupHtml).addTo(map);

    popup.once('close', () => {
        cleanupPC();
    });
}

function renderAdministrasiCharts(props) {
    const firaFont = "'Fira Sans', sans-serif";
    
    const severityMap = { 'MD': 'Meninggal Dunia', 'LB': 'Luka Berat', 'LR': 'Luka Ringan' };
    const genderMap = { 'L': 'Laki-laki', 'P': 'Perempuan' };

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { 
                    color: '#333', 
                    boxWidth: 10, 
                    font: { family: firaFont, size: 10 } 
                }
            },
            title: {
                display: true,
                color: '#1a237e',
                font: { family: firaFont, size: 12, weight: 'bold' }
            },
            tooltip: {
                bodyColor: '#fff',
                titleColor: '#fff',
                backgroundColor: 'rgba(0,0,0,0.85)',
                titleFont: { family: firaFont, size: 12, weight: 'bold' },
                bodyFont: { family: firaFont, size: 11 },
                padding: 10,
                cornerRadius: 4,
                displayColors: true
            }
        }
    };

    const ctxSev = document.getElementById('popup-chart-severity');
    if (ctxSev) {
        new Chart(ctxSev, {
            type: 'bar',
            data: {
                labels: ['MD', 'LB', 'LR'],
                datasets: [{
                    label: 'Jumlah Korban',
                    data: [
                        parseInt(props.jumlah_md || 0), 
                        parseInt(props.jumlah_lb || 0), 
                        parseInt(props.jumlah_lr || 0)
                    ],
                    backgroundColor: ['#d32f2f', '#ff9800', '#fbc02d'],
                    borderWidth: 0,
                    barPercentage: 0.7
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    legend: { display: false },
                    title: { 
                        ...commonOptions.plugins.title, 
                        text: 'Tingkat Keparahan Korban' 
                    },
                    tooltip: {
                        ...commonOptions.plugins.tooltip,
                        callbacks: {
                            title: function(tooltipItems) {
                                const label = tooltipItems[0].label;
                                return severityMap[label] || label; 
                            },
                            label: function(context) {
                                return `Jumlah: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    x: { 
                        ticks: { 
                            color: '#555', 
                            font: { family: firaFont, size: 10, weight: '600' } 
                        }, 
                        grid: { display: false } 
                    },
                    y: { 
                        ticks: { 
                            color: '#555', 
                            font: { family: firaFont, size: 10 }, 
                            precision: 0 
                        }, 
                        grid: { color: '#eee' } 
                    }
                }
            }
        });
    }

    const ctxGen = document.getElementById('popup-chart-gender');
    if (ctxGen) {
        const lk = parseInt(props.korban_l || 0);
        const pr = parseInt(props.korban_p || 0);
        
        new Chart(ctxGen, {
            type: 'doughnut',
            data: {
                labels: ['L', 'P'],
                datasets: [{
                    data: [lk, pr],
                    backgroundColor: ['#1976d2', '#ec407a'],
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                ...commonOptions,
                cutout: '55%',
                scales: { x: { display: false }, y: { display: false } },
                plugins: {
                    ...commonOptions.plugins,
                    legend: { 
                        position: 'right', 
                        labels: { 
                            color: '#333', 
                            boxWidth: 10, 
                            font: { family: firaFont, size: 11, weight: '600' },
                            padding: 15
                        } 
                    },
                    title: { 
                        ...commonOptions.plugins.title,
                        text: 'Jenis Kelamin Korban', 
                        position: 'left' 
                    },
                    tooltip: {
                        ...commonOptions.plugins.tooltip,
                        callbacks: {
                            title: function(tooltipItems) {
                                const label = tooltipItems[0].label;
                                return genderMap[label] || label;
                            },
                            label: function(context) {
                                return ` Jumlah: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    }
}

function checkRouteForHazards(routeFeature) {
    const hazardReasons = new Set();

    const troublespots = filteredLayerData.troublespot?.features || [];
    for (const spot of troublespots) {
        
        const distanceToRoute = turf.pointToLineDistance(spot, routeFeature, { units: 'meters' });

        if (distanceToRoute < 15) { 
            const keter = (spot.properties?.keterangan ?? spot.properties?.Keterangan ?? spot.properties?.jenis_gangguan ?? spot.properties?.ket ?? '').toLowerCase().trim();
            
            if (keter === 'jalan ditutup') {
                hazardReasons.add("Jalan Ditutup");
            } else if (keter === 'kecelakaan') {
                hazardReasons.add("Kecelakaan");
            }
        }
    }

    const trafficLines = filteredLayerData.traffic?.features || [];
    for (const line of trafficLines) {
        const level = Number(line.properties?.level ?? line.properties?.Level ?? line.properties?.tingkat_kepadatan ?? 0);

        if (level === 5) {
            if (turf.booleanIntersects(routeFeature, line)) {
                hazardReasons.add("Perlambatan Parah");
            }
        }
    }

    return Array.from(hazardReasons);
}

function formatDuration(seconds) {
    if (isNaN(seconds) || seconds < 0) return '- mnt';
    
    const totalMinutes = Math.round(seconds / 60);

    if (totalMinutes < 60) {
        return `${totalMinutes} mnt`;
    } else {
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;
        
        if (minutes === 0) {
            return `${hours} jam`;
        } else {
            return `${hours} jam ${minutes} mnt`;
        }
    }
}

function calculateLinearRegressionSlope(data) {
  if (!data || data.length < 2) return 0;

  const n = data.length;
  let sumX = 0;
  let sumY = 0;
  let sumXY = 0;
  let sumX2 = 0;

  for (const p of data) {
    sumX += p.x;
    sumY += p.y;
    sumXY += p.x * p.y;
    sumX2 += p.x * p.x;
  }

  const denominator = (n * sumX2) - (sumX * sumX);
  if (denominator === 0) return 0;

  const slope = ((n * sumXY) - (sumX * sumY)) / denominator;
  return slope;
}

function animateRiskSpotlight(timestamp) {
  if (!isRiskAnimationRunning) return; 

  if (!riskAnimationStartTime) riskAnimationStartTime = timestamp;
  const elapsed = timestamp - riskAnimationStartTime;
  
  const pulse = (Math.sin(elapsed / 500) + 1) / 2;
  const opacity = 0.4 + pulse * 0.6; 
  const width = 4 + pulse * 4;

  try {
    if (map.getLayer('prediksi_risiko-spotlight')) {
      map.setPaintProperty('prediksi_risiko-spotlight', 'line-opacity', opacity);
      map.setPaintProperty('prediksi_risiko-spotlight', 'line-width', width);
    }
  } catch (e) {
  }
  requestAnimationFrame(animateRiskSpotlight);
}

function animateSevereTraffic(timestamp) {
    if (!isLevel4AnimationRunning) return;

    if (!level4AnimationStartTime) level4AnimationStartTime = timestamp;
    const elapsed = timestamp - level4AnimationStartTime;
    
    const pulse = (Math.sin(elapsed / 300) + 1) / 2; 
    const width = 4 + pulse * 6;
    const opacity = 0.5 + pulse * 0.5;

    try {
        if (map.getLayer('traffic-level4-highlight')) {
            map.setPaintProperty('traffic-level4-highlight', 'line-width', width);
            map.setPaintProperty('traffic-level4-highlight', 'line-opacity', opacity);
        }
    } catch (e) {}
    
    requestAnimationFrame(animateSevereTraffic);
}

function animateExclamationMarkers(timestamp) {
  if (!isExclamationAnimationRunning) return;

  if (!exclamationAnimationStartTime) exclamationAnimationStartTime = timestamp;
  const elapsed = timestamp - exclamationAnimationStartTime;
  const pulse = (Math.sin(elapsed / 400) + 1) / 2;
  const scale = 0.8 + pulse * 0.4;
  const opacity = 0.7 + pulse * 0.3;

riskExclamationMarkers.forEach(marker => {
    const outerEl = marker.getElement();
    const innerEl = outerEl.querySelector('.risk-exclamation-icon');

    if (innerEl) {
      innerEl.style.transform = `scale(${scale})`;
      innerEl.style.opacity = opacity;
    }
  });

  requestAnimationFrame(animateExclamationMarkers);
}

function checkAndNotifySevereTraffic(geojson) {
    const alertEl = document.getElementById('severe-traffic-alert');
    const btnDetails = document.getElementById('btn-severe-details');
    const minBtn = document.getElementById('btn-severe-minimize');
    const muteBtn = document.getElementById('btn-severe-mute');
    const audioEl = document.getElementById('severe-alert-sound');
    
    alertEl.style.display = 'none';
    alertEl.classList.remove('minimized');
    isLevel4AnimationRunning = false;
    
    if(audioEl) {
        audioEl.pause();
        audioEl.currentTime = 0;
        audioEl.volume = 1.0; 
        
        audioEl.onerror = () => {
            console.error("ERROR AUDIO: File 'traffic-alert.mp3' tidak ditemukan atau format tidak didukung.");
            console.error("Pastikan file mp3 ada di folder yang sama dengan file HTML dan server berjalan.");
        };
    }
    
    if (!geojson || !geojson.features) return;

    const severeFeatures = geojson.features.filter(f => {
        const lvl = parseInt(f.properties.level || f.properties.Level || 0);
        return lvl === 4;
    });

    if (severeFeatures.length > 0) {
        alertEl.style.display = 'flex';
        
        const playSoundSmart = () => {
            if (!audioEl) return;
            if (!isAlertMuted && !alertEl.classList.contains('minimized')) {
                const playPromise = audioEl.play();
                
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        console.log("Audio sedang berputar.");
                    }).catch(error => {
                        console.warn("Autoplay diblokir. Menunggu klik user...");
                        const onUserInteract = () => {
                            if (!isAlertMuted && !alertEl.classList.contains('minimized')) {
                                audioEl.play().catch(e => console.error("Masih gagal play:", e));
                            }
                            document.removeEventListener('click', onUserInteract);
                            document.removeEventListener('keydown', onUserInteract);
                        };
                        document.addEventListener('click', onUserInteract);
                        document.addEventListener('keydown', onUserInteract);
                    });
                }
            } else {
                audioEl.pause();
                audioEl.currentTime = 0;
            }
        };
        playSoundSmart();

        if (!isLevel4AnimationRunning) {
            isLevel4AnimationRunning = true;
            level4AnimationStartTime = 0;
            requestAnimationFrame(animateSevereTraffic);
        }
        btnDetails.onclick = () => showSevereTrafficModal(severeFeatures);

        if (muteBtn) {
            muteBtn.textContent = isAlertMuted ? '🔇' : '🔊';
            muteBtn.title = isAlertMuted ? 'Hidupkan Suara' : 'Matikan Suara';
            muteBtn.onclick = (e) => {
                e.stopPropagation(); 
                isAlertMuted = !isAlertMuted; 
                muteBtn.textContent = isAlertMuted ? '🔇' : '🔊';
                muteBtn.title = isAlertMuted ? 'Hidupkan Suara' : 'Matikan Suara';
                if (isAlertMuted) {
                    audioEl.pause();
                    audioEl.currentTime = 0;
                } else {
                    playSoundSmart();
                }
            };
        }

        if(minBtn) {
            minBtn.onclick = (e) => {
                e.stopPropagation();
                alertEl.classList.add('minimized');
                if(audioEl) {
                    audioEl.pause(); 
                    audioEl.currentTime = 0;
                }
            };
        }

        alertEl.onclick = (e) => {
            if (e.target.tagName === 'BUTTON') return; 
            if(alertEl.classList.contains('minimized')) {
                alertEl.classList.remove('minimized');
                if (!isAlertMuted) playSoundSmart();
            }
        };
    }
}

function showSevereTrafficModal(features) {
    const modal = document.getElementById('severe-modal-overlay');
    const countEl = document.getElementById('severe-count-val');
    const listEl = document.getElementById('severe-street-list');
    
    countEl.textContent = features.length;
    listEl.innerHTML = '';
    
    const streetData = {};

    features.forEach(f => {
        let rawName = f.properties.Jalan || 
                      f.properties.jalan || 
                      f.properties.street || 
                      f.properties.nama_jalan || 
                      f.properties.Nm_Ruas || 
                      'Jalan Tanpa Nama';
        const cleanName = rawName.toString().trim();
        
        if (!streetData[cleanName]) {
            streetData[cleanName] = {
                count: 0,
                feature: f 
            };
        }
        streetData[cleanName].count++;
    });

    const sortedStreets = Object.entries(streetData).sort((a, b) => b[1].count - a[1].count);

    sortedStreets.forEach(([name, data]) => {
        const li = document.createElement('li');
        li.style.cursor = 'pointer'; 
        li.title = "Klik untuk zoom ke lokasi";

        li.innerHTML = `
            <div class="street-name">${name}</div>
            <div class="street-count">${data.count} Titik</div>
        `;
        
        li.addEventListener('click', () => {
            modal.style.display = 'none';

            const targetFeature = data.feature;

            if (targetFeature) {
                try {
                    const centerPoint = turf.centroid(targetFeature);
                    const coords = centerPoint.geometry.coordinates;
                    map.flyTo({
                        center: coords,
                        zoom: 17,
                        speed: 1.5,
                        curve: 1
                    });
                    setTimeout(() => {
                        showFeaturePopup(map, targetFeature, coords, 'traffic');
                    }, 1200);

                } catch (e) {
                    console.error("Gagal mendapatkan koordinat untuk zoom:", e);
                }
            }
        });

        listEl.appendChild(li);
    });

    modal.style.display = 'flex';
}

document.getElementById('severe-modal-close').addEventListener('click', () => {
    document.getElementById('severe-modal-overlay').style.display = 'none';
});
document.getElementById('severe-modal-overlay').addEventListener('click', (e) => {
    if (e.target.id === 'severe-modal-overlay') {
        document.getElementById('severe-modal-overlay').style.display = 'none';
    }
});

function createRiskMarker(coordinates, properties) {
  try {
    if (!coordinates || isNaN(coordinates[0]) || isNaN(coordinates[1])) {
      console.warn('Koordinat marker tidak valid (NaN):', coordinates);
      return;
    }

    const el = document.createElement('div');
    el.className = 'risk-exclamation-container';
    el.style.display = 'none';
    el.style.cursor = 'pointer';
    
    const innerEl = document.createElement('div');
    innerEl.className = 'risk-exclamation-icon';
    innerEl.style.backgroundImage = `url("{{ asset('satupeta_webgis') }}/assets/alert.png")`; 
    innerEl.style.width = '30px';
    innerEl.style.height = '30px';
    innerEl.style.backgroundSize = 'contain';
    innerEl.style.backgroundRepeat = 'no-repeat';
    innerEl.style.backgroundPosition = 'center';
    el.appendChild(innerEl);
    
    const marker = new maplibregl.Marker({ element: el, anchor: 'bottom' })
      .setLngLat(coordinates)
      .addTo(map);

    if (typeof riskExclamationMarkers !== 'undefined') {
        riskExclamationMarkers.push(marker);
    }
    
    el.addEventListener('click', (e) => {
        e.stopPropagation(); 
        e.preventDefault();

        console.log("Marker Risiko Diklik!");
        if (typeof popup !== 'undefined') {
            popup
            .setLngLat(coordinates)
            .setHTML(`
              <div style="text-align: center; padding: 5px;">
                  <div style="color: #d32f2f; font-weight: 800; font-size: 14px; margin-bottom: 6px; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 4px;">
                    ⚠️ Peringatan
                  </div>
                  <div style="font-size: 13px; line-height: 1.4; color: #333; font-weight: 600;">
                    Prediksi risiko kecelakaan di ruas jalan ini tinggi, harap berhati-hati jika melintas.
                  </div>
              </div>
            `)
            .addTo(map);
        } else {
            console.error("Variabel global 'popup' tidak ditemukan.");
        }
    });
    
  } catch (e) {
    console.error('Gagal membuat marker risiko:', e, coordinates);
  }
}

function initSearchAndRouting() {
    const GOOGLE_API_KEY = 'AIzaSyAOxkFGWCdGjrutgQmjJeFg2Av9k5Dqsgk';

    function uiModeToGoogleTravelMode(uiMode) {
      if (uiMode === 'car') return 'DRIVE';
      if (uiMode === 'two_wheeler') return 'TWO_WHEELER';
      if (uiMode === 'foot') return 'WALK';
      return 'DRIVE';
    }

    let extraInputs = [];     
    let extraCoords = [];
    let extraMarkers = [];
    let latestIntermediates = [];
    let latestFinalDest = null;

    function addExtraDestField() {
      const idx = extraInputs.length;
      const wrap = document.createElement('div');
      wrap.className = 'field-wrap';
      wrap.style.display = 'flex';
      wrap.style.gap = '6px';
      wrap.style.alignItems = 'center';

      const input = document.createElement('input');
      input.placeholder = `Lokasi Tujuan Tambahan ${idx + 1}`;
      input.style.flex = '1';

      const resultsEl = document.createElement('div');
      resultsEl.className = 'dropdown-results';

      const delBtn = document.createElement('button');
      delBtn.className = 'btn';
      delBtn.title = 'Hapus';
      delBtn.textContent = '✕';
      delBtn.style.flex = '0 0 auto';
      delBtn.style.background = '#fbe9e7';
      delBtn.style.color = '#c62828';

      delBtn.onclick = () => removeExtraDest(idx);

      wrap.appendChild(input);
      wrap.appendChild(delBtn);
      wrap.appendChild(resultsEl);
      document.getElementById('extra-dests').appendChild(wrap);

      extraInputs.push({ wrap, input, resultsEl, delBtn });
      extraCoords.push(null);
      extraMarkers.push(null);

      attachCombinedSearch(input, resultsEl, (pickedItem) => {
        setExtra(idx, pickedItem.coord, pickedItem.label);
      });

      input.addEventListener('focus', () => activeField = `extra:${idx}`);
      
      input.addEventListener('change', () => {
        const c = parseLatLng(input.value);
        if (c) setExtra(idx, c, input.value);
      });
    }
    const btnAddDest = document.getElementById('btn-add-dest');
    btnAddDest.addEventListener('click', (e) => {
      e.preventDefault();
      addExtraDestField();
    });

    function setExtra(i, coord, label) {
      extraCoords[i] = coord;
      const { input } = extraInputs[i];
      input.value = label || `${coord[1].toFixed(5)},${coord[0].toFixed(5)}`;

      const old = extraMarkers[i];
      if (old) old.remove();

      extraMarkers[i] = new maplibregl.Marker({ color: '#ffd54f' })
        .setLngLat(coord)
        .addTo(map);
    }

    function removeExtraDest(i) {
      const { wrap } = extraInputs[i];
      if (wrap && wrap.parentNode) wrap.parentNode.removeChild(wrap);

      if (extraMarkers[i]) extraMarkers[i].remove();

      extraInputs.splice(i, 1);
      extraCoords.splice(i, 1);
      extraMarkers.splice(i, 1);

      extraInputs.forEach((it, k) => {
        it.input.placeholder = `Lokasi Tujuan Tambahan ${k + 1}`;
        it.delBtn.onclick = () => removeExtraDest(k);
        it.input.onfocus = () => activeField = `extra:${k}`;
      });
    }

    async function fetchGoogleRoutes(fromCoord, destCoord, uiMode, wantAlternatives = true, intermediates = []) {
      const travelMode = uiModeToGoogleTravelMode(uiMode);

      const intermediateWaypoints = (intermediates || [])
        .filter(Boolean)
        .map(([lng, lat]) => ({ location: { latLng: { latitude: lat, longitude: lng } } }));

      const body = {
        origin:      { location: { latLng: { latitude: fromCoord[1], longitude: fromCoord[0] } } },
        destination: { location: { latLng: { latitude: destCoord[1],  longitude: destCoord[0]  } } },
        travelMode,
        routingPreference: travelMode === 'DRIVE' ? 'TRAFFIC_AWARE_OPTIMAL' : 'ROUTING_PREFERENCE_UNSPECIFIED',
        computeAlternativeRoutes: wantAlternatives,
        units: 'METRIC',
        intermediates: intermediateWaypoints
      };

      const res = await fetch('https://routes.googleapis.com/directions/v2:computeRoutes', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Goog-Api-Key': GOOGLE_API_KEY,
          'X-Goog-FieldMask': [
            'routes.distanceMeters',
            'routes.duration',
            'routes.polyline.encodedPolyline',
            'routes.legs.steps.navigationInstruction',
            'routes.legs.steps.distanceMeters'
          ].join(',')
        },
        body: JSON.stringify(body)
      });
      if (!res.ok) throw new Error(`Google Routes error ${res.status}`);
      const json = await res.json();

      const routes = (json.routes || []).map(r => {
        const coords = decodePolyline5(r.polyline.encodedPolyline);

        const allSteps = [];
        (r.legs || []).forEach(leg => {
          (leg.steps || []).forEach(s => {
            allSteps.push({
              instruction: s.navigationInstruction?.instructions || '',
              distance: s.distanceMeters || 0
            });
          });
        });

        return {
          type: 'Feature',
          geometry: { type: 'LineString', coordinates: coords },
          properties: {
            summary: {
              distance: r.distanceMeters || 0,
              duration: parseISODurationToSeconds(r.duration)
            },
            segments: [{ steps: allSteps }]
          }
        };
      });

      return routes;
    }

    async function fetchGoogleRoutesWithFallback(fromCoord, destCoord, uiMode, intermediates = []) {
      return await fetchGoogleRoutes(fromCoord, destCoord, uiMode, true, intermediates);
    }

    function parseISODurationToSeconds(dur) {
      if (!dur) return 0;
      if (/^\d+s$/.test(dur)) return parseInt(dur, 10);
      const m = dur.match(/P(T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?)?/);
      if (!m) return 0;
      const h = parseInt(m[2] || '0', 10), min = parseInt(m[3] || '0', 10), s = parseInt(m[4] || '0', 10);
      return h * 3600 + min * 60 + s;
    }

    const searchInput = document.getElementById('search-input');
    const fromInput = document.getElementById('from-input');
    const toInput = document.getElementById('to-input');
    const searchResultsEl = document.getElementById('search-results');
    const fromResultsEl = document.getElementById('from-results');
    const toResultsEl = document.getElementById('to-results');
    const routeControls = document.getElementById('route-controls');
    const btnToggleRoute = document.getElementById('btn-toggle-route');
    const clickPickToggle = document.getElementById('click-pick-toggle');
    const modeTabs = document.querySelectorAll('.mode-tab-btn');
    const routeBtn = document.getElementById('btn-route');
    const clearBtn = document.getElementById('btn-clear-route');
    const routeResultsBody = document.getElementById('route-results-body');
    const btnCollapse = document.getElementById('btn-collapse-route');
    const alertPopup = document.getElementById('route-alert-popup');
    const btnSwap = document.getElementById('btn-swap-route');

    btnSwap.addEventListener('click', () => {
      const tempCoord = fromCoord;
      fromCoord = toCoord;
      toCoord = tempCoord;

      const tempValue = fromInput.value;
      fromInput.value = toInput.value;
      toInput.value = tempValue;

      if (fromMarker) fromMarker.remove();
      if (toMarker) toMarker.remove();

      if (fromCoord) {
        fromMarker = new maplibregl.Marker({ color: '#00ff66' }).setLngLat(fromCoord).addTo(map);
      } else {
        fromMarker = null;
      }

      if (toCoord) {
        toMarker = new maplibregl.Marker({ color: '#ff3333' }).setLngLat(toCoord).addTo(map);
      } else {
        toMarker = null;
      }
    });

    let fromCoord = null, toCoord = null;
    let fromMarker = null, toMarker = null;
    let activeField = null;
    let searchTimeout;
    let fuse;
    let searchResultMarker = null;
    const routeSourceId = 'route-src';
    const routeLayerId = 'route-line';
    let routeResults = [];
    
    window.updateSearchIndex = () => {
        const searchConfig = {
            'cctv': ['nama_cctv', 'nama_jalan'],
            'blackspot': ['Nama', 'Nm_Ruas', 'Ket'],
            'objek_vital': ['Nama', 'Keterangan'],  
            'troublespot': ['keterangan', 'street'],
            'patrol': ['polda', 'polres'],
        };
        let searchableData = [];
        for (const [layerKey, propKeys] of Object.entries(searchConfig)) {
            if (filteredLayerData[layerKey] && filteredLayerData[layerKey].features) {
                filteredLayerData[layerKey].features.forEach(feature => {
                    let searchText = propKeys.map(key => feature.properties[key] || '').join(' ');
                    searchableData.push({ label: searchText.trim(), layerKey: layerKey, feature: feature });
                });
            }
        }
        fuse = new Fuse(searchableData, { keys: ['label'], includeScore: true, threshold: 0.4, minMatchCharLength: 3 });
    };

    async function geocode(q) {
        const headers = { 'User-Agent': 'SmartTrafficApp/1.0 (contact@example.com)' };
        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&countrycodes=ID&limit=5`, { headers });
            if (!res.ok) {
                console.error(`Nominatim request failed with status: ${res.status}`);
                return [];
            }
            const jsonData = await res.json();
            if (Array.isArray(jsonData)) {
                return jsonData.map(p => ({ label: p.display_name, coord: [+p.lon, +p.lat] }));
            } else {
                console.warn('Nominatim did not return an array:', jsonData);
                return [];
            }
        } catch (err) {
            console.error("Geocoding failed:", err);
            return [];
        }
    }

    function attachCombinedSearch(inputEl, resultsEl, onPickCallback) {
        inputEl.addEventListener('input', e => {
            const query = e.target.value.trim();
            clearTimeout(searchTimeout);

            if (query.length < 3) {
                resultsEl.style.display = 'none';
                return;
            }
            resultsEl.style.display = 'block';
            resultsEl.innerHTML = '<div class="result-item" style="color:#888;">Mencari...</div>';

            searchTimeout = setTimeout(async () => {
                const localResultsPromise = fuse ? Promise.resolve(fuse.search(query).slice(0, 5)) : Promise.resolve([]);
                const globalResultsPromise = geocode(query);
                const [localResultsRaw, globalResultsRaw] = await Promise.all([localResultsPromise, globalResultsPromise]);

                const localResults = localResultsRaw.map(res => ({
                    label: res.item.label, type: 'Lokal', feature: res.item.feature, score: res.score
                }));
                const globalResults = globalResultsRaw.map(res => ({
                    label: res.label, type: 'Global', coord: res.coord, score: 0.5
                }));
                
                const combinedResults = [...localResults, ...globalResults]
                    .sort((a, b) => a.score - b.score)
                    .slice(0, 7);

                resultsEl.innerHTML = '';
                if (!combinedResults.length) {
                    resultsEl.innerHTML = '<div class="result-item" style="color:#888;">Tidak ada hasil.</div>';
                    return;
                }

                combinedResults.forEach(item => {
                    const d = document.createElement('div');
                    d.className = 'result-item';
                    const typeLabel = item.type === 'Lokal' ? `<span style="background:#e3f2fd; color:#1e88e5; font-size:10px; padding:2px 4px; border-radius:4px; font-weight:bold;">FITUR PETA</span>` : `<span style="background:#e8f5e9; color:#43a047; font-size:10px; padding:2px 4px; border-radius:4px; font-weight:bold;">LOKASI</span>`;
                    d.innerHTML = `${item.label} <div style="margin-top:4px;">${typeLabel}</div>`;
                    
                    d.onclick = () => {
                        resultsEl.style.display = 'none';
                        inputEl.value = item.label;
                        
                        let pickedItemData = { label: item.label };
                        if (item.type === 'Lokal') {
                            pickedItemData.coord = turf.centroid(item.feature).geometry.coordinates;
                            pickedItemData.feature = item.feature;
                        } else {
                            pickedItemData.coord = item.coord;
                        }
                        onPickCallback(pickedItemData);
                    };
                    resultsEl.appendChild(d);
                });
            }, 400);
        });
    }

    attachCombinedSearch(searchInput, searchResultsEl, (pickedItem) => {
        if (searchResultMarker) {
            searchResultMarker.remove();
        }
        
        map.flyTo({ center: pickedItem.coord, zoom: 17 });
        
        searchResultMarker = new maplibregl.Marker({ color: '#ffc107' })
            .setLngLat(pickedItem.coord)
            .addTo(map);

        if (pickedItem.feature) {
            setTimeout(() => showFeaturePopup(map, pickedItem.feature, pickedItem.coord), 800);
        }
    });

    attachCombinedSearch(fromInput, fromResultsEl, (pickedItem) => {
        setFrom(pickedItem.coord, pickedItem.label);
    });

    attachCombinedSearch(toInput, toResultsEl, (pickedItem) => {
        setTo(pickedItem.coord, pickedItem.label);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#search-routing-panel')) {
            searchResultsEl.style.display = 'none';
            fromResultsEl.style.display = 'none';
            toResultsEl.style.display = 'none';
        }
    });
    
    btnToggleRoute.addEventListener('click', () => {
        routeControls.classList.toggle('minimized');
        btnToggleRoute.textContent = routeControls.classList.contains('minimized') ? 'Buka' : 'Tutup';
    });

    document.getElementById('route-header').addEventListener('click', (e) => {
        if (e.target !== btnToggleRoute) btnToggleRoute.click();
    });

    modeTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            modeTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
        });
    });

    function setFrom(c, l) {
        fromCoord = c;
        fromInput.value = l || `${c[1].toFixed(5)},${c[0].toFixed(5)}`;
        if (fromMarker) fromMarker.remove();
        fromMarker = new maplibregl.Marker({ color: '#00ff66' }).setLngLat(c).addTo(map);
    }
    function setTo(c, l) {
        toCoord = c;
        toInput.value = l || `${c[1].toFixed(5)},${c[0].toFixed(5)}`;
        if (toMarker) toMarker.remove();
        toMarker = new maplibregl.Marker({ color: '#ff3333' }).setLngLat(c).addTo(map);
    }
    
    fromInput.addEventListener('focus', () => activeField = 'from');
    toInput.addEventListener('focus', () => activeField = 'to');

    map.on('click', e => {
      if (!clickPickToggle.checked) return;
      const c = [e.lngLat.lng, e.lngLat.lat];

      if (activeField === 'from') {
        setFrom(c);
        activeField = 'to';
        return;
      }

      if (activeField === 'to') {
        setTo(c);
        return;
      }

      if (activeField && activeField.startsWith('extra:')) {
        const i = parseInt(activeField.split(':')[1], 10);
        setExtra(i, c);
        return;
      }

      if (!fromCoord) { setFrom(c); activeField = 'to'; return; }
      if (!toCoord)   { setTo(c); return; }

      const emptyIdx = extraCoords.findIndex(v => !v);
      if (emptyIdx !== -1) { setExtra(emptyIdx, c); return; }

      addExtraDestField();
      setExtra(extraCoords.length - 1, c);
    });

    function parseLatLng(t) {
        const m = t.match(/(-?\d+(\.\d+)?)[,\s]+(-?\d+(\.\d+)?)/);
        if (!m) return null;
        return [+m[3], +m[1]];
    }
    fromInput.addEventListener('change', () => { const c = parseLatLng(fromInput.value); if (c) setFrom(c); });
    toInput.addEventListener('change', () => { const c = parseLatLng(toInput.value); if (c) setTo(c); });

    function getRelevantAvoidanceCauses(startCoord, endCoord) {
        const causes = new Set();
        const featuresToAvoid = [];

        const routeLine = turf.lineString([startCoord, endCoord]);
        const routeBbox = turf.bbox(routeLine);
        const areaOfInterest = turf.buffer(turf.bboxPolygon(routeBbox), 2, { units: 'kilometers' });

        const tsFC = filteredLayerData?.troublespot || { features: [] };
        (tsFC.features || []).forEach(f => {
            if (!turf.booleanIntersects(f, areaOfInterest)) return;

            const keter = (f.properties?.keterangan || '').toLowerCase().trim();
            let causeText = null;
            if (keter === 'jalan ditutup') causeText = 'jalan ditutup';
            else if (keter === 'kecelakaan') causeText = 'Kecelakaan';

            if (causeText) {
                featuresToAvoid.push(f);
                causes.add(causeText);
            }
        });

        const trFC = filteredLayerData?.traffic || { features: [] };
        (trFC.features || []).forEach(f => {
            if (Number(f.properties?.level) === 5) {
                if (turf.booleanIntersects(f, areaOfInterest)) {
                    featuresToAvoid.push(f);
                    causes.add('perlambatan parah');
                }
            }
        });

        if (featuresToAvoid.length === 0) {
            return { avoidPolygons: null, causes: [] };
        }

        const polys = [];
        for (const feat of featuresToAvoid) {
            try {
                const buffered = turf.buffer(feat, 25, { units: 'meters' });
                if (buffered.geometry.type === 'Polygon') polys.push(buffered.geometry.coordinates);
                else if (buffered.geometry.type === 'MultiPolygon') polys.push(...buffered.geometry.coordinates);
            } catch {}
        }

        const avoidMultiPolygon = polys.length > 0 ? { type: 'MultiPolygon', coordinates: polys } : null;

        return {
            avoidPolygons: avoidMultiPolygon,
            causes: Array.from(causes)
        };
    }

    function estimateDelaySeconds(routeGeoJSON, mode) {
        if (mode === 'foot') return { seconds: 0, note: 'Pejalan kaki tidak terpengaruh perlambatan.' };
        const trFC = filteredLayerData?.traffic || { features: [] };
        let total = 0;
        for (const seg of (trFC.features || [])) {
            try {
                if ((turf.lineIntersect(routeGeoJSON, seg)?.features?.length || 0) > 0) {
                    total += Number(seg.properties?.delay) || 120;
                }
            } catch {}
        }
        return { seconds: total, note: 'Estimasi berdasarkan tingkat perlambatan di jalan.' };
    }
    
    function hasSevereTrafficOnRoute(routeGeoJSON) {
      const trFC = filteredLayerData?.traffic || { features: [] };
      for (const seg of (trFC.features || [])) {
        try {
          const lvl = Number(seg.properties?.level ?? seg.properties?.Level ?? seg.properties?.tingkat_kepadatan ?? 0);
          if (lvl === 5 && turf.booleanIntersects(routeGeoJSON, seg)) {
            return true;
          }
        } catch {}
      }
      return false;
    }

    function formatDelay(sec) {
        if (sec <= 0) return 'Tidak ada estimasi delay.';
        const m = Math.floor(sec / 60), s = sec % 60;
        return m > 0 ? `Estimasi Delay: ${m} menit ${s} detik` : `Estimasi Delay: ${s} detik`;
    }

    async function drawRoute() {
        if (!fromCoord || !toCoord) return alert('Isi lokasi awal dan akhir terlebih dahulu.');

        alertPopup.style.display = 'none';
        routeResults = [];
        document.getElementById('route-options-container').innerHTML = '';
        routeResultsBody.innerHTML = 'Menghitung rute...';
        document.getElementById('route-results').style.display = 'block';

      try {
        const activeModeTab = document.querySelector('.mode-tab-btn.active');
        const uiMode = activeModeTab ? activeModeTab.dataset.mode : 'car';

        const extras = extraCoords.filter(Boolean);

        let finalDest, intermediates;
        if (extras.length === 0) {
          finalDest = toCoord;
          intermediates = [];
        } else {
          finalDest = extras[extras.length - 1];
          intermediates = [toCoord, ...extras.slice(0, -1)];
        }

        latestFinalDest = finalDest;
        latestIntermediates = intermediates.slice();

        const googleRoutes = await fetchGoogleRoutes(fromCoord, finalDest, uiMode, true, intermediates);
        if (!googleRoutes.length) throw new Error('Tidak ada rute yang ditemukan.');

        routeResults = googleRoutes
          .map(feat => ({ type: 'smart', feature: feat }))
          .sort((a, b) => a.feature.properties.summary.duration - b.feature.properties.summary.duration);

        renderRouteOptions();
        await displayRoute(0);

        try {
          const fc = turf.featureCollection(routeResults.map(r => r.feature));
          const bbox = turf.bbox(fc);
          map.fitBounds(bbox, { padding: 60, maxZoom: 16 });
        } catch (e) {
          console.error('Gagal fitBounds rute:', e);
        }

      } catch (e) {
        console.error('Gagal mendapatkan rute (Google):', e);
        routeResultsBody.innerHTML = `<div style="color:#ffcdd2; padding: 4px;">Gagal menghitung rute: ${e.message}</div>`;
      }
    }

    function renderRouteOptions() {
        const container = document.getElementById('route-options-container');
        container.innerHTML = '';
        routeResults.forEach((taggedRoute, index) => { 
            const feat = taggedRoute.feature;
            const summary = feat.properties.summary;
            const distKm = (summary.distance / 1000).toFixed(1);
            const durationText = formatDuration(summary.duration);

            const btn = document.createElement('button');
            btn.className = 'route-option-btn';
            btn.dataset.index = index;
            btn.innerHTML = `<b>Opsi Rute ${index + 1}: ${durationText}</b> <span>(${distKm} km)</span>`;
            
            btn.onclick = () => {
                displayRoute(index);
            };

            container.appendChild(btn);
        });
    }

    async function displayRoute(selectedIndex) {
        if (!routeResults[selectedIndex]) return;

        const selectedTaggedRoute = routeResults[selectedIndex];
        const feat = selectedTaggedRoute.feature;

        if (map.getLayer(routeLayerId)) map.removeLayer(routeLayerId);
        if (map.getLayer('route-line-inactive')) map.removeLayer('route-line-inactive');
        if (map.getSource(routeSourceId)) map.removeSource(routeSourceId);

        const routeCollection = {
            type: 'FeatureCollection',
            features: routeResults.map((taggedRoute, index) => ({
                ...taggedRoute.feature,
                properties: { ...taggedRoute.feature.properties, route_index: index }
            }))
        };
        
        map.addSource(routeSourceId, { type: 'geojson', data: routeCollection });
        let layerDiAtasRute = null;
        if (map.getLayer('traffic-outline')) {
            layerDiAtasRute = 'traffic-outline';
        } else if (map.getLayer('traffic-line')) {
            layerDiAtasRute = 'traffic-line';
        } 
        else if (map.getLayer('blackspot-symbol')) {
             layerDiAtasRute = 'blackspot-symbol';
        }
        map.addLayer({
            id: 'route-line-inactive', type: 'line', source: routeSourceId,
            paint: { 'line-width': 4, 'line-color': '#2196f3', 'line-opacity': 0.3 },
            filter: ['!=', 'route_index', selectedIndex]
        }, layerDiAtasRute);
        map.addLayer({
            id: routeLayerId, type: 'line', source: routeSourceId,
            paint: { 'line-width': 6, 'line-color': '#2196f3' },
            filter: ['==', 'route_index', selectedIndex]
        }, layerDiAtasRute);

        document.querySelectorAll('.route-option-btn').forEach(btn => {
            btn.classList.toggle('active', parseInt(btn.dataset.index) === selectedIndex);
        });

        const uiMode = document.querySelector('.mode-tab-btn.active')?.dataset.mode || 'car';
        const googleRoutes = await fetchGoogleRoutesWithFallback(fromCoord, latestFinalDest || toCoord, uiMode, latestIntermediates || []);
        if (!googleRoutes.length) throw new Error('Tidak ada rute yang ditemukan.');
        routeResults = googleRoutes
          .map(feat => ({ type: 'smart', feature: feat }))
          .sort((a, b) => a.feature.properties.summary.duration - b.feature.properties.summary.duration);
          if (googleRoutes._fallbackInfo) {
          alertPopup.innerHTML = `<span>${googleRoutes._fallbackInfo}. Peta rute ditunjukkan sebagai rute pejalan kaki.</span>
            <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>`;
          alertPopup.style.display = 'block';
        }
        const distKm = (feat.properties.summary.distance / 1000).toFixed(2);
        const durationText = formatDuration(feat.properties.summary.duration);

        const routeGeo = { type: 'Feature', geometry: feat.geometry };
        const delayInfo = estimateDelaySeconds(routeGeo, uiMode);
        const delayText = formatDelay(delayInfo.seconds);

        let finalMessage;
        if (uiMode === 'foot') {
          finalMessage = `<b>${delayText}</b><br><small>${delayInfo.note || 'Pejalan kaki tidak terkena delay.'}</small>`;
        } else {
          finalMessage = `<b>${delayText}</b><br><small>Estimasi berdasarkan tingkat perlambatan di jalan.</small>`;
        }
                
        let html = `<b>Jarak:</b> ${distKm} km<br><b>Waktu Tempuh:</b> ${durationText}<br>`;
        html += finalMessage;
        html += `<hr><b>Petunjuk arah:</b><br>`;
        function stripHtml(t=''){ return t.replace(/<[^>]*>/g,''); }

        (feat.properties.segments[0]?.steps || []).forEach((s, i) => {
          const distText = s.distance > 0 ? ` (${(s.distance/1000).toFixed(2)} km)` : '';
          html += `${i + 1}. ${stripHtml(s.instruction)}${distText}<br>`;
        });
        routeResultsBody.innerHTML = html;

        const tsHazards = getTroublespotHazardsOnRoute(feat);
        const isFoot = (uiMode === 'foot'); 
        let contentHtml = '';
        if (!isFoot && tsHazards.length > 0) {
           const tsMsg = buildWarningSentence(tsHazards);
           if (tsMsg) {
               contentHtml += `<div>${tsMsg}</div>`;
           }
        }
        const geoHazards = getGeofenceHazardsOnRoute(feat);
        if (!isFoot && geoHazards.length > 0) {
           const geoMsg = buildComplexGeofenceMessage(geoHazards);
           if (geoMsg) {
               if (contentHtml !== '') {
                   contentHtml += `<hr style="margin: 8px 0; border: 0; border-top: 1px solid rgba(133, 100, 4, 0.4);">`;
               }
               contentHtml += `<div style="margin-top:2px; line-height:1.4;">${geoMsg}</div>`;
           }
        }
        if (contentHtml !== '') {
          alertPopup.innerHTML = `
            <div style="padding-right: 15px;">${contentHtml}</div>
            <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
          `;
          alertPopup.style.display = 'block';
        } else {
          alertPopup.style.display = 'none';
        }
    }
    const TROUBLESPOT_NEAR_METERS = 15;
    function categorizeTroublespot(k) {
      const v = (k || '').toLowerCase().trim();
      if (v === 'jalan ditutup') return 'Jalan Ditutup';
      if (v === 'kecelakaan')    return 'Kecelakaan';
      return 'Gangguan Jalan';
    }

    function buildWarningSentence(labels) {
      const uniq = Array.from(new Set(labels)).filter(Boolean);
      if (uniq.length === 0) return '';

      let isiKurung;
      if (uniq.length === 1) {
        isiKurung = uniq[0];
      } else if (uniq.length === 2) {
        isiKurung = `${uniq[0]} dan ${uniq[1]}`;
      } else {
        const head = uniq.slice(0, -1).join(', ');
        const last = uniq[uniq.length - 1];
        isiKurung = `${head} dan ${last}`;
      }

      return `Terdapat ${isiKurung} Pada Rute Ini`;
    }

    function getTroublespotHazardsOnRoute(routeFeature) {
      const hazards = [];
      const ts = filteredLayerData.troublespot?.features || [];
      for (const spot of ts) {
        const d = turf.pointToLineDistance(spot, routeFeature, { units: 'meters' });
        if (d <= TROUBLESPOT_NEAR_METERS) {
          const keter = spot.properties?.keterangan ?? spot.properties?.Keterangan ?? '';
          hazards.push(categorizeTroublespot(keter));
        }
      }
      return hazards;
    }
        
    routeBtn.onclick = drawRoute;
    clearBtn.onclick = () => {
        if (map.getLayer(routeLayerId)) map.removeLayer(routeLayerId);
        if (map.getLayer('route-line-inactive')) map.removeLayer('route-line-inactive');
        if (map.getSource(routeSourceId)) map.removeSource(routeSourceId);

        routeResultsBody.innerHTML = '';
        document.getElementById('route-options-container').innerHTML = '';
        document.getElementById('route-results').style.display = 'none';

        if (fromMarker) { fromMarker.remove(); fromMarker = null; }
        if (toMarker) { toMarker.remove(); toMarker = null; }
        if (searchResultMarker) { searchResultMarker.remove(); searchResultMarker = null; }

        searchInput.value = '';
        fromCoord = toCoord = null;
        fromInput.value = '';
        toInput.value = '';

        routeResults = [];
        alertPopup.style.display = 'none';

        const extraWrap = document.getElementById('extra-dests');
        if (extraWrap) extraWrap.innerHTML = '';

        if (typeof extraInputs !== 'undefined') extraInputs = [];
        if (typeof extraCoords  !== 'undefined') extraCoords  = [];
        if (typeof extraMarkers !== 'undefined') {
          extraMarkers.forEach(m => { try { m.remove(); } catch(e){} });
          extraMarkers = [];
        }

        if (typeof latestFinalDest       !== 'undefined') latestFinalDest = null;
        if (typeof latestIntermediates   !== 'undefined') latestIntermediates = [];

        if (typeof activeField !== 'undefined') activeField = 'from';
    };
    btnCollapse.onclick = () => {
        const wrap = document.getElementById('route-results');
        wrap.classList.toggle('collapsed');
        btnCollapse.textContent = wrap.classList.contains('collapsed') ? 'Buka' : 'Tutup';
    };
}

let currentRadiusGeoJSON = null;

function initMeasureTools(map) {
    const measurePanel = document.getElementById('measure-panel');
    const btnHeaderMeasure = document.getElementById('btn-header-measure');
    
    const btnDist = document.getElementById('btn-measure-dist');
    const btnArea = document.getElementById('btn-measure-area');
    const btnRadius = document.getElementById('btn-measure-radius');
    const btnClear = document.getElementById('btn-measure-clear');
    const resultEl = document.getElementById('measure-result');
    const radiusAnalysisBtnWrapper = document.getElementById('radius-analysis-btn-wrapper');

    let activeTool = null;
    let radiusCenter = null;

    if(measurePanel) measurePanel.style.display = 'none';
    if (btnHeaderMeasure && measurePanel) {
        btnHeaderMeasure.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (measurePanel.style.display === 'none' || measurePanel.style.display === '') {
                measurePanel.style.display = 'flex';
                btnHeaderMeasure.style.background = '#ffc107';
                btnHeaderMeasure.style.color = '#1a237e';
                if(!activeTool) resultEl.textContent = 'Pilih Mode';
            } else {
                measurePanel.style.display = 'none';
                btnHeaderMeasure.style.background = '';
                btnHeaderMeasure.style.color = '';
                clearMeasure();
            }
        });
    }

    let geojson = { type: 'FeatureCollection', features: [] };
    const sourceId = 'measure-source';
    const previewSourceId = 'measure-preview-source'; 

    if(!map.getSource(sourceId)) {
        map.addSource(sourceId, { type: 'geojson', data: geojson });
        map.addSource(previewSourceId, { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });

        map.addLayer({ id: 'measure-points', type: 'circle', source: sourceId, paint: { 'circle-radius': 5, 'circle-color': '#fff', 'circle-stroke-color': '#000', 'circle-stroke-width': 2 }, filter: ['in', '$type', 'Point'] });
        map.addLayer({ id: 'measure-lines', type: 'line', source: sourceId, layout: { 'line-cap': 'round', 'line-join': 'round' }, paint: { 'line-color': '#ffc107', 'line-width': 3, 'line-dasharray': [2, 2] }, filter: ['in', '$type', 'LineString'] });
        map.addLayer({ id: 'measure-poly', type: 'fill', source: sourceId, paint: { 'fill-color': '#2196f3', 'fill-opacity': 0.3 }, filter: ['in', '$type', 'Polygon'] });
        map.addLayer({ id: 'measure-poly-outline', type: 'line', source: sourceId, paint: { 'line-color': '#2196f3', 'line-width': 2 }, filter: ['in', '$type', 'Polygon'] });
        
        map.addLayer({ id: 'measure-preview-poly', type: 'fill', source: previewSourceId, paint: { 'fill-color': '#00e676', 'fill-opacity': 0.2 } });
        map.addLayer({ id: 'measure-preview-line', type: 'line', source: previewSourceId, paint: { 'line-color': '#00e676', 'line-width': 2, 'line-dasharray': [2, 2] } });
    }

    const updateSource = () => map.getSource(sourceId).setData(geojson);
    const updatePreview = (data) => map.getSource(previewSourceId).setData(data || { type: 'FeatureCollection', features: [] });

    const finishRadius = (circlePoly, distKm) => {
        circlePoly.properties = { type: 'radius', value: distKm + ' km' };
        geojson.features.push(circlePoly);
        if(radiusCenter) geojson.features.push({ type: 'Feature', geometry: { type: 'Point', coordinates: radiusCenter } });
        updateSource();
        currentRadiusGeoJSON = circlePoly;
        if(radiusAnalysisBtnWrapper) radiusAnalysisBtnWrapper.style.display = 'block';
        radiusCenter = null; 
        updatePreview(null);
        resultEl.textContent = `R: ${distKm.toFixed(2)} km`;
    };

    const clearMeasure = () => {
        if (activeTool) {
            const btn = document.getElementById(`btn-measure-${activeTool}`);
            if(btn) btn.classList.remove('active');
        }
        activeTool = null;
        radiusCenter = null;
        currentRadiusGeoJSON = null;
        geojson.features = [];
        updateSource();
        updatePreview(null);
        if(radiusAnalysisBtnWrapper) radiusAnalysisBtnWrapper.style.display = 'none';
        map.getCanvas().style.cursor = '';
        resultEl.textContent = 'Reset';
        
        map.off('click', handleMapClick);
        map.off('dblclick', handleMapDblClick);
        map.off('mousemove', handleMouseMove);
        map.off('contextmenu', handleRightClick);
    };

    const handleMouseMove = (e) => {
        if (activeTool === 'radius' && radiusCenter) {
            const currentPoint = [e.lngLat.lng, e.lngLat.lat];
            const line = turf.lineString([radiusCenter, currentPoint]);
            const dist = turf.length(line, { units: 'kilometers' });
            const circlePoly = turf.circle(radiusCenter, dist, { steps: 64, units: 'kilometers' });
            updatePreview({
                type: 'FeatureCollection',
                features: [circlePoly, { type: 'Feature', geometry: line.geometry }]
            });
            resultEl.textContent = `R: ${dist.toFixed(2)} km`;
        }
    };

    const handleRightClick = (e) => {
        if (activeTool === 'radius' && radiusCenter) {
            e.preventDefault();
            const inputVal = prompt("Jarak radius (KM):", "1.0");
            if (inputVal !== null) {
                const distKm = parseFloat(inputVal.replace(',', '.'));
                if (!isNaN(distKm) && distKm > 0) {
                    const circlePoly = turf.circle(radiusCenter, distKm, { steps: 64, units: 'kilometers' });
                    finishRadius(circlePoly, distKm);
                }
            }
        }
    };

    const handleMapClick = (e) => {
        const coords = [e.lngLat.lng, e.lngLat.lat];
        if (activeTool === 'radius') {
            if (!radiusCenter) {
                radiusCenter = coords;
                updatePreview({
                    type: 'FeatureCollection',
                    features: [{ type: 'Feature', geometry: { type: 'Point', coordinates: coords } }]
                });
                resultEl.textContent = 'Tarik...';
                if(radiusAnalysisBtnWrapper) radiusAnalysisBtnWrapper.style.display = 'none';
                currentRadiusGeoJSON = null;
            } else {
                const line = turf.lineString([radiusCenter, coords]);
                const dist = turf.length(line, { units: 'kilometers' });
                const circlePoly = turf.circle(radiusCenter, dist, { steps: 64, units: 'kilometers' });
                finishRadius(circlePoly, dist);
            }
            return; 
        }

        const points = geojson.features.filter(f => f.geometry.type === 'Point');
        points.push({ type: 'Feature', geometry: { type: 'Point', coordinates: coords } });
        geojson.features = [...points];

        if(radiusAnalysisBtnWrapper) radiusAnalysisBtnWrapper.style.display = 'none';

        if (activeTool === 'dist') {
            if (points.length > 1) {
                const line = turf.lineString(points.map(p => p.geometry.coordinates));
                const len = turf.length(line, { units: 'kilometers' });
                resultEl.textContent = `${len.toFixed(2)} km`;
                geojson.features.push(line);
            } else {
                 resultEl.textContent = 'Klik lagi';
            }
        } else if (activeTool === 'area') {
            if (points.length > 2) {
                const polyCoords = points.map(p => p.geometry.coordinates);
                polyCoords.push(points[0].geometry.coordinates);
                const polygon = turf.polygon([polyCoords]);
                const area = turf.area(polygon);
                resultEl.textContent = `${(area / 1000000).toFixed(3)} km²`;
                geojson.features.push(polygon);
            } else {
                resultEl.textContent = 'Klik lagi';
            }
        }
        updateSource();
    };

    const handleMapDblClick = () => {
        const points = geojson.features.filter(f => f.geometry.type === 'Point');
        if ((activeTool === 'area' && points.length > 2) || (activeTool === 'dist' && points.length > 1)) {
            radiusCenter = null;
            updatePreview(null);
        }
    };

    const startMeasure = (tool) => {
        if (activeTool === tool) {
             clearMeasure();
        } else {
            clearMeasure();
        }
        
        activeTool = tool;
        document.getElementById(`btn-measure-${tool}`).classList.add('active');
        map.getCanvas().style.cursor = 'crosshair';
        
        map.on('click', handleMapClick);
        map.on('dblclick', handleMapDblClick);
        map.on('mousemove', handleMouseMove);
        map.on('contextmenu', handleRightClick);
        map.doubleClickZoom.disable();
        
        if (tool === 'radius') resultEl.textContent = 'Klik Tengah';
        else resultEl.textContent = 'Klik Peta';
    };

    btnDist.onclick = () => startMeasure('dist');
    btnArea.onclick = () => startMeasure('area');
    btnRadius.onclick = () => startMeasure('radius');
    btnClear.onclick = clearMeasure;

    const _enableDbl = () => { try { map.doubleClickZoom.enable(); } catch {} };
    window.addEventListener('beforeunload', _enableDbl);
}

let radiusChartInstances = {};

document.getElementById('btn-radius-analysis').addEventListener('click', () => {
    if (!currentRadiusGeoJSON) {
        alert("Area radius belum terbentuk.");
        return;
    }
    analyzeRadiusData(currentRadiusGeoJSON);
    document.getElementById('radius-modal-overlay').style.display = 'flex';
});

document.getElementById('radius-modal-close').addEventListener('click', () => {
    document.getElementById('radius-modal-overlay').style.display = 'none';
});

document.getElementById('btn-download-radius').addEventListener('click', () => {
    if (!currentRadiusGeoJSON) return;
    downloadRadiusExcel(currentRadiusGeoJSON);
});

document.getElementById('btn-screenshot-radius').addEventListener('click', function() {
    const modalBox = document.getElementById('radius-modal-box');
    
    const btnClose = document.getElementById('radius-modal-close');
    const modalFooter = document.getElementById('radius-modal-footer');
    const btnScreenshot = this;

    const origBtnDisplay = btnScreenshot.style.display;
    const origCloseDisplay = btnClose.style.display;
    const origFooterDisplay = modalFooter.style.display;

    btnScreenshot.style.display = 'none';
    btnClose.style.display = 'none';
    modalFooter.style.display = 'none';

    html2canvas(modalBox, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true
    }).then(canvas => {
        btnScreenshot.style.display = origBtnDisplay;
        btnClose.style.display = origCloseDisplay;
        modalFooter.style.display = origFooterDisplay;

        const imageURL = canvas.toDataURL("image/png");
        const link = document.createElement('a');
        link.href = imageURL;
        const dateStr = new Date().toISOString().slice(0,10);
        link.download = `Screenshot_Analisis_Radius_${dateStr}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

    }).catch(err => {
        btnScreenshot.style.display = origBtnDisplay;
        btnClose.style.display = origCloseDisplay;
        modalFooter.style.display = origFooterDisplay;
        console.error("Gagal mengambil screenshot:", err);
        alert("Gagal mengambil screenshot.");
    });
});

function getCurrentSliderDateYMD() {
    const slider = document.getElementById('time-slider');
    if (!slider || !timeSliderData.uniqueDates.length) return null;
    
    const idx = parseInt(slider.value, 10);
    const dateObj = timeSliderData.uniqueDates[idx];
    return toYMD(dateObj);
}

function analyzeRadiusData(polygon) {
    const selectedDateYMD = getCurrentSliderDateYMD();

    const troublespotData = filterPointsInPoly(filteredLayerData.troublespot, polygon);
    const trafficData = filterPointsInPoly(filteredLayerData.traffic_point, polygon);
    const riskData = filterLinesInPoly(filteredLayerData.prediksi_risiko, polygon);

    let blackspotData = filterPointsInPoly(filteredLayerData.blackspot, polygon);
    if (selectedDateYMD) {
        blackspotData = blackspotData.filter(f => f.properties.tanggal === selectedDateYMD);
    }

    document.getElementById('val-troublespot').textContent = troublespotData.length;
    document.getElementById('val-blackspot').textContent = blackspotData.length;
    document.getElementById('val-trafficpoint').textContent = trafficData.length;
    const highRiskCount = riskData.filter(f => f.properties.risk_prediction === 2).length;
    document.getElementById('val-risk').textContent = highRiskCount;

    renderRadiusCharts({
        troublespot: troublespotData,
        prediksi_risiko: riskData
    });
}

function filterPointsInPoly(featureCollection, polygon) {
    if (!featureCollection || !featureCollection.features) return [];
    return featureCollection.features.filter(f => {
        if (f.geometry.type === 'Point') {
            return turf.booleanPointInPolygon(f, polygon);
        }
        return false; 
    });
}

function filterLinesInPoly(featureCollection, polygon) {
    if (!featureCollection || !featureCollection.features) return [];
    return featureCollection.features.filter(f => {
        return turf.booleanIntersects(f, polygon);
    });
}

function renderRadiusCharts(data) {
    const tsCtx = document.getElementById('radius-chart-troublespot').getContext('2d');
    
    let countJalanDitutup = 0;
    let countKecelakaan = 0;
    let countGangguan = 0;

    data.troublespot.forEach(f => {
        const ket = (f.properties.keterangan || f.properties.Keterangan || '').toLowerCase();
        if (ket.includes('tutup')) countJalanDitutup++;
        else if (ket.includes('celaka') || ket.includes('accident')) countKecelakaan++;
        else countGangguan++;
    });

    if (radiusChartInstances.troublespot) radiusChartInstances.troublespot.destroy();

    radiusChartInstances.troublespot = new Chart(tsCtx, {
        type: 'bar',
        plugins: [ChartDataLabels], 
        data: {
            labels: ['Total Kejadian'],
            datasets: [
                { label: `Jalan Ditutup (${countJalanDitutup})`, data: [countJalanDitutup], backgroundColor: '#d32f2f', barPercentage: 0.6, categoryPercentage: 0.8 },
                { label: `Titik Kecelakaan (${countKecelakaan})`, data: [countKecelakaan], backgroundColor: '#5b5b5b', barPercentage: 0.6, categoryPercentage: 0.8 },
                { label: `Gangguan Jalan (${countGangguan})`, data: [countGangguan], backgroundColor: '#ffc107', barPercentage: 0.6, categoryPercentage: 0.8 }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'bottom', labels: { color: '#333' } },
                
                datalabels: {
                    color: '#333', anchor: 'end', align: 'end', offset: 4, font: { weight: 'bold', size: 12 },
                    formatter: (value) => value > 0 ? value : ''
                },

                tooltip: { 
                    enabled: true,
                    callbacks: {
                        title: () => '', 
                        label: function(context) {
                            const rawLabel = context.dataset.label;
                            const cleanLabel = rawLabel.replace(/\s\(\d+\)$/, '');
                            return cleanLabel;
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true, ticks: { color: '#333', stepSize: 1, beginAtZero: true }, grid: { color: '#eee' },
                    afterDataLimits: (scale) => { scale.max += 1; } 
                },
                y: { display: false }
            }
        }
    });

    const riskCtx = document.getElementById('radius-chart-risk').getContext('2d');
    
    let countSedang = 0;
    let countTinggi = 0;

    data.prediksi_risiko.forEach(f => {
        const val = f.properties.risk_prediction;
        if (val === 1) countSedang++;
        else if (val === 2) countTinggi++;
    });

    const totalRisk = countSedang + countTinggi;

    if (radiusChartInstances.risk) radiusChartInstances.risk.destroy();

    radiusChartInstances.risk = new Chart(riskCtx, {
        type: 'doughnut',
        plugins: [ChartDataLabels],
        data: {
            labels: [`Risiko Sedang (${countSedang})`, `Risiko Tinggi (${countTinggi})`],
            datasets: [{
                data: [countSedang, countTinggi],
                backgroundColor: ['#bfb100', '#b8005c'],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: 10 },
            plugins: {
                legend: { display: true, position: 'right', labels: { color: '#333', font: { size: 11 }, boxWidth: 12 } },
                
                datalabels: {
                    color: '#fff', font: { weight: 'bold', size: 14 }, textAlign: 'center',
                    formatter: (value, ctx) => {
                        if (value === 0) return '';
                        let percentage = ((value / totalRisk) * 100).toFixed(0) + "%";
                        return value + '\n(' + percentage + ')';
                    }
                },

                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const rawLabel = context.label;
                            const cleanLabel = rawLabel.replace(/\s\(\d+\)$/, '');
                            return cleanLabel;
                        }
                    }
                }
            }
        }
    });
}

function downloadRadiusExcel(polygon) {
    const wb = XLSX.utils.book_new();
    let hasData = false;
    const selectedDateYMD = getCurrentSliderDateYMD();

    const layersToProcess = [
        { key: 'troublespot', name: 'Gangguan Lalu Lintas', type: 'point' },
        { key: 'traffic_point', name: 'Titik Perlambatan', type: 'point' },
        { key: 'blackspot', name: 'Black Spot', type: 'point_date' },
        { key: 'prediksi_risiko', name: 'Prediksi Risiko', type: 'line' }
    ];

    layersToProcess.forEach(layer => {
        const sourceData = filteredLayerData[layer.key];
        
        let filteredFeatures = [];
        if (layer.type === 'line') {
            filteredFeatures = filterLinesInPoly(sourceData, polygon);
        } else {
            filteredFeatures = filterPointsInPoly(sourceData, polygon);
            
            if (layer.key === 'blackspot' && selectedDateYMD) {
                filteredFeatures = filteredFeatures.filter(f => f.properties.tanggal === selectedDateYMD);
            }
        }

        if (filteredFeatures.length > 0) {
            hasData = true;
            const exportData = [];

            filteredFeatures.forEach(feature => {
                const props = feature.properties || {};
                const geom = feature.geometry;
                let row = {};

                Object.keys(ALIAS_MAP).forEach(key => {
                    if (props.hasOwnProperty(key)) {
                        const headerName = ALIAS_MAP[key];
                        row[headerName] = props[key];
                    }
                });

                let coords = null;
                if (geom.type === 'Point') {
                    coords = geom.coordinates;
                } else {
                    try {
                        const centerPoint = turf.centroid(feature);
                        coords = centerPoint.geometry.coordinates;
                    } catch (e) {
                    }
                }

                if (coords) {
                    row['Longitude'] = coords[0];
                    row['Latitude'] = coords[1];
                }

                if (Object.keys(row).length > 0) {
                    exportData.push(row);
                }
            });

            if (exportData.length > 0) {
                const ws = XLSX.utils.json_to_sheet(exportData);
                XLSX.utils.book_append_sheet(wb, ws, layer.name);
            }
        }
    });

    if (!hasData) {
        alert("Tidak ada data dalam radius ini untuk diekspor.");
        return;
    }

    const dateStr = new Date().toISOString().slice(0,10);
    const bsDateInfo = selectedDateYMD ? `_BS-${selectedDateYMD}` : '';
    XLSX.writeFile(wb, `Analisis_Radius${bsDateInfo}_${dateStr}.xlsx`);
}
function isTimeActive(startStr, endStr) {
  if (!startStr || !endStr) return true;
  
  const now = new Date();
  const curTotalMins = now.getHours() * 60 + now.getMinutes();

  const [sh, sm] = startStr.split(':').map(Number);
  const [eh, em] = endStr.split(':').map(Number);
  
  if (isNaN(sh) || isNaN(eh)) return true;

  const startTotal = sh * 60 + sm;
  const endTotal = eh * 60 + em;
  if (endTotal > startTotal) {
    return curTotalMins >= startTotal && curTotalMins <= endTotal;
  } 
  else {
    return curTotalMins >= startTotal || curTotalMins <= endTotal;
  }
}
function getGeofenceHazardsOnRoute(routeFeature) {
  const hazards = [];
  const geofences = filteredLayerData.geofencing?.features || [];
  
  for (const zone of geofences) {
    try {
      if (turf.booleanIntersects(routeFeature, zone)) {
        const p = zone.properties || {};
        const jamMulai = p.jam_mulai || '00:00';
        const jamSelesai = p.jam_selesai || '23:59';
        if (isTimeActive(jamMulai, jamSelesai)) {
          hazards.push({
            tipe: p.tipe_zona || 'Zona Khusus',
            mulai: jamMulai,
            selesai: jamSelesai
          });
        }
      }
    } catch (e) {}
  }
  return hazards;
}
function formatNameList(names) {
  const formatted = [...names];
  if (formatted.length === 0) return '';
  if (formatted.length === 1) return formatted[0];
  if (formatted.length === 2) return `${formatted[0]} dan ${formatted[1]}`;
  const last = formatted.pop();
  return `${formatted.join(', ')} dan ${last}`;
}

function formatSentenceList(sentences) {
  if (sentences.length === 0) return '';
  if (sentences.length === 1) return sentences[0];
  if (sentences.length === 2) return `${sentences[0]} dan ${sentences[1]}`;
  
  const last = sentences.pop();
  return `${sentences.join(', ')} dan ${last}`;
}

function buildComplexGeofenceMessage(hazards) {
  if (!hazards.length) return '';
  const groups = {};
  
  hazards.forEach(h => {
    const key = `${h.mulai}|${h.selesai}`;
    if (!groups[key]) groups[key] = [];
    if (!groups[key].includes(h.tipe)) {
      groups[key].push(h.tipe);
    }
  });
  const clauses = [];
  
  for (const key in groups) {
    const [start, end] = key.split('|');
    const names = groups[key];
    const nameString = formatNameList(names);
    clauses.push(`Zona Geofencing ${nameString} yang berlaku mulai pukul ${start} sampai ${end}`);
  }
  return "Rute Anda melewati " + formatSentenceList(clauses);
}

let geoWatchId = null;

function checkUserGeofence(coords) {
  const geofences = filteredLayerData.geofencing?.features || [];
  const panel = document.getElementById('geofence-status-panel');
  const content = document.getElementById('geofence-alert-content');
  if (!panel || !content) return;
  const userPoint = turf.point(coords);
  let activeMatches = [];
  for (const zone of geofences) {
    try {
      if (turf.booleanPointInPolygon(userPoint, zone)) {
        const p = zone.properties || {};
        
        const jamMulai = p.jam_mulai || '00:00';
        const jamSelesai = p.jam_selesai || '23:59';

        if (isTimeActive(jamMulai, jamSelesai)) {
          activeMatches.push({
            tipe: p.tipe_zona || 'Zona Khusus',
            limit: parseInt(p.geofence_limit || p.speed_limit || 999)
          });
        }
      }
    } catch (e) {
      console.warn('Error logic geofence:', e);
    }
  }
  if (activeMatches.length === 0) {
    panel.style.display = 'none';
    return;
  }

  const limits = activeMatches.map(m => m.limit);
  const minLimit = Math.min(...limits);
  const displayLimit = (minLimit === 999) ? '--' : minLimit;
  const uniqueNames = [...new Set(activeMatches.map(m => m.tipe))];
  
  let zonaString = '';
  if (uniqueNames.length === 1) {
    zonaString = uniqueNames[0];
  } else if (uniqueNames.length === 2) {
    zonaString = `${uniqueNames[0]} dan ${uniqueNames[1]}`;
  } else {
    const lastZone = uniqueNames.pop();
    zonaString = `${uniqueNames.join(', ')} dan ${lastZone}`;
  }
  const finalMessage = `Anda berada di Zona Geofencing ${zonaString} dengan batas kecepatan ${displayLimit} km/jam`;
  content.innerHTML = finalMessage;
  panel.style.display = 'block';
}
function startGeofenceWatch() {
  if (!navigator.geolocation) return;
  if (geoWatchId) navigator.geolocation.clearWatch(geoWatchId);

  geoWatchId = navigator.geolocation.watchPosition(
    (position) => {
      const lng = position.coords.longitude;
      const lat = position.coords.latitude;
      checkUserGeofence([lng, lat]);
    },
    (err) => {
      console.warn('Geofence Watch Error:', err);
    },
    {
      enableHighAccuracy: true,
      maximumAge: 5000,
      timeout: 10000
    }
  );
}
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(startGeofenceWatch, 3000);
});
const originalLocBtn = document.getElementById('my-location-btn');
if (originalLocBtn) {
    originalLocBtn.addEventListener('click', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((pos) => {
                checkUserGeofence([pos.coords.longitude, pos.coords.latitude]);
            });
        }
    });
}
const LAYER_INFO_DATA = {
  basemap_osm: {
    title: "OpenStreetMap (OSM)",
    desc: "Peta Detail Berbasis Komunitas.",
    detail: `OpenStreetMap merupakan peta digital yang dibangun secara kolaboratif oleh komunitas di seluruh dunia. Keunggulan utamanya terletak pada tingkat detail yang sangat tinggi, terutama untuk area perkotaan. Peta ini tidak hanya menampilkan jalan raya utama, tetapi sering kali mencakup detail spesifik seperti jalan setapak, jejak bangunan (building footprints), hingga fasilitas umum yang kecil.`,
    source: "OpenStreetMap Contributors"
  },
  basemap_esri: {
    title: "ESRI World Topographic Map",
    desc: "Peta Topografi & Kontur.",
    detail: `Peta ini dirancang oleh Esri dengan estetika yang sangat rapi dan profesional. Fokus utamanya adalah menampilkan bentuk permukaan bumi—seperti kontur ketinggian tanah, tutupan lahan (hutan, taman, area terbangun), dan hidrografi (sungai/danau), namun dengan tampilan yang diredam (muted). Label jalan dan nama tempat tetap ada, tetapi tidak mendominasi tampilan peta.`,
    source: "ESRI"
  },
  basemap_google: {
    title: "Google Maps",
    desc: "Peta Navigasi & Lokasi Bisnis.",
    detail: `Basemap dengan tampilan peta standar yang paling familiar bagi hampir semua orang. Desainnya dioptimalkan untuk navigasi dan orientasi cepat. Fitur yang paling menonjol adalah kejelasan jaringan jalan, label nama tempat yang mudah dibaca, serta penanda lokasi penting (Points of Interest) seperti gedung perkantoran, sekolah, dan tempat komersial.`,
    source: "Google Maps"
  },
  basemap_imagery: {
    title: "Google Imagery (Satellite)",
    desc: "Citra Satelit Murni.",
    detail: `Peta ini menyajikan potret nyata permukaan bumi yang diambil dari satelit. Tampilannya adalah representasi fotografi sesungguhnya dari kondisi lapangan, memperlihatkan atap bangunan, pepohonan, lahan kosong, hingga kepadatan lalu lintas.`,
    source: "Google Earth / Satellite Providers"
  },
  basemap_hybrid: {
    title: "Google Hybrid",
    desc: "Citra Satelit dengan Label.",
    detail: `Basemap ini menampilkan lapisan jalan transparan, nama tempat dan batas wilayah di atas citra satelit. User bisa melihat kondisi fisik lapangan yang nyata, namun tetap tahu nama jalan dan lokasi daerah tersebut.`,
    source: "Google Maps"
  },
  basemap_google_dark: {
    title: "Google Maps - Dark Mode",
    desc: "Tampilan mode gelap dari Google Maps.",
    detail: `Basemap ini menyajikan data Google dengan skema warna gelap. Ideal untuk penggunaan di ruangan minim cahaya karena mengurangi ketegangan mata dan membuat layer data berwarna (seperti perlambatan lalu lintas atau titik kecelakaan) terlihat lebih kontras dan tajam.`,
    source: "Google Maps"
  },
  basemap_esri_dark: {
    title: "ESRI Dark Gray Canvas",
    desc: "Peta dasar bernuansa gelap yang minimalis.",
    detail: `Peta ini dirancang untuk meminimalkan gangguan visual dari latar belakang. Warna gelap sangat cocok untuk menonjolkan overlay data yang berwarna cerah (seperti jalur lalu lintas berwarna merah/kuning) agar lebih kontras dan mudah dibaca.`,
    source: "ESRI"
  },
  basemap_esri_light: {
    title: "ESRI Light Gray Canvas",
    desc: "Peta dasar bernuansa abu-abu terang dan netral.",
    detail: `Peta ini memberikan konteks geografis (jalan atau batas wilayah) dengan warna yang sangat halus dan diredam. Tujuannya adalah agar lapisan data operasional di atasnya menjadi fokus utama tanpa bertabrakan dengan warna peta dasar.`,
    source: "ESRI"
  },
  basemap_carto_dark: {
    title: "CartoDB Dark Matter",
    desc: "Peta tema gelap dengan kontras tinggi.",
    detail: `Didesain khusus untuk visualisasi data geografis. Penggunaan warna dasar hitam pekat dan abu-abu gelap membuat layer informasi di atasnya (seperti garis traffic atau rute) terlihat sangat mencolok dan mudah dianalisis.`,
    source: "CartoDB"
  },
  basemap_stadia: {
    title: "Stadia Alidade Smooth Dark",
    desc: "Peta dasar bertema gelap dengan desain modern dan minimalis.",
    detail: `Dirancang untuk keterbacaan tinggi dengan palet warna yang halus dan tidak menyilaukan. Peta ini menonjolkan fitur geografis utama tanpa gangguan visual, sehingga sangat efektif sebagai latar belakang untuk menonjolkan visualisasi data operasional.`,
    source: "Stadia Maps"
  },  
  troublespot: {
    title: "Titik Gangguan Lalu Lintas",
    desc: "Titik gangguan lalu lintas yang dipantau dan dilaporkan secara langsung (real-time).",
    detail: `Titik Gangguan Lalu Lintas mencakup 4 jenis gangguan, yaitu:
      <ul>
        <li><b>Jalan Ditutup:</b> Area yang ditutup petugas dan tidak bisa dilewati kendaraan apapun.</li>
        <li><b>Titik Kecelakaan:</b> Lokasi kecelakaan yang berpotensi menghambat arus lalu lintas.</li>
        <li><b>Gangguan Jalan:</b> Hambatan fisik seperti kendaraan mogok, jalan rusak/licin, atau sedang ada perbaikan.</li>
      </ul>`,
    source: "Waze"
  },
  blackspot: {
    title: "Black Spot",
    desc: "Titik lokasi dengan konsentrasi kecelakaan yang tinggi dalam 2 tahun terakhir.",
    detail: `Penentuan titik black spot dihitung menggunakan sistem bobot nilai berdasarkan tingkat keparahan korban di tiap kecelakaan, yaitu:
      <ul>
        <li><b>Meninggal Dunia:</b> bobot 10.</li>
        <li><b>Luka Berat:</b> bobot 5.</li>
        <li><b>Luka Ringan:</b> bobot 1.</li>
      </ul>`,
    source: "Analisis data kecelakaan IRSMS"
  },
  traffic_point: {
    title: "Titik Perlambatan",
    desc: "Lokasi sebaran titik perlambatan lalu lintas.",
    detail: "Data ini menampilkan titik-titik perlambatan yang yang terjadi di tiap segmen jalan secara real-time.",
    source: "Waze"
  },
  traffic: {
    title: "Perlambatan",
    desc: "Informasi kondisi kepadatan lalu lintas yang terjadi saat ini (real-time).",
    detail: `Tingkat perlambatan dibagi menjadi 5 level, yaitu:
      <ul>
        <li><b>Level 1 (Light Traffic):</b> Lancar/ringan, yaitu lalu lintas normal dan kecepatan mendekati kecepatan bebas hambatan.</li>
        <li><b>Level 2 (Moderate Traffic):</b> Sedang, yaitu kecepatan sedikit menurun, namun arus kendaraan masih mengalir lancar.</li>
        <li><b>Level 3 (Heavy Traffic):</b> Padat, yaitu kecepatan turun drastis dan mulai terasa lambat.</li>
        <li><b>Level 4 (Very Heavy Traffic):</b> Sangat padat, yaitu kendaraan bergerak sangat lambat dan sering berhenti (stop & go).</li>
        <li><b>Level 5 (Standstill):</b> Perlambatan total, yaitu lalu lintas benar-benar berhenti.</li>
      </ul>`,
    source: "Waze"
  },
  blacklink: {
    title: "Black Link",
    desc: "Ruas jalan dengan konsentrasi kecelakaan yang tinggi dalam 2 tahun terakhir.",
    detail: `Black link ditentukan dengan memetakan lokasi kecelakaan ke dalam dua kategori jalan:
      <ul>
        <li>Persimpangan jalan sepanjang 0 – 100 meter.</li>
        <li>Jalan lurus/menikung sepanjang 0 – 500 meter.</li>
      </ul>`,
    source: "Analisis data kecelakaan IRSMS"
  },
  blackarea: {
    title: "Black Area",
    desc: "Cakupan kawasan black spot.",
    detail: "Area ini mencakup radius 500 meter dari titik black spot.",
    source: "Analisis data kecelakaan IRSMS"
  },
  objek_vital: {
    title: "Objek Vital",
    desc: "Lokasi strategis yang menjadi pusat aktivitas dan keramaian masyarakat.",
    detail: `Objek Vital dikategorikan dalam 3 kelompok:
      <ul>
        <li><b>Transportasi & Perhubungan:</b> Bandara, Stasiun, Terminal dan Pelabuhan.</li>
        <li><b>Layanan Publik:</b> Fasilitas Pendidikan, Fasilitas Kesehatan, Kantor Pemerintahan dan Kantor Polisi.</li>
        <li><b>Rekreasi & Ekonomi:</b> Tempat Pariwisata.</li>
      </ul>`,
    source: "Geocode Location Google"
  },
  cctv: {
    title: "CCTV",
    desc: "Sebaran lokasi kamera pemantau kondisi lalu lintas.",
    detail: `Status CCTV dibagi menjadi dua, yaitu:
      <ul>
        <li><b>Hidup:</b> CCTV aktif dan sedang memantau lalu lintas.</li>
        <li><b>Mati:</b> CCTV sedang dalam gangguan dan tidak aktif.</li>
      </ul>`,
    source: "Smart City"
  },
  cctv_coverage: {
    title: "Area Pantauan CCTV",
    desc: "Visualisasi area jangkauan pandang kamera CCTV.",
    detail: "Layer ini menggambarkan estimasi area yang dapat dilihat oleh kamera CCTV berdasarkan arah hadap kamera dan radius jangkauan lensa (Field of View). Warna hijau menunjukkan area yang tercover.",
    source: "Analisis Spasial CCTV"
  },
  gerbang_tol: {
    title: "Gerbang Tol",
    desc: "Lokasi titik sebaran gerbang tol.",
    detail: `Data gerbang tol diklasifikasikan berdasarkan status operasional:
      <ul>
        <li><b>Operasi (Hijau):</b> Gerbang tol yang sudah aktif beroperasi.</li>
        <li><b>Konstruksi (Orange):</b> Gerbang tol yang masih dalam tahap pembangunan.</li>
      </ul>`,
    source: "Kementerian Pekerjaan Umum"
  },
    patrol: {
    title: "Petugas Patroli",
    desc: "Posisi real-time petugas patroli kepolisian.",
    detail: "Menampilkan lokasi terkini dari petugas patroli yang sedang bertugas di lapangan untuk pemantauan keamanan dan ketertiban lalu lintas.",
    source: "GPS Tracker Petugas Patroli"
  },
  rescue: {
    title: "Kendaraan Rescue",
    desc: "Posisi siaga unit kendaraan penyelamat atau derek.",
    detail: "Layer ini menampilkan sebaran lokasi unit kendaraan rescue (mobil derek, ambulans, kepolisian, atau unit reaksi cepat) yang siap dikerahkan jika terjadi insiden lalu lintas.",
    source: "Data Operasional Satlantas"
  },
  data_kecelakaan: {
    title: "Data Sebaran Kecelakaan",
    desc: "Titik lokasi historis kejadian kecelakaan lalu lintas yang tercatat pada IRSMS.",
    detail: `Data kecelakaan diklasifikasikan berdasarkan bobot keparahan korban, yaitu:
      <ul>
        <li><b>Kecelakaan Ringan (Kuning):</b> Bobot 1 (Terjadinya Luka Ringan).</li>
        <li><b>Kecelakaan Sedang (Orange):</b> Bobot 5 (Terjadinya Luka Berat).</li>
        <li><b>Kecelakaan Berat (Merah):</b> Bobot 10 (Terjadinya Meninggal Dunia).</li>
      </ul>`,
    source: "Data Kecelakaan IRSMS"
  },
  prediksi_risiko: {
    title: "Prediksi Risiko Kecelakaan",
    desc: "Sistem peringatan dini (early warning) yang memperkirakan potensi kecelakaan di setiap segmen jalan. Analisis ini menggabungkan data historis kecelakaan dengan kondisi lingkungan saat ini (real-time) untuk membantu pencegahan kecelakaan.",
    detail: `Model prediksi ini menggunakan metode <b>Random Forest</b> dengan memanfaatkan data historis kecelakaan dan kondisi lingkungan jalan. Sistem akan menganalisis hubungan antara kondisi lalu lintas, kepadatan penduduk di sekitar jalan, keberadaan fasilitas penting, cuaca dan waktu kejadian serta riwayat kecelakaan yang pernah terjadi di sekitar lokasi tersebut untuk menemukan faktor penyebab utama kecelakaan. Hasil analisis tersebut digunakan untuk memprediksi risiko kecelakaan dengan tingkat klasifikasi sebagai berikut:
      <br><br><b>Klasifikasi Risiko:</b>
      <ul>
        <li>Skor 0 : <b>Risiko Kecelakaan Rendah,</b> yaitu ruas jalan berada dalam kondisi relatif aman dan tidak menunjukkan tanda-tanda potensi kecelakaan yang berarti.</li>
        <li>Skor 1 : <b>Risiko Kecelakaan Sedang,</b> yaitu ruas jalan memiliki beberapa faktor yang perlu diwaspadai, seperti cuaca kurang baik, kepadatan kendaraan meningkat, atau ada riwayat kecelakaan ringan sebelumnya.</li>
        <li>Skor 2 : <b>Risiko Kecelakaan Tinggi,</b> yaitu ruas jalan menunjukkan banyak faktor yang meningkatkan potensi kecelakaan, misalnya lalu lintas sangat padat, cuaca ekstrem, riwayat kecelakaan berat, ataupun adanya lingkungan sekitar yang berisiko. Segmen dengan kategori ini membutuhkan perhatian khusus dan langkah mitigasi yang lebih cepat.</li>
      </ul>`,
    source: "Hasil analisis model prediksi risiko"
  },
  speedlimit: {
    title: "Batas Kecepatan",
    desc: "Informasi batas kecepatan maksimum yang diizinkan pada ruas jalan tertentu.",
    detail: `Penentuan batas kecepatan ini mengacu pada regulasi pemerintah serta kondisi lingkungan jalan, yaitu:
      <ul>
        <li><b>Jalan Tol:</b> 100 km/jam.</li>
        <li><b>Jalan Nasional (Luar Kota):</b> 80 km/jam.</li>
        <li><b>Jalan Nasional (Dalam Kota):</b> 60 km/jam.</li>
        <li><b>Jalan Provinsi:</b> 60 km/jam.</li>
        <li><b>Jalan Kota/Kabupaten:</b> 40 km/jam.</li>
      </ul>`,
    source: "PM 111/2015 Kementerian Perhubungan"
  },
  geofencing: {
    title: "Geofencing",
    desc: "Zona virtual yang berfungsi sebagai pemicu otomatis untuk mengubah aturan batas kecepatan saat kendaraan memasuki kawasan tertentu.",
    detail: `Radius zona dan batas kecepatan diatur berdasarkan jenis lokasi dan jam operasionalnya, yaitu:
    <ul>
      <li><b>Zona Kecepatan Maksimal 30 km/jam:</b></li>
        <ul>
          <li>Fasilitas Pendidikan:Radius 200 m (Pukul 06:30 – 14:30).</li>
          <li>Tempat Pariwisata: Radius 100 m (Pukul 08:00 – 18:00).</li>
          <li>Terminal, Stasiun dan Pelabuhan: Radius 300 m (24 Jam).</li>
        </ul>  
      <li><b>Zona Kecepatan Maksimal 40 km/jam:</b></li>
        <ul>
          <li>Fasilitas Kesehatan: Radius 150 m (24 Jam).</li>
          <li>Kantor Pemerintahan: Radius 100 m (Pukul 07:00 – 17:00).</li>
          <li>Kantor Polisi: Radius 100 m (24 Jam).</li>
        </ul> 
      <li><b>Zona Kecepatan Maksimal 60 km/jam:</b></li>
        <ul>
          <li>Bandara: Radius 500 m (24 Jam).</li>
        </ul> `,
    source: "Analisis spasial geofencing"
  },
  administrasi: {
    title: "Wilayah Administrasi",
    desc: "Peta batas wilayah resmi yang mengacu pada Keputusan Menteri Dalam Negeri No. 100.1.1-6117 Tahun 2022.",
    detail: "Peta ini memuat batas wilayah mulai dari tingkat Provinsi, Kabupaten/Kota, hingga Kecamatan. Selain itu, peta ini juga dilengkapi informasi jumlah penduduk di setiap Kecamatan.",
    source: "Dukcapil Kemendagri Tahun 2024"
  }
};

document.addEventListener('click', function(e) {
  const btn = e.target.closest('.btn-info-layer');
  
  if (btn) {
    e.preventDefault();
    e.stopPropagation();
    const key = btn.getAttribute('data-info');
    openInfoModal(key);
  }
  if (e.target.id === 'info-modal-close' || e.target.closest('#info-modal-close')) {
    closeInfoModal();
  }
  if (e.target.id === 'info-modal-overlay') {
    closeInfoModal();
  }
});

function openInfoModal(layerKey) {
  const modalOverlay = document.getElementById('info-modal-overlay');
  const modalTitle = document.getElementById('info-modal-title');
  const modalBody = document.getElementById('info-modal-body');
  
  const info = LAYER_INFO_DATA[layerKey];

  if (info && modalOverlay && modalTitle && modalBody) {
    modalTitle.textContent = info.title;
    modalBody.innerHTML = `
      <div class="info-section">
        <span class="info-label">Deskripsi Singkat:</span>
        <div class="info-content">${info.desc}</div>
      </div>
      <div class="info-section">
        <span class="info-label">Detail:</span>
        <div class="info-content">${info.detail}</div>
      </div>
      <div class="info-section">
        <span class="info-label">Sumber Data:</span>
        <span class="source-badge">${info.source}</span>
      </div>
    `;
    modalOverlay.style.display = 'flex';
  } else {
    console.warn('Data Info tidak ditemukan untuk:', layerKey);
  }
}

function closeInfoModal() {
  const modalOverlay = document.getElementById('info-modal-overlay');
  if (modalOverlay) {
    modalOverlay.style.display = 'none';
  }
}

document.getElementById('btn-download-fab').addEventListener('click', function(e) {
    e.stopPropagation();
    const menu = document.getElementById('download-menu-list');
    menu.classList.toggle('visible');
});

document.addEventListener('click', function(e) {
    const container = document.getElementById('download-control-container');
    const menu = document.getElementById('download-menu-list');
    if (!container.contains(e.target)) {
        menu.classList.remove('visible');
    }
});

function generateExcel(layerKey, fileName) {
    const dataGeoJSON = filteredLayerData[layerKey];

    if (!dataGeoJSON || !dataGeoJSON.features || dataGeoJSON.features.length === 0) {
        alert("Tidak ada data untuk didownload pada layer ini di Satwil saat ini.");
        return;
    }

    const exportData = [];

    dataGeoJSON.features.forEach(feature => {
        const props = feature.properties || {};
        const geom = feature.geometry;
        let row = {};
        Object.keys(ALIAS_MAP).forEach(key => {
            if (props.hasOwnProperty(key)) {
                const headerName = ALIAS_MAP[key];
                row[headerName] = props[key];
            }
        });
        let coords = null;

        if (geom.type === 'Point') {
            coords = geom.coordinates;
        } else {
            try {
                const centerPoint = turf.centroid(feature);
                coords = centerPoint.geometry.coordinates;
            } catch (e) {
                console.warn('Gagal mengambil titik tengah geometri', e);
            }
        }

        if (coords) {
            row['Longitude'] = coords[0];
            row['Latitude'] = coords[1];
        }

        exportData.push(row);
    });

    if (exportData.length === 0) {
        alert("Data tersedia namun tidak memiliki atribut yang sesuai dengan konfigurasi Alias Map.");
        return;
    }
    const worksheet = XLSX.utils.json_to_sheet(exportData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Data");
    const dateStr = new Date().toISOString().slice(0,10);
    const fullFileName = `${fileName}_${selectedSatwilName || 'Satwil'}_${dateStr}.xlsx`;

    XLSX.writeFile(workbook, fullFileName);
    
    document.getElementById('download-menu-list').classList.remove('visible');
}
function checkPatrolAlerts() {
    const container = document.getElementById('patrol-alert-container');
    if (!container) return;

    const patrols = filteredLayerData.patrol?.features || [];
    const geofences = filteredLayerData.geofencing?.features || [];
    const cctvs = filteredLayerData.cctv?.features || [];

    if (patrols.length === 0) {
        createPatrolNotificationUI([]); 
        return;
    }

    let activePatrols = [];

    patrols.forEach(patrol => {
        if (!patrol.geometry || !patrol.geometry.coordinates) return;

        let patrolCoord;
        try {
            const center = turf.centroid(patrol);
            patrolCoord = center.geometry.coordinates;
        } catch (e) { return; }

        const pProps = patrol.properties || {};
        const pName = pProps.Nama || pProps.nama || pProps.vehicleName || 'Unit Patroli'; 
        
        let detectedItems = []; 

        for (const zone of geofences) {
            try {
                if (turf.booleanPointInPolygon(patrolCoord, zone)) {
                    const zProps = zone.properties || {};
                    const zName = zProps.nama_zona || zProps.tipe_zona || 'Zona Khusus';
                    detectedItems.push({
                        type: 'geofence',
                        label: `Area ${zName}`,
                        coord: null
                    });
                }
            } catch (e) {}
        }

        for (const cam of cctvs) {
            try {
                let camCoord = cam.geometry.coordinates;
                const dist = turf.distance(patrolCoord, camCoord, { units: 'kilometers' });

                if (dist <= 0.05) { 
                    const cProps = cam.properties || {};
                    const cName = cProps.nama_cctv || cProps.nama_jalan || 'CCTV';
                    detectedItems.push({
                        type: 'cctv',
                        label: `Dekat ${cName}`,
                        coord: camCoord
                    });
                }
            } catch (e) {}
        }

        const uniqueItems = [];
        const seenLabels = new Set();
        detectedItems.forEach(item => {
            if (!seenLabels.has(item.label)) {
                seenLabels.add(item.label);
                uniqueItems.push(item);
            }
        });

        if (uniqueItems.length === 0) {
            uniqueItems.push({
                type: 'info',
                label: 'Petugas tidak berada di Area Geofencing atau CCTV manapun.',
                coord: null
            });
        }

        activePatrols.push({
            unit: pName,
            coord: patrolCoord,
            items: uniqueItems
        });
    });

    const currentAlertState = JSON.stringify(activePatrols);
    if (currentAlertState === lastPatrolAlertState) return;
    
    lastPatrolAlertState = currentAlertState;
    createPatrolNotificationUI(activePatrols);
}

function createPatrolNotificationUI(activePatrols) {
    const container = document.getElementById('patrol-alert-container');
    if (!container) return;

    if (isPatrolAlertMinimized) {
        const countText = activePatrols.length > 0 ? activePatrols.length : "0";
        container.innerHTML = `
            <div class="patrol-minimized-tab" title="Klik untuk melihat detail" id="btn-maximize-patrol">
                <span style="font-size:18px;">🚓</span>
                <span>${countText} Petugas Patroli Terpantau</span>
            </div>
        `;
        document.getElementById('btn-maximize-patrol').addEventListener('click', () => {
            isPatrolAlertMinimized = false;
            lastPatrolAlertState = ""; 
            checkPatrolAlerts(); 
        });
        return;
    }

    let listHtml = '';
    let summaryText = '';

    if (activePatrols.length === 0) {
        summaryText = `Tidak ada data patroli saat ini.`;
        listHtml = `
            <li class="patrol-detail-item" style="justify-content: center; padding: 20px 0; color: #ccc; font-style: italic;">
               Tidak ada Petugas Patroli
            </li>
        `;
    } else {
        summaryText = `Terpantau <span style="color:#ffeb3b; font-size:15px;">${activePatrols.length} Petugas</span> aktif:
                       ${followedPatrolUnit ? '<br><small style="color:#f44336">Sedang Mengikuti: ' + followedPatrolUnit + '</small>' : ''}`;

        activePatrols.forEach(p => {
            let locationsHtml = '';
            
            p.items.forEach(item => {
                let actionButton = '';
                if (item.type === 'cctv' && item.coord) {
                    actionButton = `
                        <button onclick="locateAndOpenCCTV(${item.coord[0]}, ${item.coord[1]})" 
                                style="background: #00e5ff; color: #000; border: none; border-radius: 4px; padding: 2px 6px; font-size: 9px; font-weight: 700; cursor: pointer; margin-left: 6px; text-transform: uppercase;">
                            Cek CCTV
                        </button>
                    `;
                }

                const bulletColor = item.type === 'info' ? '#777' : '#00e5ff';
                const textColor = item.type === 'info' ? '#aaa' : '#fff';

                locationsHtml += `
                    <div style="display: flex; align-items: flex-start; gap: 6px; margin-bottom: 4px;">
                        <span style="color: ${bulletColor}; font-size: 10px; margin-top: 3px;">●</span>
                        <span style="flex: 1; color: ${textColor};">${item.label}</span>
                        ${actionButton}
                    </div>
                `;
            });

            const isFollowing = (followedPatrolUnit === p.unit);
            const followBtnColor = isFollowing ? '#f44336' : '#ffeb3b';
            const followBtnText = isFollowing ? 'STOP' : 'FOLLOW';
            const activeClass = isFollowing ? 'pulse-animation' : '';

            listHtml += `
              <li class="patrol-detail-item" 
                  style="display: flex; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                
                <div style="flex: 0 0 40px; display: flex; flex-direction: column; align-items: center; padding-top: 2px;">
                    <span style="font-size: 20px;">👮</span>
                    <button onclick="toggleFollowPatrol('${p.unit}', [${p.coord}])"
                            class="${activeClass}"
                            style="margin-top: 4px; background: ${followBtnColor}; color: #000; border: none; border-radius: 4px; padding: 2px 4px; font-size: 8px; font-weight: 800; cursor: pointer; width: 100%; text-align: center;">
                        ${followBtnText}
                    </button>
                </div>

                <div style="flex: 1; padding-left: 8px;">
                    <div style="font-weight: 700; color: #ffeb3b; font-size: 13px; margin-bottom: 4px; cursor:pointer;"
                         onclick="zoomToPatrol([${p.coord}])" title="Zoom ke lokasi">
                        ${p.unit} ${isFollowing ? '<span style="color:#00e676; font-size:10px;">(LIVE)</span>' : ''}
                    </div>
                    <div style="font-size: 11px; line-height: 1.3;">
                        ${locationsHtml}
                    </div>
                </div>
              </li>`;
        });
    }

    const existingList = document.getElementById('patrol-dynamic-list');
    const existingSummary = document.getElementById('patrol-dynamic-summary');

    if (existingList && existingSummary) {
        existingList.innerHTML = listHtml;
        existingSummary.innerHTML = summaryText;
    } else {
        const card = document.createElement('div');
        card.className = 'patrol-alert-card';
        card.innerHTML = `
          <div class="patrol-card-header">
            <div class="patrol-card-title">
              <span style="font-size:16px;">🚓</span> MONITORING PATROLI
            </div>
            <button id="btn-minimize-patrol" 
                    style="background:none; border:none; color:#fff; cursor:pointer; font-weight:800; font-size:18px; line-height:1; transform: translateY(-5px); padding: 0 5px;" 
                    title="Minimize">_</button>
          </div>
          
          <div class="patrol-card-body" style="padding: 0 15px 10px 15px;">
            <div id="patrol-dynamic-summary" class="patrol-summary-text" style="margin-top: 12px; margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom:8px;">
              ${summaryText}
            </div>
            <ul id="patrol-dynamic-list" class="patrol-detail-list" style="border-top: none; max-height: 200px;">
              ${listHtml}
            </ul>
          </div>
        `;

        container.innerHTML = '';
        container.appendChild(card);
        document.getElementById('btn-minimize-patrol').addEventListener('click', (e) => {
            e.stopPropagation();
            isPatrolAlertMinimized = true;
            lastPatrolAlertState = ""; 
            checkPatrolAlerts(); 
        });
    }
}

function zoomToPatrol(coord) {
  if (map) {
    map.flyTo({ center: coord, zoom: 18, speed: 1.5 });
  }
}
let followedPatrolUnit = null;

function initMapInteractionListeners() {
    map.on('dragstart', () => {
        if (followedPatrolUnit) {
            followedPatrolUnit = null;
            console.log("Follow mode stopped by user interaction.");
            lastPatrolAlertState = ""; 
            checkPatrolAlerts();
        }
    });
}

function toggleFollowPatrol(unitName, coordArray) {
    if (followedPatrolUnit === unitName) {
        followedPatrolUnit = null;
    } else {
        followedPatrolUnit = unitName;
        map.flyTo({ center: coordArray, zoom: 17, speed: 1.5 });
    }

    lastPatrolAlertState = ""; 
    checkPatrolAlerts();
}

function locateAndOpenCCTV(lng, lat) {
    map.flyTo({ center: [lng, lat], zoom: 18, speed: 1.5 });

    const cctvFeatures = filteredLayerData.cctv?.features || [];
    const targetFeature = cctvFeatures.find(f => {
        const c = f.geometry.coordinates;
        return Math.abs(c[0] - lng) < 0.000001 && Math.abs(c[1] - lat) < 0.000001;
    });

    if (targetFeature) {
        setTimeout(() => {
            showFeaturePopup(map, targetFeature, [lng, lat], 'cctv');
        }, 1000);
    } else {
        console.warn("Feature CCTV tidak ditemukan di koordinat ini.");
    }
}
</script>
<div id="info-modal-overlay">
  <div id="info-modal-box">
    <div id="info-modal-header">
      <h3 id="info-modal-title">Judul Layer</h3>
      <button id="info-modal-close">&times;</button>
    </div>
    <div id="info-modal-body">
      </div>
  </div>
</div>
<!-- Injected Laravel Auth & Variables -->
<script>
    window.id_unique = '{{ $id_unique ?? '' }}';
    window.tber = '{{ $token_bearer ?? '' }}';
    window.user_lat = '{{ $user_lat ?? '' }}';
    window.user_lng = '{{ $user_lng ?? '' }}';
    window.polda_id = '{{ $polda_id ?? '' }}';
    window.polres_id = '{{ $polres_id ?? '' }}';
</script>
<style>
    .btn-logout-float {
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 9999;
        background: #1a237e;
        color: #ffc107;
        padding: 10px 15px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(0,0,0,0.4);
        border: 2px solid #ffc107;
    }
    .btn-logout-float:hover {
        background: #0d47a1;
        color: #fff;
    }
</style>
<a href="{{ route('satupeta.auth.logout') }}" class="btn-logout-float">Logout / Kembali</a>
</body>
</html>
