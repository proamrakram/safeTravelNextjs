<?php

use App\Services\UserService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public int $pagination = 10;
    public string $sort_field = 'id';
    public string $sort_direction = 'asc';

    public string $search = '';
    public array $filters = [];

    public array $selectedUsers = [];
    public bool $selectAll = false;

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedUsers = [];
    }

    public function updatingPagination()
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedUsers = [];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = $this->getCurrentPageUsersIds();
        } else {
            $this->selectedUsers = [];
        }
    }

    private function loadUsers()
    {
        $this->filters = [
            'search' => $this->search,
        ];

        return app(UserService::class)->data($this->filters, $this->sort_field, $this->sort_direction, $this->pagination);
    }

    private function getCurrentPageUsersIds(): array
    {
        $users = $this->loadUsers(); // paginator
        return collect($users->items())->pluck('id')->map(fn($v) => (int) $v)->toArray();
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
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filters = [];
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedUsers = [];
    }

    public function confirmDelete(int $id)
    {
        // بدون نظام إشعارات: اعرض رسالة داخل الصفحة أو نفّذ delete مباشرة
        $ok = app(UserService::class)->delete($id);

        // هنا بس refresh
        $this->selectAll = false;
        $this->selectedUsers = [];
        $this->resetPage();
    }

    public function confirmDeleteSelected()
    {
        $ids = array_map('intval', $this->selectedUsers);
        if (count($ids) === 0) {
            return;
        }

        foreach ($ids as $id) {
            app(UserService::class)->delete($id);
        }

        $this->selectAll = false;
        $this->selectedUsers = [];
        $this->resetPage();
    }

    #[Layout('layouts.admin.panel')]
    #[Title('Users List')]
    public function render()
    {
        $users = $this->loadUsers();
        return view('pages.admin.panel.⚡users', compact('users'));
    }
};

?>

