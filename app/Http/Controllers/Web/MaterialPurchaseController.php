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
            'quantity'     => 'required|numeric|min:0.01',
            'unit_price'   => 'required|numeric|min:0',
            'purchased_at' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        // Resolver proveedor
        $supplierId = null;
        if ($request->filled('new_supplier_name')) {
            $supplier = \App\Models\Supplier::create([
                'name'   => $request->new_supplier_name,
                'phone'  => $request->new_supplier_phone,
                'active' => true,
            ]);
            $supplierId = $supplier->id;
        } elseif ($request->filled('supplier_id')) {
            $supplierId = $request->supplier_id;
        }

        // Resolver material
        if ($request->filled('new_material_name')) {
            $request->validate([
                'new_material_name' => 'required|string|max:255',
                'new_material_unit' => 'required|in:kg,g,lt,ml,unidad',
            ]);
            $material = \App\Models\Material::create([
                'name'        => $request->new_material_name,
                'unit'        => $request->new_material_unit,
                'supplier_id' => $supplierId,
                'active'      => true,
            ]);
            $materialId = $material->id;
        } else {
            $request->validate(['material_id' => 'required|exists:materials,id']);
            $materialId = $request->material_id;
        }

        $total = $request->quantity * $request->unit_price;

        MaterialPurchase::create([
            'material_id'   => $materialId,
            'supplier_id'   => $supplierId,
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