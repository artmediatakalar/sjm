<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BonusTransaction;
use App\Models\IncomeDetail;
use App\Helpers\TreeHelper;
use App\Models\Withdrawal;

use Carbon\Carbon;
class DashboardController extends Controller
{
    public function superAdmin() {
    $user = Auth::user();
    $totalBonus = BonusTransaction::where('user_id', $user->id)->sum('amount');

    return view('dashboards.super_admin', [
        'user' => $user,
        'totalBonus' => $totalBonus,
    ]);
     
    }

    public function admin() {
        return view('dashboards.admin');
    }

  public function finance()
{
    $data = IncomeDetail::orderBy('date')->get();

    $daily = $data->sortBy('date')->take(-30)->values(); // ambil 30 hari terakhir

    $currentMonth = Carbon::now()->format('Y-m');
    $bulanIni = $data->filter(fn($i) => $i->date->format('Y-m') === $currentMonth);

    $incomePie = [
        'pendaftaran_member' => $bulanIni->sum('pendaftaran_member'),
        'produk' => $bulanIni->sum('produk'),
        'manajemen' => $bulanIni->sum('manajemen'),
    ];

    $expensePie = [
        'pairing_bonus' => $bulanIni->sum('pairing_bonus'),
        'ro_bonus' => $bulanIni->sum('ro_bonus'),
        'reward_poin' => $bulanIni->sum('reward_poin'),
        'withdraw' => $bulanIni->sum('withdraw'),
    ];

    return view('finance.dashboard', compact('daily', 'incomePie', 'expensePie'));
}

    public function member() {
           $user = Auth::user();
            $totalBonus = BonusTransaction::where('user_id', $user->id)->sum('amount');
    $totalBonusnet = BonusTransaction::where('user_id', $user->id)
     ->where('status', 'paid')
     ->sum('amount');
    $userBagans = $user->bagans()->orderBy('bagan')->get();
    $userBaganAktif = $userBagans->pluck('bagan')->toArray(); // array angka: [1, 2]
      $leftDownline = TreeHelper::countDownlines($user, 'left');
    $rightDownline = TreeHelper::countDownlines($user, 'right');
    $totalBonusPaid = BonusTransaction::where('user_id', $user->id)
    ->where('status', 'paid')
    ->sum('net_amount');

$totalWithdrawn = Withdrawal::where('user_id', $user->id)
    ->where('status', 'approved')
    ->sum('amount');

$saldoBonusTersedia = $totalBonusPaid - $totalWithdrawn;
    return view('dashboards.member', [
        'user' => $user,
        'userBagans' => $userBagans,
        'userBaganAktif' => $userBaganAktif,
        'totalBonus' => $totalBonus,
        'totalBonusnett' => $totalBonusnet,
         'leftDownline'=> $leftDownline,   
        'rightDownline'=> $rightDownline,
        'saldoBonusTersedia'=> $saldoBonusTersedia,
    ]);

     
    }



}


