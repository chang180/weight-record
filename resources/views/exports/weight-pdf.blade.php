<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weight Record Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #4F46E5;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        
        .header p {
            color: #666;
            margin: 5px 0;
        }
        
        .summary {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .summary h3 {
            color: #4F46E5;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-item .label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #4F46E5;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th {
            background-color: #4F46E5;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }
        
        .table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .table tr:hover {
            background-color: #f3f4f6;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Weight Record Report</h1>
        <p>User: {{ $user->name }}</p>
        <p>Generated: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    @if($weights->count() > 0)
        <div class="summary">
            <h3>Data Summary</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="label">Total Records</div>
                    <div class="value">{{ $weights->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="label">Latest Weight</div>
                    <div class="value">{{ $weights->first()->weight }} kg</div>
                </div>
                <div class="summary-item">
                    <div class="label">Weight Change</div>
                    <div class="value">
                        @if($weights->count() > 1)
                            @php
                                $latest = $weights->first()->weight;
                                $oldest = $weights->last()->weight;
                                $change = $latest - $oldest;
                            @endphp
                            @if($change > 0)
                                +{{ $change }} kg
                            @elseif($change < 0)
                                {{ $change }} kg
                            @else
                                No change
                            @endif
                        @else
                            No change
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Weight (kg)</th>
                    <th>Note</th>
                    <th>Recorded At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($weights as $weight)
                    <tr>
                        <td>{{ $weight->record_at->format('Y-m-d') }}</td>
                        <td>{{ $weight->weight }}</td>
                        <td>{{ $weight->note ?? '-' }}</td>
                        <td>{{ $weight->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <h3>No Weight Records</h3>
            <p>No weight data has been recorded yet</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was automatically generated by Weight Record System | {{ now()->format('Y-m-d') }}</p>
    </div>
</body>
</html>
