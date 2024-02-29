<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Info PdM | Executive Summary</title>

    {{-- CSS --}} 
    <link rel="stylesheet" href="{{ public_path('css/app.css')}}"> 
    <style>
        body {
            font-family: Helvetica;
        }
    </style>
    
</head>
<body>
    <div class="card-body bg-white">
        <div class="row m-2">
            {{-- img --}}
            <div class="align-item-center">
                <table class="table mb-0">
                    <tbody class="m-0">
                        <tr class="align-middle mb-0">
                            {{-- Gambar --}}
                            <td class="mb-0">  
                                @php
                                    $imgHeader = $path.'/'.$unitPembangkit->image;  
                                @endphp  
                                @if ($unitPembangkit->image!= null)
                                    @if (file_exists($imgHeader))   
                                        <img src="{{ $path.'/'.$unitPembangkit->image  }}" alt="Logo PLN" height="60px" width="auto">
                                    @else 
                                        <img src="{{public_path('assets/img/pln-uikl.png') }}" alt="Logo PLN" height="60px" width="auto">
                                    @endif 
                                @else
                                    <img src="{{public_path('assets/img/pln-uikl.png') }}" alt="Logo PLN" height="60px" width="auto"> 
                                @endif 
                            </td>
                            {{-- Executive Summary --}}
                            <td class="text-right">
                                <h5 class="m-0">
                                    <b><i>Executive Summary</i> PdM </b>
                                </h5>
                                {{$bulan}} {{$tahun}}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-body mt-0">
            <div class="row m-0">
                <p class="break text-justify text-serif">
                    Berikut ini adalah <i>executive summary</i> dari hasil pengukuran kondisi peralatan di {{$unitPembangkit->name}} oleh tim pemeliharaan prediktif (PdM) {{$unitPembangkit->name}} pada Bulan <b> {{$bulan ?? ''}} </b> Tahun <b> {{$tahun ?? ''}}</b>.
                </p>
            </div>
            <div class="row m-3">
                {{-- Vibrasi --}}
                <b>1. Pengukuran Vibrasi</b> 
                <div class="w-100 pl-3">
                    <p class="break text-justify mb-0">
                        Pengukuran vibrasi yang dilakukan oleh tim PdM menggunakan <a href="https://www.emerson.com/en-us/catalog/ams-a2140" target="blank()" class="text-primary font-italic">CSI-2140</a> dengan standar <a href="https://www.iso.org/obp/ui/#iso:std:50528:en" target="blank()" class="text-primary">ISO 18016-3</a>.
                    </p>
                    @if ($dataVibrasi) 
                        <ol type="a">
                            @foreach ($dataVibrasi as $vib)
                           
                                <li class="text-left"><b>{{$vib['unit_name']}}</b></li> 
                                @if ($vib['total'] != 0)  
                                {{-- Nama unit --}}
                                
                                {{-- Pengecekan apakah terdapat data vibrasi --}} 
                                {{-- Pengecekan apakah terdapat kondisi abnormal peralatan --}}
                                {{-- untuk index kedua merupakan zona, mulai dari A s.d D, dimulai dari 0 karena berupa array --}} 
                                Total peralatan yang diukur: <strong> {{ $vib['total']}} peralatan</strong>, terdapat beberapa peralatan dengan status tidak normal 
                                <strong class="text-danger">
                                    ({{ $vib['zone']['C']['total'] ?? ''}} peralatan pada zona C
                                    dan {{ $vib['zone']['D']['total'] ?? '0'}} peralatan pada zona D)
                                </strong> 
                                <ol type="I"> 
                                    @if ($vib['zone']['C']['total'] != 0) 
                                        <li>Zona C </li>
                                        <ol type="1"> 
                                            @foreach($vib['zone']['C']['data'] as $data) 
                                                <li>{{$data['equipment_name']}}</li>
                                                <ul>
                                                    <li>Vibrasi Tertinggi sebesar: <b class="badge badge-warning" >{{$data['peak']}} mm/s</b></li>
                                                    <li>Sisi: <b> {{$data['point']}}</b></li>
                                                    <li>Analisis: {{$data['analisis']}}</li>
                                                    <li>Rekomendasi: {{$data['rekomendasi']}}</li>
                                                    <li>Lampiran:
                                                        @if ($data['attachment'] === null)
                                                        -
                                                        @else
                                                            @php
                                                                $fileImg = $path.'/'.$data['attachment'];
                                                                $imageVibrasiC = \App\Helper::isImage($data['attachment']);  
                                                            @endphp  
                                                            @if ($imageVibrasiC == true) 
                                                            <div class="col-12 justify-content-left">
                                                                @if (file_exists($fileImg))  
                                                                    <img src="{{$fileImg}}" alt="lampiran" height="300vh" width="auto" class="img-fluid float-left">  
                                                                @endif 
                                                            </div> 
                                                            @else
                                                            -
                                                            @endif 
                                                        @endif
                                                    </li>  
                                                </ul>
                                            @endforeach
                                        </ol> 
                                    @endif
                                    @if ($vib['zone']['D']['total'] != 0)
                                        <li>Zona D </li>
                                        <ol type="1"> 
                                            @foreach($vib['zone']['D']['data'] as $data) 
                                                <li>{{$data['equipment_name']}}</li>
                                                <ul>
                                                    <li>Vibrasi Tertinggi sebesar: <b class="badge badge-danger" >{{$data['peak']}} mm/s</b></li>
                                                    <li>Sisi: <b> {{$data['point']}}</b></li>
                                                    <li>Analisis: {{$data['analisis']}}</li>
                                                    <li>Rekomendasi: {{$data['rekomendasi']}}</li>
                                                    <li>Lampiran:
                                                        @if ($data['attachment'] === null)
                                                        -
                                                        @else
                                                            @php
                                                                $fileImg = $path.'/'.$data['attachment'];
                                                                $imageVibrasiD = \App\Helper::isImage($data['attachment']);  
                                                            @endphp  
                                                            @if ($imageVibrasiD == true) 
                                                            <div class="col-12 justify-content-left">
                                                                @if (file_exists($fileImg))  
                                                                    <img src="{{$fileImg}}" alt="lampiran" height="300vh" width="auto" class="img-fluid float-left">  
                                                                @endif 
                                                            </div> 
                                                            @else
                                                            -
                                                            @endif 
                                                        @endif
                                                    </li>  
                                                </ul>
                                            @endforeach
                                        </ol> 
                                    @endif 
                                </ol>   
                                @else
                                    Tidak ada data pengukuran pada Bulan {{$bulan}} {{$tahun}}. Kemungkinan unit sedang <em>outage</em>.
                                @endif  
                            @endforeach
                        </ol>
                    @else 
                        Belum ada data pengukuran vibrasi di database
                    @endif
                </div>
                
                {{-- Termografi --}}
                <b>2. Termografi</b>
                <div class="w-100 pl-3">
                    <p class="break text-justify mb-0">
                        {{-- Belum ada data pengukuran termografi di database --}}
                        Pengukuran termografi yang dilakukan oleh tim PdM menggunakan <a href="https://www.fluke.com/id-id/produk/kamera-termal/ti400" target="blank" rel="noopener noreferrer" class="text-primary font-italic">Fluke Ti-400</a>.
                    </p>
                    @if ($dataTermografi)
                        <ol type="a">
                            @foreach ($dataTermografi as $dataTermo) 
                                {{-- Nama unit --}}
                                <li class="text-left"><b>{{$dataTermo['unit_name']}}</b></li>
                                {{-- Pengecekan apakah terdapat data termografi --}}
                                @if ($dataTermo['total']!=0)  
                                    Total Peralatan yang diukur: <strong> {{ $dataTermo['total'] }} peralatan</strong>, terdapat
                                    <strong class="text-danger">
                                        {{ $dataTermo['detail']['value'] ?? '' }} peralatan dengan status abnormal
                                    </strong>
                                    <ol>
                                        @foreach ($dataTermo['detail']['data'] as $termo) 
                                            <li>{{ $termo['equipment_name'] }} - Status: {{ $termo['status'] }}</li>
                                            <ul>
                                                <li>
                                                    Pengukuran Suhu tertinggi sebesar:
                                                    <b>{{ $termo['peak'] }}</b>
                                                </li>
                                                <li>Sisi: <b> {{ $termo['point'] }} </b></li>
                                                <li>Analisis: {{ $termo['analisis'] ?? '-'}}</li>
                                                <li>Rekomendasi: {{ $termo['rekomendasi'] ?? '-' }}</li>
                                                <li>Lampiran:  
                                                    
                                                    @if ($termo['attachment'] == null)
                                                        -
                                                    @else
                                                        @php
                                                            $fileImg = $path.'/'.$data['attachment'];
                                                            $imageTremo = \App\Helper::isImage($data['attachment']);  
                                                        @endphp  
                                                        @if ($imageTremo == true) 
                                                        <div class="col-12 justify-content-left">
                                                            @if (file_exists($fileImg))  
                                                                <img src="{{$fileImg}}" alt="lampiran" height="300vh" width="auto" class="img-fluid float-left">  
                                                            @endif 
                                                        </div> 
                                                        @else
                                                        -
                                                        @endif
                                                    @endif
                                                </li>
                                            </ul> 
                                        @endforeach
                                    </ol> 
                                @else  
                                    Tidak ada data pengukuran pada Bulan {{$bulan}} {{$tahun}} / Kemungkinan unit sedang <em>outage</em>. 
                                @endif
                            @endforeach
                        </ol>
                    @else
                        Belum ada data pengukuran termografi di database
                    @endif
                </div>

                {{-- Tribologi --}}
                <b>3. Tribologi</b>
                <div class="w-100 pl-3">
                    <p class="break text-justify mb-0">
                        {{-- Belum ada data pengukuran termografi di database --}}
                        Pengukuran termografi yang dilakukan oleh tim PdM menggunakan <a href="https://www.fluke.com/id-id/produk/kamera-termal/ti400" target="blank" rel="noopener noreferrer" class="text-primary font-italic">Fluke Ti-400</a>.
                    </p>
                    @if ($dataTribologi)
                        <ol type="a">
                            @foreach ($dataTribologi as $data) 
                                {{-- Nama unit --}}
                                <li class="text-left"><b>{{$data['unit_name']}}</b></li>
                                {{-- Pengecekan apakah terdapat data termografi --}}
                                @if ($data['total']!=0)  
                                    Total Peralatan yang diukur: <strong> {{ $data['total'] }} peralatan</strong> 
                                    {{-- <strong class="text-danger">
                                        {{ $data['detail']['value'] ?? '' }} peralatan dengan status abnormal
                                    </strong> --}}
                                    <ol>
                                        @foreach ($data['detail']['data'] as $tribologi) 
                                            @php
                                                $detail = collect(json_decode($tribologi['data_detail'] ))
                                            @endphp
                                            <li>{{ $tribologi['equipment_name'] }} - Status: <b class="badge badge-{{$tribologi['status']==='abnormal'? 'danger' :'success'}}" >{{ $tribologi['status'] }}</b></li>
                                            <ul>
                                                {{-- @foreach ($detail as $item) --}}
                                                <table class="table table-sm small table-bordered mt-0"> 
                                                    <tbody> 
                                                        {{-- @foreach ($tribologi_config as $key => $configtrib)
                                                        <tr> 
                                                            @php
                                                                $ckey = $configtrib->key;
                                                                $ckey_note = $configtrib->key.'_note';
                                                                $ckey_attachment = $configtrib->key.'_attachment';
                                                            @endphp
                                                            <td>{{$configtrib->name}}</td>    
                                                            @foreach ($detail as $d => $det)  
                                                                @endphp 
                                                                @if ($configtrib->no ==  $no)
                                                                    <td>
                                                                        @if(isset($det->$ckey))
                                                                            {{ $det->$ckey != '' ? $det->$ckey : '-'}}
                                                                        @else 
                                                                        -
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if(isset($det->$ckey_note)) 
                                                                        <{{ $det->$ckey_note != ''  ? $det->$ckey_note: '-'}}
                                                                        @else 
                                                                         -
                                                                        @endif 
                                                                    </td>
                                                                    <td>
                                                                        @if(isset($det->$ckey_attachment)) 
                                                                            @if ($det->$ckey_attachment == Null) 
                                                                            -
                                                                            @else
                                                                                <img src="http://103.163.139.182:8060/{{ $det->$ckey_attachment  }}" alt="lampiran" height="300vh" width="auto" class="img-fluid float-left"> 
                                                                                 
                                                                            @endif 
                                                                        @else 
                                                                        -
                                                                        @endif 
                                                                    </td>
                                                                @endif  
                                                            @endforeach  
                                                        </tr> 
                                                        @endforeach  --}}  
                                                            @for ($i = 0; $i <count($detail); $i++)
                                                                <tr>
                                                                @foreach ($detail[$i] as $key =>$value)
                                                                <td> 
                                                                   {{ $key != "" ? $key : "-"}}
                                                                </td>  
                                                                <td> 
                                                                    @php 
                                                                    $string = $key;
                                                                        $afterunderscore = substr($string, strpos($string, "_") + 1);
                                                                        $contains = Str::contains($afterunderscore, 'attachment'); 
                                                                    @endphp
                                                                    @if ($contains == true)
                                                                        @php
                                                                            $file = $path.'/'.$value;
                                                                            $image = \App\Helper::isImage($value);  
                                                                        @endphp  
                                                                        @if ($image == true) 
                                                                            @if (file_exists($file)) 
                                                                                <img src="{{ $file }}" alt="lampiran" height="300vh" width="auto" class="img-fluid float-left">  
                                                                            @endif 
                                                                        @else 
                                                                        -
                                                                        @endif  
                                                                    @else
                                                                        {{ $value != "" ? $value : "-" }} 
                                                                    @endif
                                                                 </td>  
                                                                @endforeach
                                                                </tr>
                                                            @endfor   
                                                            
                                                    </tbody>
                                                </table> 
                                            </ul> 
                                        @endforeach
                                    </ol> 
                                @else  
                                    Tidak ada data pengukuran pada Bulan {{$bulan}} {{$tahun}} / Kemungkinan unit sedang <em>outage</em>. 
                                @endif
                            @endforeach
                        </ol>
                    @else
                        Belum ada data pengukuran termografi di database
                    @endif
                </div>
            </div>
        </div>  
    </div>


</body>
</html>