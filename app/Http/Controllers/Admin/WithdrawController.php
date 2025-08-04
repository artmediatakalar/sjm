<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\CashTransaction;
use GuzzleHttp\Client;

class WithdrawController extends Controller
{
   public function index()
{
    $withdraws = Withdrawal::where('status', 'pending')->get();
    return view('admin.withdraw.index', compact('withdraws'));
}

public function approve($id, Request $request)
{
    $withdraw = Withdrawal::findOrFail($id);
    $withdraw->status = 'menunggu';
    $withdraw->admin_notes = $request->admin_notes;
    $withdraw->approved_at = now();
    $withdraw->save();

    return response()->json([
        'success' => true,
        'message' => 'Withdraw berhasil disetujui.'
    ]);
}

public function reject($id, Request $request)
    {
        $withdraw = Withdrawal::findOrFail($id);

        if ($withdraw->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $withdraw->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Permintaan withdraw ditolak.');
    }
    protected function sendWhatsApp($phone, $message)
{
    if (str_starts_with($phone, '0')) {
        $phone = '+62' . substr($phone, 1);
    }

    try {
        $client = new Client();
        $client->post('https://api.fonnte.com/send', [
            'headers' => [
                'Authorization' => env('FONNTE_TOKEN'),
            ],
            'form_params' => [
                'target' => $phone,
                'message' => $message,
                'delay' => 2,
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error("❌ Gagal kirim WA ke {$phone}: " . $e->getMessage());
    }
}

}
