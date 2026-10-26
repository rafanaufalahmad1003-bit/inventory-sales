<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('sales');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / HP pelanggan wajib diisi untuk verifikasi rental kamera.',
            'email.email' => 'Format email pelanggan tidak valid.',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Data pelanggan rental kamera berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales.items.product', 'sales.user']);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'nik' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / HP pelanggan wajib diisi.',
            'email.email' => 'Format email pelanggan tidak valid.',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena memiliki riwayat transaksi rental/pembelian.');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil dihapus.');
    }
}
