@extends('layouts.admin')

@section('title', 'Admin - KPR Screening')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Dashboard Admin
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Manajemen Dokumen Pendaftar NUP</h1>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Registrasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode Pembayaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokumen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Screening</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($registrations as $registration)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $registration->registration_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $registration->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($registration->payment_method === 'kpr_bank_flpp')
                                    KPR Bank FLPP
                                @elseif($registration->payment_method === 'tunai_mitra_pintar')
                                    Tunai Mitra Pintar
                                @elseif(in_array($registration->payment_method, ['inhouse', 'installment'], true))
                                    In-House @if($registration->kpr_switched)<span class="text-[10px] text-indigo-600 font-bold block">(Alih dari KPR)</span>@endif
                                @elseif(in_array($registration->payment_method, ['cash', 'tunai'], true))
                                    Tunai @if($registration->kpr_switched)<span class="text-[10px] text-emerald-600 font-bold block">(Alih dari KPR)</span>@endif
                                @else
                                    {{ strtoupper(str_replace('_', ' ', $registration->payment_method)) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-900 space-x-1">
                                @if($registration->ktp_file) <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-medium"><svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>KTP</span> @endif
                                @if($registration->kk_file) <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-medium"><svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>KK</span> @endif
                                @if($registration->npwp_file) <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-medium"><svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>NPWP</span> @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($registration->kprScreening)
                                    @php
                                        $scr = $registration->kprScreening;
                                        $isScrCancelled = ($scr->participant_decision === 'not_agree' || $scr->rejection_reason === 'User membatalkan pengajuan KPR');
                                        $isSwitchedInhouse = ($scr->participant_decision === 'switch_to_inhouse' || ($registration->kpr_switched && in_array($registration->payment_method, ['inhouse', 'installment'], true)));
                                        $isSwitchedCash = ($scr->participant_decision === 'switch_to_cash' || ($registration->kpr_switched && in_array($registration->payment_method, ['cash', 'tunai'], true)));
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @if($isSwitchedInhouse) bg-indigo-100 text-indigo-800
                                        @elseif($isSwitchedCash) bg-emerald-100 text-emerald-800
                                        @elseif($isScrCancelled) bg-slate-100 text-slate-700
                                        @elseif($scr->status === 'approved') bg-green-100 text-green-800
                                        @elseif($scr->status === 'layak_bersyarat') bg-yellow-100 text-yellow-800
                                        @elseif($scr->status === 'rejected') bg-red-100 text-red-800
                                        @elseif($scr->status === 'processing') bg-blue-100 text-blue-800
                                        @elseif($scr->status === 'submitted') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800 @endif
                                    ">
                                        @if($isSwitchedInhouse) ALIH IN-HOUSE
                                        @elseif($isSwitchedCash) ALIH TUNAI
                                        @elseif($isScrCancelled) BATAL PEMOHON
                                        @elseif($scr->status === 'approved') LAYAK PROSES
                                        @elseif($scr->status === 'layak_bersyarat') LAYAK BERSYARAT
                                        @elseif($scr->status === 'rejected') TIDAK LAYAK
                                        @else {{ strtoupper(str_replace('_', ' ', $scr->status)) }} @endif
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">BELUM SCREENING</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registration->created_at->format('d-m-Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($registration->kprScreening)
                                    <a href="{{ route('admin.kpr.show', $registration->kprScreening->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Tinjau Dokumen</a>
                                @else
                                    <a href="{{ route('admin.nup.show', $registration->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Lihat Dokumen NUP</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada data pendaftar dengan dokumen</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
            <div class="mt-6">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
