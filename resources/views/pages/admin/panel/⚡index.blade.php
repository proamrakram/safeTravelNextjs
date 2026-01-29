<?php

use App\Models\ContactMessage;
use App\Models\Document;
use App\Models\FamilyMember;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new class extends Component {
    public array $stats = [];
    public array $registrations = [];
    public array $contacts = [];
    public array $documents = [];
    public array $topDestinations = [];
    public array $recent = [];

    #[Layout('layouts::admin.panel')]
    #[Title('لوحة التحكم - الرئيسية')]
    public function mount()
    {
        $this->loadDashboard();
    }

    public function refreshStats(): void
    {
        $this->loadDashboard();
        $this->dispatch('toast', message: __('✅ Statistics have been updated successfully.'), type: 'success');
    }

    private function loadDashboard(): void
    {
        // ---------- Top KPIs ----------
        $this->stats = [
            'users_total' => (int) User::query()->count(),
            'registrations_total' => (int) Registration::query()->count(),
            'contacts_total' => (int) ContactMessage::query()->count(),
            'family_total' => (int) FamilyMember::query()->count(),
            'documents_total' => (int) Document::query()->count(),
        ];

        // ---------- Registrations Breakdown ----------
        $regByStatus = Registration::query()->select('status', DB::raw('COUNT(*) as c'))->groupBy('status')->pluck('c', 'status')->toArray();

        $regByPackage = Registration::query()->select('travel_package', DB::raw('COUNT(*) as c'))->groupBy('travel_package')->pluck('c', 'travel_package')->toArray();

        $regByGender = Registration::query()->select('gender', DB::raw('COUNT(*) as c'))->groupBy('gender')->pluck('c', 'gender')->toArray();

        $this->registrations = [
            'by_status' => [
                'pending' => (int) ($regByStatus['pending'] ?? 0),
                'processing' => (int) ($regByStatus['processing'] ?? 0),
                'approved' => (int) ($regByStatus['approved'] ?? 0),
                'rejected' => (int) ($regByStatus['rejected'] ?? 0),
            ],
            'by_package' => [
                'Economic' => (int) ($regByPackage['Economic'] ?? 0),
                'Comfortable' => (int) ($regByPackage['Comfortable'] ?? 0),
                'VIP' => (int) ($regByPackage['VIP'] ?? 0),
            ],
            'by_gender' => [
                'Male' => (int) ($regByGender['Male'] ?? 0),
                'Female' => (int) ($regByGender['Female'] ?? 0),
            ],
        ];

        // ---------- Contact Messages Breakdown ----------
        $contactByStatus = ContactMessage::query()->select('status', DB::raw('COUNT(*) as c'))->groupBy('status')->pluck('c', 'status')->toArray();

        $this->contacts = [
            'by_status' => [
                'new' => (int) ($contactByStatus['new'] ?? 0),
                'seen' => (int) ($contactByStatus['seen'] ?? 0),
                'closed' => (int) ($contactByStatus['closed'] ?? 0),
            ],
        ];

        // ---------- Documents Breakdown by type (photo/passport/id_card) ----------
        $docByType = Document::query()->select('type', DB::raw('COUNT(*) as c'))->groupBy('type')->pluck('c', 'type')->toArray();

        $this->documents = [
            'by_type' => [
                'photo' => (int) ($docByType['photo'] ?? 0),
                'passport' => (int) ($docByType['passport'] ?? 0),
                'id_card' => (int) ($docByType['id_card'] ?? 0),
            ],
        ];

        // ---------- Top Destinations ----------
        $this->topDestinations = Registration::query()
            ->select('destination', DB::raw('COUNT(*) as c'))
            ->whereNotNull('destination')
            ->where('destination', '!=', '')
            ->groupBy('destination')
            ->orderByDesc('c')
            ->limit(7)
            ->get()
            ->map(
                fn($row) => [
                    'destination' => $row->destination,
                    'count' => (int) $row->c,
                ],
            )
            ->toArray();

        // ---------- Recent Activity (10 items) ----------
        $recentRegs = Registration::query()
            ->select('id', 'full_name', 'status', 'created_at')
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(
                fn($r) => [
                    'type' => 'registration',
                    'title' => "تسجيل: {$r->full_name}",
                    'meta' => "Status: {$r->status}",
                    'time' => optional($r->created_at)->diffForHumans(),
                    'ts' => $r->created_at?->timestamp ?? 0,
                ],
            )
            ->toArray();

        $recentContacts = ContactMessage::query()
            ->select('id', 'name', 'status', 'created_at')
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(
                fn($m) => [
                    'type' => 'contact',
                    'title' => "رسالة: {$m->name}",
                    'meta' => "Status: {$m->status}",
                    'time' => optional($m->created_at)->diffForHumans(),
                    'ts' => $m->created_at?->timestamp ?? 0,
                ],
            )
            ->toArray();

        $recentDocs = Document::query()
            ->select('id', 'type', 'original_name', 'created_at')
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(
                fn($d) => [
                    'type' => 'document',
                    'title' => 'مستند: ' . ($d->original_name ?: "#{$d->id}"),
                    'meta' => "Type: {$d->type}",
                    'time' => optional($d->created_at)->diffForHumans(),
                    'ts' => $d->created_at?->timestamp ?? 0,
                ],
            )
            ->toArray();

        $merged = array_merge($recentRegs, $recentContacts, $recentDocs);
        usort($merged, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
        $this->recent = array_slice($merged, 0, 10);
    }
};

?>

<style>
    /* ✅ Scroll containers */
    .scroll-box {
        max-height: 320px;
        overflow: auto;
    }

    /* ✅ لو حبيت تحكم أعلى/أقل */
    @media (max-width: 991px) {
        .scroll-box {
            max-height: 260px;
        }
    }

    /* تحسين بسيط لشكل الجدول داخل الـ scroll */
    .table-sticky thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--mdb-surface, #fff);
    }
