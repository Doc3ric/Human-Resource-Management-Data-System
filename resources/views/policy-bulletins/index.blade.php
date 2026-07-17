<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('HR Policy & Ruling Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Policies Tracked</h5>
                            <p class="card-text fs-2">{{ $metrics['total'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Unread Policies</h5>
                            <p class="card-text fs-2">{{ $metrics['unread'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Highly Applicable (1st Class Prov)</h5>
                            <p class="card-text fs-2">{{ $metrics['high_applicability'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Source</th>
                                    <th>Reference No.</th>
                                    <th>Title & Summary</th>
                                    <th>Applicability Score</th>
                                    <th>Date Issued</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bulletins as $bulletin)
                                    <tr class="{{ $bulletin->is_acknowledged ? '' : 'table-warning' }}">
                                        <td>
                                            @if($bulletin->is_acknowledged)
                                                <span class="badge bg-secondary">Read</span>
                                            @else
                                                <span class="badge bg-danger">New</span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $bulletin->source_agency }}</strong></td>
                                        <td>{{ $bulletin->reference_no }}</td>
                                        <td>
                                            <a href="{{ $bulletin->url }}" target="_blank" class="fw-bold">{{ $bulletin->title }}</a>
                                            <p class="mb-0 text-muted" style="font-size: 0.85rem;">{{ Str::limit($bulletin->summary, 100) }}</p>
                                        </td>
                                        <td>
                                            @if($bulletin->applicability_score >= 10)
                                                <span class="badge bg-success">{{ $bulletin->applicability_score }} (High)</span>
                                            @else
                                                <span class="badge bg-info">{{ $bulletin->applicability_score }} (Low)</span>
                                            @endif
                                        </td>
                                        <td>{{ $bulletin->date_issued ? $bulletin->date_issued->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            @if(!$bulletin->is_acknowledged)
                                            <form action="{{ route('policy-bulletins.acknowledge', $bulletin) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Acknowledge</button>
                                            </form>
                                            @else
                                            <button disabled class="btn btn-sm btn-outline-secondary">Acknowledged</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No policy bulletins found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $bulletins->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-app>
