<?php

use App\Services\RegistrationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Registration;

new class extends Component {
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public int $pagination = 10;
    public string $sort_field = 'id';
    public string $sort_direction = 'desc';

    public string $search = '';

    public string $status = '';
    public string $gender = '';
    public string $travel_package = '';

    public array $selectedIds = [];
    public bool $selectAll = false;

    public bool $showModal = false;
    public ?int $activeId = null;
    public array $activeRegistration = [];
    public array $activeFamily = [];
    public array $activeDocuments = [];

    public string $admin_notes = '';
    public bool $updatingStatus = false;

    private function normalizeStatus(string $status): string
    {
        $allowed = ['pending', 'processing', 'approved', 'rejected'];
        return in_array($status, $allowed, true) ? $status : 'pending';
    }

    public function show(int $id)
    {
        $this->activeId = $id;

        $reg = Registration::with(['familyMembers', 'documents'])->find($id);
        if (!$reg) {
            return;
        }

        $this->activeRegistration = $reg->only(['id', 'full_name', 'age', 'gender', 'email', 'travelers', 'destination', 'stay_duration', 'travel_package', 'status', 'admin_notes', 'created_at']);

        $this->admin_notes = (string) ($reg->admin_notes ?? '');

        $this->activeFamily = $reg->familyMembers->map(fn($m) => $m->only(['name', 'age', 'gender']))->values()->all();

        $this->activeDocuments = $reg->documents
            ->map(
                fn($d) => [
                    'type' => $d->type,
                    'url' => Storage::disk('public')->url($d->path),
                    'original_name' => $d->original_name,
                    'mime' => $d->mime,
                    'size' => $d->size,
                ],
            )
            ->values()
            ->all();

        $this->showModal = true;

        if (($this->activeRegistration['status'] ?? '') === 'pending') {
            $this->markAsSeen($id);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->activeId = null;
        $this->activeRegistration = [];
        $this->activeFamily = [];
        $this->activeDocuments = [];
        $this->admin_notes = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->clearSelection();
    }
    public function updatingStatus()
    {
        $this->resetPage();
        $this->clearSelection();
    }
    public function updatingGender()
    {
        $this->resetPage();
        $this->clearSelection();
    }
    public function updatingTravelPackage()
    {
        $this->resetPage();
        $this->clearSelection();
    }
    public function updatingPagination()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    private function clearSelection(): void
    {
        $this->selectAll = false;
        $this->selectedIds = [];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = $this->getCurrentPageIds();
        } else {
            $this->selectedIds = [];
        }
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status,
            'gender' => $this->gender,
            'travel_package' => $this->travel_package,
        ];
    }

    private function loadRows()
    {
        return app(RegistrationService::class)->data($this->filters(), $this->sort_field, $this->sort_direction, $this->pagination);
    }

    private function getCurrentPageIds(): array
    {
        $rows = $this->loadRows();
        return collect($rows->items())->pluck('id')->map(fn($v) => (int) $v)->toArray();
    }

    public function sortBy(string $field)
    {
        if ($this->sort_field === $field) {
            $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort_field = $field;
            $this->sort_direction = 'asc';
        }
        $this->resetPage();
        $this->clearSelection();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->gender = '';
        $this->travel_package = '';
        $this->resetPage();
        $this->clearSelection();
    }

    public function deleteOne(int $id)
    {
        app(RegistrationService::class)->delete($id);
        $this->resetPage();
        $this->clearSelection();

        if ($this->activeId === $id) {
            $this->closeModal();
        }
    }

    public function deleteSelected()
    {
        $ids = array_map('intval', $this->selectedIds);
        foreach ($ids as $id) {
            app(RegistrationService::class)->delete($id);
        }
        $this->resetPage();
        $this->clearSelection();
    }

    public function markAsSeen(int $id)
    {
        $reg = Registration::find($id);
        if (!$reg) {
            return;
        }

        // optional: no-op placeholder (if you treat "pending" as new)
        // keep as is, or set to processing automatically if you want.
    }

    public function changeStatus(int $id, string $status)
    {
        $status = $this->normalizeStatus($status);

        $this->updatingStatus = true;

        try {
            DB::transaction(function () use ($id, $status) {
                /** @var Registration $reg */
                $reg = Registration::lockForUpdate()->find($id);
                if (!$reg) {
                    return;
                }

                $reg->status = $status;

                // keep notes in sync if modal is open on the same record
                if ($this->activeId === $id) {
                    $reg->admin_notes = $this->admin_notes ?: null;
                }

                $reg->save();
            });

            if ($this->activeId === $id) {
                $this->activeRegistration['status'] = $status;
                $this->activeRegistration['admin_notes'] = $this->admin_notes ?: null;
            }
        } finally {
            $this->updatingStatus = false;
        }
    }

    public function saveAdminNotes()
    {
        if (!$this->activeId) {
            return;
        }

        $this->updatingStatus = true;

        try {
            $reg = Registration::find($this->activeId);
            if (!$reg) {
                return;
            }

            $reg->admin_notes = $this->admin_notes ?: null;
            $reg->save();

            $this->activeRegistration['admin_notes'] = $this->admin_notes ?: null;
        } finally {
            $this->updatingStatus = false;
        }
    }

    public function approve(int $id)
    {
        $this->changeStatus($id, 'approved');
    }
    public function reject(int $id)
    {
        $this->changeStatus($id, 'rejected');
    }
    public function process(int $id)
    {
        $this->changeStatus($id, 'processing');
    }
    public function resetToPending(int $id)
    {
        $this->changeStatus($id, 'pending');
    }

    #[Layout('layouts.admin.panel')]
    #[Title('Registrations')]
    public function render()
    {
        $rows = $this->loadRows();
        return view('pages.admin.panel.⚡registrations', compact('rows'));
    }
};

