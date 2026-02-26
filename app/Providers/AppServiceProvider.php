<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\Category;
use App\Models\Letter;
use App\Models\Subject;
use App\Policies\AssignmentPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\LetterPolicy;
use App\Policies\SubjectPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
