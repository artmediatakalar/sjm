<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PreRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use App\Events\NewMemberApproved;
use App\Models\CashTransaction;

class PreRegistrationApprovalController extends Controller
{
   public function approve($id)
{
    $pre = PreRegistration::findOrFail($id);

    if ($pre->status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'Data ini sudah diproses sebelumnya.'
        ], 422);
    }

    $username = 'user' . \Str::random(5);
    $password = \Str::random(8);
    $sponsor = User::find($pre->sponsor_id);

    $user = User::create([
        'name' => $pre->name,
        'email' => $pre->email,
        'username' => $username,
        'password' => \Hash::make($password),
        'sponsor_id' => $sponsor->id,
        'must_change_credentials' => true,
    ]);

    $pre->update(['status' => 'approved']);

    // ✅ Tambahkan pencatatan cash masuk
       $already = CashTransaction::where('source', 'registration')
        ->where('user_id', $user->id)
        ->exists();

    if (! $already) {
        CashTransaction::create([
            'user_id' => $user->id,
            'type' => 'in',
            'source' => 'registration',
            'amount' => 1500000,
            'notes' => 'Dari pre-registration: ' . $pre->name,
            'payment_channel' => $pre->payment_method,
            'payment_reference' => $pre->payment_proof,
        ]);
    }

    // ✅ Broadcast event ke upline
    event(new NewMemberApproved($user->sponsor_id, $user));

    // ✅ Kirim WhatsApp
    $message = "Assalamu'alaikum {$user->name},\n\nAkun Anda di *PT. SAIR JAYA MANDIRI* telah dibuat:\n\n📌 Username: *{$username}*\n🔒 Password: *{$password}*\n\nSilakan login di https://sairjayamandiri.com/login dan segera ganti username dan password Anda.";
    $this->sendWhatsApp($pre->phone, $message);

    return response()->json([
        'success' => true,
        'message' => "Akun untuk {$user->name} berhasil dibuat, kas dicatat, dan WA telah dikirim."
    ]);
}

    // ✅ PASTIKAN fungsi ini ada DI DALAM class
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
