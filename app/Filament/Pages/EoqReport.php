<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class EoqReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan EOQ';
    protected static string $view = 'filament.pages.eoq-report';

    public $products = [];

    /**
     * ✅ Load data EOQ (hanya sekali saat halaman dibuka)
     */
    public function mount(): void
    {
        $this->products = Product::with(['category', 'supplier'])
            ->get()
            ->map(function ($product) {
                return [
                    'id'            => $product->id,
                    'code'          => $product->code,
                    'name'          => $product->name,
                    'category'      => $product->category->name ?? '-',
                    'supplier'      => $product->supplier->name ?? '-',
                    'stock'         => $product->stock,
                    'annual_demand' => $product->annual_demand,
                    'ordering_cost' => $product->ordering_cost,
                    'holding_cost'  => $product->holding_cost,
                    'eoq'           => $product->calculateEOQ(),
                    'reorder_point' => $product->reorder_point,
                    'safety_stock'  => $product->safety_stock,
                ];
            });
    }

    /**
     * ✅ Pagination untuk tampilan agar tidak berat jika data banyak
     */
    public function getViewData(): array
    {
        $page     = request()->get('page', 1);
        $perPage  = 10;
        $offset   = ($page - 1) * $perPage;

        // Buat pagination manual berdasarkan collection yang sudah dimap sebelumnya
        $paginated = new LengthAwarePaginator(
            collect($this->products)->slice($offset, $perPage)->values(),
            collect($this->products)->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        return [
            'products' => $paginated,
        ];
    }

    /**
     * ✅ Role-based Access (Owner & Admin only)
     */
    public static function canAccess(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['owner', 'admin']);
    }
}
