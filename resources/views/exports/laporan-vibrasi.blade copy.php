<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="{{public_path('css/app.css')}}">
    {{-- Favicon --}}
    @if($unitPembangkit->image != null)
        <link rel="icon" type="image/png" sizes="96x96" href="http://103.163.139.182:8060/{{$unitPembangkit->image}}">
    @else 
        <link rel="icon" type="image/png" sizes="96x96" href="{{public_path('assets/img/favicon.png')}}"> 
    @endif   
     
     <style>
         .pie-chart {
             width: 900px;
             height: 500px;
             margin: 0 auto;
         }
     </style>
     {{-- make sure you are using http, and not https --}}
     {{-- <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> --}}
     <script type="text/javascript" src="http://www.gstatic.com/charts/loader.js"></script>
 
     <script type="text/javascript">
        function init() {
            google.load("visualization", "44", {
            packages: ["corechart"]
            });
            {
            var interval = setInterval(function () {
                if (google.visualization !== undefined && google.visualization.DataTable !== undefined
                && google.visualization.ColumnChart !== undefined) {
                clearInterval(interval);
                drawCharts();
                window.status = 'ready';
                }
            }, 100);
            }
        }
         function drawCharts() {
             var data = google.visualization.arrayToDataTable([
                ['Zona A', zone[0]],
                ['Zona B', zone[1]],
                ['Zona C', zone[2]],
                ['Zona D', zone[3]],
             ]);
             var options = {
                 title: 'My Daily Activities',
             };
             var chart = new google.visualization.PieChart(document.getElementById('piechart'));
             chart.draw(data, options);
         }
     </script>
</head>
<body> 
{{-- <body onload="init()">   --}}
<section class="content bg-white">
    <div class="row mb-2">
        {{-- Picture --}}
        <div class="col"> 
            <img src="{{$unitPembangkit->image != null ? 'http://103.163.139.182:8060/'.$unitPembangkit->image : public_path('assets/img/pln-uikl.png') }}" alt="Logo PLN" height="60px" width="auto"> 
        </div>
    </div>
    {{-- Title --}}
    <div class="row mb-2 justify-content-center">
        <div class="col">
            <h4 class="text-center m-0">
                <b>
                    Data Vibrasi {{$unitItem->name}}
                </b> 
            </h4>
            <h5 class="text-center mb-0">
                {{$bulan}} {{$tahun}}
            </h5>
        </div>
    </div> 
    <table class="table table-sm small table-bordered mt-0">
        <thead class="text-center bg-secondary">
            <tr>
                <th>No.</th>
                <th>Equipmet</th>
                @php
                    $config_vibrasi = collect(json_decode($unitPembangkit->vibrasi_config_detail));
                @endphp
                @foreach ($config_vibrasi as $conv)
                    <th>{{$conv->no}}</th>
                @endforeach 
                <th>Zona</th>
                <th>Analisis</th>
                <th>Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
                if($non_rutin_vibrasi == null){
                    $vibrasi = $laporan;
                }else {
                    $vibrasi = $laporan->vibrasi;
                }
            @endphp
            @if (count($vibrasi) != 0) 
            @foreach ($vibrasi as $vib)
            <tr>
                <td class="text-center">{{$i++}}</td>
                <td>{{$vib->equipment}}</td> 
                @php
                    $data_detail = collect(json_decode($vib->data_detail));
                @endphp
                @foreach ($data_detail as $detail)
                    <th>{{$detail->value}}</th>
                @endforeach 
                @if ($vib->zone == 'A')
                    <td class="bg-success text-center">
                        {{$vib->zone}}
                    </td>
                @elseif ($vib->zone == 'B')
                    <td class="bg-info text-center">
                        {{$vib->zone}}
                    </td>
                @elseif ($vib->zone == 'C')
                    <td class="bg-warning text-center">
                        {{$vib->zone}}
                    </td>
                @elseif ($vib->zone == 'D')
                    <td class="bg-danger text-center">
                        {{$vib->zone}}
                    </td>
                @else
                    <td class="text-center">
                        -
                    </td>
                @endif
                <td>{{$vib->analisis}}</td>
                <td>{{$vib->rekomendasi}}</td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan = "100%" class="text-center">Tidak Ada Data</td>
            </tr>
            @endif
        </tbody> 
    </table>
    <table class="table">
        <tbody>
            <tr>
                {{-- Tabel Keterangan --}}
                <th class="align-top">
                    {{-- tabel Keterangan --}}
                    <table class="table table-sm text-center table-bordered">
                        <thead class="small bg-secondary">
                            <tr>
                                <th>Kondisi</th>
                                <th>Deskripsi</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="small text-center"> 
                            <tr class="bg-success">
                                <td>Zona A</td>
                                <td>Peralatan dalam kondisi sangat baik</td>
                                <td>{{$zone[0] ?? '-'}}</td>
                            </tr>
                            <tr class="bg-info">
                                <td>Zona B</td>
                                <td>Diizinkan beroperasi jangka panjang tanpa batasan</td>
                                <td>{{$zone[1] ?? '-'}}</td>
                            </tr>
                            <tr class="bg-warning">
                                <td>Zona C</td>
                                <td>Diizinkan beroperasi jangka pendek</td>
                                <td>{{$zone[2] ?? '-'}}</td>
                            </tr>
                            <tr class="bg-danger">
                                <td>Zona D</td>
                                <td>Berbahaya untuk dioperasikan</td>
                                <td>{{$zone[3] ?? '-'}}</td>
                            </tr> 
                        </tbody>
                    </table>
                </th>

                {{-- Chart --}}
                {{-- <th> --}}
                    {{-- Pie Chart --}}
                    {{-- <div id="piechart" class="pie-chart"></div> --}}
                    <div id="piechart"  class="pie-chart"> </div>
                {{-- </th> --}}
                {{-- Tabel Titik --}}

                <th>
                    {{-- Tabel Keterangann Titik --}}
                    <table class="table table-sm table-bordered">
                        <thead class="text-center small bg-secondary">
                            <tr>
                                <th>Titik</th>
                                <th>Keterangan</th> 
                            </tr>
                        </thead>
                        <tbody class="small text-center"> 
                            @foreach ($config_vibrasi as $conv)
                                <tr>
                                    <td>{{ $conv->no }}</td>
                                    <td>{{ $conv->name }}</td> 
                                </tr> 
                            @endforeach  
                        </tbody>
                    </table>
                </th>
            </tr>
        </tbody> 
    </table>
    @if ($non_rutin_vibrasi == 0)
    <div class="row mt-2">
        <span class="small ml-3">Dokumen ini di-print oleh: {{auth()->user()->name}}, NIP: {{auth()->user()->nip}}  pada {{$date}} WITA</span>
    </div>
    @endif
