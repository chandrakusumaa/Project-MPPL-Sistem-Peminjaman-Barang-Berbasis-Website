<?php

namespace App\Http\Controllers;

use App\Models\BorrowerCharge;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ExportController extends Controller
{
    public function __invoke(Request $request, Organization $organization, string $type)
    {
        Gate::authorize('administer', $organization);
        $tables = ['assets' => ['assets', ['code' => 'Kode', 'name' => 'Nama', 'location' => 'Lokasi', 'status' => 'Status', 'physical_condition' => 'Kondisi']], 'borrowings' => ['borrowings', ['id' => 'ID', 'asset_id' => 'Aset', 'user_id' => 'Peminjam', 'borrow_date' => 'Mulai', 'due_date' => 'Jatuh tempo', 'status' => 'Status']], 'damage' => ['damage_reports', ['id' => 'ID', 'asset_id' => 'Aset', 'description' => 'Deskripsi', 'severity' => 'Tingkat', 'status' => 'Status']], 'charges' => ['borrower_charges', ['id' => 'ID', 'borrower_id' => 'Peminjam', 'charge_type' => 'Jenis', 'amount' => 'Nominal', 'status' => 'Status']], 'payments' => ['charge_payments', ['id' => 'ID', 'charge_id' => 'Tagihan', 'amount' => 'Nominal', 'status' => 'Status', 'paid_at' => 'Tanggal pembayaran']], 'credits' => ['charge_credits', ['id' => 'ID', 'charge_id' => 'Tagihan', 'amount' => 'Pembebasan', 'reason' => 'Alasan']]];
        abort_unless(isset($tables[$type]), 404);
        [$table,$columns] = $tables[$type];
        $data = $request->validate(['from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d|after_or_equal:from', 'status' => 'nullable|string|max:30']);
        $query = DB::table($table)->where('organization_id', $organization->id)->when($data['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))->when($data['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
        if (($data['status'] ?? null) && $table !== 'charge_credits') {
            $query->where('status', $data['status']);
        }

        return response()->streamDownload(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            if (isset($columns['charge_type'])) {
                $headers = array_merge(array_values($columns), ['Dibayar confirmed', 'Pembebasan', 'Sisa', 'Status pelunasan']);
            } else {
                $headers = array_values($columns);
            }fputcsv($out, $headers, ',', '"', '');
            $query->select(array_keys($columns))->orderBy('id')->chunk(500, function ($rows) use ($out, $columns) {
                $charges = isset($columns['charge_type']) ? BorrowerCharge::whereIn('id', $rows->pluck('id'))->with('payments', 'credits')->get()->keyBy('id') : collect();
                foreach ($rows as $row) {
                    $values = array_map(fn ($v) => is_string($v) && preg_match('/^[=+@\-\t\r]/u', $v) ? "'".$v : $v, array_values((array) $row));
                    if (isset($columns['charge_type'])) {
                        $c = $charges[$row->id];
                        $values = array_merge($values, [(int) $c->payments->where('status', 'confirmed')->sum('amount'), (int) $c->credits->sum('amount'), $c->balance, $c->settlement]);
                    }fputcsv($out, $values, ',', '"', '');
                }
            });
            fclose($out);
        }, $type.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
