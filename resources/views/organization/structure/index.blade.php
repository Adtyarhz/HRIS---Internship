@extends('layouts.admin')

@section('title', 'Struktur Organisasi')
@section('header_icon', 'icon-park-outline--branch-one')
@section('content_header', 'Struktur Organisasi')

@push('styles')
    <style>
        .google-visualization-orgchart-node {
            border: 2px solid #337ab7 !important;
            border-radius: 8px !important;
            background-color: #f0f8ff;
            padding: 8px !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            cursor: pointer;
        }
        .google-visualization-orgchart-node:hover {
            background-color: #e6f0fa !important;
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bagan Struktur Organisasi Perusahaan</h3>
            <div class="card-tools">
                <a href="{{ route('organization.structure.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Jabatan
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(!empty($chartData))
                <div id="chart_div" style="width: 100%; overflow-x: auto;"></div>
            @else
                <div class="text-center p-4">
                    <p>Belum ada data jabatan untuk ditampilkan. Silakan tambahkan jabatan pertama.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {packages: ['orgchart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Manager');
            data.addColumn('string', 'ToolTip');

            var chartData = {!! json_encode($chartData) !!};
            var formattedRows = chartData.nodes.map(function(row) {
                return [row[0], row[1], row[2]];
            });

            data.addRows(formattedRows);

            var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));

            google.visualization.events.addListener(chart, 'select', function() {
                var selection = chart.getSelection();
                if (selection.length > 0) {
                    var nodeId = data.getRowProperties(selection[0].row).v;
                    var urlTemplate = "{{ route('organization.structure.show', ['position' => ':id']) }}";
                    var redirectUrl = urlTemplate.replace(':id', nodeId);
                    window.location.href = redirectUrl;
                }
            });

            chart.draw(data, {
                allowHtml: true,
                allowCollapse: true,
                nodeClass: 'google-visualization-orgchart-node'
            });

            // Tambahkan logika untuk menampilkan pengawasan tidak langsung
            if (chartData.indirectLinks) {
                chartData.indirectLinks.forEach(function(link) {
                    console.log('Pengawasan tidak langsung dari ' + link.from + ' ke ' + link.to);
                    // Di sini bisa ditambahkan anotasi visual seperti garis putus-putus (memerlukan custom library)
                });
            }
        }
    </script>
@endpush