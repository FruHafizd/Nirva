<div class="space-y-8 pb-12 font-['Fira_Sans']" 
    x-data="dashboardCharts(@js($chartData))" 
    x-init="initCharts()"
    wire:key="dashboard-stats-{{ $dateRange }}-{{ $startDate }}-{{ $endDate }}">
    
    <!-- Header & Filter Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight font-['Fira_Code']">Dasbor Analitik</h1>
            <p class="text-slate-500 mt-2 text-lg">Wawasan real-time untuk performa bisnis Anda.</p>
        </div>
        
        <div class="flex items-center gap-2 bg-white p-1.5 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex p-1 bg-slate-50 rounded-xl">
                @foreach(['today' => 'Hari Ini', 'this_week' => 'Minggu', 'this_month' => 'Bulan'] as $val => $label)
                    <button wire:click="$set('dateRange', '{{ $val }}')" 
                        class="px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200 {{ $dateRange === $val ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
                <select wire:model.live="dateRange" class="bg-transparent border-none focus:ring-0 text-sm font-bold text-slate-500 cursor-pointer pr-8">
                    <option value="today" hidden>Lainnya</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="last_month">Bulan Lalu</option>
                    <option value="this_year">Tahun Ini</option>
                    <option value="custom">Kustom</option>
                </select>
            </div>
            
            @if($dateRange === 'custom')
                <div class="flex items-center gap-2 px-3 border-l border-slate-200 animate-in fade-in slide-in-from-right-4">
                    <input type="date" wire:model.live="startDate" class="text-xs border-slate-200 rounded-lg py-1.5 focus:ring-blue-500 focus:border-blue-500">
                    <span class="text-slate-400 text-xs font-bold">KE</span>
                    <input type="date" wire:model.live="endDate" class="text-xs border-slate-200 rounded-lg py-1.5 focus:ring-blue-500 focus:border-blue-500">
                </div>
            @endif
        </div>
    </div>

    <!-- KPI Cards Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Sales Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-4 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-black uppercase tracking-widest {{ $stats['sales_change'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $stats['sales_change'] >= 0 ? 'Naik' : 'Turun' }}
                        </span>
                        <span class="text-sm font-bold {{ $stats['sales_change'] >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                            {{ $stats['sales_change'] >= 0 ? '+' : '' }}{{ abs(round($stats['sales_change'], 1)) }}%
                        </span>
                    </div>
                </div>
                <h3 class="text-slate-500 text-sm font-bold uppercase tracking-wider">Total Penjualan</h3>
                <p class="text-3xl font-black text-slate-900 mt-2 font-['Fira_Code']">
                    Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Transactions Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-4 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-black uppercase tracking-widest text-slate-400">Volume</span>
                        <span class="text-sm font-bold {{ $stats['count_change'] >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                            {{ $stats['count_change'] >= 0 ? '+' : '' }}{{ abs(round($stats['count_change'], 1)) }}%
                        </span>
                    </div>
                </div>
                <h3 class="text-slate-500 text-sm font-bold uppercase tracking-wider">Transaksi</h3>
                <p class="text-3xl font-black text-slate-900 mt-2 font-['Fira_Code']">
                    {{ number_format($stats['transaction_count'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Avg Value Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-4 rounded-2xl bg-amber-500 text-white shadow-lg shadow-amber-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2a2 2 0 002-2zm0 0h5a2 2 0 002-2v-3a2 2 0 00-2-2h-5m14 0h1v1a2 2 0 002 2h2a2 2 0 002-2v-1a2 2 0 00-2-2h-2a2 2 0 00-2 2v3a2 2 0 002 2zm0 0V9a2 2 0 00-2-2h-5" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-slate-500 text-sm font-bold uppercase tracking-wider">Rata-rata Penjualan</h3>
                <p class="text-3xl font-black text-slate-900 mt-2 font-['Fira_Code']">
                    Rp {{ number_format($stats['avg_value'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Profit Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-4 rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-slate-500 text-sm font-bold uppercase tracking-wider">Laba Kotor</h3>
                <p class="text-3xl font-black text-emerald-700 mt-2 font-['Fira_Code']">
                    Rp {{ number_format($stats['gross_profit'], 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 h-[450px]">
        <div class="lg:col-span-2 bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col">
            <div class="flex items-center justify-between mb-8 shrink-0">
                <div>
                    <h3 class="text-2xl font-black text-slate-900 font-['Fira_Code']">Tren Penjualan</h3>
                    <p class="text-slate-400 text-sm font-medium mt-1">Pergerakan pendapatan harian</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Pendapatan</span>
                </div>
            </div>
            <div class="flex-1 min-h-0 relative">
                <div id="salesTrendChart" wire:ignore class="absolute inset-0"></div>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col">
            <h3 class="text-2xl font-black text-slate-900 mb-2 font-['Fira_Code'] shrink-0">Kategori</h3>
            <p class="text-slate-400 text-sm font-medium mb-8 shrink-0">Distribusi penjualan</p>
            <div class="flex-1 min-h-0 relative">
                <div id="categoryChart" wire:ignore class="absolute inset-0"></div>
            </div>
        </div>
    </div>

    <!-- Inventory & Products Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Inventory Management -->
        <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-2xl font-black text-slate-900 font-['Fira_Code']">Status Stok</h3>
                <a href="/products" class="text-blue-600 font-bold text-xs uppercase tracking-widest hover:underline transition-all">Kelola Produk &rarr;</a>
            </div>
            
            <div class="grid grid-cols-3 gap-6 mb-10">
                <div class="group cursor-pointer p-6 rounded-3xl bg-slate-50 hover:bg-slate-100 transition-colors border border-transparent hover:border-slate-200">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Normal</p>
                    <p class="text-3xl font-black text-slate-900 font-['Fira_Code']">{{ $stats['inventory']['normal'] }}</p>
                </div>
                <div class="group cursor-pointer p-6 rounded-3xl bg-amber-50 hover:bg-amber-100 transition-colors border border-transparent hover:border-amber-200">
                    <p class="text-[10px] text-amber-500 font-black uppercase tracking-widest mb-2">Rendah</p>
                    <p class="text-3xl font-black text-amber-600 font-['Fira_Code']">{{ $stats['inventory']['low'] }}</p>
                </div>
                <div class="group cursor-pointer p-6 rounded-3xl bg-rose-50 hover:bg-rose-100 transition-colors border border-transparent hover:border-rose-200">
                    <p class="text-[10px] text-rose-500 font-black uppercase tracking-widest mb-2">Habis</p>
                    <p class="text-3xl font-black text-rose-600 font-['Fira_Code']">{{ $stats['inventory']['out_of_stock'] }}</p>
                </div>
            </div>

            <div class="relative overflow-hidden p-8 bg-slate-900 rounded-[2rem] text-white group cursor-pointer">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32 transition-transform group-hover:scale-125 duration-500"></div>
                <div class="relative z-10">
                    <p class="text-xs text-slate-400 font-black uppercase tracking-widest mb-2">Nilai Aset</p>
                    <div class="flex items-end justify-between">
                        <p class="text-4xl font-black font-['Fira_Code']">Rp {{ number_format($stats['inventory']['total_value'], 0, ',', '.') }}</p>
                        <div class="p-4 bg-white/10 rounded-2xl backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Sellers -->
        <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100 relative overflow-hidden">
            <h3 class="text-2xl font-black text-slate-900 mb-8 font-['Fira_Code']">Performa Terbaik</h3>
            <div class="space-y-4">
                @foreach($stats['top_sellers'] as $index => $product)
                <div class="flex items-center justify-between p-5 rounded-[1.5rem] border border-slate-50 hover:bg-slate-50 hover:border-slate-100 transition-all cursor-pointer group">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl {{ $index === 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-black text-lg transition-transform group-hover:scale-110">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="text-base font-black text-slate-900">{{ $product->product_name }}</p>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ $product->total_qty }} Unit terjual</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-black text-slate-900 font-['Fira_Code']">Rp {{ number_format($product->total_sales, 0, ',', '.') }}</p>
                        <div class="w-16 h-1 bg-slate-100 rounded-full mt-2 overflow-hidden">
                            <div class="h-full bg-blue-500 transition-all duration-1000" style="width: {{ $loop->first ? '100' : rand(40, 90) }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Slow Moving Section -->
    <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100 relative overflow-hidden">
        <h3 class="text-2xl font-black text-slate-900 mb-8 font-['Fira_Code']">Produk Pasif</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($stats['slow_moving'] as $product)
            <div class="flex items-center gap-4 p-4 rounded-2xl border border-slate-50 bg-slate-50/50">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 font-black text-xs">
                    {{ substr($product->name, 0, 1) }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-black text-slate-900 truncate">{{ $product->name }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[10px] font-black text-slate-400 uppercase">Stok: {{ $product->stock }}</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        <span class="text-[10px] font-black text-rose-500 uppercase">Pasif</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardCharts', (initialData) => ({
                salesChart: null,
                categoryChart: null,

                initCharts() {
                    this.destroyCharts();
                    this.renderCharts(initialData);
                },

                init() {
                    window.addEventListener('livewire:initialized', () => {
                        Livewire.on('stats-updated', (data) => {
                            this.updateCharts(data[0]);
                        });
                    });
                    
                    this.$cleanup(() => this.destroyCharts());
                },

                destroyCharts() {
                    if (this.salesChart) {
                        this.salesChart.destroy();
                        this.salesChart = null;
                    }
                    if (this.categoryChart) {
                        this.categoryChart.destroy();
                        this.categoryChart = null;
                    }
                    // Clear containers to be absolutely sure
                    document.querySelector("#salesTrendChart").innerHTML = '';
                    document.querySelector("#categoryChart").innerHTML = '';
                },

                renderCharts(data) {
                    const salesEl = document.querySelector("#salesTrendChart");
                    const catEl = document.querySelector("#categoryChart");

                    if (!salesEl || !catEl) return;

                    const commonOptions = {
                        chart: { 
                            fontFamily: "'Fira Sans', sans-serif", 
                            toolbar: { show: false },
                            animations: { enabled: true }
                        },
                        grid: { borderColor: '#F1F5F9', strokeDashArray: 4 },
                        dataLabels: { enabled: false }
                    };

                    this.salesChart = new ApexCharts(salesEl, {
                        ...commonOptions,
                        series: [{
                            name: 'Pendapatan',
                            data: data.sales_trend.map(item => item.total)
                        }],
                        chart: { ...commonOptions.chart, type: 'area', height: '100%', width: '100%' },
                        colors: ['#2563EB'],
                        stroke: { curve: 'smooth', width: 4 },
                        xaxis: {
                            categories: data.sales_trend.map(item => item.date),
                            labels: { rotate: -45, style: { fontSize: '10px', fontWeight: 700 } }
                        },
                        yaxis: { labels: { formatter: (val) => 'Rp ' + (val/1000).toLocaleString() + 'k' } }
                    });
                    this.salesChart.render();

                    this.categoryChart = new ApexCharts(catEl, {
                        ...commonOptions,
                        series: data.categories.map(item => parseFloat(item.total)),
                        labels: data.categories.map(item => item.name),
                        chart: { ...commonOptions.chart, type: 'donut', height: '100%', width: '100%' },
                        legend: { position: 'bottom', fontSize: '11px', fontWeight: 700 },
                        colors: ['#2563EB', '#4F46E5', '#F59E0B', '#10B981', '#EC4899', '#8B5CF6'],
                        plotOptions: { pie: { donut: { size: '75%', labels: { show: true, total: { show: true, label: 'TOTAL', formatter: (w) => 'Rp ' + (w.globals.seriesTotals.reduce((a, b) => a + b, 0) / 1000000).toFixed(1) + 'M' } } } } }
                    });
                    this.categoryChart.render();
                },

                updateCharts(data) {
                    if (this.salesChart) {
                        this.salesChart.updateOptions({
                            series: [{ data: data.sales_trend.map(item => item.total) }],
                            xaxis: { categories: data.sales_trend.map(item => item.date) }
                        }, false, true);
                    }
                    if (this.categoryChart) {
                        this.categoryChart.updateOptions({
                            series: data.categories.map(item => parseFloat(item.total)),
                            labels: data.categories.map(item => item.name)
                        }, false, true);
                    }
                }
            }));
        });
    </script>
    @endpush
</div>
