<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialPurchase;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MaterialPurchaseController extends Controller
{
    public function index()
    {
        // Filtros de período
        $period    = request('period', 'month');
        $dateStart = match($period) {
            'week'      => now()->startOfWeek(),
            'month'     => now()->startOfMonth(),
            'biweekly'  => now()->day <= 15 ? now()->startOfMonth() : now()->startOfMonth()->addDays(15),
            'all'       => null,
            default     => now()->startOfMonth(),
        };

        $purchases = MaterialPurchase::with(['material', 'supplier', 'registeredBy'])
            ->when($dateStart, fn($q) => $q->where('purchased_at', '>=', $dateStart))
            ->when(request('material_id'), fn($q, $m) => $q->where('material_id', $m))
            ->orderByDesc('purchased_at')
            ->get();

        $totalPeriod   = $purchases->sum('total');
        $byMaterial    = $purchases->groupBy('material.name')
                            ->map(fn($g) => ['total' => $g->sum('total'), 'qty' => $g->sum('quantity')]);

        $materials  = Material::where('active', true)->orderBy('name')->get();

        return view('material-purchases.index', compact(
            'purchases', 'totalPeriod', 'byMaterial', 'materials', 'period'
        ));
    }

    public function create()
    {
        $materials = Material::with('supplier')->where('active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        return view('material-purchases.form', compact('materials', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'material_id'  => 'required|exists:materials,id',
            'supplier_id'  => 'nullable|exists:suppliers,id',
            'quantity'     => 'required|numeric|min:0.01',
            'unit_price'   => 'required|numeric|min:0',
            'purchased_at' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $total = $request->quantity * $request->unit_price;

        MaterialPurchase::create([
            'material_id'   => $request->material_id,
            'supplier_id'   => $request->supplier_id,
            'quantity'      => $request->quantity,
            'unit_price'    => $request->unit_price,
            'total'         => $total,
            'purchased_at'  => $request->purchased_at,
            'notes'         => $request->notes,
            'registered_by' => auth()->id(),
        ]);

        return redirect('/material-purchases')->with('success', 'Compra registrada correctamente.');
    }

    public function destroy(MaterialPurchase $materialPurchase)
    {
        $materialPurchase->delete();
        return redirect('/material-purchases')->with('success', 'Compra eliminada.');
    }
}