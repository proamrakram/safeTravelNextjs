<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // تحديد اللغة
        $lang = $request->query('lang')
            ?? $request->header('Accept-Language')
            ?? session('lang')
            ?? config('app.locale'); // الافتراضي من config/app.php

        // تنظيف اللغة (حتى ما يدخل لغات غير مدعومة)
        $supported = ['ar', 'en'];
        if (!in_array($lang, $supported)) {
            $lang = config('app.locale');
        }

        // تخزين الاتجاه
        $dir = $lang === 'ar' ? 'rtl' : 'ltr';
        session()->put('dir', 'rtl');

        // ضبط اللغة
        app()->setLocale('ar');
        session()->put('lang', 'ar');

        return $next($request);
    }
}
