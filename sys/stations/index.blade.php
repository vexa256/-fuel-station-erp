@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
    <!-- Header Section -->
    <div class="border-b border-border bg-card/50 backdrop-blur-sm sticky top-0 z-10">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Station Management</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage and monitor fuel stations across all regions
                    </p>
                </div>
                @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                <div class="flex items-center gap-3">
                    <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded-md font-medium">
                        {{ auth()->user()->role }} Access
                    </span>
                    <a href="{{ route('stations.create') }}"
                        class="btn btn-primary inline-flex items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                        <i class="fas fa-plus text-sm"></i>
                        <span>Add New Station</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="container mx-auto px-4 py-6">
        <div class="card bg-card border border-border rounded-lg p-6 mb-6 shadow-sm">
            <form method="GET" action="{{ route('stations.index') }}"
                class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-foreground mb-2">
                        Search Stations
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-muted-foreground text-sm"></i>
                        </div>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by name or code..."
                            class="input w-full pl-10 transition-all focus:ring-2 focus:ring-primary/20">
                    </div>
                </div>

                <!-- Region Filter -->
                <div class="sm:w-48">
                    <label for="region" class="block text-sm font-medium text-foreground mb-2">
                        Region
                    </label>
                    <select name="region" id="region" class="select w-full">
                        <option value="">All Regions</option>
                        @foreach($regions as $region)
                        <option value="{{ $region }}" {{ request('region')===$region ? 'selected' : '' }}>
                            {{ $region }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="sm:w-40">
                    <label for="status" class="block text-sm font-medium text-foreground mb-2">
                        Status
                    </label>
                    <select name="status" id="status" class="select w-full">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ request('status')==='inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary inline-flex items-center gap-2 px-6">
                        <i class="fas fa-filter text-sm"></i>
                        <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'region', 'status']))
                    <a href="{{ route('stations.index') }}" class="btn btn-ghost inline-flex items-center gap-2 px-4">
                        <i class="fas fa-times text-sm"></i>
                        <span>Clear</span>
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        @if($stations->count() > 0)
        <div class="flex items-center justify-between mb-6">
            <div class="text-sm text-muted-foreground">
                Showing {{ $stations->firstItem() }} - {{ $stations->lastItem() }} of {{ $stations->total() }} stations
            </div>
            <div class="flex items-center gap-4 text-sm">
                @if(request()->hasAny(['search', 'region', 'status']))
                <span class="inline-flex items-center gap-2 text-primary">
                    <i class="fas fa-filter"></i>
                    Filtered Results
                </span>
                @endif
            </div>
        </div>
        @endif

        <!-- Stations Grid -->
        @if($stations->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            @foreach($stations as $station)
            <div
                class="card bg-card border border-border rounded-lg p-6 hover:shadow-lg transition-all duration-200 group">
                <!-- Station Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg text-foreground group-hover:text-primary transition-colors">
                            {{ $station->station_name }}
                        </h3>
                        <p class="text-sm text-muted-foreground mt-1">
                            {{ $station->station_code }}
                        </p>
                    </div>
                    <div class="ml-3">
                        @if($station->is_active)
                        <span class="badge badge-default bg-green-100 text-green-700 border border-green-200">
                            <i class="fas fa-check-circle text-xs mr-1"></i>
                            Active
                        </span>
                        @else
                        <span class="badge badge-default bg-red-100 text-red-700 border border-red-200">
                            <i class="fas fa-times-circle text-xs mr-1"></i>
                            Inactive
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Location Info -->
                <div class="flex items-center gap-2 mb-4 text-sm text-muted-foreground">
                    <i class="fas fa-map-marker-alt text-xs"></i>
                    <span>{{ $station->district }}, {{ $station->region }}</span>
                </div>

                <!-- Station Metrics -->
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="text-center p-2 bg-muted/50 rounded-md">
                        <div class="text-lg font-semibold text-foreground">{{ $station->tank_count ?? 0 }}</div>
                        <div class="text-xs text-muted-foreground">Tanks</div>
                    </div>
                    <div class="text-center p-2 bg-muted/50 rounded-md">
                        <div class="text-lg font-semibold text-foreground">{{ $station->pump_count ?? 0 }}</div>
                        <div class="text-xs text-muted-foreground">Pumps</div>
                    </div>
                    <div class="text-center p-2 bg-muted/50 rounded-md">
                        <div class="text-lg font-semibold text-foreground">{{ $station->operational_pumps ?? 0 }}</div>
                        <div class="text-xs text-muted-foreground">Active</div>
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="flex items-center gap-2 mb-4 text-sm">
                    <i class="fas fa-clock text-muted-foreground text-xs"></i>
                    <span class="text-muted-foreground">
                        {{ date('H:i', strtotime($station->operating_hours_open)) }} -
                        {{ date('H:i', strtotime($station->operating_hours_close)) }}
                    </span>
                </div>

                <!-- Manager Info -->
                @if($station->manager_name)
                <div class="flex items-center gap-3 mb-4 p-2 bg-accent/50 rounded-md">
                    <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-primary text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-foreground">{{ $station->manager_name }}</div>
                        <div class="text-xs text-muted-foreground">Station Manager</div>
                    </div>
                </div>
                @else
                <div class="flex items-center gap-3 mb-4 p-2 bg-amber-50 border border-amber-200 rounded-md">
                    <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-amber-600 text-xs"></i>
                    </div>
                    <div class="text-sm text-amber-700">No manager assigned</div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 pt-4 border-t border-border">
                    <a href="{{ route('stations.dashboard', $station->id) }}"
                        class="btn btn-primary flex-1 text-center text-sm py-2">
                        <i class="fas fa-chart-line text-xs mr-2"></i>
                        Dashboard
                    </a>
                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) ||
                    DB::table('user_stations')->where('user_id', auth()->id())->where('station_id',
                    $station->id)->exists())
                    <a href="{{ route('stations.edit', $station->id) }}" class="btn btn-secondary px-3 py-2">
                        <i class="fas fa-edit text-xs"></i>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between">
            <div class="text-sm text-muted-foreground">
                {{ $stations->appends(request()->query())->links() }}
            </div>
        </div>

        @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="w-24 h-24 bg-muted rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-gas-pump text-3xl text-muted-foreground"></i>
            </div>
            <h3 class="text-xl font-semibold text-foreground mb-2">No Stations Found</h3>
            @if(request()->hasAny(['search', 'region', 'status']))
            <p class="text-muted-foreground mb-6">
                No stations match your current filter criteria. Try adjusting your search terms or filters.
            </p>
            <a href="{{ route('stations.index') }}" class="btn btn-secondary inline-flex items-center gap-2">
                <i class="fas fa-times text-sm"></i>
                Clear Filters
            </a>
            @else
            <p class="text-muted-foreground mb-6">
                {{ in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
                ? 'No stations have been created yet. Start by adding your first station.'
                : 'No stations are assigned to your account. Contact your administrator for access.' }}
            </p>
            @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
            <a href="{{ route('stations.create') }}" class="btn btn-primary inline-flex items-center gap-2">
                <i class="fas fa-plus text-sm"></i>
                Create First Station
            </a>
            @endif
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Real-time Search Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const form = searchInput.closest('form');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        if (this.value.length >= 2 || this.value.length === 0) {
            searchTimeout = setTimeout(() => {
                form.submit();
            }, 300);
        }
    });

    // Auto-submit on filter change
    const filters = ['region', 'status'];
    filters.forEach(filterId => {
        const element = document.getElementById(filterId);
        if (element) {
            element.addEventListener('change', () => form.submit());
        }
    });
});
</script>
@endsection
