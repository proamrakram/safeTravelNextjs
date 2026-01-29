<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\User;

new class extends Component {
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public bool $isLoading = false;

    // رسالة عامة تظهر أعلى الفورم (بدون أي package)
    public ?string $flashMessage = null;
    public string $flashType = 'danger'; // success | danger | warning | info

    #[
        Layout('layouts.admin.auth.login', [
            'headerTitle' => 'لوحة التحكم - تسجيل الدخول',
        ]),
    ]
    #[Title('Admin Login')]
    public function login()
    {
        $this->isLoading = true;
        $this->flashMessage = null;

        try {
            $data = $this->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                $this->flashType = 'danger';
                $this->flashMessage = 'البريد الإلكتروني غير مسجل';
                return;
            }

            if (!Hash::check($data['password'], $user->password)) {
                $this->flashType = 'danger';
                $this->flashMessage = 'كلمة المرور غير صحيحة';
                return;
            }

            Auth::guard('web')->login($user, $this->remember);

            // خيار 1: Redirect مباشرة بدون رسائل
            return redirect()->route('admin.panel.index', [
                'lang' => app()->getLocale(),
            ]);

            // خيار 2 (بديل): لو بدك رسالة بعد التحويل استخدم session()->flash في Controller/Route الهدف
        } catch (\Throwable $e) {
            $this->flashType = 'danger';
            $this->flashMessage = 'حدث خطأ أثناء تسجيل الدخول';
            dd($e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }
};

?>

<div style="width: 30rem;">
    <form wire:submit.prevent="login">

        {{-- رسالة عامة (MDB/Bootstrap Alert) --}}
        @if ($flashMessage)
            <div class="alert alert-{{ $flashType }} mb-3" role="alert">
                {{ $flashMessage }}
            </div>
        @endif

        <!-- Email -->
        <div class="form-outline" wire:ignore>
            <input type="email" id="email" maxlength="50" class="form-control form-control-lg"
                wire:model.defer="email" />
            <label class="form-label" for="email">Email</label>
        </div>
        @error('email')
            <div class="form-helper text-danger">{{ $message }}</div>
        @enderror

        <!-- Password -->
        <div class="form-outline mt-4" wire:ignore>
            <input type="password" id="passwordID" maxlength="30" class="form-control form-control-lg"
                wire:model.defer="password" />
            <label class="form-label" for="passwordID">Password</label>
        </div>
        @error('password')
            <div class="form-helper text-danger">{{ $message }}</div>
        @enderror

        <!-- Remember -->
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4 px-1" wire:ignore>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" wire:model="remember" id="rememberMe" />
                <label class="form-check-label" for="rememberMe">
                    Remember me
                </label>
            </div>

            <a href="#">Forgot password?</a>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-lg btn-block bg-primary text-white" wire:loading.attr="disabled">
            <span class="spinner-border spinner-border-sm me-2" role="status" wire:loading></span>
            Log in
        </button>

    </form>
</div>
