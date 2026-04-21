<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use App\Services\TransactionService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Riwayat Transaksi')]
class TransactionHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';
    
    public $selectedTransaction = null;
    public $showDetailModal = false;

    protected $updatesQueryString = ['search', 'status', 'dateFrom', 'dateTo'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showDetail(int $id)
    {
        $this->selectedTransaction = Transaction::with(['items', 'customer', 'user'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function voidTransaction(int $id, TransactionService $service)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            $service->voidTransaction($transaction);
            
            session()->flash('success', "Transaksi {$transaction->invoice_number} berhasil dibatalkan.");
            $this->showDetailModal = false;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $query = Transaction::with(['customer', 'items'])
            ->withCount('items')
            ->orderBy('transaction_date', 'desc');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%"));
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->dateFrom) {
            $query->whereDate('transaction_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('transaction_date', '<=', $this->dateTo);
        }

        return view('livewire.transactions.transaction-history', [
            'transactions' => $query->paginate(15)
        ]);
    }
}
