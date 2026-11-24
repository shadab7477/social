<?php

namespace App\Livewire\Reports;

use App\Enums\ReportFormat;
use App\Enums\ReportFrequency;
use App\Enums\ReportStatus;
use App\Models\Client;
use App\Models\Report;
use App\Models\ReportConfig;
use App\Services\Reports\ReportBuilderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ReportBuilder extends Component
{
    use WithPagination;

    public string $activeTab = 'reports'; // 'reports' or 'schedules'

    // Filter State
    public ?int $selectedClientId = null;
    public string $formatFilter = '';

    // Create Report Modal State
    public bool $showCreateModal = false;
    public string $reportTitle = '';
    public ?int $modalClientId = null;
    public string $modalFormat = 'pdf'; // 'pdf' or 'csv'
    public string $datePreset = '30d'; // '7d', '30d', '90d', 'custom'
    public string $customDateFrom = '';
    public string $customDateTo = '';
    public string $agencyName = '';
    public string $brandColor = '#4f46e5';

    // Automated Schedule Modal State
    public bool $showScheduleModal = false;
    public string $scheduleName = '';
    public ?int $scheduleClientId = null;
    public string $scheduleFrequency = 'monthly';
    public string $scheduleRecipients = '';

    // Flash Feedback
    public ?string $feedbackMessage = null;
    public string $feedbackType = 'success';

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $workspace = Auth::user()->activeWorkspace() ?? app('current_workspace');
        $this->agencyName = $workspace?->name ?? 'PostSync Agency';
        $this->reportTitle = 'Monthly Performance Review — ' . now()->format('F Y');
        $this->customDateFrom = now()->subDays(30)->format('Y-m-d');
        $this->customDateTo = now()->format('Y-m-d');
    }

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function openScheduleModal(): void
    {
        $this->showScheduleModal = true;
    }

    public function closeScheduleModal(): void
    {
        $this->showScheduleModal = false;
    }

    /**
     * Generate report synchronously via ReportBuilderService.
     */
    public function generateReport(ReportBuilderService $reportService): void
    {
        $this->validate([
            'reportTitle' => 'required|min:3|max:255',
            'modalFormat' => 'required|in:pdf,csv',
        ]);

        [$dateFrom, $dateTo] = match ($this->datePreset) {
            '7d' => [now()->subDays(7)->startOfDay(), now()->endOfDay()],
            '90d' => [now()->subDays(90)->startOfDay(), now()->endOfDay()],
            'custom' => [
                Carbon::parse($this->customDateFrom)->startOfDay(),
                Carbon::parse($this->customDateTo)->endOfDay(),
            ],
            default => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
        };

        $format = ReportFormat::from($this->modalFormat);

        $branding = [
            'agency_name' => $this->agencyName,
            'primary_color' => $this->brandColor,
        ];

        $report = $reportService->createReport(
            Auth::user(),
            $this->reportTitle,
            $format,
            $dateFrom,
            $dateTo,
            $this->modalClientId,
            $branding
        );

        $this->feedbackMessage = "Report '{$report->title}' has been successfully compiled and is ready for download!";
        $this->feedbackType = 'success';
        $this->closeCreateModal();
    }

    /**
     * Save recurring automated client delivery schedule.
     */
    public function saveSchedule(): void
    {
        $this->validate([
            'scheduleName' => 'required|min:3|max:255',
            'scheduleFrequency' => 'required|in:weekly,monthly',
            'scheduleRecipients' => 'required',
        ]);

        $workspace = Auth::user()->activeWorkspace() ?? app('current_workspace');
        $recipients = array_map('trim', explode(',', $this->scheduleRecipients));

        ReportConfig::create([
            'workspace_id' => $workspace->id,
            'client_id' => $this->scheduleClientId,
            'name' => $this->scheduleName,
            'frequency' => ReportFrequency::from($this->scheduleFrequency),
            'recipients' => $recipients,
            'branding_options' => [
                'agency_name' => $this->agencyName,
                'primary_color' => $this->brandColor,
            ],
            'is_active' => true,
        ]);

        $this->feedbackMessage = "Automated report schedule '{$this->scheduleName}' saved successfully!";
        $this->feedbackType = 'success';
        $this->closeScheduleModal();
        $this->activeTab = 'schedules';
    }

    /**
     * Toggle active status of a schedule.
     */
    public function toggleSchedule(int $scheduleId): void
    {
        $schedule = ReportConfig::find($scheduleId);
        if ($schedule) {
            $schedule->update(['is_active' => !$schedule->is_active]);
            $this->feedbackMessage = "Schedule status updated.";
            $this->feedbackType = 'info';
        }
    }

    /**
     * Delete a report record and its file.
     */
    public function deleteReport(int $reportId): void
    {
        $report = Report::find($reportId);
        if ($report) {
            if ($report->file_path && Storage::disk('public')->exists($report->file_path)) {
                Storage::disk('public')->delete($report->file_path);
            }
            $report->delete();
            $this->feedbackMessage = "Report deleted.";
            $this->feedbackType = 'warning';
        }
    }

    public function render()
    {
        $query = Report::query()
            ->with(['client', 'createdBy'])
            ->latest();

        if ($this->selectedClientId) {
            $query->where('client_id', $this->selectedClientId);
        }

        if ($this->formatFilter) {
            $query->where('format', $this->formatFilter);
        }

        $reports = $query->paginate(10);
        $schedules = ReportConfig::with('client')->latest()->get();
        $clients = Client::orderBy('name')->get();

        return view('livewire.reports.report-builder', [
            'reports' => $reports,
            'schedules' => $schedules,
            'clients' => $clients,
        ]);
    }
}

