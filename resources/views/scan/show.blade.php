<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle QR Scan — {{ $registration->vehicle->plate_number }}</title>
    <!-- Tailwind CSS (via CDN for standalone scan page) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f3f4f6; font-family: 'Inter', system-ui, sans-serif; }
        .school-header { background: linear-gradient(135deg, #7b1113 0%, #4e0710 100%); color: white; }
    </style>
</head>
<body class="antialiased min-h-screen pb-12">

    <!-- Header -->
    <div class="school-header text-center py-6 px-4 shadow-md rounded-b-[2rem] mb-6 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 flex items-center justify-center">
            <i class="fas fa-graduation-cap text-9xl"></i>
        </div>
        <div class="relative z-10">
            <h1 class="text-sm font-semibold tracking-wider uppercase opacity-90">Pangasinan State University</h1>
            <h2 class="text-xs font-medium opacity-75 mt-1">Asingan Campus</h2>
            <div class="mt-4">
                <span class="bg-white/20 px-4 py-1.5 rounded-full text-sm font-bold tracking-wide border border-white/30 backdrop-blur-sm">
                    A.Y. {{ $registration->school_year }}
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-md mx-auto px-4">

        <!-- Status Alert -->
        @if(strtolower((string) $registration->status) === 'approved')
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-5 flex items-start shadow-sm">
                <i class="fas fa-check-circle text-green-600 text-2xl mr-3 mt-0.5"></i>
                <div>
                    <h3 class="font-bold text-green-800 text-lg leading-tight">VALID REGISTRATION</h3>
                    <p class="text-green-700 text-sm mt-0.5">This vehicle is approved for entry.</p>
                </div>
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5 flex items-start shadow-sm">
                <i class="fas fa-times-circle text-red-600 text-2xl mr-3 mt-0.5"></i>
                <div>
                    <h3 class="font-bold text-red-800 text-lg leading-tight">INVALID REGISTRATION</h3>
                    <p class="text-red-700 text-sm mt-0.5">Status: {{ ucfirst($registration->status) }}</p>
                </div>
            </div>
        @endif

        <!-- Vehicle Details Card -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-5 border border-gray-100">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-100">
                <h3 class="text-gray-800 font-bold flex items-center text-sm uppercase tracking-wide">
                    <i class="fas fa-car text-gray-500 mr-2"></i> Vehicle Identity
                </h3>
            </div>
            
            <!-- Vehicle Photo -->
            <div class="w-full bg-gray-100 relative group" style="padding-top: 56.25%;">
                @if($registration->vehicle->photo_path)
                    <img src="{{ asset('storage/' . $registration->vehicle->photo_path) }}" alt="Vehicle" class="absolute inset-0 w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
                        <i class="fas fa-camera text-4xl mb-2 opacity-50"></i>
                        <span class="text-xs font-medium">No photo provided</span>
                    </div>
                @endif
            </div>

            <!-- Plate Number -->
            <div class="px-5 pt-5 pb-2 text-center">
                <div class="inline-block border-2 border-gray-800 rounded px-6 py-2 bg-gray-50 shadow-inner">
                    <span class="font-mono text-3xl font-black tracking-[0.2em] text-gray-900">{{ $registration->vehicle->plate_number }}</span>
                </div>
            </div>

            <div class="px-4 pb-5">
                <table class="w-full text-sm mt-3">
                    <tbody>
                        <tr class="border-b border-gray-50">
                            <td class="py-3 px-2 text-gray-500 font-medium w-1/3">Make/Model</td>
                            <td class="py-3 px-2 text-gray-900 font-bold text-right">{{ $registration->vehicle->make }} {{ $registration->vehicle->model }}</td>
                        </tr>
                        <tr class="border-b border-gray-50">
                            <td class="py-3 px-2 text-gray-500 font-medium">Color</td>
                            <td class="py-3 px-2 text-gray-900 font-bold text-right">{{ ucfirst($registration->vehicle->color) }}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-2 text-gray-500 font-medium">Sticker ID</td>
                            <td class="py-3 px-2 text-gray-900 font-mono text-xs text-right">{{ $registration->qr_sticker_id }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Owner Card -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-100">
                <h3 class="text-gray-800 font-bold flex items-center text-sm uppercase tracking-wide">
                    <i class="fas fa-user-circle text-gray-500 mr-2"></i> Owner Information
                </h3>
            </div>
            
            <div class="p-5 flex items-center">
                @if($registration->user->profile_photo_path)
                    <img src="{{ asset('storage/' . $registration->user->profile_photo_path) }}" alt="Profile" class="w-16 h-16 rounded-full object-cover border-2 border-gray-200 mr-4 shadow-sm">
                @else
                    <div class="w-16 h-16 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center mr-4 border-2 border-gray-100 shadow-sm">
                        <i class="fas fa-user text-2xl"></i>
                    </div>
                @endif
                
                <div>
                    <h4 class="font-bold text-gray-900 text-lg">{{ $registration->user->name }}</h4>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $registration->user->email }}</p>
                    @if(!empty($registration->user->contact_number))
                        @php $phoneHref = preg_replace('/\s+/', '', $registration->user->contact_number); @endphp
                        <a href="tel:{{ $phoneHref }}" class="text-sm font-semibold text-blue-700 mt-1 inline-flex items-center" style="text-decoration:none;">
                            <i class="fas fa-phone-alt mr-1"></i>{{ $registration->user->contact_number }}
                        </a>
                    @endif
                    <span class="inline-block mt-2 text-xs font-semibold px-2 py-0.5 bg-gray-100 text-gray-600 rounded">
                        Vehicle Owner
                    </span>
                </div>
            </div>
        </div>

        <!-- Guard / Admin Quick Link (Only visible to logged-in security/admins) -->
        @auth
            @if(auth()->user()->role === 'security' || auth()->user()->role === 'admin')
            <div class="mt-6 text-center">
                <a href="{{ route('security.search') }}?query={{ $registration->qr_sticker_id }}" class="inline-flex items-center justify-center w-full px-5 py-3 text-sm font-medium text-white transition-colors bg-[#7b1113] border border-transparent rounded-xl shadow-sm hover:bg-[#5a0c0e] focus:outline-none focus:ring-2 focus:ring-[#7b1113] focus:ring-offset-2">
                    <i class="fas fa-shield-alt mr-2"></i> View Full Security Profile
                </a>
            </div>
            @endif
        @endauth

    </div>

</body>
</html>
