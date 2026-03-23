<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Sticker — {{ $registration->qr_sticker_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            min-height: 100vh;
        }

        /* Actions (hidden when printing) */
        .actions {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }
        .btn-print {
            background: #6b0a16;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-back {
            background: #6b7280;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        /* The sticker itself */
        .sticker {
            width: 280px;
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            text-align: center;
        }

        .sticker-header {
            background: #6b0a16;
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 10px 16px;
            margin: -20px -20px 16px -20px;
        }
        .sticker-header .school-name {
            font-size: 9px;
            letter-spacing: 0.5px;
            opacity: 0.85;
            text-transform: uppercase;
        }
        .sticker-header .sticker-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 2px;
        }

        .qr-container {
            margin: 0 auto 12px;
            display: inline-block;
            padding: 8px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
        }
        .qr-container svg {
            display: block;
        }

        .plate {
            font-family: 'Courier New', monospace;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 3px;
            color: #111;
            border: 3px solid #111;
            display: inline-block;
            padding: 4px 16px;
            border-radius: 4px;
            margin: 8px 0;
        }

        .vehicle-info {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .sticker-id {
            font-size: 8px;
            color: #9ca3af;
            letter-spacing: 0.5px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }

        .owner-info {
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            margin-top: 8px;
        }
        .school-year {
            font-size: 10px;
            color: #9ca3af;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .actions { display: none; }
            .sticker {
                box-shadow: none;
                border: 2px dashed #ccc;
            }
        }
    </style>
</head>
<body>

    <!-- Print/Back Buttons (hidden on print) -->
    <div class="actions">
        <a href="{{ route('admin.approved.index') }}" class="btn-back">← Back</a>
        <button class="btn-print" onclick="window.print()">🖨 Print Sticker</button>
    </div>

    <!-- QR Sticker -->
    <div class="sticker">
        <div class="sticker-header">
            <div class="school-name">Pangasinan State University Asingan</div>
            <div class="sticker-title">Vehicle Parking Sticker</div>
        </div>

        <!-- QR Code -->
        <div class="qr-container">
            {!! $qrCodeSvg !!}
        </div>

        <!-- Plate Number -->
        <div class="plate">{{ $registration->vehicle->plate_number }}</div>

        <!-- Vehicle Details -->
        <div class="vehicle-info">
            {{ $registration->vehicle->make }} {{ $registration->vehicle->model }} · {{ $registration->vehicle->color }}
        </div>

        <!-- Owner -->
        <div class="owner-info">{{ $registration->user->name }}</div>
        <div class="school-year">S.Y. {{ $registration->school_year }}</div>

        <!-- Sticker ID -->
        <div class="sticker-id">Sticker ID: {{ $registration->qr_sticker_id }}</div>
    </div>

</body>
</html>
