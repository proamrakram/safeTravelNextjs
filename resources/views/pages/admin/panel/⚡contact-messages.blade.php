<?php

use App\Services\ContactMessageService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public int $pagination = 10;
    public string $sort_field = 'id';
    public string $sort_direction = 'desc';

    public string $search = '';
    public string $status = '';

    public array $selectedIds = [];
    public bool $selectAll = false;

    // Modal
    public bool $isModalOpen = false;
    public ?int $showId = null;
    public ?array $showRow = null;

    /* ---------- lifecycle ---------- */
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

    public function updatedSelectAll($value): void
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
        ];
    }

    private function loadRows()
    {
        return app(ContactMessageService::class)->data($this->filters(), $this->sort_field, $this->sort_direction, $this->pagination);
    }

    private function getCurrentPageIds(): array
    {
        $rows = $this->loadRows();
        return collect($rows->items())->pluck('id')->map(fn($v) => (int) $v)->toArray();
    }

    public function sortBy(string $field): void
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

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
        $this->clearSelection();
    }

    public function changeStatus(int $id, string $status): void
    {
        app(ContactMessageService::class)->updateStatus($id, $status);

        // إذا الرسالة المعروضة بالـ modal هي نفسها، حدّثها مباشرة
        if ($this->showId === $id && $this->showRow) {
            $this->showRow['status'] = $status;
        }
    }

    public function show(int $id): void
    {
        $row = app(ContactMessageService::class)->find($id);
        if (!$row) {
            return;
        }

        // Auto mark as seen when open if new
        if ($row->status === 'new') {
            app(ContactMessageService::class)->updateStatus($id, 'seen');
            $row->status = 'seen';
        }

        $this->showId = $id;
        $this->showRow = [
            'id' => $row->id,
            'name' => $row->name,
            'message' => $row->message,
            'status' => $row->status,
            'created_at' => optional($row->created_at)->format('Y-m-d H:i'),
        ];

        $this->isModalOpen = true;

        // لو عندك Bootstrap modal JS، يمكنك تشغيله عبر event
        $this->dispatch('open-contact-modal');
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->showId = null;
        $this->showRow = null;

        $this->dispatch('close-contact-modal');
    }

    public function deleteOne(int $id): void
    {
        app(ContactMessageService::class)->delete($id);
        $this->resetPage();
        $this->clearSelection();

        // لو حذفنا الرسالة المعروضة بالـ modal
        if ($this->showId === $id) {
            $this->closeModal();
        }
    }

    public function deleteSelected(): void
    {
        $ids = array_map('intval', $this->selectedIds);

        foreach ($ids as $id) {
            app(ContactMessageService::class)->delete($id);
        }

        $this->resetPage();
        $this->clearSelection();

        if ($this->showId && in_array($this->showId, $ids, true)) {
            $this->closeModal();
        }
    }

    #[Layout('layouts.admin.panel')]
    #[Title('Contact Messages')]
    public function render()
    {
        $rows = $this->loadRows();
        return view('pages.admin.panel.⚡contact-messages', compact('rows'));
    }
};

?>

