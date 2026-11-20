@extends('layouts.admin')

@section('title', 'Test WhatsApp Gateway')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><svg class="w-5 h-5 inline-block text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Test WhatsApp Gateway</h1>
        <p class="text-gray-500 mt-1 text-sm">Kirim pesan WhatsApp percobaan untuk memverifikasi konfigurasi.</p>
    </div>

    {{-- Flash messages --}}
    @session('success')
        <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 text-green-800 text-sm">
            <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> {{ session('success') }}
        </div>
    @endsession
    @session('error')
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-red-800 text-sm">
            <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> {{ session('error') }}
        </div>
    @endsession

    {{-- Form Test Kirim WA --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2"><svg class="w-5 h-5 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Kirim Pesan Test</h2>
        <form action="{{ route('admin.wa.send') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp Tujuan</label>
                <input type="text" name="phone" placeholder="Contoh: 08123456789 atau 628123456789"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    value="{{ old('phone') }}" required>
                <p class="text-xs text-gray-400 mt-1">Boleh format 08xx atau 628xx, sistem akan otomatis format.</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pesan</label>
                <textarea name="message" rows="5"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required>{{ old('message', "Halo! Ini adalah pesan test dari Aplikasi NUP Perumahan.\n\nJika Anda menerima pesan ini, berarti WhatsApp Gateway sudah berfungsi dengan baik.\n\nTerima kasih!") }}</textarea>
            </div>
            <button type="submit" @if(!$isConfigured) disabled @endif
                class="w-full py-3 px-6 rounded-lg font-semibold text-white transition
                @if($isConfigured) bg-green-600 hover:bg-green-700 @else bg-gray-400 cursor-not-allowed @endif">
                {{ $isConfigured ? '<svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> Kirim Test WA Sekarang' : '<svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Gateway Belum Dikonfigurasi' }}
            </button>
        </form>
    </div>

    {{-- Riwayat Notifikasi WA --}}
    <div class="mt-6 bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-4"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Riwayat Notifikasi WhatsApp (10 Terakhir)</h2>
        @if($notifications->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-200">
                            <th class="pb-2 font-medium text-gray-600">No. HP</th>
                            <th class="pb-2 font-medium text-gray-600">Jenis</th>
                            <th class="pb-2 font-medium text-gray-600">Status</th>
                            <th class="pb-2 font-medium text-gray-600">Respon Gateway</th>
                            <th class="pb-2 font-medium text-gray-600">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($notifications as $notif)
                        <tr>
                            <td class="py-2 text-gray-700">{{ $notif->phone_number }}</td>
                            <td class="py-2 text-gray-600">{{ $notif->notification_type }}</td>
                            <td class="py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    @if($notif->status === 'sent') bg-green-100 text-green-700
                                    @elseif($notif->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($notif->status === 'failed') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-600 @endif">
                                    {{ strtoupper($notif->status) }}
                                </span>
                            </td>
                            <td class="py-2 text-gray-500 text-xs max-w-xs truncate" title="{{ $notif->gateway_response }}">
                                @php
                                    $gw = json_decode($notif->gateway_response, true);
                                    $gwMsg = '-';
                                    if (is_array($gw)) {
                                        $gwMsg = $gw['data']['reason'] ?? $gw['data']['detail'] ?? $gw['message'] ?? $gw['reason'] ?? $gw['detail'] ?? '-';
                                    } elseif ($notif->gateway_response) {
                                        $gwMsg = $notif->gateway_response;
                                    }
                                @endphp
                                {{ $gwMsg }}
                            </td>
                            <td class="py-2 text-gray-500 text-xs">{{ $notif->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-400 text-sm text-center py-4">Belum ada riwayat notifikasi.</p>
        @endif
    </div>
</div>
@endsection
