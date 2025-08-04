<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\PreRegistration;
use App\Models\User;
use App\Services\MidtransService;

class PreRegistrationController extends Controller
{
    public function create()
    {
        $pending = PreRegistration::where('status', 'pending')->get();
        return view('pre_register', compact('pending'));
    }

    public function store(Request $request, MidtransService $midtrans)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:pre_registrations,email|unique:users,email',
            'phone' => 'required|string|max:20',
            'sponsor_id' => 'required|string',
            'payment_method' => 'required|in:rekening,qris',
            'payment_proof' => 'required_if:payment_method,rekening|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cari user sponsor
        $sponsor = User::where('referral_code', $request->sponsor_id)->first();
        if (! $sponsor) {
            return response()->json(['errors' => ['sponsor_id' => ['Kode sponsor tidak valid.']]], 422);
        }

        // ✅ === Metode Transfer Manual ===
        if ($request->payment_method === 'rekening') {
            $filePath = $request->file('payment_proof')->store('payment_proofs', 'public');

            PreRegistration::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'sponsor_id' => $sponsor->id,
                'payment_method' => 'rekening',
                'payment_proof' => $filePath,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran berhasil. Bukti pembayaran telah diunggah.'
            ]);
        }

        // ✅ === Metode QRIS Midtrans ===
        $orderId = 'REG-' . strtoupper(Str::random(10));
        $amount = 1500000; // Harga pendaftaran

        PreRegistration::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'sponsor_id' => $sponsor->id,
            'payment_method' => 'qris',
            'payment_proof' => $orderId, // Sementara simpan order_id di sini
            'status' => 'pending',
        ]);

        $payment = $midtrans->createQrisTransaction($orderId, $amount, $request->name);
        $qrAction = collect($payment->actions)->firstWhere('name', 'generate-qr-code');
        $qrUrl = $qrAction?->url ?? null;

        if (!$qrUrl) {
            return response()->json(['error' => 'Gagal mendapatkan QR dari Midtrans'], 500);
        }

        return response()->json([
    'redirect' => route('preregistration.qris') . "?orderId={$orderId}&qrUrl=" . urlencode($qrUrl)
]);
    }

    public function showQris(Request $request)
    {
        $qrUrl = $request->query('qrUrl') ?? session('qrUrl');
        $orderId = $request->query('orderId') ?? session('orderId');

        if (! $qrUrl || ! $orderId) {
            abort(404, 'QRIS atau Order ID tidak ditemukan');
        }

        return view('payment.qris', compact('qrUrl', 'orderId'));
    }
    
}