<div class="container-fluid px-5">

    <!-- Heading -->
    <div class="pt-5 bg-body-tertiary mb-4">
        <h1 class="">{{ __('Users') }}</h1>

        <nav class="d-flex">
            <h6 class="mb-0">
                <a href="{{ route('admin.panel.index', ['lang' => app()->getLocale()]) }}" class="text-reset">
                    {{ __('Home') }}
                </a>
                <span>/</span>
                <a href="#" class="text-reset"><u>{{ __('Users') }}</u></a>
            </h6>
        </nav>
    </div>
    <!-- Heading -->

    <!-- Filters -->
    <div class="row p-2 mb-3 align-items-center justify-content-between">
        <div class="col-md-9 d-flex gap-3" wire:ignore>

            <div class="mb-2" style="min-width: 320px;">
                <label class="form-label mb-1" for="search"><strong>{{ __('Search') }}</strong></label>
                <div class="form-outline" data-mdb-input-init>
                    <input type="search" id="search" wire:model.live.debounce.500ms="search"
                        class="form-control form-icon-trailing"
                        placeholder="{{ __('Search by name, email, username') }}" />
                    <label class="form-label" for="search">{{ __('Search by name, email, username') }}</label>
                    <i class="fas fa-search trailing"></i>
                </div>
            </div>

            <div class="d-flex align-items-end">
                <button class="btn btn-secondary btn-sm" wire:click="resetFilters">
                    <i class="fas fa-undo"></i> {{ __('Reset') }}
                </button>
            </div>

        </div>

        <div class="col-md-3 d-flex justify-content-end gap-2 align-items-end">
            @if (count($selectedUsers) > 0)
                <button class="btn btn-danger btn-sm" wire:click="confirmDeleteSelected">
                    <i class="fas fa-trash-alt"></i>
                    {{ __('Delete') }} ({{ count($selectedUsers) }})
                </button>
            @endif
        </div>
    </div>
    <!-- Filters -->

    <!-- Table -->
    <div class="table-responsive-md text-center">
        <div style="height: 8px; margin-bottom: 12px;">
            <div class="datatable-loader bg-light" style="height: 8px;" wire:loading>
                <span class="datatable-loader-inner">
                    <span class="datatable-progress bg-primary"></span>
                </span>
            </div>
        </div>

        <table class="table table-bordered align-middle text-center rounded-3 shadow-lg">
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">
                        <div class="form-check font-size-16 d-flex justify-content-center">
                            <input type="checkbox" class="form-check-input" wire:model.live="selectAll" id="select-all">
                        </div>
                    </th>

                    <th role="button" wire:click="sortBy('name')">
                        {{ __('Name') }}
                        @if ($sort_field === 'name')
                            <i class="fas fa-sort-{{ $sort_direction === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>

                    <th role="button" wire:click="sortBy('username')">
                        {{ __('Username') }}
                        @if ($sort_field === 'username')
                            <i class="fas fa-sort-{{ $sort_direction === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>

                    <th role="button" wire:click="sortBy('email')">
                        {{ __('Email') }}
                        @if ($sort_field === 'email')
                            <i class="fas fa-sort-{{ $sort_direction === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>

                    {{-- <th>{{ __('Email Verified') }}</th> --}}

                    <th role="button" wire:click="sortBy('created_at')">
                        {{ __('Created At') }}
                        @if ($sort_field === 'created_at')
                            <i class="fas fa-sort-{{ $sort_direction === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>

                    {{-- <th>{{ __('Actions') }}</th> --}}
                </tr>
            </thead>

            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="text-center">
                            <div class="form-check font-size-16 d-flex justify-content-center align-items-center">
                                <input type="checkbox" class="form-check-input" value="{{ $user->id }}"
                                    wire:model.live="selectedUsers">
                            </div>
                        </td>

                        <td>{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>

                        {{-- <td>
                            @if ($user->email_verified_at)
                                <span class="badge badge-success">{{ __('Yes') }}</span>
                            @else
                                <span class="badge badge-danger">{{ __('No') }}</span>
                            @endif
                        </td> --}}

                        <td>{{ optional($user->created_at)->format('Y-m-d') }}</td>

                        {{-- <td>
                            <!-- Edit Icon -->
                            <span wire:loading.remove wire:target="edit({{ $user->id }})">
                                <a href="#edit" wire:click="edit({{ $user->id }})"
                                    class="text-dark fa-lg me-2 ms-2" title="{{ __('Edit') }}">
                                    <x-icons.edit />
                                </a>
                            </span>
                            <span wire:loading wire:target="edit({{ $user->id }})">
                                <span class="spinner-border spinner-border-sm text-dark me-2 ms-2"
                                    role="status"></span>
                            </span>

                            <!-- Delete Icon -->
                            <span wire:loading.remove wire:target="confirmDelete({{ $user->id }})">
                                <a href="#" wire:click="confirmDelete({{ $user->id }})"
                                    class="text-danger fa-lg me-2 ms-2" title="{{ __('Delete') }}">
                                    <x-icons.delete />
                                </a>
                            </span>
                            <span wire:loading wire:target="confirmDelete({{ $user->id }})">
                                <span class="spinner-border spinner-border-sm text-danger me-2 ms-2"
                                    role="status"></span>
                            </span>

                            <!-- Show Icon -->
                            <span wire:loading.remove wire:target="show({{ $user->id }})">
                                <a href="#" wire:click="show({{ $user->id }})"
                                    class="text-primary fa-lg me-2 ms-2" title="{{ __('Show') }}">
                                    <x-icons.show />
                                </a>
                            </span>
                            <span wire:loading wire:target="show({{ $user->id }})">
                                <span class="spinner-border spinner-border-sm text-primary me-2 ms-2"
                                    role="status"></span>
                            </span>

                        </td> --}}
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('No data found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between mt-4">

        <nav aria-label="...">
            <ul class="pagination pagination-circle">
                {{ $users->withQueryString()->onEachSide(0)->links() }}
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
</div>
