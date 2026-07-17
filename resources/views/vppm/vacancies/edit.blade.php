<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Edit Vacant Position') }}</h2>
    </x-slot>

    <div class="content-wrapper p-4">
        <div class="card shadow-sm">
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('vppm.vacancies.update', $vacancy) }}">
                    @csrf
                    @method('PUT')
                    @include('vppm.vacancies._form', ['vacancy' => $vacancy])
                    <div class="mt-3">
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Changes</button>
                        <a href="{{ route('vppm.vacancies.show', $vacancy) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-dashboard-app>