</section> 
@if ($non_rutin_vibrasi != 0)
<section class="content bg-white"> 
    {{-- Title --}}
    <div class="row mb-2 justify-content-center">
        <div class="col">
            <h4 class="text-center m-0">
                <b>
                    Data Non Rutin Vibrasi {{$unitItem->name}}
                </b> 
            </h4>
            <h5 class="text-center mb-0">
                {{$bulan}} {{$tahun}}
            </h5>
        </div>
    </div>
        <table class="table table-sm small table-bordered mt-0">
            <thead class="text-center bg-secondary">
                <tr>
                    <th>No.</th>
                    <th>Equipmet</th> 
                    @foreach ($config_vibrasi as $conv)
                        <th>{{$conv->no}}</th>
                    @endforeach 
                    <th>Waktu</th>
                    <th>Zona</th>
                    <th>Analisis</th>
                    <th>Rekomendasi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $i = 1; 
                @endphp
                @if (count($laporan->non_rutin_vibrasi) != 0) 
                @foreach ($laporan->non_rutin_vibrasi as $vib)
                <tr>
                    <td class="text-center">{{$i++}}</td>
                    <td>{{$vib->equipment}}</td> 
                    @php
                        $data_detail = collect(json_decode($vib->data_detail));
                    @endphp
                    @foreach ($data_detail as $detail)
                        <th>{{$detail->value}}</th>
                    @endforeach 
                    <th>{{$vib->time->format('h:i')}}</th> 
                    @if ($vib->zone == 'A')
                        <td class="bg-success text-center">
                            {{$vib->zone}}
                        </td>
                    @elseif ($vib->zone == 'B')
                        <td class="bg-info text-center">
                            {{$vib->zone}}
                        </td>
                    @elseif ($vib->zone == 'C')
                        <td class="bg-warning text-center">
                            {{$vib->zone}}
                        </td>
                    @elseif ($vib->zone == 'D')
                        <td class="bg-danger text-center">
                            {{$vib->zone}}
                        </td>
                    @else
                        <td class="text-center">
                            -
                        </td>
                    @endif
                    <td>{{$vib->analisis}}</td>
                    <td>{{$vib->rekomendasi}}</td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan = "100%" class="text-center">Tidak Ada Data</td>
                </tr>
                @endif
            </tbody> 
        </table>
        @if ($non_rutin_vibrasi != 0)
        <div class="row mt-2">
            <span class="small ml-3">Dokumen ini di-print oleh: {{auth()->user()->name}}, NIP: {{auth()->user()->nip}}  pada {{$date}} WITA</span>
        </div>
        @endif
    </section>
@endif 
</body>
</html>