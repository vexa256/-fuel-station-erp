@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
    <!-- Header Section -->
    <div class="border-b border-border bg-card">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
            <div class="py-4 sm:py-6">
                <!-- Breadcrumb -->
                <nav class="flex items-center space-x-2 text-sm text-muted-foreground mb-3 sm:mb-4">
                    <a href="{{ route('tanks.select') }}" class="hover:text-foreground transition-colors truncate">Tank Management</a>
                    <i class="fas fa-chevron-right text-xs flex-shrink-0"></i>
                    <span class="text-foreground font-medium truncate">{{ $station->station_name }}</span>
                </nav>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                    <div class="flex items-center space-x-3 sm:space-x-4 min-w-0">
                        <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-cyan-600 text-white shadow-lg flex-shrink-0">
                            <i class="fas fa-oil-can text-sm sm:text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h1 class="text-xl sm:text-2xl font-bold text-foreground truncate">{{ $station->station_name }} Tanks</h1>
                            <p class="text-xs sm:text-sm text-muted-foreground truncate">{{ $station->station_code }} • Manage fuel tanks and inventory levels</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2 sm:space-x-3 flex-shrink-0">
                        @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) || (auth()->user()->can_approve_variances ?? false))
                        <a href="{{ route('tanks.create', $station->id) }}"
                            class="inline-flex items-center px-3 sm:px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-lg font-medium text-xs sm:text-sm transition-all duration-200 shadow-sm">
                            <i class="fas fa-plus w-3 h-3 sm:w-4 sm:h-4 mr-1.5 sm:mr-2"></i>
                            <span class="hidden xs:inline">Add Tank</span>
                            <span class="xs:hidden">Add</span>
                        </a>
                        @endif

                        @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                        <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                            <i class="fas fa-crown w-2.5 h-2.5 sm:w-3 sm:h-3 mr-1"></i>
                            <span class="hidden sm:inline">Full Access</span>
                            <span class="sm:hidden">Admin</span>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-4 sm:py-6 lg:py-8">
        @if($tanks->count() > 0)

        <!-- Desktop/Tablet Table View -->
        <div class="hidden md:block">
            <div class="bg-card border border-border rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/30">
                            <tr>
                                <th scope="col" class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                    Tank
                                </th>
                                <th scope="col" class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                    Product
                                </th>
                                <th scope="col" class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                    Current Stock
                                </th>
                                <th scope="col" class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                    Capacity
                                </th>
                                <th scope="col" class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                    Status
                                </th>
                                <th scope="col" class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                    Calibration
                                </th>
                                <th scope="col" class="px-4 lg:px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider whitespace-nowrap">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-card divide-y divide-border">
                            @foreach($tanks as $tank)
                            <tr class="hover:bg-muted/20 transition-colors group">
                                <!-- Tank Number -->
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 rounded-lg bg-cyan-100 flex items-center justify-center">
                                                <span class="text-sm font-semibold text-cyan-700">{{ $tank->tank_number }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-3 min-w-0">
                                            <div class="text-sm font-medium text-foreground">Tank {{ $tank->tank_number }}</div>
                                            <div class="text-xs text-muted-foreground truncate">{{ ucfirst(strtolower(str_replace('_', ' ', $tank->tank_type))) }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Product -->
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-foreground">{{ $tank->product_name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $tank->product_code }}</div>
                                </td>

                                <!-- Current Stock -->
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center min-w-0">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-foreground">
                                                {{ number_format($tank->current_stock_liters, 0) }}L
                                            </div>
                                            <div class="w-full bg-muted rounded-full h-1.5 mt-1">
                                                <div class="h-1.5 rounded-full transition-all duration-300
                                                    @if($tank->stock_status === 'CRITICAL') bg-red-500
                                                    @elseif($tank->stock_status === 'LOW') bg-amber-500
                                                    @elseif($tank->stock_status === 'HIGH') bg-blue-500
                                                    @else bg-green-500
                                                    @endif" style="width: {{ min(100, $tank->fill_percentage) }}%">
                                                </div>
                                            </div>
                                            <div class="text-xs text-muted-foreground mt-1">{{ $tank->fill_percentage }}% full</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Capacity -->
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-foreground">{{ number_format($tank->capacity_liters, 0) }}L</div>
                                    <div class="text-xs text-muted-foreground">
                                        Min: {{ number_format($tank->minimum_stock_level_liters, 0) }}L
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col space-y-1">
                                        <!-- Stock Status -->
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($tank->stock_status === 'CRITICAL') bg-red-100 text-red-700 border border-red-200
                                            @elseif($tank->stock_status === 'LOW') bg-amber-100 text-amber-700 border border-amber-200
                                            @elseif($tank->stock_status === 'HIGH') bg-blue-100 text-blue-700 border border-blue-200
                                            @else bg-green-100 text-green-700 border border-green-200
                                            @endif">
                                            <div class="w-1.5 h-1.5 rounded-full mr-1.5
                                                @if($tank->stock_status === 'CRITICAL') bg-red-500
                                                @elseif($tank->stock_status === 'LOW') bg-amber-500
                                                @elseif($tank->stock_status === 'HIGH') bg-blue-500
                                                @else bg-green-500
                                                @endif">
                                            </div>
                                            {{ ucfirst(strtolower($tank->stock_status)) }}
                                        </span>

                                        <!-- Active Status -->
                                        @if($tank->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                            <i class="fas fa-check w-3 h-3 mr-1"></i>
                                            Active
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                            <i class="fas fa-pause w-3 h-3 mr-1"></i>
                                            Inactive
                                        </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Calibration -->
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col space-y-1">
                                        @if($tank->calibration_complete)
                                            @if($tank->calibration_expired)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                                <i class="fas fa-exclamation-triangle w-3 h-3 mr-1"></i>
                                                Expired
                                            </span>
                                            @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                                <i class="fas fa-check w-3 h-3 mr-1"></i>
                                                Valid
                                            </span>
                                            @endif
                                            <div class="text-xs text-muted-foreground">
                                                Until: {{ \Carbon\Carbon::parse($tank->calibration_valid_until)->format('M j, Y') }}
                                            </div>
                                        @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">
                                            <i class="fas fa-tools w-3 h-3 mr-1"></i>
                                            Required
                                        </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Primary Action - Edit -->
                                        <a href="{{ route('tanks.edit', $tank->id) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-primary text-primary-foreground hover:bg-primary/90 rounded-md text-xs font-medium transition-colors">
                                            <i class="fas fa-edit w-3 h-3 mr-1.5"></i>
                                            Edit
                                        </a>

                                        <!-- FIFO Layers Button - More Prominent -->
                                        <a href="{{ route('tanks.layers', $tank->id) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white hover:bg-blue-700 rounded-md text-xs font-medium transition-colors shadow-sm"
                                            title="View FIFO Inventory Layers">
                                            <i class="fas fa-layer-group w-3 h-3 mr-1.5"></i>
                                            Layers
                                        </a>

                                        <!-- Calibration Button -->
                                        <a href="{{ route('tanks.calibration', $tank->id) }}"
                                            class="inline-flex items-center px-2 py-1.5 text-gray-600 hover:text-gray-800 hover:bg-gray-100 border border-gray-300 rounded-md text-xs transition-colors"
                                            title="Calibration Table">
                                            <i class="fas fa-ruler w-3 h-3"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3 sm:space-y-4">
            @foreach($tanks as $tank)
            <div class="bg-card border border-border rounded-lg p-3 sm:p-4 space-y-3 sm:space-y-4">
                <!-- Tank Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                        <div class="h-10 w-10 rounded-lg bg-cyan-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-bold text-cyan-700">{{ $tank->tank_number }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-semibold text-foreground truncate">Tank {{ $tank->tank_number }}</h3>
                            <p class="text-sm text-muted-foreground truncate">{{ $tank->product_name }}</p>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @if($tank->stock_status === 'CRITICAL') bg-red-100 text-red-700 border border-red-200
                            @elseif($tank->stock_status === 'LOW') bg-amber-100 text-amber-700 border border-amber-200
                            @elseif($tank->stock_status === 'HIGH') bg-blue-100 text-blue-700 border border-blue-200
                            @else bg-green-100 text-green-700 border border-green-200
                            @endif">
                            {{ ucfirst(strtolower($tank->stock_status)) }}
                        </span>
                    </div>
                </div>

                <!-- Stock Level -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-foreground">Current Stock</span>
                        <span class="text-sm text-muted-foreground">{{ $tank->fill_percentage }}% full</span>
                    </div>
                    <div class="w-full bg-muted rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-300
                            @if($tank->stock_status === 'CRITICAL') bg-red-500
                            @elseif($tank->stock_status === 'LOW') bg-amber-500
                            @elseif($tank->stock_status === 'HIGH') bg-blue-500
                            @else bg-green-500
                            @endif" style="width: {{ min(100, $tank->fill_percentage) }}%">
                        </div>
                    </div>
                    <div class="flex justify-between mt-1 text-xs text-muted-foreground">
                        <span>{{ number_format($tank->current_stock_liters, 0) }}L</span>
                        <span>{{ number_format($tank->capacity_liters, 0) }}L</span>
                    </div>
                </div>

                <!-- Additional Info Grid -->
                <div class="grid grid-cols-2 gap-4 py-2 border-t border-border">
                    <div>
                        <span class="text-xs text-muted-foreground block">Tank Type</span>
                        <span class="text-sm font-medium text-foreground">{{ ucfirst(strtolower(str_replace('_', ' ', $tank->tank_type))) }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-muted-foreground block">Product Code</span>
                        <span class="text-sm font-medium text-foreground">{{ $tank->product_code }}</span>
                    </div>
                </div>

                <!-- Calibration Status -->
                <div class="flex items-center justify-between py-2 border-t border-border">
                    <span class="text-sm text-muted-foreground">Calibration</span>
                    @if($tank->calibration_complete)
                        @if($tank->calibration_expired)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                            <i class="fas fa-exclamation-triangle w-3 h-3 mr-1"></i>
                            Expired
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                            <i class="fas fa-check w-3 h-3 mr-1"></i>
                            Valid
                        </span>
                        @endif
                    @else
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">
                        <i class="fas fa-tools w-3 h-3 mr-1"></i>
                        Required
                    </span>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex flex-col space-y-2 pt-2 border-t border-border">
                    <!-- Primary Actions Row -->
                    <div class="flex space-x-2">
                        <a href="{{ route('tanks.edit', $tank->id) }}"
                            class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-edit w-4 h-4 mr-2"></i>
                            Edit Tank
                        </a>

                        <!-- FIFO Layers Button - Prominent -->
                        <a href="{{ route('tanks.layers', $tank->id) }}"
                            class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-sm font-medium transition-colors shadow-sm"
                            title="View FIFO Inventory Layers">
                            <i class="fas fa-layer-group w-4 h-4 mr-2"></i>
                            FIFO Layers
                        </a>
                    </div>

                    <!-- Secondary Actions Row -->
                    <div class="flex justify-center">
                        <a href="{{ route('tanks.calibration', $tank->id) }}"
                            class="inline-flex items-center px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 border border-gray-300 rounded-lg text-sm transition-colors"
                            title="Calibration Table">
                            <i class="fas fa-ruler w-4 h-4 mr-2"></i>
                            Calibration Table
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Summary Footer -->
        <div class="mt-6 sm:mt-8 bg-card border border-border rounded-lg p-4 sm:p-6">
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-bold text-foreground">{{ $tanks->count() }}</div>
                    <div class="text-xs sm:text-sm text-muted-foreground">Total Tanks</div>
                </div>
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-bold text-green-600">{{ $tanks->where('is_active', 1)->count() }}</div>
                    <div class="text-xs sm:text-sm text-muted-foreground">Active Tanks</div>
                </div>
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-bold text-cyan-600">{{ number_format($tanks->sum('capacity_liters'), 0) }}L</div>
                    <div class="text-xs sm:text-sm text-muted-foreground">Total Capacity</div>
                </div>
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-bold text-blue-600">{{ number_format($tanks->sum('current_stock_liters'), 0) }}L</div>
                    <div class="text-xs sm:text-sm text-muted-foreground">Current Stock</div>
                </div>
            </div>
        </div>

        @else
        <!-- Empty State -->
        <div class="text-center py-12 sm:py-16">
            <div class="mx-auto flex h-12 w-12 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-muted/50">
                <i class="fas fa-oil-can text-xl sm:text-2xl text-muted-foreground"></i>
            </div>
            <h3 class="mt-4 sm:mt-6 text-base sm:text-lg font-semibold text-foreground">No Tanks Configured</h3>
            <p class="mt-2 text-sm text-muted-foreground max-w-md mx-auto px-4">
                This station doesn't have any fuel tanks configured yet. Add your first tank to get started.
            </p>
            @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) || (auth()->user()->can_approve_variances ?? false))
            <div class="mt-4 sm:mt-6">
                <a href="{{ route('tanks.create', $station->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-lg font-medium text-sm transition-colors">
                    <i class="fas fa-plus w-4 h-4 mr-2"></i>
                    Add First Tank
                </a>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
