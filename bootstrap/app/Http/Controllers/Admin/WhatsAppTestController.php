<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappNotification;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppTestController extends Controller
{
    protected $waService;

    public function __construct(WhatsAppGatewayService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Tampilkan halaman test WA gateway
     */
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $isConfigured = $this->waService->isConfigured();
        $notifications = WhatsappNotification::latest()->take(10)->get();

        return view('admin.wa-test', compact('isConfigured', 'notifications'));
    }

    /**
     * Kirim pesan WhatsApp test
     */
    public function send(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'phone'   => 'required|string|min:9|max:20',
            'message' => 'required|string|max:1000',
        ]);

        if (!$this->waService->isConfigured()) {
            return back()->with('error', 'WhatsApp Gateway belum dikonfigurasi. Isi WHATSAPP_GATEWAY_API_KEY di file .env terlebih dahulu.');
        }

        $result = $this->waService->sendMessage(
            $request->phone,
            $request->message,
            'test',
            Auth::id()
        );

        if ($result['success'] ?? false) {
            return back()->with('success', 'Pesan WhatsApp berhasil dikirim! Message ID: ' . ($result['message_id'] ?? '-'));
        } else {
            $errMsg = $result['message'] ?? 'Gagal mengirim pesan.';
            return back()->with('error', 'Gagal mengirim pesan: ' . $errMsg);
        }
    }
}