</style>

<div class="container-fluid px-5" style="height: 1150px;">

    <!-- Header -->
    <div class="pt-5 bg-body-tertiary mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-1">{{ __('Dashboard') }}</h1>
            <p class="text-muted mb-0">{{ __('Overview') }}</p>
        </div>

        <button class="btn btn-primary btn-sm" wire:click="refreshStats" wire:loading.attr="disabled" type="button">
            <span wire:loading.remove wire:target="refreshStats">
                <i class="fas fa-sync-alt me-2"></i> {{ __('Refresh Stats') }}
            </span>
            <span wire:loading wire:target="refreshStats">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                {{ __('Updating...') }}
            </span>
        </button>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-5">
                <div class="card-body text-center py-4">
                    <i class="fas fa-users fa-2x mb-3 text-primary"></i>
                    <h6 class="fw-bold mb-1">{{ __('Users') }}</h6>
                    <h3 class="fw-bold mb-1">{{ $stats['users_total'] ?? 0 }}</h3>
                    <small class="text-muted">{{ __('Total') }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-5">
                <div class="card-body text-center py-4">
                    <i class="fas fa-clipboard-list fa-2x mb-3 text-success"></i>
                    <h6 class="fw-bold mb-1">{{ __('Registrations') }}</h6>
                    <h3 class="fw-bold mb-1">{{ $stats['registrations_total'] ?? 0 }}</h3>
                    <small class="text-muted">{{ __('Total Requests') }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-5">
                <div class="card-body text-center py-4">
                    <i class="fas fa-envelope fa-2x mb-3 text-warning"></i>
                    <h6 class="fw-bold mb-1">{{ __('Contact Messages') }}</h6>
                    <h3 class="fw-bold mb-1">{{ $stats['contacts_total'] ?? 0 }}</h3>
                    <small class="text-muted">{{ __('Inbox') }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-5">
                <div class="card-body text-center py-4">
                    <i class="fas fa-file-alt fa-2x mb-3 text-danger"></i>
                    <h6 class="fw-bold mb-1">{{ __('Documents') }}</h6>
                    <h3 class="fw-bold mb-1">{{ $stats['documents_total'] ?? 0 }}</h3>
                    <small class="text-muted">{{ __('Uploaded') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Widgets -->
    <div class="row g-4 mt-1">

        <!-- Family Members -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-5">
                <div class="card-body text-center py-4">
                    <i class="fas fa-users-cog fa-2x mb-3 text-info"></i>
                    <h6 class="fw-bold mb-1">{{ __('Family Members') }}</h6>
                    <h3 class="fw-bold mb-1">{{ $stats['family_total'] ?? 0 }}</h3>
                    <small class="text-muted">{{ __('Attached') }}</small>
                </div>
            </div>
        </div>

        <!-- Registrations Breakdown -->
        <div class="col-lg-9">
            <div class="card shadow-5">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>
                        {{ __('Registration Statistics') }}
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3">
                                <div class="fw-bold mb-2">{{ __('By Status') }}</div>
                                @php
                                    $st = $registrations['by_status'] ?? [];
                                    $badge = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                    ];
                                @endphp

                                @foreach (['pending', 'processing', 'approved', 'rejected'] as $k)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-capitalize">{{ $k }}</span>
                                        <span class="badge badge-{{ $badge[$k] }}">{{ $st[$k] ?? 0 }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded-3 p-3">
                                <div class="fw-bold mb-2">{{ __('By Package') }}</div>
                                @php $pk = $registrations['by_package'] ?? []; @endphp

                                @foreach (['Economic', 'Comfortable', 'VIP'] as $k)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>{{ $k }}</span>
                                        <span class="badge badge-primary">{{ $pk[$k] ?? 0 }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded-3 p-3">
                                <div class="fw-bold mb-2">{{ __('By Gender') }}</div>
                                @php $g = $registrations['by_gender'] ?? []; @endphp

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ __('Male') }}</span>
                                    <span class="badge badge-info">{{ $g['Male'] ?? 0 }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ __('Female') }}</span>
                                    <span class="badge badge-danger">{{ $g['Female'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div><!-- row -->
                </div>
            </div>
        </div>
    </div>

    <!-- Documents By Type + Top Destinations + Recent -->
    <div class="row g-4 mt-1">

        <!-- Documents by type -->
        <div class="col-lg-4">
            <div class="card shadow-5 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-folder-open me-2 text-danger"></i>
                        {{ __('Documents by Type') }}
                    </h5>

                    @php $dt = $documents['by_type'] ?? []; @endphp

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ __('photo') }}</span>
                        <span class="badge badge-primary">{{ $dt['photo'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ __('passport') }}</span>
                        <span class="badge badge-success">{{ $dt['passport'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ __('id_card') }}</span>
                        <span class="badge badge-warning">{{ $dt['id_card'] ?? 0 }}</span>
                    </div>

                    <hr>

                    <div class="small text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('The distribution helps you monitor the completeness of registration documents.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Destinations -->
        <div class="col-lg-4">
            <div class="card shadow-5 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-map-marked-alt me-2 text-success"></i>
                        {{ __('Top Destinations') }}
                    </h5>

                    @if (count($topDestinations ?? []) > 0)
                        <div class="scroll-box">
                            <table class="table table-sm align-middle mb-0 table-sticky">
                                <thead>
                                    <tr>
                                        <th class="text-start">{{ __('Destination') }}</th>
                                        <th style="width: 90px;" class="text-end">{{ __('Count') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topDestinations as $row)
                                        <tr>
                                            <td class="text-start">{{ $row['destination'] }}</td>
                                            <td class="text-end">
                                                <span class="badge badge-success">{{ $row['count'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">{{ __('No destinations found') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity (Scrollable Table) -->
        <div class="col-lg-4">
            <div class="card shadow-5 h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-history me-2 text-primary"></i>
                        {{ __('Recent Activity') }}
                    </h5>

                    <div class="scroll-box">
                        <table class="table table-sm align-middle mb-0 table-sticky">
                            <thead>
                                <tr>
                                    <th class="text-start">{{ __('Activity') }}</th>
                                    <th style="width: 110px;" class="text-end">{{ __('When') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent as $item)
                                    <tr>
                                        <td class="text-start">
                                            <div class="fw-semibold">{{ $item['title'] }}</div>
                                            <div class="small text-muted">{{ $item['meta'] }}</div>
                                        </td>
                                        <td class="text-end">
                                            <small class="text-muted">{{ $item['time'] }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-muted">{{ __('No messages found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="small text-muted mt-3">
                        <i class="fas fa-mouse-pointer me-2"></i>
                        {{ __('You can scroll inside the table to see more without extending the page downwards.') }}
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
