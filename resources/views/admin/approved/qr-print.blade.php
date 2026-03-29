<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Sticker — {{ $registration->qr_sticker_id }}</title>
    <!-- Add FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            background: #f0f2f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 30px 20px;
            min-height: 100vh;
        }

        /* ── Screen-only controls ── */
        .page-header {
            margin-bottom: 24px;
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
        .instruction-alert {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            color: #1e3a8a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
        }
        
        .controls {
            background: #fff;
            padding: 14px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 16px;
            align-items: center;
        }
        
        .shape-selector {
            display: flex;
            gap: 8px;
            align-items: center;
            background: #f3f4f6;
            padding: 6px;
            border-radius: 8px;
        }
        .btn-shape {
            background: transparent;
            border: 1px solid transparent;
            color: #4b5563;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-shape.active {
            background: #fff;
            color: #6b0a16;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-color: #e5e7eb;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            width: 100%;
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
        }
        .btn-print {
            background: #6b0a16;
            color: #fff;
            border: none;
            padding: 11px 26px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(107,10,22,0.3);
            transition: background 0.15s;
        }
        .btn-print:hover { background: #9b1224; }
        .btn-back {
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s;
        }
        .btn-back:hover { background: #f9fafb; }

        /* ── Sticker Frame / Shapes ── */
        .sticker-content {
            display: contents;
        }

        .sticker-wrap {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .sticker {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            border: 1px solid #e5e7eb;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: scale(var(--sticker-scale, 1));
            transform-origin: top center;
        }

        /* Portrait (Base styling) */
        .sticker.shape-portrait {
            width: 280px;
            display: flex;
            flex-direction: column;
        }

        /* Landscape */
        .sticker.shape-landscape {
            width: 500px;
            display: grid;
            grid-template-columns: 240px 1fr;
            grid-template-rows: auto 1fr auto;
        }
        .shape-landscape .sticker-header { grid-column: 1 / -1; }
        .shape-landscape .qr-block { 
            grid-column: 1; 
            grid-row: 2; 
            border-right: 1px solid #f3f4f6;
            align-items: center;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .shape-landscape .details-wrapper {
            grid-column: 2;
            grid-row: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .shape-landscape .plate-block { padding: 15px 18px 5px; }
        .shape-landscape .info-block { padding: 5px 18px 15px; }
        .shape-landscape .sticker-footer { grid-column: 1 / -1; }

        /* Square */
        .sticker.shape-square {
            width: 250px;
            display: flex;
            flex-direction: column;
        }
        .shape-square .info-block {
            display: none !important; /* Hide extra info completely */
        }
        .shape-square .qr-block {
            padding: 20px 20px 10px;
        }
        .shape-square .plate-block {
            padding: 5px 20px 20px;
        }

        /* Circle */
        .sticker.shape-circle {
            width: 380px;
            height: 380px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            position: relative;
            background: #fff;
            border: 8px solid #7b1113;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15); /* Removed yellow inner ring */
            padding: 24px; /* More padding to relieve stuffing */
            box-sizing: border-box;
            overflow: hidden;
        }
        .shape-circle .sticker-content {
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        .shape-circle .sticker-header {
            background: transparent;
            color: #111;
            padding: 0 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 6px;
            margin-bottom: 2px;
            width: 100%;
        }
        .shape-circle .seal {
            width: 44px;
            height: 44px;
            background: #7b1113;
            border: 2px solid #fff;
            color: #fff;
            font-size: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 4px;
        }
        .shape-circle .header-text {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .shape-circle .header-text .school { color: #7b1113; font-size: 9px; font-weight: 800; letter-spacing: 0.2px; text-transform: uppercase; margin-bottom: 2px; }
        .shape-circle .header-text .title { color: #111; font-size: 15px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2px; }
        .shape-circle .header-text .sy { 
            background: #7b1113; 
            color: #fff; 
            font-size: 9.5px; 
            font-weight: 600; 
            padding: 3px 12px; 
            border-radius: 12px; 
            margin-top: 5px; 
        }
        .shape-circle .qr-block {
            padding: 8px 0;
            flex: 0 0 auto;
            align-items: center;
            display: flex;
            justify-content: center;
        }
        .shape-circle .qr-frame {
            border: 3px solid #7b1113;
            border-radius: 8px;
            padding: 8px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .shape-circle .qr-frame svg {
            max-width: 130px;
        }
        .shape-circle .details-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0;
            width: 100%;
        }
        .shape-circle .info-block {
            display: none !important; /* Keep circle printable; hide extra details */
        }
        .shape-circle .plate-block {
            padding: 2px 0 6px;
            width: 100%;
            text-align: center;
        }
        .shape-circle .plate {
            font-size: 24px;
            padding: 4px 16px;
            border: 2px dashed #7b1113;
            border-radius: 6px;
            color: #111;
            background: #fdfbfb;
            letter-spacing: 3px;
            font-weight: 900;
            font-family: 'Courier New', monospace;
        }
        .shape-circle .sticker-footer {
            background: transparent;
            border-top: none;
            position: static;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            width: 100%;
        }
        .shape-circle .sticker-id-text { font-size: 10px; color: #6b7280; font-family: monospace; font-weight: 600; letter-spacing: 0.5px; }
        .shape-circle .valid-badge { 
            background: #16a34a; 
            color: #fff; 
            font-size: 10px; 
            padding: 3px 14px; 
            border-radius: 999px; 
            font-weight: 800; 
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 6px rgba(22, 163, 74, 0.3);
        }

        /* ── Internal Elements ── */
        .sticker-header {
            background: linear-gradient(135deg, #6b0a16 0%, #4e0710 100%);
            color: #fff;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .seal {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .header-text .school { font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; line-height: 1.3; }
        .header-text .title { font-size: 12px; font-weight: 800; letter-spacing: 0.2px; margin-top: 2px; }
        .header-text .sy { font-size: 9px; opacity: 0.7; margin-top: 1px; }

        .qr-block {
            padding: 16px;
            display: flex;
            justify-content: center;
        }
        .qr-frame {
            border: 3px solid #6b0a16;
            border-radius: 10px;
            padding: 8px;
            display: inline-block;
            background: #fff;
        }
        .qr-frame svg { display: block; width: 100%; height: auto; max-width: 200px; }

        .plate-block { text-align: center; padding: 0 16px 12px; }
        .plate {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 3px;
            color: #111;
            border: 3px solid #111;
            display: inline-block;
            padding: 4px 14px;
            border-radius: 4px;
            background: #fff;
        }

        .info-block { padding: 4px 16px 14px; }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 8px; }
        .info-value { color: #111827; font-weight: 700; text-align: right; max-width: 60%; }

        .sticker-footer {
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
            padding: 8px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sticker-id-text { font-size: 8px; color: #9ca3af; letter-spacing: 0.5px; font-family: monospace; }
        .valid-badge {
            background: #dcfce7; color: #15803d; font-size: 9px; font-weight: 700;
            padding: 2px 8px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px;
        }

        /* ── Editable Text States ── */
        [contenteditable="true"] { outline: none; transition: all 0.2s ease; border-radius: 3px; }
        [contenteditable="true"]:hover, [contenteditable="true"]:focus {
            background: rgba(250, 204, 21, 0.25);
            box-shadow: 0 0 0 2px rgba(250, 204, 21, 0.5);
            cursor: text;
        }

        /* ── Print styles ── */
        @media print {
            body { background: #fff; padding: 0; justify-content: flex-start; align-items: flex-start; }
            .page-header { display: none !important; }
            .sticker {
                box-shadow: none !important;
                border: 1.5px dashed #bbb !important; /* Print guide */
                margin: 10mm;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border-radius: 8px;
            }
            .sticker.shape-circle {
                border-radius: 50% !important;
                width: 90mm;
                height: 90mm;
                border: 8px solid #7b1113 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .sticker-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .qr-frame { border-color: #6b0a16; }
            [contenteditable="true"] { background: transparent !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>

    <!-- Controls & Instructions (hidden when printing) -->
    <div class="page-header">
        <div class="instruction-alert">
            <span style="font-size: 20px;"><i class="fas fa-lightbulb"></i></span>
            <div>
                <strong>Pro Tip:</strong> Select the shape below, and double-click any text inside the sticker to edit or correct it before you press Print.
            </div>
        </div>
        
        <div class="controls">
            <div class="shape-selector">
                <button class="btn-shape active" onclick="setShape('portrait')" id="btn-portrait">
                    <i class="fas fa-mobile-alt"></i> Portrait
                </button>
                <button class="btn-shape" onclick="setShape('landscape')" id="btn-landscape">
                    <i class="fas fa-id-card"></i> Landscape
                </button>
                <button class="btn-shape" onclick="setShape('square')" id="btn-square">
                    <i class="fas fa-square"></i> Square
                </button>
                <button class="btn-shape" onclick="setShape('circle')" id="btn-circle">
                    <i class="fas fa-circle"></i> Circle
                </button>
            </div>

            <div style="width:100%;max-width:420px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <div style="font-size:12px;font-weight:800;color:#111827;">
                        Sticker size
                    </div>
                    <div style="font-size:12px;color:#6b7280;">
                        <span id="sizeLabel">100%</span>
                    </div>
                </div>
                <input id="sizeRange"
                       type="range"
                       min="70"
                       max="140"
                       value="100"
                       step="5"
                       style="width:100%;accent-color:#6b0a16;">
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#9ca3af;margin-top:4px;">
                    <span>Smaller</span>
                    <span>Larger</span>
                </div>
            </div>
            
            <div class="actions">
                <a href="{{ route('admin.approved.index') }}" class="btn-back">
                    ← Back to List
                </a>
                <button class="btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Sticker
                </button>
            </div>
        </div>
    </div>

    <!-- QR Sticker -->
    <div class="sticker-wrap">
        <div class="sticker shape-portrait" id="print-sticker">
            <div class="sticker-content">
                <!-- Header -->
                <div class="sticker-header">
                    <div class="seal"><i class="fas fa-graduation-cap"></i></div>
                    <div class="header-text">
                        <div class="school" contenteditable="true">Pampanga State Agricultural University</div>
                        <div class="title" contenteditable="true">Vehicle Parking Sticker</div>
                        <div class="sy" contenteditable="true">A.Y. {{ $registration->school_year }}</div>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="qr-block">
                    <div class="qr-frame">
                        {!! $qrCodeSvg !!}
                    </div>
                </div>

                <div class="details-wrapper">
                    <!-- Plate Number -->
                    <div class="plate-block">
                        <div class="plate" contenteditable="true">{{ $registration->vehicle->plate_number }}</div>
                    </div>


                    <!-- Info Box -->
                    <div class="info-block">
                        <div class="info-row">
                            <span class="info-label">Owner</span>
                            <span class="info-value" contenteditable="true">{{ $registration->user->name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Vehicle</span>
                            <span class="info-value" contenteditable="true">{{ $registration->vehicle->make }} {{ $registration->vehicle->model }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Color</span>
                            <span class="info-value" contenteditable="true">{{ ucfirst($registration->vehicle->color) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Valid Thru</span>
                            <span class="info-value" contenteditable="true">July {{ date('Y') + 1 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticker-footer">
                    <span class="sticker-id-text">{{ $registration->qr_sticker_id }}</span>
                    <span class="valid-badge"><i class="fas fa-check-circle mr-1"></i> VALID</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Logic -->
    <script>
        // Disable BFCache reload on this page
        window.onpageshow = null;
        window.addEventListener('pageshow', function(e) { e.stopImmediatePropagation(); }, true);
        
        // Prevent enter key in editables from blowing up the layout
        document.querySelectorAll('[contenteditable="true"]').forEach(function(el) {
            el.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    el.blur();
                }
            });
        });

        // Shape Toggler
        function setShape(shape) {
            // Update buttons
            document.querySelectorAll('.btn-shape').forEach(btn => btn.classList.remove('active'));
            document.getElementById('btn-' + shape).classList.add('active');
            
            // Update sticker layout class
            var sticker = document.getElementById('print-sticker');
            sticker.className = 'sticker shape-' + shape;
        }

        // Size Slider (scales on screen and print)
        (function initStickerSizer(){
            var slider = document.getElementById('sizeRange');
            var label = document.getElementById('sizeLabel');
            var sticker = document.getElementById('print-sticker');
            if (!slider || !label || !sticker) return;

            function apply() {
                var pct = parseInt(slider.value, 10) || 100;
                var scale = Math.max(0.5, Math.min(2, pct / 100));
                sticker.style.setProperty('--sticker-scale', String(scale));
                label.textContent = pct + '%';
            }

            slider.addEventListener('input', apply);
            apply();
        })();
    </script>
</body>
</html>