?>

<div class="container-fluid px-5">

    <div class="pt-5 bg-body-tertiary mb-4">
        <h1 class="">{{ __('Registrations') }}</h1>

        <nav class="d-flex">
            <h6 class="mb-0">
                <a href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}" class="text-reset">
                    {{ __('Home') }}
                </a>
                <span>/</span>
                <a href="#" class="text-reset"><u>{{ __('Registrations') }}</u></a>
            </h6>
        </nav>
    </div>

    <div class="row p-2 mb-3 align-items-end justify-content-between">
        <div class="col-md-9 d-flex gap-3 flex-wrap" wire:ignore>

            <div class="mb-2" style="min-width: 320px;">
                <label class="form-label mb-1" for="search"><strong>{{ __('Search') }}</strong></label>
                <div class="form-outline" data-mdb-input-init>
                    <input type="search" id="search" wire:model.live.debounce.500ms="search"
                        class="form-control form-icon-trailing" placeholder="{{ __('Name, Email, Destination') }}" />
                    <label class="form-label" for="search">{{ __('Name, Email, Destination') }}</label>
                    <i class="fas fa-search trailing"></i>
                </div>
            </div>

            <div>
                <label class="form-label mb-1" for="status"><strong>{{ __('Status') }}</strong></label>
                <select id="status" class="select" wire:model.live="status">
                    <option value="">{{ __('All') }}</option>
                    <option value="pending">pending</option>
                    <option value="processing">processing</option>
                    <option value="approved">approved</option>
                    <option value="rejected">rejected</option>
                </select>
            </div>

            <div>
                <label class="form-label mb-1" for="gender"><strong>{{ __('Gender') }}</strong></label>
                <select id="gender" class="select" wire:model.live="gender">
                    <option value="">{{ __('All') }}</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div>
                <label class="form-label mb-1" for="travel_package"><strong>{{ __('Package') }}</strong></label>
                <select id="travel_package" class="select" wire:model.live="travel_package">
                    <option value="">{{ __('All') }}</option>
                    <option value="Economic">Economic</option>
                    <option value="Comfortable">Comfortable</option>
                    <option value="VIP">VIP</option>
                </select>
            </div>

        </div>

        <div class="col-md-3 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary btn-sm" wire:click="resetFilters" wire:loading.attr="disabled">
                <i class="fas fa-undo"></i> {{ __('Reset') }}
            </button>

            @if (count($selectedIds) > 0)
                <button class="btn btn-danger btn-sm" wire:click="deleteSelected" wire:loading.attr="disabled">
                    <i class="fas fa-trash-alt"></i> {{ __('Delete') }} ({{ count($selectedIds) }})
                </button>
            @endif
        </div>
    </div>

    <div class="table-responsive-md text-center">
        <div style="height: 8px; margin-bottom: 12px;">
            <div class="datatable-loader bg-light" style="height: 8px;" wire:loading>
                <span class="datatable-loader-inner">
                    <span class="datatable-progress bg-primary"></span>
                </span>
            </div>
        </div>

        <table class="table table-bordered table-hover align-middle text-center rounded-3 shadow-lg">
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">
                        <div class="form-check font-size-16 d-flex justify-content-center">
                            <input type="checkbox" class="form-check-input" wire:model.live="selectAll">
                        </div>
                    </th>

                    <th role="button" wire:click="sortBy('full_name')">{{ __('Full Name') }}</th>
                    <th role="button" wire:click="sortBy('age')">{{ __('Age') }}</th>
                    <th role="button" wire:click="sortBy('gender')">{{ __('Gender') }}</th>
                    <th role="button" wire:click="sortBy('email')">{{ __('Email') }}</th>

                    <th>{{ __('Travelers') }}</th>
                    <th>{{ __('Destination') }}</th>
                    <th>{{ __('Stay (days)') }}</th>
                    <th>{{ __('Package') }}</th>

                    <th role="button" wire:click="sortBy('status')">{{ __('Status') }}</th>
                    <th role="button" wire:click="sortBy('created_at')">{{ __('Created') }}</th>

                    <th>{{ __('Family') }}</th>
                    <th>{{ __('Documents') }}</th>

                    <th style="width: 220px;">{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td class="text-center">
                            <div class="form-check font-size-16 d-flex justify-content-center align-items-center">
                                <input type="checkbox" class="form-check-input" value="{{ $r->id }}"
                                    wire:model.live="selectedIds">
                            </div>
                        </td>

                        <td>{{ $r->full_name }}</td>
                        <td>{{ $r->age }}</td>
                        <td>{{ $r->gender }}</td>
                        <td>{{ $r->email }}</td>

                        <td>{{ $r->travelers }}</td>
                        <td>{{ $r->destination }}</td>
                        <td>{{ $r->stay_duration }}</td>
                        <td>{{ $r->travel_package }}</td>

                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                ];
                            @endphp
                            <span class="badge badge-{{ $statusColors[$r->status] ?? 'light' }}">
                                {{ ucfirst($r->status) }}
                            </span>
                        </td>

                        <td>{{ optional($r->created_at)->format('Y-m-d') }}</td>

                        <td>
                            <span class="badge badge-info">
                                {{ $r->family_members_count ?? 0 }}
                            </span>
                        </td>

                        <td>
                            <span class="badge badge-secondary">
                                {{ $r->documents_count ?? 0 }}
                            </span>
                        </td>

                        <td class="text-center">
                            <span wire:loading.remove wire:target="show({{ $r->id }})">
                                <a href="#" wire:click.prevent="show({{ $r->id }})"
                                    class="text-primary fa-lg me-2 ms-2" title="{{ __('Show') }}">
                                    <x-icons.show />
                                </a>
                            </span>
                            <span wire:loading wire:target="show({{ $r->id }})">
                                <span class="spinner-border spinner-border-sm text-primary me-2 ms-2"
                                    role="status"></span>
                            </span>

                            <span wire:loading.remove wire:target="process({{ $r->id }})">
                                <a href="#" wire:click.prevent="process({{ $r->id }})"
                                    class="text-info fa-lg me-2 ms-2" title="{{ __('Processing') }}">
                                    <x-icons.sync />
                                </a>
                            </span>
                            <span wire:loading wire:target="process({{ $r->id }})">
                                <span class="spinner-border spinner-border-sm text-info me-2 ms-2"
                                    role="status"></span>
                            </span>

                            <span wire:loading.remove wire:target="approve({{ $r->id }})">
                                <a href="#" wire:click.prevent="approve({{ $r->id }})"
                                    class="text-success fa-lg me-2 ms-2" title="{{ __('Approve') }}">
                                    <x-icons.check />
                                </a>
                            </span>
                            <span wire:loading wire:target="approve({{ $r->id }})">
                                <span class="spinner-border spinner-border-sm text-success me-2 ms-2"
                                    role="status"></span>
                            </span>

                            <span wire:loading.remove wire:target="reject({{ $r->id }})">
                                <a href="#" wire:click.prevent="reject({{ $r->id }})"
                                    class="text-danger fa-lg me-2 ms-2" title="{{ __('Reject') }}">
                                    <x-icons.close />
                                </a>
                            </span>
                            <span wire:loading wire:target="reject({{ $r->id }})">
                                <span class="spinner-border spinner-border-sm text-danger me-2 ms-2"
                                    role="status"></span>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('No data found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <nav aria-label="...">
            <ul class="pagination pagination-circle">
                {{ $rows->withQueryString()->onEachSide(0)->links() }}
            </ul>
        </nav>

        <div class="col-md-1" wire:ignore>
            <select class="select" wire:model.live="pagination">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content rounded-3">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Registration Details') }} #{{ $activeRegistration['id'] ?? '' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-body-tertiary rounded">
                                    <strong>{{ __('Full Name') }}:</strong>
                                    <div>{{ $activeRegistration['full_name'] ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-body-tertiary rounded">
                                    <strong>{{ __('Age') }}:</strong>
                                    <div>{{ $activeRegistration['age'] ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-body-tertiary rounded">
                                    <strong>{{ __('Gender') }}:</strong>
                                    <div>{{ $activeRegistration['gender'] ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-body-tertiary rounded">
                                    <strong>{{ __('Email') }}:</strong>
                                    <div>{{ $activeRegistration['email'] ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-body-tertiary rounded">
                                    <strong>{{ __('Destination') }}:</strong>
                                    <div>{{ $activeRegistration['destination'] ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 bg-body-tertiary rounded">
                                    <strong>{{ __('Travelers') }}:</strong>
                                    <div>{{ $activeRegistration['travelers'] ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-body-tertiary rounded">
                                    <strong>{{ __('Stay (days)') }}:</strong>
                                    <div>{{ $activeRegistration['stay_duration'] ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-body-tertiary rounded">
                                    <strong>{{ __('Package') }}:</strong>
                                    <div>{{ $activeRegistration['travel_package'] ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                            <h5 class="mb-0">{{ __('Status') }}:</h5>

                            @php
                                $s = $activeRegistration['status'] ?? 'pending';
                                $btn = fn($ok) => $ok ? '' : 'disabled';
                            @endphp

                            <button class="btn btn-sm btn-info {{ $btn($s !== 'processing') }}"
                                wire:click="changeStatus({{ (int) ($activeRegistration['id'] ?? 0) }}, 'processing')"
                                wire:loading.attr="disabled" wire:target="changeStatus,saveAdminNotes">
                                <i class="fas fa-sync-alt me-1"></i> processing
                            </button>

                            <button class="btn btn-sm btn-success {{ $btn($s !== 'approved') }}"
                                wire:click="changeStatus({{ (int) ($activeRegistration['id'] ?? 0) }}, 'approved')"
                                wire:loading.attr="disabled" wire:target="changeStatus,saveAdminNotes">
                                <i class="fas fa-check-circle me-1"></i> approved
                            </button>

                            <button class="btn btn-sm btn-danger {{ $btn($s !== 'rejected') }}"
                                wire:click="changeStatus({{ (int) ($activeRegistration['id'] ?? 0) }}, 'rejected')"
                                wire:loading.attr="disabled" wire:target="changeStatus,saveAdminNotes">
                                <i class="fas fa-times-circle me-1"></i> rejected
                            </button>

                            <button class="btn btn-sm btn-warning {{ $btn($s !== 'pending') }}"
                                wire:click="changeStatus({{ (int) ($activeRegistration['id'] ?? 0) }}, 'pending')"
                                wire:loading.attr="disabled" wire:target="changeStatus,saveAdminNotes">
                                <i class="fas fa-undo me-1"></i> pending
                            </button>

                            <span class="ms-auto text-muted small" wire:loading
                                wire:target="changeStatus,saveAdminNotes">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                {{ __('Updating...') }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><strong>{{ __('Admin Notes') }}</strong></label>
                            <textarea class="form-control" rows="3" wire:model.defer="admin_notes"
                                placeholder="{{ __('Write notes...') }}"></textarea>
                            <div class="d-flex justify-content-end mt-2">
                                <button class="btn btn-primary btn-sm" wire:click="saveAdminNotes"
                                    wire:loading.attr="disabled" wire:target="saveAdminNotes,changeStatus">
                                    <i class="fas fa-save me-1"></i> {{ __('Save Notes') }}
                                </button>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3">{{ __('Family Members') }} ({{ count($activeFamily) }})</h5>
                        @if (count($activeFamily) === 0)
                            <div class="text-muted">{{ __('No family members') }}</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th style="width: 90px;">{{ __('Age') }}</th>
                                            <th style="width: 120px;">{{ __('Gender') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($activeFamily as $m)
                                            <tr>
                                                <td>{{ $m['name'] }}</td>
                                                <td>{{ $m['age'] }}</td>
                                                <td>{{ $m['gender'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <hr>

                        <h5 class="mb-3">{{ __('Documents') }} ({{ count($activeDocuments) }})</h5>
                        @if (count($activeDocuments) === 0)
                            <div class="text-muted">{{ __('No documents') }}</div>
                        @else
                            <div class="list-group">
                                @foreach ($activeDocuments as $d)
                                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                        href="{{ $d['url'] }}" target="_blank" rel="noreferrer">
                                        <div>
                                            <strong>{{ strtoupper($d['type']) }}</strong>
                                            <div class="small text-muted">{{ $d['original_name'] }}</div>
                                        </div>
                                        <span class="badge badge-primary">{{ $d['mime'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