<div class="container-fluid px-5">

    <!-- Heading -->
    <div class="pt-5 bg-body-tertiary mb-4">
        <h1>{{ __('Contact Messages') }}</h1>

        <nav class="d-flex">
            <h6 class="mb-0">
                <a href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}" class="text-reset">
                    {{ __('Home') }}
                </a>
                <span>/</span>
                <u>{{ __('Contact Messages') }}</u>
            </h6>
        </nav>
    </div>

    <!-- Filters -->
    <div class="row p-2 mb-3 align-items-end justify-content-between" wire:ignore>
        <div class="col-md-9 d-flex gap-3 flex-wrap">

            <div style="min-width: 320px;">
                <label class="form-label mb-1"><strong>{{ __('Search') }}</strong></label>
                <div class="form-outline" data-mdb-input-init>
                    <input type="search" wire:model.live.debounce.500ms="search"
                        class="form-control form-icon-trailing" placeholder="{{ __('Name or Message') }}" />
                    <i class="fas fa-search trailing"></i>
                </div>
            </div>

            <div>
                <label class="form-label mb-1"><strong>{{ __('Status') }}</strong></label>
                <select class="select" wire:model.live="status">
                    <option value="">{{ __('All') }}</option>
                    <option value="new">New</option>
                    <option value="seen">Seen</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

        </div>

        <div class="col-md-3 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary btn-sm" wire:click="resetFilters" type="button">
                <i class="fas fa-undo"></i> {{ __('Reset') }}
            </button>

            @if (count($selectedIds) > 0)
                <button class="btn btn-danger btn-sm" wire:click="deleteSelected" type="button">
                    <i class="fas fa-trash-alt"></i> {{ __('Delete') }} ({{ count($selectedIds) }})
                </button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive-md text-center">
        <div style="height: 8px; margin-bottom: 12px;">
            <div class="datatable-loader bg-light" style="height: 8px;" wire:loading>
                <span class="datatable-loader-inner">
                    <span class="datatable-progress bg-primary"></span>
                </span>
            </div>
        </div>

        <table class="table table-bordered table-hover align-middle rounded-3 shadow-lg">
            <thead>
                <tr>
                    <th style="width: 30px;">
                        <div class="form-check d-flex justify-content-center">
                            <input type="checkbox" class="form-check-input" wire:model.live="selectAll">
                        </div>
                    </th>

                    <th wire:click="sortBy('name')" role="button">{{ __('Name') }}</th>
                    <th>{{ __('Message') }}</th>
                    <th wire:click="sortBy('status')" role="button">{{ __('Status') }}</th>
                    <th wire:click="sortBy('created_at')" role="button">{{ __('Date') }}</th>
                    <th style="width: 180px;">{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($rows as $msg)
                    <tr>
                        <td>
                            <div class="form-check d-flex justify-content-center align-items-center">
                                <input type="checkbox" class="form-check-input" value="{{ $msg->id }}"
                                    wire:model.live="selectedIds">
                            </div>
                        </td>

                        <td>{{ $msg->name }}</td>

                        <td class="text-start">
                            {{ Str::limit($msg->message, 120) }}
                        </td>

                        <td>
                            @php
                                $colors = [
                                    'new' => 'primary',
                                    'seen' => 'warning',
                                    'closed' => 'secondary',
                                ];
                            @endphp
                            <span class="badge badge-{{ $colors[$msg->status] ?? 'light' }}">
                                {{ ucfirst($msg->status) }}
                            </span>
                        </td>

                        <td>{{ optional($msg->created_at)->format('Y-m-d H:i') }}</td>

                        <td class="text-nowrap">

                            <!-- Show -->
                            <span wire:loading.remove wire:target="show({{ $msg->id }})">
                                <a href="#" wire:click.prevent="show({{ $msg->id }})"
                                    class="text-primary fa-lg me-2" title="{{ __('Show') }}">
                                    <x-icons.show />
                                </a>
                            </span>
                            <span wire:loading wire:target="show({{ $msg->id }})">
                                <span class="spinner-border spinner-border-sm text-primary me-2" role="status"></span>
                            </span>

                            <!-- Mark Seen -->
                            <span wire:loading.remove wire:target="changeStatus({{ $msg->id }}, 'seen')">
                                <a href="#" wire:click.prevent="changeStatus({{ $msg->id }}, 'seen')"
                                    class="text-warning fa-lg me-2" title="{{ __('Mark as Seen') }}">
                                    <x-icons.check />
                                </a>
                            </span>
                            <span wire:loading wire:target="changeStatus({{ $msg->id }}, 'seen')">
                                <span class="spinner-border spinner-border-sm text-warning me-2" role="status"></span>
                            </span>

                            <!-- Close -->
                            <span wire:loading.remove wire:target="changeStatus({{ $msg->id }}, 'closed')">
                                <a href="#" wire:click.prevent="changeStatus({{ $msg->id }}, 'closed')"
                                    class="text-success fa-lg me-2" title="{{ __('Close') }}">
                                    <x-icons.lock />
                                </a>
                            </span>
                            <span wire:loading wire:target="changeStatus({{ $msg->id }}, 'closed')">
                                <span class="spinner-border spinner-border-sm text-success me-2" role="status"></span>
                            </span>

                            <!-- Delete -->
                            <span wire:loading.remove wire:target="deleteOne({{ $msg->id }})">
                                <a href="#" wire:click.prevent="deleteOne({{ $msg->id }})"
                                    class="text-danger fa-lg" title="{{ __('Delete') }}">
                                    <x-icons.delete />
                                </a>
                            </span>
                            <span wire:loading wire:target="deleteOne({{ $msg->id }})">
                                <span class="spinner-border spinner-border-sm text-danger" role="status"></span>
                            </span>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('No messages found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between mt-4">
        <nav>
            {{ $rows->withQueryString()->onEachSide(0)->links() }}
        </nav>

        <div class="col-md-1">
            <select class="select" wire:model.live="pagination">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <!-- Modal (Bootstrap/MDB style) -->
    @if ($isModalOpen && $showRow)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.5)">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Message Details') }}</h5>
                        <button type="button" class="btn-close" aria-label="Close"
                            wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body text-start">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-bold">{{ $showRow['name'] }}</div>
                                <div class="text-muted small">{{ $showRow['created_at'] }}</div>
                            </div>

                            @php
                                $colors = ['new' => 'primary', 'seen' => 'warning', 'closed' => 'secondary'];
                            @endphp
                            <span class="badge badge-{{ $colors[$showRow['status']] ?? 'light' }}">
                                {{ ucfirst($showRow['status']) }}
                            </span>
                        </div>

                        <div class="border rounded-3 p-3 bg-body-tertiary" style="white-space: pre-wrap;">
                            {{ $showRow['message'] }}
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" type="button" wire:click="closeModal">
                            {{ __('Close') }}
                        </button>

                        <button class="btn btn-warning" type="button"
                            wire:click="changeStatus({{ $showRow['id'] }}, 'seen')">
                            {{ __('Mark as Seen') }}
                        </button>

                        <button class="btn btn-success" type="button"
                            wire:click="changeStatus({{ $showRow['id'] }}, 'closed')">
                            {{ __('Set Closed') }}
                        </button>

                        <button class="btn btn-primary" type="button"
                            wire:click="changeStatus({{ $showRow['id'] }}, 'new')">
                            {{ __('Set New') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
