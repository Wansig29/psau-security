<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk QR Sticker Print</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            padding: 20px;
            color: #111827;
        }
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 14px;
        }
        .toolbar .title { font-weight: 700; font-size: 14px; }
        .toolbar .hint { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .actions { display: flex; gap: 8px; }
        .btn {
            text-decoration: none;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 12px;
            cursor: pointer;
            background: #fff;
            color: #374151;
        }
        .btn-primary {
            background: #6b0a16;
            color: #fff;
            border-color: #6b0a16;
        }
        .sheet {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 12px;
        }
        .sticker {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .sticker-header {
            background: linear-gradient(135deg, #6b0a16 0%, #4e0710 100%);
            color: #fff;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 700;
        }
        .sticker-body {
            padding: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .plate {
            font-family: "Courier New", monospace;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 2px;
            padding: 2px 10px;
            border: 2px solid #111;
            border-radius: 4px;
        }
        .meta {
            width: 100%;
            font-size: 11px;
            color: #4b5563;
            line-height: 1.4;
        }
        .meta strong { color: #111827; }
        .sticker-footer {
            border-top: 1px solid #f3f4f6;
            padding: 7px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10px;
            color: #6b7280;
            font-family: monospace;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .toolbar { display: none !important; }
            .sheet {
                grid-template-columns: repeat(2, 1fr);
                gap: 8mm;
                padding: 6mm;
            }
            .sticker {
                break-inside: avoid;
                page-break-inside: avoid;
                border: 1px dashed #aaa;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <div class="title">Bulk QR Sticker Print ({{ $registrations->count() }})</div>
            <div class="hint">Print this page to produce stickers for all selected users.</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.approved.index') }}" class="btn">Back</a>
            <button class="btn btn-primary" onclick="trackAndPrint()">Print All</button>
        </div>
    </div>

    <div class="sheet">
        @foreach($registrations as $registration)
            <div class="sticker">
                <div class="sticker-header">
                    PSAU Vehicle Parking Sticker · A.Y. {{ $registration->school_year }}
                </div>
                <div class="sticker-body">
                    <div>{!! $qrCodeByRegistrationId[$registration->id] ?? '' !!}</div>
                    <div class="plate">{{ $registration->vehicle->plate_number }}</div>
                    <div class="meta">
                        <div><strong>Owner:</strong> {{ $registration->user->name }}</div>
                        <div><strong>Vehicle:</strong> {{ $registration->vehicle->make }} {{ $registration->vehicle->model }}</div>
                        <div><strong>Color:</strong> {{ ucfirst($registration->vehicle->color) }}</div>
                    </div>
                </div>
                <div class="sticker-footer">
                    <span>{{ $registration->qr_sticker_id }}</span>
                    <span>VALID</span>
                </div>
            </div>
        @endforeach
    </div>
    <script>
        function trackAndPrint() {
            var ids = {!! json_encode($registrations->pluck('id')) !!};
            fetch("{{ route('admin.approved.track-bulk-print') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ registration_ids: ids })
            }).catch(e => console.error(e));
            
            // Print dialog
            window.print();
        }
    </script>
</body>
</html>
