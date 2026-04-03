<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Vehicle — PSAU Parking</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; color: #1f2937; min-height: 100vh; }

        /* Topbar */
        .topbar {
            background: linear-gradient(135deg, #6b0a16 0%, #9b1224 100%);
            color: #fff; display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; height: 60px; box-shadow: 0 2px 8px rgba(0,0,0,0.25);
            position: sticky; top: 0; z-index: 100;
        }
        .topbar-brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; }
        .topbar-link {
            color: rgba(255,255,255,0.80); text-decoration: none; font-size: 13px;
            padding: 6px 12px; border-radius: 6px; transition: background 0.2s;
        }
        .topbar-link:hover { background: rgba(255,255,255,0.15); color: #fff; }

        /* Layout */
        .page-wrapper { max-width: 860px; margin: 36px auto; padding: 0 20px; }
        .page-title { font-size: 22px; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
        .page-subtitle { font-size: 14px; color: #6b7280; margin-bottom: 28px; }

        /* Card */
        .card { background: #fff; border-radius: 14px; box-shadow: 0 1px 6px rgba(0,0,0,0.09); overflow: hidden; margin-bottom: 22px; }
        .card-header { padding: 18px 24px; background: linear-gradient(to right, #6b0a16, #9b1224); }
        .card-header h2 { color: #fff; font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .card-header p { color: rgba(255,255,255,0.75); font-size: 12px; margin-top: 4px; }
        .card-body { padding: 24px; }

        /* Form */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }

        label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        label span.required { color: #dc2626; margin-left: 2px; }

        input[type="text"], input[type="tel"], input[type="file"] {
            width: 100%; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 9px 12px;
            font-size: 13px; font-family: 'Inter', sans-serif; color: #111827;
            background: #fafafa; transition: border-color 0.2s, box-shadow 0.2s; outline: none;
        }
        input[type="text"]:focus,
        input[type="tel"]:focus { border-color: #6b0a16; box-shadow: 0 0 0 3px rgba(107,10,22,0.1); }
        input[type="file"] { cursor: pointer; padding: 7px 10px; }
        input[type="file"]:focus { border-color: #6b0a16; box-shadow: 0 0 0 3px rgba(107,10,22,0.1); }

        .field-hint { font-size: 11px; color: #9ca3af; margin-top: 4px; }
        .field-error { font-size: 11px; color: #dc2626; margin-top: 4px; }

        /* Document upload grid */
        .doc-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }

        .doc-card {
            border: 1.5px dashed #d1d5db; border-radius: 10px; padding: 16px; text-align: center;
            transition: border-color 0.2s, background 0.2s; background: #fafafa; position: relative;
        }
        .doc-card:hover { border-color: #6b0a16; background: #fdf8f8; }
        .doc-card.has-file { border-color: #16a34a; border-style: solid; background: #f0fdf4; }

        .doc-icon { font-size: 28px; margin-bottom: 8px; }
        .doc-label { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 2px; }
        .doc-sublabel { font-size: 11px; color: #9ca3af; margin-bottom: 10px; }

        .doc-input { display: none; }
        .doc-trigger {
            display: inline-block; background: #6b0a16; color: #fff; font-size: 12px; font-weight: 600;
            padding: 6px 14px; border-radius: 6px; cursor: pointer; transition: background 0.2s;
        }
        .doc-trigger:hover { background: #8b0e1e; }
        .doc-card.has-file .doc-trigger { background: #16a34a; }

        .doc-preview-wrap { margin-top: 10px; display: none; }
        .doc-preview-wrap img { width: 100%; max-height: 100px; object-fit: cover; border-radius: 6px; }
        .doc-filename { font-size: 10px; color: #6b7280; margin-top: 4px; word-break: break-all; }

        /* Divider */
        .divider { border: none; border-top: 1px solid #f3f4f6; margin: 20px 0; }

        /* Buttons */
        .form-actions { display: flex; align-items: center; justify-content: flex-end; gap: 12px; padding-top: 8px; }
        .btn-primary {
            background: #6b0a16; color: #fff; padding: 10px 24px; border-radius: 8px; border: none;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-primary:hover { background: #8b0e1e; }
        .btn-secondary {
            color: #6b7280; font-size: 13px; text-decoration: none;
            padding: 10px 16px; border-radius: 8px; transition: background 0.2s;
        }
        .btn-secondary:hover { background: #f3f4f6; color: #374151; }

        /* Alert */
        .alert-error-list { background: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; }
        .alert-error-list p { font-size: 13px; color: #991b1b; font-weight: 600; margin-bottom: 6px; }
        .alert-error-list ul { padding-left: 16px; }
        .alert-error-list li { font-size: 12px; color: #b91c1c; margin-top: 3px; }

        .step-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 50%; background: rgba(255,255,255,0.25);
            font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
        }
    </style>
</head>
<body>

    {{-- Topbar --}}
    <header class="topbar">
        <div class="topbar-brand">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            PSAU Parking Portal
        </div>
        <a href="{{ route('user.dashboard') }}" class="topbar-link">← Back to Dashboard</a>
    </header>

    <div class="page-wrapper">
        <h1 class="page-title">Register a New Vehicle</h1>
        <p class="page-subtitle">Fill in your vehicle details and upload all required supporting documents.</p>

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="alert-error-list">
                <p>⚠️ Please fix the following errors:</p>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('user.registration.store') }}" enctype="multipart/form-data" id="registrationForm">
            @csrf
            
            <div id="total-size-error" class="alert-error-list" style="display: none;">
                <p>⚠️ Total Upload Size Too Large</p>
                <ul>
                    <li>The combined size of all your documents exceeds the 20MB limit.</li>
                    <li>Please compress your images and try again.</li>
                </ul>
            </div>

            {{-- Step 1: Vehicle Info --}}
            <div class="card">
                <div class="card-header">
                    <h2><span class="step-badge">1</span> Vehicle Information</h2>
                    <p>Enter the basic details of the vehicle you want to register.</p>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div>
                            <label for="make">Vehicle Make <span class="required">*</span></label>
                            <input type="text" id="make" name="make" value="{{ old('make') }}" placeholder="e.g. Toyota, Honda, Yamaha" required>
                            @error('make')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="model">Vehicle Model <span class="required">*</span></label>
                            <input type="text" id="model" name="model" value="{{ old('model') }}" placeholder="e.g. Vios, Civic, Mio" required>
                            @error('model')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="color">Vehicle Color <span class="required">*</span></label>
                            <input type="text" id="color" name="color" value="{{ old('color') }}" placeholder="e.g. White, Black, Red" required>
                            @error('color')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="contact_number">Contact Number</label>
                            <input type="tel"
                                   id="contact_number"
                                   name="contact_number"
                                   value="{{ old('contact_number', auth()->user()->contact_number) }}"
                                   placeholder="09XXXXXXXXX or +639XXXXXXXXX"
                                   maxlength="13"
                                   pattern="(09\d{9}|\+639\d{9})"
                                   oninput="enforcePHPhone(this)"
                                   autocomplete="tel" />
                            @error('contact_number')<div class="field-error">{{ $message }}</div>@enderror
                            <div class="field-hint">Format: 09XXXXXXXXX (11 digits) or +639XXXXXXXXX. Used for security coordination.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Documents --}}
            <div class="card">
                <div class="card-header">
                    <h2><span class="step-badge">2</span> Required Documents</h2>
                    <p>Upload clear photos or scans of each document. JPEG or PNG only, max 5MB each.</p>
                </div>
                <div class="card-body">
                    <div class="doc-grid">

                        {{-- OR --}}
                        <div class="doc-card" id="card-or">
                            <div class="doc-icon">📄</div>
                            <div class="doc-label">Official Receipt (OR) <span class="required">*</span></div>
                            <div class="doc-sublabel">LTO Registration Receipt</div>
                            <label class="doc-trigger" for="doc_or">Choose File</label>
                            <input class="doc-input" type="file" id="doc_or" name="doc_or" accept="image/jpeg,image/png,image/jpg">
                            <div class="doc-preview-wrap" id="preview-or">
                                <img id="img-or" src="" alt="OR Preview">
                                <div class="doc-filename" id="name-or"></div>
                            </div>
                            @error('doc_or')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        {{-- CR --}}
                        <div class="doc-card" id="card-cr">
                            <div class="doc-icon">📋</div>
                            <div class="doc-label">Certificate of Registration (CR) <span class="required">*</span></div>
                            <div class="doc-sublabel">LTO Vehicle Certificate</div>
                            <label class="doc-trigger" for="doc_cr">Choose File</label>
                            <input class="doc-input" type="file" id="doc_cr" name="doc_cr" accept="image/jpeg,image/png,image/jpg">
                            <div class="doc-preview-wrap" id="preview-cr">
                                <img id="img-cr" src="" alt="CR Preview">
                                <div class="doc-filename" id="name-cr"></div>
                            </div>
                            @error('doc_cr')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        {{-- COR --}}
                        <div class="doc-card" id="card-cor">
                            <div class="doc-icon">🎓</div>
                            <div class="doc-label">Certificate of Registration (COR) <span class="required">*</span></div>
                            <div class="doc-sublabel">School enrollment COR</div>
                            <label class="doc-trigger" for="doc_cor">Choose File</label>
                            <input class="doc-input" type="file" id="doc_cor" name="doc_cor" accept="image/jpeg,image/png,image/jpg">
                            <div class="doc-preview-wrap" id="preview-cor">
                                <img id="img-cor" src="" alt="COR Preview">
                                <div class="doc-filename" id="name-cor"></div>
                            </div>
                            @error('doc_cor')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        {{-- License --}}
                        <div class="doc-card" id="card-license">
                            <div class="doc-icon">🪪</div>
                            <div class="doc-label">Driver's License <span class="required">*</span></div>
                            <div class="doc-sublabel">LTO Driver's License (front)</div>
                            <label class="doc-trigger" for="doc_license">Choose File</label>
                            <input class="doc-input" type="file" id="doc_license" name="doc_license" accept="image/jpeg,image/png,image/jpg">
                            <div class="doc-preview-wrap" id="preview-license">
                                <img id="img-license" src="" alt="License Preview">
                                <div class="doc-filename" id="name-license"></div>
                            </div>
                            @error('doc_license')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        {{-- School ID --}}
                        <div class="doc-card" id="card-school_id">
                            <div class="doc-icon">🏫</div>
                            <div class="doc-label">School ID <span class="required">*</span></div>
                            <div class="doc-sublabel">Valid PSAU Student/Faculty ID</div>
                            <label class="doc-trigger" for="doc_school_id">Choose File</label>
                            <input class="doc-input" type="file" id="doc_school_id" name="doc_school_id" accept="image/jpeg,image/png,image/jpg">
                            <div class="doc-preview-wrap" id="preview-school_id">
                                <img id="img-school_id" src="" alt="School ID Preview">
                                <div class="doc-filename" id="name-school_id"></div>
                            </div>
                            @error('doc_school_id')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('user.dashboard') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" id="submitBtn">
                    <svg id="submitIcon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                    <span id="submitText">Submit Registration</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        /* ── Fix 5: File-size guard (max 5 MB per file, max 20 MB total) ── */
        const MAX_BYTES_PER_FILE = 5 * 1024 * 1024; // 5 MB
        const MAX_BYTES_TOTAL = 20 * 1024 * 1024;   // 20 MB
        const docs = ['or', 'cr', 'cor', 'license', 'school_id'];
        
        document.getElementById('registrationForm').addEventListener('submit', function (e) {
            let totalBytes = 0;
            let missingDocs = [];

            docs.forEach(key => {
                const input = document.getElementById('doc_' + key);
                if (input && input.files.length > 0) {
                    totalBytes += input.files[0].size;
                } else {
                    missingDocs.push(key.toUpperCase());
                }
            });

            if (missingDocs.length > 0) {
                e.preventDefault();
                alert('Please upload all required documents. Missing: ' + missingDocs.join(', '));
                return;
            }
            
            if (totalBytes > MAX_BYTES_TOTAL) {
                e.preventDefault();
                document.getElementById('total-size-error').style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            } else {
                document.getElementById('total-size-error').style.display = 'none';
            }

            // Visual feedback for upload
            const btn = document.getElementById('submitBtn');
            const icon = document.getElementById('submitIcon');
            const text = document.getElementById('submitText');
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';
            icon.style.display = 'none';
            text.textContent = 'Uploading... Please wait';
        });

        docs.forEach(key => {
            const input = document.getElementById('doc_' + key);
            const card  = document.getElementById('card-' + key);
            const wrap  = document.getElementById('preview-' + key);
            const img   = document.getElementById('img-' + key);
            const name  = document.getElementById('name-' + key);

            // Remove any previous size-error element
            function clearSizeError() {
                const old = card.querySelector('.size-error');
                if (old) old.remove();
            }

            input.addEventListener('change', function () {
                clearSizeError();
                const file = this.files[0];
                if (!file) return;

                // ⚠️ Block files larger than 5 MB
                if (file.size > MAX_BYTES_PER_FILE) {
                    this.value = '';
                    card.classList.remove('has-file');
                    wrap.style.display = 'none';
                    const err = document.createElement('div');
                    err.className = 'field-error size-error';
                    err.style.marginTop = '8px';
                    err.textContent = '⚠️ File is too large. Maximum allowed size is 5 MB. Please choose a smaller file.';
                    card.appendChild(err);
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = e => { img.src = e.target.result; };
                reader.readAsDataURL(file);

                wrap.style.display = 'block';
                name.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                card.classList.add('has-file');
            });
        });

        /* ── Fix 1: Philippine phone enforcement ── */
        function enforcePHPhone(input) {
            let v = input.value.replace(/(?!^\+)[^\d]/g, '');
            input.value = v.startsWith('+') ? v.slice(0, 13) : v.slice(0, 11);
        }

        (function () {
            const IDLE_MS   = 15 * 60 * 1000; // 15 minutes
            const CSRF      = document.querySelector('meta[name="csrf-token"]') &&
                              document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let idleTimer;

            function resetIdle() {
                clearTimeout(idleTimer);
                idleTimer = setTimeout(function () {
                    fetch('{{ route('logout') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
                    }).finally(function () {
                        window.location.href = '{{ route('login') }}';
                    });
                }, IDLE_MS);
            }

            ['mousemove','mousedown','keydown','touchstart','scroll','click']
                .forEach(evt => document.addEventListener(evt, resetIdle, true));

            resetIdle(); // kick off on page load

            // Auto-scroll to errors if present
            const errorList = document.querySelector('.alert-error-list');
            if (errorList) {
                errorList.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        })();
    </script>

</body>
</html>
