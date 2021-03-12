<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('統計圖') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- {{ dd($weights) }} --}}
                    @if (count($weights) != 0)
                        <canvas id="myChart" height="280" width="600">123</canvas>
                    @else
                        <h1>目前還沒有記錄</h1>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<script>
    // 前端靠自己，拿到資料再說
    var weights = {!! json_encode($weights) !!}
    // console.log(weights)
    let label = []
    let data = []
    weights.forEach(element => {
        label.push(element.record_at)
        data.push(element.weight)
    });
    // console.log(label,data)
    var ctx = document.getElementById('myChart');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: label,
            datasets: [{
                label: '體重記錄',
                data: data,
                backgroundColor: 'darkblue',
                borderColor: 'green',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: false
                    }
                }]
            }
        }
    });

</script>
