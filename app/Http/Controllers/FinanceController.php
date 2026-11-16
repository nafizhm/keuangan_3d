<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

class FinanceController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate(['username' => 'required|string|max:100', 'password' => 'required|string']);
        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['username' => 'Username atau kata sandi salah.'])->onlyInput('username');
        }
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function dashboard()
    {
        $income = Transaction::where('type', 'pemasukan')->sum('amount');
        $expense = Transaction::where('type', 'pengeluaran')->sum('amount');
        $transactions = Transaction::orderByDesc('date')->orderByDesc('id')->limit(5)->get();

        return view('dashboard', compact('income', 'expense', 'transactions'));
    }

    public function password()
    {
        return view('password');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ]);
        $request->user()->password = $data['password'];
        $request->user()->setRememberToken(Str::random(60));
        $request->user()->save();
        $request->session()->regenerate();

        return redirect()->route('password.edit')->with('success', 'Password berhasil diubah. Gunakan password baru saat login berikutnya.');
    }

    public function transactions(Request $request)
    {
        $filters = $request->validate(['type' => 'nullable|in:pemasukan,pengeluaran', 'month' => 'nullable|date_format:Y-m', 'q' => 'nullable|string|max:100']);
        $transactions = Transaction::query()->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['month'] ?? null, fn ($q, $month) => $q->whereBetween('date', [$month.'-01', date('Y-m-t', strtotime($month.'-01'))]))
            ->when($filters['q'] ?? null, fn ($q, $search) => $q->where('description', 'like', '%'.$search.'%'))
            ->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();

        $previousInput = $request->session()->getOldInput();
        $formErrors = $request->session()->get('errors', new ViewErrorBag);

        return view('transactions', compact('transactions', 'previousInput', 'formErrors'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'pengelola', 403);
        $this->normalizeMoney($request);
        $data = $request->validate(['date' => 'required|date|before_or_equal:today', 'type' => 'required|in:pemasukan,pengeluaran',
            'category' => 'required|in:Penjualan cetak 3D,Modal usaha,Filamen & resin,Peralatan & perawatan,Listrik & operasional,Lainnya',
            'description' => 'required|string|max:255', 'amount' => 'required|integer|min:1|max:9999999999999',
            'discount' => 'nullable|integer|min:0|lte:amount',
            'attachment' => 'required_if:type,pengeluaran|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120']);
        $data['discount'] = $data['discount'] ?? 0;
        $data['amount'] -= $data['discount'];
        $path = $request->hasFile('attachment') ? $request->file('attachment')->store('receipts', 'local') : null;
        $data['attachment'] = $path;
        $data['user_id'] = $request->user()->id;
        try {
            Transaction::create($data);
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            } throw $e;
        }

        return redirect()->route('transactions')->with('success', 'Transaksi berhasil dicatat.');
    }

    public function update(Request $request, Transaction $transaction)
    {
        abort_unless($request->user()->role === 'pengelola', 403);
        $this->normalizeMoney($request);
        $hasReceipt = $transaction->attachment && Storage::disk('local')->exists($transaction->attachment);
        $data = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'type' => 'required|in:pemasukan,pengeluaran',
            'category' => 'required|in:Penjualan cetak 3D,Modal usaha,Filamen & resin,Peralatan & perawatan,Listrik & operasional,Lainnya',
            'description' => 'required|string|max:255',
            'amount' => 'required|integer|min:1|max:9999999999999',
            'discount' => 'nullable|integer|min:0|lte:amount',
            'attachment' => ($hasReceipt ? '' : 'required_if:type,pengeluaran|').'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $data['discount'] = $data['discount'] ?? 0;
        $data['amount'] -= $data['discount'];
        unset($data['attachment']);
        $oldPath = $transaction->attachment;
        $newPath = null;
        if ($request->hasFile('attachment')) {
            $newPath = $request->file('attachment')->store('receipts', 'local');
            $data['attachment'] = $newPath;
        }
        try {
            $transaction->update($data);
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            throw $e;
        }
        if ($newPath && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('transactions')->with('success', 'Transaksi berhasil diperbarui.');
    }

    private function normalizeMoney(Request $request): void
    {
        foreach (['amount', 'discount'] as $field) {
            $value = $request->input($field);
            if (is_string($value) && preg_match('/^[0-9]{1,3}(\.[0-9]{3})+$/D', $value)) {
                $request->merge([$field => str_replace('.', '', $value)]);
            }
        }
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        abort_unless($request->user()->role === 'pengelola', 403);
        $path = $transaction->attachment;
        $transaction->delete();
        if ($path) {
            Storage::disk('local')->delete($path);
        }

        return redirect()->route('transactions')->with('success', 'Transaksi berhasil dihapus.');
    }

    public function attachment(Transaction $transaction)
    {
        abort_unless($transaction->attachment && Storage::disk('local')->exists($transaction->attachment), 404);

        return response()->file(Storage::disk('local')->path($transaction->attachment), ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
