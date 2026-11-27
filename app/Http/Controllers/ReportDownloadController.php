<?php

namespace App\Http\Controllers;

use App\Enums\ReportFormat;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ReportDownloadController extends Controller
{
    /**
     * Download or view generated report file with tenant security check.
     */
    public function download(Request $request, int $reportId)
    {
        $user = Auth::user();
        $workspace = $user->activeWorkspace() ?? app('current_workspace');

        $report = Report::where('workspace_id', $workspace->id)->findOrFail($reportId);

        if (!$report->file_path || !Storage::disk('public')->exists($report->file_path)) {
            abort(Response::HTTP_NOT_FOUND, 'The generated report file was not found.');
        }

        $fullPath = Storage::disk('public')->path($report->file_path);
        $cleanTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $report->title);

        if ($report->format === ReportFormat::PDF && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $html = Storage::disk('public')->get($report->file_path);

            // Remove external font/CSS CDN links so DomPDF renders instantly without remote timeouts
            $html = preg_replace('/<link[^>]*>/i', '', $html) ?? $html;

            $accentColor = $report->branding_options['primary_color'] ?? '#4f46e5';
            $html = str_replace('var(--primary-accent)', $accentColor, $html);

            $downloadFilename = "{$cleanTitle}-{$report->date_from->format('Ymd')}-{$report->date_to->format('Ymd')}.pdf";

            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setOption(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans'])
                ->setPaper('a4', 'portrait')
                ->download($downloadFilename);
        }

        $extension = $report->format === ReportFormat::CSV ? 'csv' : 'html';
        $downloadFilename = "{$cleanTitle}-{$report->date_from->format('Ymd')}-{$report->date_to->format('Ymd')}.{$extension}";

        return response()->download($fullPath, $downloadFilename);
    }
}

