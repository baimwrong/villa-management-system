<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkImportRequest;
use App\Models\ImportLog;
use App\Services\BulkImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BulkImportController extends Controller
{
    public function __construct(
        protected BulkImportService $importService
    ) {}

    public function index(): View
    {
        $logs = ImportLog::with('user')->latest()->paginate(10);

        $totalImports = ImportLog::count();
        $successfulImports = ImportLog::where('status', ImportLog::STATUS_COMPLETED)->count();
        $totalRecordsImported = ImportLog::sum('imported_rows');

        return view('admin.bulk-import.index', [
            'logs' => $logs,
            'totalImports' => $totalImports,
            'successfulImports' => $successfulImports,
            'totalRecordsImported' => $totalRecordsImported,
        ]);
    }

    public function import(BulkImportRequest $request): RedirectResponse
    {
        $type = $request->string('type')->toString();
        $file = $request->file('file');

        try {
            $result = $this->importService->import($type, $file, Auth::user());

            if ($result['failed'] > 0) {
                return back()->with('warning', "Imported {$result['imported']} of {$result['total']} {$type}. {$result['failed']} row(s) had errors.")
                    ->with('import_errors', $result['errors']);
            }

            return back()->with('success', "Successfully imported all {$result['imported']} {$type} records without errors.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    public function downloadTemplate(string $type): Response
    {
        if (! in_array($type, [ImportLog::TYPE_VILLAS, ImportLog::TYPE_GUESTS, ImportLog::TYPE_BOOKINGS, ImportLog::TYPE_EXPENSES], true)) {
            abort(404, 'Invalid template type.');
        }

        $csvContent = $this->importService->getSampleCsv($type);
        $fileName = "villa_{$type}_template.csv";

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function viewErrors(ImportLog $log): View
    {
        return view('admin.bulk-import.errors', [
            'log' => $log,
        ]);
    }
}
